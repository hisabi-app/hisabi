<?php

namespace App\GraphQL\Queries;

use App\Models\Brand;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class BrandStats
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

        // Most used brand (highest transaction count)
        $mostUsedBrand = (clone $query)
            ->select('brands.id', 'brands.name', DB::raw('COUNT(transactions.id) as transaction_count'))
            ->join('brands', 'transactions.brand_id', '=', 'brands.id')
            ->groupBy('brands.id', 'brands.name')
            ->orderBy('transaction_count', 'DESC')
            ->first();

        // Highest spending brand (highest total amount) - only expenses
        $highestSpendingBrand = (clone $query)
            ->select('brands.id', 'brands.name', DB::raw('SUM(transactions.amount) as total_amount'))
            ->join('brands', 'transactions.brand_id', '=', 'brands.id')
            ->join('categories', 'brands.category_id', '=', 'categories.id')
            ->where('categories.type', \App\Models\Category::EXPENSES)
            ->groupBy('brands.id', 'brands.name')
            ->orderBy('total_amount', 'DESC')
            ->first();

        // Highest income brand (highest total amount) - only income
        $highestIncomeBrand = (clone $query)
            ->select('brands.id', 'brands.name', DB::raw('SUM(transactions.amount) as total_amount'))
            ->join('brands', 'transactions.brand_id', '=', 'brands.id')
            ->join('categories', 'brands.category_id', '=', 'categories.id')
            ->where('categories.type', \App\Models\Category::INCOME)
            ->groupBy('brands.id', 'brands.name')
            ->orderBy('total_amount', 'DESC')
            ->first();

        return [
            'mostUsedBrand' => $mostUsedBrand ? [
                'name' => $mostUsedBrand->name,
                'count' => $mostUsedBrand->transaction_count
            ] : null,
            'highestSpendingBrand' => $highestSpendingBrand ? [
                'name' => $highestSpendingBrand->name,
                'amount' => $highestSpendingBrand->total_amount
            ] : null,
            'highestIncomeBrand' => $highestIncomeBrand ? [
                'name' => $highestIncomeBrand->name,
                'amount' => $highestIncomeBrand->total_amount
            ] : null,
        ];
    }
}

