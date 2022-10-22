<?php

namespace App\GraphQL\Queries;

use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use App\Domain\Metrics\RelationPartitionMetric;

class NumberOfTransactionsPerBrand extends RelationPartitionMetric
{
    protected $showCurrency = false;
    protected $relationGraphqlQuery = 'allCategories';
    protected $relationDisplayUsing = 'name';
    protected $relationForeignKey = 'id';

    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     */
    public function __invoke($_, array $args)
    {
        $rangeData = app('findRangeByKey', ["key" => $args['range']]);
        $categoryId = $args['id'];

        $query = Transaction::query()
            ->join('brands', 'brands.id', '=', 'transactions.brand_id')
            ->whereHas('brand.category', function ($query) use($categoryId) {
                return $query->where('id', $categoryId);
            })
            ->select("brands.name as label", DB::raw("count(transactions.id) as value"))
            ->groupBy("brands.name")
            ->orderBy('value', 'DESC');

        if($rangeData) {
            $query->whereBetween('transactions.created_at', [$rangeData->start(), $rangeData->end()]);
        }
            
        return $query->get();
    }
}
