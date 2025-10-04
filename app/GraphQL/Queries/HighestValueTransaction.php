<?php

namespace App\GraphQL\Queries;

use App\Domains\Transaction\Models\Transaction;
use Illuminate\Support\Facades\DB;
use App\Domain\Metrics\PartitionMetric;

class HighestValueTransaction extends PartitionMetric
{
    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     */
    public function __invoke($_, array $args)
    {
        $rangeData = app('findRangeByKey', ["key" => $args['range']]);

        $query = Transaction::query()
            ->join('brands', 'brands.id', '=', 'transactions.brand_id')
            ->join('categories', 'categories.id', '=', 'brands.category_id')
            ->select("categories.name as label", DB::raw("max(transactions.amount) as value"))
            ->groupBy("categories.name")
            ->orderBy('value', 'DESC');

        if($rangeData) {
            $query->whereBetween('transactions.created_at', [$rangeData->start(), $rangeData->end()]);
        }
            
        return $query->get();
    }
}
