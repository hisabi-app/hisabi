<?php

namespace App\Domains\Metrics\Metrics;

use App\Domains\Metrics\Metric;
use App\Domains\Transaction\Models\Transaction;
use Illuminate\Support\Facades\DB;

class TransactionsByBrandMetric extends Metric
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

        $query = Transaction::query()
            ->join('brands', 'brands.id', '=', 'transactions.brand_id')
            ->where('brands.category_id', $this->categoryId)
            ->select("brands.name as label", DB::raw("count(transactions.id) as value"))
            ->groupBy("brands.id")
            ->orderBy('value', 'DESC');

        if ($rangeData) {
            $query->whereBetween('transactions.created_at', [$rangeData->start(), $rangeData->end()]);
        }

        return $query->get()->toArray();
    }
}
