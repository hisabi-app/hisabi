<?php

namespace App\GraphQL\Queries;

use App\Models\Transaction;
use App\Domain\Ranges\AllTime;
use App\Domain\Ranges\LastYear;
use Illuminate\Support\Facades\DB;
use App\Domain\Ranges\CurrentYear;
use App\Domain\Metrics\TrendMetric;
use App\Domain\Ranges\LastTwelveMonths;

class NetWorthTrend extends TrendMetric
{
    protected $name = 'Net Worth Over Time';

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

        // Query all income and expenses without range filter
        // Net worth is cumulative and needs all historical data
        $income = Transaction::query()
            ->income()
            ->select(DB::raw("date_format(created_at, '%Y-%m') as label, SUM(transactions.amount) as value"))
            ->groupBy(DB::raw("label"))
            ->orderBy("label")
            ->get()
            ->keyBy('label');

        $expenses = Transaction::query()
            ->expenses()
            ->select(DB::raw("date_format(created_at, '%Y-%m') as label, SUM(transactions.amount) as value"))
            ->groupBy(DB::raw("label"))
            ->orderBy("label")
            ->get()
            ->keyBy('label');

        // Get all unique labels (months)
        $allLabels = $income->keys()->merge($expenses->keys())->unique()->sort()->values();

        $runningNetWorth = 0;
        $results = [];

        // Calculate running net worth for all time
        foreach ($allLabels as $label) {
            $incomeValue = $income->get($label)->value ?? 0;
            $expenseValue = $expenses->get($label)->value ?? 0;
            
            $runningNetWorth += ($incomeValue - $expenseValue);
            
            $results[] = [
                'label' => $label,
                'value' => $runningNetWorth
            ];
        }

        // Filter results based on the selected range
        if ($rangeData) {
            $results = array_filter($results, function($item) use ($rangeData) {
                // Convert label (Y-m format) to date for comparison
                $itemDate = $item['label'] . '-01';
                return $itemDate >= $rangeData->start() && $itemDate <= $rangeData->end();
            });
            
            // Re-index the array to ensure sequential keys
            $results = array_values($results);
        }

        return $results;
    }
}

