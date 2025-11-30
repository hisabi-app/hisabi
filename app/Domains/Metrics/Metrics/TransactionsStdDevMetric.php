<?php

namespace App\Domains\Metrics\Metrics;

use App\Domains\Metrics\Metric;
use App\Domains\Transaction\Models\Transaction;

class TransactionsStdDevMetric extends Metric
{
    protected int $categoryId;

    public function __construct(?string $from, ?string $to, int $categoryId)
    {
        parent::__construct($from, $to);
        $this->categoryId = $categoryId;
    }

    public function calculate(): array
    {
        $query = Transaction::query()
            ->whereHas('brand.category', fn($q) => $q->where('id', $this->categoryId));

        if ($this->hasDateRange()) {
            $query->whereBetween('transactions.created_at', [$this->getStartDate(), $this->getEndDate()]);
        }

        $amounts = $query->pluck('amount')->toArray();

        if (count($amounts) < 2) {
            return ['value' => 0];
        }

        $mean = array_sum($amounts) / count($amounts);
        $variance = array_sum(array_map(fn($x) => pow($x - $mean, 2), $amounts)) / (count($amounts) - 1);

        return ['value' => sqrt($variance)];
    }
}
