<?php

namespace App\Domains\Metrics\Metrics;

use App\Domains\Metrics\Metric;
use App\Domains\Category\Models\Category;
use Illuminate\Support\Facades\DB;

class IncomeByCategoryMetric extends Metric
{
    public function calculate(): array
    {
        $query = Category::query()
            ->where('type', Category::INCOME)
            ->join('brands', 'brands.category_id', '=', 'categories.id')
            ->join('transactions', 'transactions.brand_id', '=', 'brands.id')
            ->select("categories.name as label", DB::raw("SUM(transactions.amount) as value"))
            ->groupBy("categories.id")
            ->orderBy('value', 'DESC');

        if ($this->hasDateRange()) {
            $query->whereBetween('transactions.created_at', [$this->getStartDate(), $this->getEndDate()]);
        }

        return $query->get()->toArray();
    }
}
