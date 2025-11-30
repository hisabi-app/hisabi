<?php

namespace App\Domains\Metrics\Metrics;

use App\Domains\Metrics\Metric;
use App\Domains\Transaction\Models\Transaction;

class TotalIncomeMetric extends Metric
{
    public function calculate(): array
    {
        $query = Transaction::query()->income();

        if ($this->hasDateRange()) {
            $query->whereBetween('created_at', [$this->getStartDate(), $this->getEndDate()]);
        }

        $previous = 0;
        $previousRange = $this->getPreviousRange();
        if ($previousRange) {
            $previous = Transaction::query()
                ->income()
                ->whereBetween('created_at', [$previousRange['start'], $previousRange['end']])
                ->sum('amount');
        }

        return [
            'value' => $query->sum('amount'),
            'previous' => $previous
        ];
    }
}
