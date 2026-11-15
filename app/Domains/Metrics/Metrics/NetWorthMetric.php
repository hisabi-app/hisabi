<?php

namespace App\Domains\Metrics\Metrics;

use App\Domains\Metrics\Metric;
use App\Domains\Transaction\Models\Transaction;
use Illuminate\Database\Eloquent\Builder;

class NetWorthMetric extends Metric
{
    public function calculate(): array
    {
        $range = $this->getDayRange();
        // Nothing dated after today counts yet
        $end = $range ? $range[1]->min(now()->endOfDay()) : now()->endOfDay();
        $value = $this->net(Transaction::where('created_at', '<=', $end));

        if (! $range) {
            return ['value' => $value, 'change' => null];
        }

        // Where it stood when the range ended, and how much the range itself added or took away
        return ['value' => $value, 'change' => $this->net(Transaction::whereBetween('created_at', [$range[0], $end]))];
    }

    private function net(Builder $query): float
    {
        return (float) $query->clone()->income()->sum('amount') - (float) $query->clone()->expenses()->sum('amount');
    }
}
