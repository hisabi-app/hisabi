<?php

namespace App\Domains\Metrics\Metrics;

use App\Domains\Metrics\Metric;
use App\Domains\Category\Models\Category;
use App\Domains\Transaction\Models\Transaction;
use Illuminate\Support\Facades\DB;

class CategoryStatsMetric extends Metric
{
    public function calculate(): array
    {
        $query = Transaction::query()
            ->join('brands', 'transactions.brand_id', '=', 'brands.id')
            ->join('categories', 'brands.category_id', '=', 'categories.id');

        if ($this->hasDateRange()) {
            $query->whereBetween('transactions.created_at', [$this->getStartDate(), $this->getEndDate()]);
        }

        $mostUsedCategory = (clone $query)
            ->select('categories.id', 'categories.name', DB::raw('COUNT(transactions.id) as transaction_count'))
            ->groupBy('categories.id', 'categories.name')
            ->orderBy('transaction_count', 'DESC')
            ->first();

        $highestSpendingCategory = (clone $query)
            ->select('categories.id', 'categories.name', DB::raw('SUM(transactions.amount) as total_amount'))
            ->where('categories.type', Category::EXPENSES)
            ->groupBy('categories.id', 'categories.name')
            ->orderBy('total_amount', 'DESC')
            ->first();

        $highestIncomeCategory = (clone $query)
            ->select('categories.id', 'categories.name', DB::raw('SUM(transactions.amount) as total_amount'))
            ->where('categories.type', Category::INCOME)
            ->groupBy('categories.id', 'categories.name')
            ->orderBy('total_amount', 'DESC')
            ->first();

        return [
            'mostUsedCategory' => $mostUsedCategory ? [
                'name' => $mostUsedCategory->name,
                'count' => $mostUsedCategory->transaction_count
            ] : null,
            'highestSpendingCategory' => $highestSpendingCategory ? [
                'name' => $highestSpendingCategory->name,
                'amount' => $highestSpendingCategory->total_amount
            ] : null,
            'highestIncomeCategory' => $highestIncomeCategory ? [
                'name' => $highestIncomeCategory->name,
                'amount' => $highestIncomeCategory->total_amount
            ] : null,
        ];
    }
}
