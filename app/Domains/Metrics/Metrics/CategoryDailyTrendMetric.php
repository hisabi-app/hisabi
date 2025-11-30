<?php

namespace App\Domains\Metrics\Metrics;

use Carbon\Carbon;
use App\Domains\Metrics\Metric;
use App\Domains\Transaction\Models\Transaction;
use Illuminate\Support\Facades\DB;

class CategoryDailyTrendMetric extends Metric
{
    protected int $categoryId;

    public function __construct(?string $from, ?string $to, int $categoryId)
    {
        parent::__construct($from, $to);
        $this->categoryId = $categoryId;
    }

    public function calculate(): array
    {
        if (!$this->hasDateRange()) {
            return [];
        }

        $dateFormat = $this->getDateFormat('%Y-%m-%d');

        $transactions = Transaction::whereHas('brand.category', fn($q) => $q->where('id', $this->categoryId))
            ->whereBetween('transactions.created_at', [$this->getStartDate(), $this->getEndDate()])
            ->select(DB::raw("{$dateFormat} as label, SUM(transactions.amount) as value"))
            ->groupBy(DB::raw("label"))
            ->orderBy('label')
            ->get()
            ->keyBy('label');

        $startDate = Carbon::parse($this->getStartDate());
        $endDate = Carbon::parse($this->getEndDate());
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
