<?php

namespace App\Domains\Metrics\Metrics;

use App\Domains\Metrics\Metric;
use App\Domains\Transaction\Models\Transaction;
use Illuminate\Support\Facades\DB;

class BrandTrendMetric extends Metric
{
    protected int $brandId;

    public function __construct(?string $from, ?string $to, int $brandId)
    {
        parent::__construct($from, $to);
        $this->brandId = $brandId;
    }

    public function calculate(): array
    {
        $dateFormat = $this->getDateFormat('%Y-%m');

        $query = Transaction::query()
            ->whereHas('brand', fn($q) => $q->where('id', $this->brandId))
            ->select(DB::raw("{$dateFormat} as label, SUM(transactions.amount) as value"))
            ->groupBy(DB::raw("label"))
            ->orderBy("label");

        if ($this->hasDateRange()) {
            $query->whereBetween('transactions.created_at', [$this->getStartDate(), $this->getEndDate()]);
        }

        return $query->get()->toArray();
    }
}
