<?php

namespace App\Domains\Metrics\Metrics;

use App\Domains\Metrics\Metric;
use App\Domains\Transaction\Models\Transaction;

class TotalCashMetric extends Metric
{
    public function calculate(): array
    {
        $income = Transaction::income()->sum('amount');
        $expenses = Transaction::expenses()->sum('amount');
        $investment = Transaction::investment()->sum('amount');
        $savings = Transaction::savings()->sum('amount');

        return [
            'value' => $income - ($expenses + $investment + $savings)
        ];
    }
}
