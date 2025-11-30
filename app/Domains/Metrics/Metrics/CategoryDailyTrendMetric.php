<?php

namespace App\Domains\Metrics\Metrics;

use Carbon\Carbon;
use App\Domains\Metrics\Metric;
use App\Domains\Transaction\Models\Transaction;
use Illuminate\Support\Facades\DB;

class CategoryDailyTrendMetric extends Metric
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
        if (!$rangeData) {
            return [];
        }

        $dateFormat = $this->getDateFormat('%Y-%m-%d');

        $transactions = Transaction::whereHas('brand.category', fn($q) => $q->where('id', $this->categoryId))
            ->whereBetween('transactions.created_at', [$rangeData->start(), $rangeData->end()])
            ->select(DB::raw("{$dateFormat} as label, SUM(transactions.amount) as value"))
            ->groupBy(DB::raw("label"))
            ->orderBy('label')
            ->get()
            ->keyBy('label');

        $startDate = Carbon::parse($rangeData->start());
        $endDate = Carbon::parse($rangeData->end());
        $currentDate = $startDate->copy();
        $results = [];

        while ($currentDate->lte($endDate)) {
            $date = $currentDate->format('Y-m-d');
            $results[] = [
                'label' => $date,
                'value' => $transactions->get($date)->value ?? 0,
            ];
            $currentDate->addDay();
        }

        return $results;
    }
}
