<?php

namespace App\GraphQL\Queries;

use App\Models\Category;
use Illuminate\Support\Facades\DB;
use App\Domain\Metrics\PartitionMetric;

class IncomePerCategory extends PartitionMetric
{
    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     */
    public function __invoke($_, array $args)
    {
        $rangeData = app('findRangeByKey', ["key" => $args['range']]);

        $query = Category::query()
            ->where('type', Category::INCOME)
            ->join('brands', 'brands.category_id', '=', 'categories.id')
            ->join('transactions', 'transactions.brand_id', '=', 'brands.id')
            ->select("categories.name as label", DB::raw("SUM(transactions.amount) as value"))
            ->groupBy("categories.id")
            ->orderBy('value', 'DESC');

        if($rangeData) {
            $query->whereBetween('transactions.created_at', [$rangeData->start(), $rangeData->end()]);
        }
            
        return $query->get();
    }
}
