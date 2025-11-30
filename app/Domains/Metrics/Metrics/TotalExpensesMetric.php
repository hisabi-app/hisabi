<?php

namespace App\Domains\Metrics\Metrics;

use App\Domains\Metrics\Metric;
use App\Domains\Transaction\Models\Transaction;

class TotalExpensesMetric extends Metric
{
    public function calculate(): array
    {
        $rangeData = $this->getRange();
        $query = Transaction::query()->expenses();

        if ($rangeData) {
            $query->whereBetween('created_at', [$rangeData->start(), $rangeData->end()]);
        }

        $previous = 0;
        if ($this->hasPreviousRange($rangeData)) {
            $previous = Transaction::query()
                ->expenses()
                ->whereBetween('created_at', [$rangeData->previousRangeStart(), $rangeData->previousRangeEnd()])
                ->sum('amount');
        }

        return [
            'value' => $query->sum('amount'),
            'previous' => $previous
        ];
    }
}
