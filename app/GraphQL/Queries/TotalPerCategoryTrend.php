<?php

namespace App\GraphQL\Queries;

use App\Models\Transaction;
use App\Domain\Ranges\LastYear;
use Illuminate\Support\Facades\DB;
use App\Domain\Ranges\CurrentYear;
use App\Domain\Ranges\LastTwelveMonths;
use App\Domain\Metrics\RelationTrendMetric;

class TotalPerCategoryTrend extends RelationTrendMetric
{
    protected $relationGraphqlQuery = 'allCategories';
    protected $relationDisplayUsing = 'name';
    protected $relationForeignKey = 'id';

    public function ranges()
    {
        return [
            new LastTwelveMonths,
            new CurrentYear,
            new LastYear,
        ];
    }

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
            ->select(DB::raw("date_format(created_at, '%Y-%m') as label, SUM(transactions.amount) as value"))
            ->groupBy(DB::raw("label"))
            ->orderBy('label');

        if($rangeData) {
            $query->whereBetween('transactions.created_at', [$rangeData->start(), $rangeData->end()]);
        }
            
        return $query->get();
    }
}
