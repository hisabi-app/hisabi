<?php

namespace App\Domains\Metrics\Metrics;

use App\Domains\Metrics\Metric;
use App\Domains\Brand\Models\Brand;
use Illuminate\Support\Facades\DB;

class SpendingByBrandMetric extends Metric
{
    protected int $categoryId;

    public function __construct(?string $from, ?string $to, int $categoryId)
    {
        parent::__construct($from, $to);
        $this->categoryId = $categoryId;
    }

    public function calculate(): array
    {
        $query = Brand::query()
            ->where('category_id', $this->categoryId)
            ->join('transactions', 'transactions.brand_id', '=', 'brands.id')
            ->select("brands.name as label", DB::raw("SUM(transactions.amount) as value"))
            ->groupBy("brands.id")
            ->orderBy('value', 'DESC');

        if ($this->hasDateRange()) {
            $query->whereBetween('transactions.created_at', [$this->getStartDate(), $this->getEndDate()]);
        }

        return $query->get()->toArray();
    }
}
