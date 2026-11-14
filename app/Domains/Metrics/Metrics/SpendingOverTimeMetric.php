<?php

namespace App\Domains\Metrics\Metrics;

use App\Domains\Metrics\Metric;
use App\Domains\Transaction\Models\Transaction;
use Carbon\Carbon;

/**
 * Spending per day or per month across the range, with empty periods as zero so every bar has a place.
 */
class SpendingOverTimeMetric extends Metric
{
    public function __construct(?string $from = null, ?string $to = null, private readonly string $by = 'month')
    {
        parent::__construct($from, $to);
    }

    public function calculate(): array
    {
        $byDay = $this->by === 'day';
        $range = $this->getDayRange();

        $query = Transaction::query()
            ->expenses()
            ->selectRaw(($byDay ? 'DATE(created_at)' : $this->getDateFormat('%Y-%m')) . ' as period, SUM(amount) as total')
            ->groupBy('period');

        if ($range) {
            $query->whereBetween('created_at', $range);
        }

        $totals = $query->pluck('total', 'period');

        if ($totals->isEmpty() && ! $range) {
            return [];
        }

        // Without a range, run from the first month with spending to now
        [$from, $to] = $range ?? [Carbon::parse($totals->keys()->min() . ($byDay ? '' : '-01')), now()];
        $cursor = $byDay ? $from->copy()->startOfDay() : $from->copy()->startOfMonth();
        $last = $to->copy()->min(now());
        $series = [];

        while ($cursor->lte($last)) {
            $key = $cursor->format($byDay ? 'Y-m-d' : 'Y-m');
            $series[] = ['period' => $key, 'total' => round((float) ($totals[$key] ?? 0), 2)];
            $byDay ? $cursor->addDay() : $cursor->addMonthNoOverflow();
        }

        return $series;
    }
}
