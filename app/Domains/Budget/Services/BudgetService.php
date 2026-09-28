<?php

namespace App\Domains\Budget\Services;

use App\Domains\Budget\Models\Budget;
use App\Domains\Transaction\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\QueryBuilder;

class BudgetService
{
    // Daily and weekly budgets can have hundreds of windows in the range; keep the most recent ones.
    private const MAX_HISTORY_WINDOWS = 60;

    public function getAll(): Collection
    {
        return QueryBuilder::for(Budget::class)
            ->allowedSorts(['id', 'name', 'amount', 'start_at'])
            ->get();
    }

    /**
     * Spending per window for every recurring budget, for windows starting between $from and $to,
     * compared with the budget's current amount.
     */
    public function getHistory(Carbon $from, Carbon $to): array
    {
        $budgets = Budget::where('reoccurrence', '!=', Budget::CUSTOM)
            ->orderBy('id')
            ->get();

        $windows = $budgets->mapWithKeys(fn (Budget $budget) => [
            $budget->id => array_slice($budget->getWindowsStartingBetween($from, $to), -self::MAX_HISTORY_WINDOWS),
        ]);

        $dailyTotals = $this->getDailyTotalsPerBudget($budgets->modelKeys(), $windows->flatten(1));

        return $budgets
            ->map(fn (Budget $budget) => [
                'id' => $budget->id,
                'name' => $budget->name,
                'reoccurrence' => $budget->reoccurrence,
                'period' => $budget->period,
                'amount' => (float) $budget->amount,
                'periods' => $this->getWindowsSpending($budget, $windows[$budget->id], $dailyTotals[$budget->id] ?? collect()),
            ])
            ->all();
    }

    public function update(int $id, array $data): Budget
    {
        $budget = Budget::findOrFail($id);
        $budget->update($data);
        return $budget;
    }

    public function getFirstTransactionDate(): ?Carbon
    {
        $first = Transaction::min('created_at');

        return $first ? Carbon::parse($first) : null;
    }

    /**
     * Spending per budget per day, covering all the given windows, in a single query.
     * Windows start at the beginning of a day and span whole days, so daily totals add up to window totals.
     *
     * @return SupportCollection<int, SupportCollection<string, string>> budget id => [Y-m-d => total]
     */
    private function getDailyTotalsPerBudget(array $budgetIds, SupportCollection $windows): SupportCollection
    {
        if ($windows->isEmpty()) {
            return collect();
        }

        return DB::table('budget_category')
            ->join('brands', 'budget_category.category_id', '=', 'brands.category_id')
            ->join('transactions', 'brands.id', '=', 'transactions.brand_id')
            ->whereIn('budget_category.budget_id', $budgetIds)
            ->where('transactions.created_at', '>=', $windows->min(fn ($window) => $window[0]))
            ->where('transactions.created_at', '<', $windows->max(fn ($window) => $window[1]))
            ->selectRaw('budget_category.budget_id, DATE(transactions.created_at) as day, SUM(transactions.amount) as total')
            ->groupBy('budget_category.budget_id', 'day')
            ->get()
            ->groupBy('budget_id')
            ->map(fn (SupportCollection $rows) => $rows->pluck('total', 'day'));
    }

    private function getWindowsSpending(Budget $budget, array $windows, SupportCollection $dailyTotals): array
    {
        return array_map(function (array $window) use ($budget, $dailyTotals) {
            [$startAt, $endAt] = $window;
            $startDay = $startAt->format('Y-m-d');
            $endDay = $endAt->format('Y-m-d');

            $spent = (float) $dailyTotals
                ->filter(fn ($total, $day) => $day >= $startDay && $day < $endDay)
                ->sum();

            return [
                'start_date' => $startDay,
                'end_date' => $endDay,
                'spent' => $spent,
                'percentage' => $budget->amount > 0 ? (int) round($spent / $budget->amount * 100) : 0,
                'is_current' => now()->lt($endAt),
            ];
        }, $windows);
    }
}
