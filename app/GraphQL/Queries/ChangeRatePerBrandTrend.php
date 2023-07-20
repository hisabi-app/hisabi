<?php

namespace App\GraphQL\Queries;

use App\Models\Transaction;
use App\Domain\Ranges\AllTime;
use App\Domain\Ranges\LastYear;
use App\Domain\Ranges\CurrentYear;
use Illuminate\Support\Facades\DB;
use App\Domain\Ranges\LastTwelveMonths;
use App\Domain\Metrics\RelationTrendMetric;

class ChangeRatePerBrandTrend extends RelationTrendMetric
{
    protected $relationGraphqlQuery = 'allBrands';
    protected $relationDisplayUsing = 'name';
    protected $relationForeignKey = 'id';

    public function ranges()
    {
        return [
            new LastTwelveMonths,
            new CurrentYear,
            new LastYear,
            new AllTime,
        ];
    }

    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     */
    public function __invoke($_, array $args)
    {
        $rangeData = app('findRangeByKey', ["key" => $args['range']]);
        $brandId = $args['id'];

        $rateChangeQuery = Transaction::where('brand_id', $brandId)
            ->selectRaw("date_format(created_at, '%Y-%m') as label, SUM(amount) as value")
            ->groupBy(DB::raw("label"))
            ->orderBy('label');
        
        if($rangeData) {
            $rateChangeQuery->whereBetween('transactions.created_at', [$rangeData->start(), $rangeData->end()]);
        }

        $rateChangeQuery = $rateChangeQuery->get();

        $changeRates = [];
        
        $rateChangeQuery->each(function ($item, $key) use ($rateChangeQuery, &$changeRates) {
            if ($key > 0) {
                $previousAmount = $rateChangeQuery[$key - 1]->value;
                $changeRate = ($item->value - $previousAmount) / $previousAmount * 100;
                $changeRates[] = ["label" => "$item->label ($item->value)", "value" => $changeRate];
            }else {
                $changeRates[] = ["label" => "$item->label ($item->value)", "value" => 0];
            }
        });

        return $changeRates;
    }
}
