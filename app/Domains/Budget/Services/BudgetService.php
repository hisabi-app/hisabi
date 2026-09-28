<?php

namespace App\Domains\Budget\Services;

use App\Domains\Budget\Models\Budget;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Collection;
use Spatie\QueryBuilder\QueryBuilder;

class BudgetService
{
    public function getAll(): Collection
    {
        return QueryBuilder::for(Budget::class)
            ->allowedSorts(['id', 'name', 'amount', 'start_at'])
            ->get();
    }

    /**
     * Spending per day in the budget's current window, from its start up to today
     * (or the window's end, if that is earlier). Days without transactions are 0.
     *
     * @return array<int, array{date: string, amount: float}>
     */
    public function getDailySpending(Budget $budget): array
    {
        $startAt = Carbon::parse($budget->start_at_date)->startOfDay();
        $endAt = Carbon::parse($budget->end_at_date)->startOfDay();
        $lastDay = now()->startOfDay()->min($endAt);

        $totals = $budget->categories()
            ->join('brands', 'categories.id', '=', 'brands.category_id')
            ->join('transactions', 'brands.id', '=', 'transactions.brand_id')
            ->whereBetween('transactions.created_at', [$startAt, $endAt])
            ->selectRaw('DATE(transactions.created_at) as day, SUM(transactions.amount) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $days = [];

        foreach (CarbonPeriod::create($startAt, $lastDay) as $day) {
            $date = $day->format('Y-m-d');
            $days[] = ['date' => $date, 'amount' => (float) ($totals[$date] ?? 0)];
        }

        return $days;
    }
}
