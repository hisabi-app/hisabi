<?php

namespace App\GraphQL\Queries;

use App\Models\Category;
use App\Domains\Transaction\Models\Transaction;
use Illuminate\Support\Facades\DB;

class CategoryStats
{
    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     */
    public function __invoke($_, array $args)
    {
        $rangeData = app('findRangeByKey', ["key" => $args['range']]);

        // Build base query for the range
        $query = Transaction::query();
        if($rangeData) {
            $query->whereBetween('transactions.created_at', [$rangeData->start(), $rangeData->end()]);
        }

        // Most used category (highest transaction count)
        $mostUsedCategory = (clone $query)
            ->select('categories.id', 'categories.name', DB::raw('COUNT(transactions.id) as transaction_count'))
            ->join('brands', 'transactions.brand_id', '=', 'brands.id')
            ->join('categories', 'brands.category_id', '=', 'categories.id')
            ->groupBy('categories.id', 'categories.name')
            ->orderBy('transaction_count', 'DESC')
            ->first();

        // Highest spending category (highest total amount) - only expenses
        $highestSpendingCategory = (clone $query)
            ->select('categories.id', 'categories.name', DB::raw('SUM(transactions.amount) as total_amount'))
            ->join('brands', 'transactions.brand_id', '=', 'brands.id')
            ->join('categories', 'brands.category_id', '=', 'categories.id')
            ->where('categories.type', \App\Models\Category::EXPENSES)
            ->groupBy('categories.id', 'categories.name')
            ->orderBy('total_amount', 'DESC')
            ->first();

        // Highest income category (highest total amount) - only income
        $highestIncomeCategory = (clone $query)
            ->select('categories.id', 'categories.name', DB::raw('SUM(transactions.amount) as total_amount'))
            ->join('brands', 'transactions.brand_id', '=', 'brands.id')
            ->join('categories', 'brands.category_id', '=', 'categories.id')
            ->where('categories.type', \App\Models\Category::INCOME)
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

