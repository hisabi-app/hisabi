<?php

namespace App\Domains\Metrics\Metrics;

use App\Domains\Metrics\Metric;
use App\Domains\Transaction\Models\Transaction;

/**
 * Where the income went in the range: spent, saved, invested. What's left stayed in cash.
 */
class IncomeAllocationMetric extends Metric
{
    public function calculate(): array
    {
        $range = $this->getDayRange();
        $total = function (string $scope) use ($range) {
            $query = Transaction::query()->{$scope}();

            if ($range) {
                $query->whereBetween('created_at', $range);
            }

            return (float) $query->sum('amount');
        };

        return [
            'income' => $total('income'),
            'spent' => $total('expenses'),
            'saved' => $total('savings'),
            'invested' => $total('investment'),
        ];
    }
}
