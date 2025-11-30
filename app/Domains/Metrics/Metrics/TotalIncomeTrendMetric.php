<?php

namespace App\Domains\Metrics\Metrics;

use App\Domains\Metrics\Metric;
use App\Domains\Transaction\Models\Transaction;
use Illuminate\Support\Facades\DB;

class TotalIncomeTrendMetric extends Metric
{
    public function calculate(): array
    {
        $rangeData = $this->getRange();
        $dateFormat = $this->getDateFormat('%Y-%m');

        $query = Transaction::query()
            ->income()
            ->select(DB::raw("{$dateFormat} as label, SUM(transactions.amount) as value"))
            ->groupBy(DB::raw("label"))
            ->orderBy("label");

        if ($rangeData) {
            $query->whereBetween('transactions.created_at', [$rangeData->start(), $rangeData->end()]);
        }

        return $query->get()->toArray();
    }
}
