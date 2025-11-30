<?php

namespace App\Domains\Metrics\Metrics;

use App\Domains\Metrics\Metric;
use App\Domains\Transaction\Models\Transaction;

class TotalIncomeMetric extends Metric
{
    public function calculate(): array
    {
        $rangeData = $this->getRange();
        $query = Transaction::query()->income();

        if ($rangeData) {
            $query->whereBetween('created_at', [$rangeData->start(), $rangeData->end()]);
        }

        $previous = 0;
        if ($this->hasPreviousRange($rangeData)) {
            $previous = Transaction::query()
                ->income()
                ->whereBetween('created_at', [$rangeData->previousRangeStart(), $rangeData->previousRangeEnd()])
                ->sum('amount');
        }

        return [
            'value' => $query->sum('amount'),
            'previous' => $previous
        ];
    }
}
