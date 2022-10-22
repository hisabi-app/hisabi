<?php

namespace App\GraphQL\Queries;

use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use App\Domain\Metrics\RelationTrendMetric;

class TransactionsStandardDeviation extends RelationTrendMetric
{
    protected $relationGraphqlQuery = 'allCategories';
    protected $relationDisplayUsing = 'name';
    protected $relationForeignKey = 'id';
    protected $showStandardDeviation = true;
    
    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     */
    public function __invoke($_, array $args)
    {
        $rangeData = app('findRangeByKey', ["key" => $args['range']]);
        $categoryId = $args['id'];

        $query = Transaction::query()
            ->whereHas('brand.category', function ($query) use($categoryId) {
                return $query->where('id', $categoryId);
            })
            ->select(DB::raw("transactions.id as label, transactions.amount as value"))
            ->orderBy('transactions.created_at', 'asc');

        if($rangeData) {
            $query->whereBetween('transactions.created_at', [$rangeData->start(), $rangeData->end()]);
        }
            
        return $query->get();
    }
}
