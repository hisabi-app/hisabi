<?php

namespace App\Domains\Metrics\Metrics;

use App\Domains\Metrics\Metric;
use App\Domains\Transaction\Models\Transaction;
use Illuminate\Support\Facades\DB;

class BrandChangeRateMetric extends Metric
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

        $query = Transaction::where('brand_id', $this->brandId)
            ->selectRaw("{$dateFormat} as label, SUM(amount) as value")
            ->groupBy(DB::raw("label"))
            ->orderBy('label');

        if ($rangeData) {
            $query->whereBetween('transactions.created_at', [$rangeData->start(), $rangeData->end()]);
        }

        $data = $query->get();
        $changeRates = [];

        $data->each(function ($item, $key) use ($data, &$changeRates) {
            if ($key > 0) {
                $previousAmount = $data[$key - 1]->value;
                $changeRate = $previousAmount > 0 ? ($item->value - $previousAmount) / $previousAmount * 100 : 0;
                $changeRates[] = ["label" => "$item->label ($item->value)", "value" => $changeRate];
            } else {
                $changeRates[] = ["label" => "$item->label ($item->value)", "value" => 0];
            }
        });

        return $changeRates;
    }
}
