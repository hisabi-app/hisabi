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

    // What a balance adds up, as the app counts it elsewhere: net worth is income less spending
    // (money saved or invested is still yours), cash is also less what went to savings and investment
    public const BALANCES = [
        'net_worth' => [Category::INCOME => 1, Category::EXPENSES => -1],
        'cash' => [Category::INCOME => 1, Category::EXPENSES => -1, Category::SAVINGS => -1, Category::INVESTMENT => -1],
        'savings' => [Category::SAVINGS => 1],
        'investment' => [Category::INVESTMENT => 1],
    ];

    public const STEPS = ['day', 'week', 'month'];

    // More points than a wall can read in one line
    public const MAX_POINTS = 36;

    // A balance's line runs over the last twelve months, this one included
    private const BALANCE_MONTHS = 12;

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
     * A comparison in words, for Claude to read: "1,320 less than this time in October", "the same as in October".
     *
     * @param  array{difference: float, direction: string, label: string}|null  $comparison
     */
    public function describe(?array $comparison): ?string
    {
        if ($comparison === null) {
            return null;
        }

        return $comparison['direction'] === 'same'
            ? preg_replace('/^than /', 'the same as ', $comparison['label'])
            : number_format($comparison['difference']) . " {$comparison['direction']} {$comparison['label']}";
    }

    /**
     * Totals per day, week (from Monday) or month from $from to $to, the quiet ones as zero.
     *
     * @return array<int, array{label: string, start: string, end: string, value: float}>
     */
    public function trend(Carbon $from, Carbon $to, string $step, string $type, ?Category $category = null, ?Brand $brand = null): array
    {
        $daily = $this->transactions($type, $category, $brand)
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('DATE(created_at) as day, SUM(amount) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        return array_map(fn (array $period) => [
            ...$period,
            'value' => round((float) $daily->filter(fn ($total, $day) => $day >= $period['start'] && $day <= $period['end'])->sum(), 2),
        ], $this->periods($from, $to, $step));
    }

    /**
     * How many points a trend from $from to $to would have, to refuse ones too long to draw.
     */
    public function countPeriods(Carbon $from, Carbon $to, string $step): int
    {
        return count($this->periods($from, $to, $step));
    }

    /**
     * What there is now (a balance like savings or net worth, or everything a category or brand ever added up to),
     * what it was when this month started, and where it stood at the end of each of the last twelve months.
     *
     * @return array{total: float, month_start: float, points: array<int, array{label: string, value: float}>}
     */
    public function balance(string $kind, ?Category $category = null, ?Brand $brand = null): array
    {
        $now = now();
        $monthStart = $now->copy()->startOfMonth();
        $from = $monthStart->copy()->subMonthsNoOverflow(self::BALANCE_MONTHS - 1);
        $signs = self::BALANCES[$kind] ?? self::BALANCES['net_worth'];

        $base = fn () => Transaction::query()
            ->join('brands', 'brands.id', '=', 'transactions.brand_id')
            ->join('categories', 'categories.id', '=', 'brands.category_id')
            ->when($brand, fn ($query) => $query->where('brands.id', $brand->id))
            ->when($category, fn ($query) => $query->where('categories.id', $category->id))
            ->when(! $category && ! $brand, fn ($query) => $query->whereIn('categories.type', array_keys($signs)));
        // A category or brand adds up as it is; a balance adds and takes away by kind of money
        $sign = fn (string $type) => ($category || $brand) ? 1 : ($signs[$type] ?? 0);

        $opening = $base()
            ->where('transactions.created_at', '<', $from)
            ->selectRaw('categories.type as type, SUM(transactions.amount) as total')
            ->groupBy('categories.type')
            ->get()
            ->sum(fn ($row) => $sign($row->type) * (float) $row->total);

        $days = $base()
            ->whereBetween('transactions.created_at', [$from, $now])
            ->selectRaw('categories.type as type, DATE(transactions.created_at) as day, SUM(transactions.amount) as total')
            ->groupBy('categories.type', 'day')
            ->get()
            ->map(fn ($row) => ['day' => (string) $row->day, 'amount' => $sign($row->type) * (float) $row->total]);

        $until = fn (string $day) => round($opening + $days->filter(fn (array $row) => $row['day'] <= $day)->sum('amount'), 2);
        $points = array_map(
            fn (array $period) => ['label' => $period['label'], 'value' => $until($period['end'])],
            $this->periods($from, $now, 'month')
        );

        return [
            'total' => $until($now->format('Y-m-d')),
            'month_start' => $until($monthStart->copy()->subDay()->format('Y-m-d')),
            'points' => $points,
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
     * The days, weeks (from Monday) or months from $from to $to, each with a short label for the chart:
     * "17" for a day (with the month on the first and on the 1st), "3 Nov" for a week, "Nov" for a month
     * (the card's label carries the years).
     *
     * @return array<int, array{label: string, start: string, end: string}>
     */
    private function periods(Carbon $from, Carbon $to, string $step): array
    {
        $cursor = match ($step) {
            'day' => $from->copy()->startOfDay(),
            'week' => $from->copy()->startOfWeek(Carbon::MONDAY),
            default => $from->copy()->startOfMonth(),
        };
        $periods = [];

        while ($cursor->lte($to) && count($periods) <= self::MAX_POINTS) {
            $end = match ($step) {
                'day' => $cursor->copy()->endOfDay(),
                'week' => $cursor->copy()->endOfWeek(Carbon::SUNDAY),
                default => $cursor->copy()->endOfMonth(),
            };
            $first = $periods === [];

            $periods[] = [
                'label' => match ($step) {
                    'day' => $first || $cursor->day === 1 ? $cursor->format('j M') : $cursor->format('j'),
                    'week' => $cursor->max($from)->format('j M'),
                    default => $cursor->format('M'),
                },
                'start' => $cursor->max($from)->format('Y-m-d'),
                'end' => $end->min($to)->format('Y-m-d'),
            ];

            $cursor = match ($step) {
                'day' => $cursor->addDay(),
                'week' => $cursor->addWeek(),
                default => $cursor->addMonthNoOverflow(),
            };
        }

        return $periods;
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
