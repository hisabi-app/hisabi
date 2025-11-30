<?php

namespace App\Domains\Metrics\Metrics;

use App\Domains\Metrics\Metric;
use App\Domains\Transaction\Models\Transaction;

class TotalSavingsMetric extends Metric
{
    public function calculate(): array
    {
        return [
            'value' => Transaction::savings()->sum('amount')
        ];
    }
}
