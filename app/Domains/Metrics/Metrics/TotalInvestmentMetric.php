<?php

namespace App\Domains\Metrics\Metrics;

use App\Domains\Metrics\Metric;
use App\Domains\Transaction\Models\Transaction;

class TotalInvestmentMetric extends Metric
{
    public function calculate(): array
    {
        return [
            'value' => Transaction::investment()->sum('amount')
        ];
    }
}
