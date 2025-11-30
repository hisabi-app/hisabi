<?php

namespace App\Domains\Metrics\Metrics;

use App\Domains\Metrics\Metric;
use App\Domains\Transaction\Models\Transaction;
use Illuminate\Support\Facades\DB;

class BrandTrendMetric extends Metric
{
    protected int $brandId;

    public function __construct(?string $range, int $brandId)
    {
        parent::__construct($range);
        $this->brandId = $brandId;
    }

    public function calculate(): array
    {
        $rangeData = $this->getRange();
        $dateFormat = $this->getDateFormat('%Y-%m');

        $query = Transaction::query()
            ->whereHas('brand', fn($q) => $q->where('id', $this->brandId))
            ->select(DB::raw("{$dateFormat} as label, SUM(transactions.amount) as value"))
            ->groupBy(DB::raw("label"))
            ->orderBy("label");

        if ($rangeData) {
            $query->whereBetween('transactions.created_at', [$rangeData->start(), $rangeData->end()]);
        }

        return $query->get()->toArray();
    }
}
