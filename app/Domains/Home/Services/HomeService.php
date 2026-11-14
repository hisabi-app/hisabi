<?php

namespace App\Domains\Home\Services;

use App\Domains\Brand\Models\Brand;
use App\Domains\Budget\Models\Budget;
use App\Domains\Budget\Services\BudgetService;
use App\Domains\Sms\Models\Sms;
use App\Domains\Transaction\Models\Transaction;
use App\Domains\Transaction\Services\TransactionService;
use App\Models\Category;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection as SupportCollection;

class HomeService
{
    public const STAGE_NEW = 'new';
    public const STAGE_EARLY = 'early';
    public const STAGE_FULL = 'full';

    public const BUDGET_SUGGESTION = 'budget-suggestion';

    private const RECENT_TRANSACTIONS = 5;

    public function __construct(
        private readonly BudgetService $budgetService,
        private readonly TransactionService $transactionService
    ) {}

    /**
     * How far along the user is, so the app can unfold as data arrives:
     * new (no transactions yet), early (less than a month of them) or full.
     */
    public function getStage(): string
    {
        $firstTransactionDate = $this->budgetService->getFirstTransactionDate();

        return match (true) {
            $firstTransactionDate === null => self::STAGE_NEW,
            $firstTransactionDate->gt(now()->subMonthNoOverflow()) => self::STAGE_EARLY,
            default => self::STAGE_FULL,
        };
    }

    /**
     * What the sidebar and dock need on every page: the stage and the counts of things waiting.
     */
    public function getNavigation(): array
    {
        return [
            'stage' => $this->getStage(),
            'has_budgets' => Budget::exists(),
            'counts' => [
                'budgets' => $this->budgetService->getCurrent()->filter(fn (Budget $budget) => $budget->needsAttention())->count(),
                'brands' => $this->uncategorizedBrands()->count(),
            ],
        ];
    }

    /**
     * Everything the home shows: what needs the user, how the month is going, and where the money went.
     */
    public function getHome(User $user): array
    {
        $now = now();
        $monthStart = $now->copy()->startOfMonth();
        // The same moment last month, e.g. Mar 31 18:00 → Feb 28 18:00
        $sameTimeLastMonth = $now->copy()->subMonthNoOverflow();
        $lastMonthStart = $sameTimeLastMonth->copy()->startOfMonth();
        $lastMonthEnd = $lastMonthStart->copy()->endOfMonth();

        $stage = $this->getStage();
        $budgets = $this->budgetService->getCurrent();
        $firstTransactionDate = $this->budgetService->getFirstTransactionDate();
        $lastMonthDaily = $this->getCumulativeDailyExpenses($lastMonthStart, $lastMonthEnd);

        return [
            'stage' => $stage,
            'needs_you' => [
                'brands' => [
                    'count' => $this->uncategorizedBrands()->count(),
                    // Enough to assign categories right from the home
                    'items' => $this->uncategorizedBrands()
                        ->withCount('transactions')
                        ->withSum('transactions', 'amount')
                        ->latest('id')
                        ->limit(20)
                        ->get(['id', 'name'])
                        ->map(fn (Brand $brand) => [
                            'id' => $brand->id,
                            'name' => $brand->name,
                            'transactions' => $brand->transactions_count,
                            'total' => (float) $brand->transactions_sum_amount,
                        ]),
                ],
                'budgets' => $budgets
                    ->filter(fn (Budget $budget) => $budget->needsAttention())
                    ->map(fn (Budget $budget) => [
                        'id' => $budget->id,
                        'name' => $budget->name,
                        'spent' => (float) $budget->total_transactions_amount,
                        'amount' => (float) $budget->amount,
                        'status' => $budget->status(),
                    ])
                    ->values(),
                'unparsed_sms' => Sms::query()->whereNull('transaction_id')->count(),
            ],
            'month' => [
                'spent' => $this->transactionService->getExpensesTotal($monthStart, $now),
                'spent_last_month_to_date' => $this->transactionService->getExpensesTotal($lastMonthStart, $sameTimeLastMonth),
                // Comparisons only make sense once there's a previous month to compare with
                'has_last_month' => end($lastMonthDaily) > 0,
                // Set while the first month is still being filled in, e.g. "since 8 Nov"
                'started_at' => $firstTransactionDate?->gte($monthStart) ? $firstTransactionDate->format('Y-m-d') : null,
                'daily' => $this->getCumulativeDailyExpenses($monthStart, $now),
                'last_month_daily' => $lastMonthDaily,
            ],
            'insights' => $stage === self::STAGE_FULL ? $this->getInsights($monthStart, $now, $lastMonthStart, $sameTimeLastMonth) : [],
            'budgets' => $budgets,
            'recent' => Transaction::query()
                ->with('brand.category')
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->limit(self::RECENT_TRANSACTIONS)
                ->get(),
            'suggestion' => $this->getBudgetSuggestion($user, $monthStart, $now),
        ];
    }

    /**
     * A budget's spending in its current window: by brand, and every transaction.
     */
    public function getBudgetDetail(Budget $budget): array
    {
        [$startAt, $endAt] = $budget->currentWindow();
        $categoryIds = $budget->categories()->pluck('categories.id')->all();

        $inWindow = fn (): Builder => Transaction::query()
            ->whereBetween('created_at', [$startAt, $endAt])
            ->whereHas('brand', fn (Builder $query) => $query->whereIn('category_id', $categoryIds));

        return [
            'budget' => $budget,
            'category' => Category::query()->whereIn('id', $categoryIds)->first(['id', 'name', 'color', 'icon']),
            'brands' => $inWindow()
                ->selectRaw('brand_id, SUM(amount) as total, COUNT(*) as count')
                ->groupBy('brand_id')
                ->orderByDesc('total')
                ->with('brand:id,name')
                ->get()
                ->map(fn (Transaction $row) => [
                    'id' => $row->brand_id,
                    'name' => $row->brand?->name,
                    'total' => (float) $row->total,
                    'count' => (int) $row->count,
                ]),
            'transactions' => $inWindow()
                ->with('brand.category')
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->limit(50)
                ->get(),
        ];
    }

    public function uncategorizedBrands(): Builder
    {
        return Brand::query()->whereNull('category_id')->has('transactions');
    }

    /**
     * Running total of spending per day from $from to $to, one entry per day.
     */
    public function getCumulativeDailyExpenses(Carbon $from, Carbon $to): array
    {
        $totals = Transaction::query()
            ->expenses()
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('DATE(created_at) as day, SUM(amount) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $running = 0;
        $series = [];
        for ($day = $from->copy()->startOfDay(); $day->lte($to); $day->addDay()) {
            $running += (float) ($totals[$day->format('Y-m-d')] ?? 0);
            $series[] = round($running, 2);
        }

        return $series;
    }

    /**
     * Up to three things worth knowing about this month, each pointing somewhere to dig in.
     */
    private function getInsights(Carbon $monthStart, Carbon $now, Carbon $lastMonthStart, Carbon $sameTimeLastMonth): array
    {
        $insights = [];

        // Where the money keeps going: the brand with the most spending among those visited more than once,
        // so a single rent payment doesn't hide the day-to-day
        $topBrand = Transaction::query()
            ->expenses()
            ->whereBetween('created_at', [$monthStart, $now])
            ->selectRaw('brand_id, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('brand_id')
            ->havingRaw('COUNT(*) > 1')
            ->orderByDesc('total')
            ->with('brand.category')
            ->first();

        if ($topBrand) {
            $insights[] = [
                'kind' => 'top_brand',
                'brand' => ['id' => $topBrand->brand_id, 'name' => $topBrand->brand?->name],
                'category' => $this->categorySummary($topBrand->brand?->category),
                'total' => (float) $topBrand->total,
                'count' => (int) $topBrand->count,
            ];
        }

        // The category that grew the most compared with the same point last month
        $thisMonth = $this->expensesByCategory($monthStart, $now);
        $lastMonth = $this->expensesByCategory($lastMonthStart, $sameTimeLastMonth);
        $increase = $thisMonth
            ->map(fn ($total, $categoryId) => $total - ($lastMonth[$categoryId] ?? 0))
            ->filter(fn ($difference) => $difference > 0)
            ->sortDesc();

        if ($lastMonth->isNotEmpty() && $increase->isNotEmpty()) {
            $categoryId = $increase->keys()->first();
            $insights[] = [
                'kind' => 'category_up',
                'category' => $this->categorySummary(Category::find($categoryId)),
                'difference' => round($increase->first(), 2),
                'total' => round($thisMonth[$categoryId], 2),
            ];
        }

        // Money put aside this month
        $saved = Transaction::query()->savings()->whereBetween('created_at', [$monthStart, $now]);
        $savedTotal = (float) (clone $saved)->sum('amount');

        if ($savedTotal > 0) {
            $latest = (clone $saved)->with('brand.category')->orderByDesc('created_at')->first();
            $insights[] = [
                'kind' => 'saved',
                'category' => $this->categorySummary($latest?->brand?->category),
                'total' => $savedTotal,
                'last_at' => $latest?->created_at?->format('Y-m-d'),
            ];
        }

        return $insights;
    }

    /**
     * Suggest a first budget for the category with the most spending this month,
     * unless the user already has budgets or said "Not now" this month.
     */
    private function getBudgetSuggestion(User $user, Carbon $monthStart, Carbon $now): ?array
    {
        if (Budget::exists() || $user->dismissedThisMonth(self::BUDGET_SUGGESTION)) {
            return null;
        }

        $top = $this->expensesByCategory($monthStart, $now)->sortDesc();

        if ($top->isEmpty()) {
            return null;
        }

        return [
            'category' => $this->categorySummary(Category::find($top->keys()->first())),
            'spent' => round($top->first(), 2),
        ];
    }

    /**
     * @return SupportCollection<int, float> category id => total spent
     */
    private function expensesByCategory(Carbon $from, Carbon $to): SupportCollection
    {
        return Transaction::query()
            ->join('brands', 'brands.id', '=', 'transactions.brand_id')
            ->join('categories', 'categories.id', '=', 'brands.category_id')
            ->where('categories.type', Category::EXPENSES)
            ->whereBetween('transactions.created_at', [$from, $to])
            ->groupBy('categories.id')
            ->selectRaw('categories.id as category_id, SUM(transactions.amount) as total')
            ->pluck('total', 'category_id')
            ->map(fn ($total) => (float) $total);
    }

    private function categorySummary(?Category $category): ?array
    {
        return $category ? [
            'id' => $category->id,
            'name' => $category->name,
            'color' => $category->color,
            'icon' => $category->icon,
        ] : null;
    }
}
