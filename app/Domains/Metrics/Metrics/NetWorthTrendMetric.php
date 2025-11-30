<?php

namespace App\Domains\Metrics\Metrics;

use App\Domains\Metrics\Metric;
use App\Domains\Transaction\Models\Transaction;
use Illuminate\Support\Facades\DB;

class NetWorthTrendMetric extends Metric
{
    public function calculate(): array
    {
        $dateFormat = $this->getDateFormat('%Y-%m');

        $income = Transaction::query()
            ->income()
            ->select(DB::raw("{$dateFormat} as label, SUM(transactions.amount) as value"))
            ->groupBy(DB::raw("label"))
            ->orderBy("label")
            ->get()
            ->keyBy('label');

        $expenses = Transaction::query()
            ->expenses()
            ->select(DB::raw("{$dateFormat} as label, SUM(transactions.amount) as value"))
            ->groupBy(DB::raw("label"))
            ->orderBy("label")
            ->get()
            ->keyBy('label');

        $allLabels = $income->keys()->merge($expenses->keys())->unique()->sort()->values();

        $runningNetWorth = 0;
        $results = [];

        foreach ($allLabels as $label) {
            $incomeValue = $income->get($label)->value ?? 0;
            $expenseValue = $expenses->get($label)->value ?? 0;
            $runningNetWorth += ($incomeValue - $expenseValue);
            $results[] = ['label' => $label, 'value' => $runningNetWorth];
        }

        if ($this->hasDateRange()) {
            $results = array_filter($results, function ($item) {
                $itemDate = $item['label'] . '-01';
                return $itemDate >= $this->getStartDate() && $itemDate <= $this->getEndDate();
            });
            $results = array_values($results);
        }

        return $results;
    }
}
