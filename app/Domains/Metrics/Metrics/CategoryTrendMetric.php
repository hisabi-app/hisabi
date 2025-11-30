<?php

namespace App\Domains\Metrics\Metrics;

use App\Domains\Metrics\Metric;
use App\Domains\Transaction\Models\Transaction;
use Illuminate\Support\Facades\DB;

class CategoryTrendMetric extends Metric
{
    protected int $categoryId;

    public function __construct(?string $range, int $categoryId)
    {
        parent::__construct($range);
        $this->categoryId = $categoryId;
    }

    public function calculate(): array
    {
        $rangeData = $this->getRange();
        $dateFormat = $this->getDateFormat('%Y-%m');

        $query = Transaction::query()
            ->whereHas('brand.category', fn($q) => $q->where('id', $this->categoryId))
            ->select(DB::raw("{$dateFormat} as label, SUM(transactions.amount) as value"))
            ->groupBy(DB::raw("label"))
            ->orderBy('label');

        if ($rangeData) {
            $query->whereBetween('transactions.created_at', [$rangeData->start(), $rangeData->end()]);
        }

        return $query->get()->toArray();
    }
}
