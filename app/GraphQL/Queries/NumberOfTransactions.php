<?php

namespace App\GraphQL\Queries;

use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use App\Domain\Metrics\PartitionMetric;

class NumberOfTransactions extends PartitionMetric
{
    protected $showCurrency = false;

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
            ->select("categories.type as label", DB::raw("count(transactions.id) as value"))
            ->groupBy("categories.type")
            ->orderBy('value', 'DESC');

        if($rangeData) {
            $query->whereBetween('transactions.created_at', [$rangeData->start(), $rangeData->end()]);
        }
            
        return $query->get();
    }
}
