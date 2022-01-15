<?php

namespace App\GraphQL\Queries;

use App\Models\Brand;
use Illuminate\Support\Facades\DB;
use App\Domain\Metrics\RelationPartitionMetric;

class TotalPerBrand extends RelationPartitionMetric
{
    protected $relationGraphqlQuery = 'allCategories';
    protected $relationDisplayUsing = 'name';
    protected $relationForeignKey = 'category_id';

    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     */
    public function __invoke($_, array $args)
    {
        $rangeData = app('findRangeByKey', ["key" => $args['range']]);
        $categoryId = $args['category_id'];

        $query = Brand::query()
            ->where('category_id', $categoryId)
            ->join('transactions', 'transactions.brand_id', '=', 'brands.id')
            ->select("brands.name as label", DB::raw("SUM(transactions.amount) as value"))
            ->groupBy("brands.id")
            ->orderBy('value', 'DESC');

        if($rangeData) {
            $query->whereBetween('transactions.created_at', [$rangeData->start(), $rangeData->end()]);
        }
            
        return $query->get();
    }
}
