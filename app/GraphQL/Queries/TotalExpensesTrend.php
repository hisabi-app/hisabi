<?php

namespace App\GraphQL\Queries;

use App\Domains\Transaction\Models\Transaction;
use App\Domain\Ranges\AllTime;
use App\Domain\Ranges\LastYear;
use Illuminate\Support\Facades\DB;
use App\Domain\Ranges\CurrentYear;
use App\Domain\Metrics\TrendMetric;
use App\Domain\Ranges\LastTwelveMonths;

class TotalExpensesTrend extends TrendMetric
{
    protected $name = 'Spending Over Time';

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

        $query = Transaction::query()
            ->expenses()
            ->select(DB::raw("date_format(created_at, '%Y-%m') as label, SUM(transactions.amount) as value"))
            ->groupBy(DB::raw("label"))
            ->orderBy("label");

        if($rangeData) {
            $query->whereBetween('transactions.created_at', [$rangeData->start(), $rangeData->end()]);
        }
            
        return $query->get();
    }
}
