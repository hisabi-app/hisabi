<?php

namespace App\Domains\Metrics\Metrics;

use App\Domains\Metrics\Metric;
use App\Domains\Transaction\Models\Transaction;
use Illuminate\Support\Facades\DB;

class TransactionsCountMetric extends Metric
{
    public function calculate(): array
    {
        $rangeData = $this->getRange();

        $query = Transaction::query()
            ->join('brands', 'brands.id', '=', 'transactions.brand_id')
            ->join('categories', 'categories.id', '=', 'brands.category_id')
            ->select("categories.type as label", DB::raw("count(transactions.id) as value"))
            ->groupBy("categories.type")
            ->orderBy('value', 'DESC');

        if ($rangeData) {
            $query->whereBetween('transactions.created_at', [$rangeData->start(), $rangeData->end()]);
        }

        return $query->get()->toArray();
    }
}
