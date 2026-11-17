<?php

namespace App\Domains\Assistant\Services;

use App\Domains\Brand\Models\Brand;
use App\Domains\Budget\Models\Budget;
use App\Domains\Budget\Services\BudgetService;
use App\Domains\Home\Services\HomeService;
use App\Domains\Transaction\Models\Transaction;
use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * The read-only lookups behind "Ask Claude" on the kiosk. Each one works out exact figures
 * (comparisons included), so Claude never has to do arithmetic.
 */
class LookupService
{
    // The kinds of money Claude can ask about, by the word it uses
    public const TYPES = [
        'expenses' => Category::EXPENSES,
        'income' => Category::INCOME,
        'savings' => Category::SAVINGS,
        'investment' => Category::INVESTMENT,
    ];

    public function __construct(
        private readonly HomeService $homeService,
        private readonly BudgetService $budgetService
    ) {}

    /**
     * The total from $from to $to (both days in full) and the span before it. This month also gets
     * the running totals the kiosk's month chart draws, against the whole of last month.
     *
     * @return array{total: float, previous: float, comparison: ?array{difference: float, direction: string, label: string}, daily: array<int, float>, previous_daily: array<int, float>}
     */
    public function total(Carbon $from, Carbon $to, string $type, ?Category $category = null, ?Brand $brand = null): array
    {
        [$from, $to, $previousFrom, $previousTo, $label] = $this->withSpanBefore($from, $to);
        $transactions = fn () => $this->transactions($type, $category, $brand);
        $chart = $from->isSameDay(now()->startOfMonth());
        $total = round((float) $transactions()->whereBetween('created_at', [$from, $to])->sum('amount'), 2);
        $previous = round((float) $transactions()->whereBetween('created_at', [$previousFrom, $previousTo])->sum('amount'), 2);

        return [
            'total' => $total,
            'previous' => $previous,
            'comparison' => $this->comparison($total, $previous, $label),
            'daily' => $chart ? $this->homeService->getCumulativeDailyExpenses($from, $to, $transactions()) : [],
            'previous_daily' => $chart
                ? $this->homeService->getCumulativeDailyExpenses($previousFrom->copy()->startOfMonth(), $previousFrom->copy()->endOfMonth(), $transactions())
                : [],
        ];
    }

    /**
     * How a total compares with the one before ("320 less than in October"), or null with nothing to compare with.
     *
     * @return array{difference: float, direction: string, label: string}|null
     */
    public function comparison(float $total, float $previous, string $label): ?array
    {
        if ($previous <= 0) {
            return null;
        }

        $difference = round(abs($total - $previous));

        return [
            'difference' => $difference,
            'direction' => $difference < 1 ? 'same' : ($total < $previous ? 'less' : 'more'),
            'label' => $label,
        ];
    }

    /**
     * Totals per category or per brand from $from to $to, biggest first.
     *
     * @return Collection<int, array{name: string, total: float, count: int, color: ?string, icon: ?string}>
     */
    public function breakdown(Carbon $from, Carbon $to, string $by, string $type, ?Category $category, int $limit): Collection
    {
        $group = $by === 'brand' ? 'brands' : 'categories';

        return Transaction::query()
            ->join('brands', 'brands.id', '=', 'transactions.brand_id')
            ->join('categories', 'categories.id', '=', 'brands.category_id')
            ->whereBetween('transactions.created_at', [$from, $to])
            ->when(
                $category,
                fn ($query) => $query->where('categories.id', $category->id),
                fn ($query) => $query->where('categories.type', self::TYPES[$type])
            )
            ->groupBy("$group.id", "$group.name", 'categories.color', 'categories.icon')
            ->selectRaw("$group.name as name, categories.color, categories.icon, SUM(transactions.amount) as total, COUNT(*) as count")
            ->orderByDesc('total')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => [
                'name' => $row->name,
                'total' => round((float) $row->total, 2),
                'count' => (int) $row->count,
                'color' => $row->color,
                'icon' => $row->icon,
            ]);
    }

    /**
     * The latest or largest transactions from $from to $to, with how many there were in all.
     *
     * @return array{count: int, total: float, rows: Collection<int, array{id: int, brand: ?string, amount: float, date: string, color: ?string, icon: ?string}>}
     */
    public function transactionList(Carbon $from, Carbon $to, string $type, ?Category $category, ?Brand $brand, string $sort, int $limit): array
    {
        $query = $this->transactions($type, $category, $brand)->whereBetween('created_at', [$from, $to]);

        $rows = (clone $query)
            ->with('brand.category')
            ->orderByDesc($sort === 'largest' ? 'amount' : 'created_at')
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->map(fn (Transaction $transaction) => [
                'id' => $transaction->id,
                'brand' => $transaction->brand?->name,
                'amount' => (float) $transaction->amount,
                'date' => $transaction->created_at->format('Y-m-d'),
                'color' => $transaction->brand?->category?->color,
                'icon' => $transaction->brand?->category?->icon,
            ]);

        return [
            'count' => (clone $query)->count(),
            'total' => round((float) (clone $query)->sum('amount'), 2),
            'rows' => $rows,
        ];
    }

    /**
     * Current budgets, or the ones whose name matches.
     *
     * @return EloquentCollection<int, Budget>
     */
    public function budgets(?string $name = null): EloquentCollection
    {
        $budgets = $this->budgetService->getCurrent();
        $needle = Str::lower(trim((string) $name));

        if ($needle === '') {
            return $budgets;
        }

        return $budgets
            ->filter(fn (Budget $budget) => str_contains(Str::lower($budget->name), $needle) || str_contains($needle, Str::lower($budget->name)))
            ->values();
    }

    /**
     * The category meant by $name, or the closest names when it isn't clear.
     *
     * @return array{0: ?Category, 1: array<int, string>}
     */
    public function findCategory(string $name): array
    {
        return $this->closest($name, Category::query()->get());
    }

    /**
     * The brand meant by $name, or the closest names when it isn't clear. A brand name or alias
     * inside what was said wins, as it does for an SMS merchant ("Carrefour Mall of the Emirates").
     *
     * @return array{0: ?Brand, 1: array<int, string>}
     */
    public function findBrand(string $name): array
    {
        if ($brand = Brand::findForSms($name)) {
            return [$brand, []];
        }

        return $this->closest($name, Brand::query()->has('transactions')->get());
    }

    /**
     * Category names by type, for Claude to pass back exactly.
     *
     * @return array<string, array<int, string>>
     */
    public function categoryNames(): array
    {
        $byType = Category::query()->orderBy('name')->get(['name', 'type'])->groupBy('type');

        return collect(self::TYPES)
            ->map(fn (string $type) => $byType->get($type, collect())->pluck('name')->all())
            ->filter()
            ->all();
    }

    /**
     * Transactions of one kind; a category or brand brings its own kind with it.
     */
    private function transactions(string $type, ?Category $category, ?Brand $brand): Builder
    {
        return Transaction::query()
            ->when($brand, fn ($query) => $query->where('brand_id', $brand->id))
            ->when($category, fn ($query) => $query->whereHas('brand', fn ($query) => $query->where('category_id', $category->id)))
            ->when(! $category && ! $brand, fn ($query) => $query->whereHas('brand.category', fn ($query) => $query->where('type', self::TYPES[$type])));
    }

    /**
     * What a span is compared with: this month so far with the same point last month (as the kiosk does),
     * whole months with as many months before, anything else with as many days just before.
     *
     * @return array{Carbon, Carbon, Carbon, Carbon, string}
     */
    private function withSpanBefore(Carbon $from, Carbon $to): array
    {
        $now = now();

        if ($from->isSameDay($now->copy()->startOfMonth()) && $to->isSameDay($now)) {
            [$previousFrom, $previousTo] = $this->homeService->sameTimeLastMonth($now);

            return [$from, $now, $previousFrom, $previousTo, 'than this time in ' . $previousFrom->format('F')];
        }

        if ($from->day === 1 && $to->isSameDay($to->copy()->endOfMonth())) {
            $months = ($to->year - $from->year) * 12 + $to->month - $from->month + 1;
            $previousFrom = $from->copy()->subMonthsNoOverflow($months);

            return [
                $from, $to, $previousFrom, $from->copy()->subDay()->endOfDay(),
                $months === 1 ? 'than in ' . $previousFrom->format('F') : "than the {$months} months before",
            ];
        }

        $days = (int) round($from->copy()->startOfDay()->diffInDays($to->copy()->startOfDay())) + 1;
        $label = match ($days) {
            1 => 'than the day before',
            7 => 'than the week before',
            default => "than the {$days} days before",
        };

        return [$from, $to, $from->copy()->subDays($days), $from->copy()->subDay()->endOfDay(), $label];
    }

    /**
     * An exact name, else the only name that contains what was said (or is contained in it),
     * else a small slip ("carefour"), else the nearest names to offer back.
     *
     * @template TModel of \Illuminate\Database\Eloquent\Model
     * @param  Collection<int, TModel>  $candidates
     * @return array{0: ?TModel, 1: array<int, string>}
     */
    private function closest(string $name, Collection $candidates): array
    {
        $needle = Str::lower(trim($name));
        $lower = fn ($candidate) => Str::lower($candidate->name);

        if ($exact = $candidates->first(fn ($candidate) => $lower($candidate) === $needle)) {
            return [$exact, []];
        }

        $containing = $candidates->filter(fn ($candidate) => str_contains($lower($candidate), $needle) || str_contains($needle, $lower($candidate)));

        if ($containing->count() === 1) {
            return [$containing->first(), []];
        }

        if ($containing->isNotEmpty()) {
            return [null, $containing->pluck('name')->take(5)->values()->all()];
        }

        $nearest = $candidates->sortBy(fn ($candidate) => levenshtein($needle, $lower($candidate)))->take(3)->values();
        $best = $nearest->first();

        if ($best && levenshtein($needle, $lower($best)) <= max(1, intdiv(mb_strlen($needle), 5))) {
            return [$best, []];
        }

        return [null, $nearest->pluck('name')->all()];
    }
}
