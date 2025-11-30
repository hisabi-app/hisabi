<?php

namespace App\Domains\Metrics\Metrics;

use App\Domains\Metrics\Metric;
use App\Domains\Category\Models\Category;
use Illuminate\Support\Facades\DB;

class ExpensesByCategoryMetric extends Metric
{
    public function calculate(): array
    {
        $rangeData = $this->getRange();

        $query = Category::query()
            ->where('type', Category::EXPENSES)
            ->join('brands', 'brands.category_id', '=', 'categories.id')
            ->join('transactions', 'transactions.brand_id', '=', 'brands.id')
            ->select("categories.name as label", DB::raw("SUM(transactions.amount) as value"))
            ->groupBy("categories.id")
            ->orderBy('value', 'DESC');

        if ($rangeData) {
            $query->whereBetween('transactions.created_at', [$rangeData->start(), $rangeData->end()]);
        }

        return $query->get()->toArray();
    }
}
