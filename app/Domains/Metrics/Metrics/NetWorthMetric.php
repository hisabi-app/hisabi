<?php

namespace App\Domains\Metrics\Metrics;

use App\Domains\Metrics\Metric;
use App\Domains\Transaction\Models\Transaction;

class NetWorthMetric extends Metric
{
    public function calculate(): array
    {
        $income = Transaction::income()->sum('amount');
        $expenses = Transaction::expenses()->sum('amount');

        return [
            'value' => $income - $expenses
        ];
    }
}
