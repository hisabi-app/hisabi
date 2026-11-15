<?php

namespace App\Domains\Metrics\Metrics;

use App\Domains\Metrics\Metric;
use App\Domains\Transaction\Models\Transaction;
use Carbon\Carbon;

/**
 * Net worth at the end of every day or month across the range, carrying in everything from before it.
 * The series starts at the first transaction when the range starts earlier, and stops today.
 */
class NetWorthTrendMetric extends Metric
{
    public function __construct(?string $from = null, ?string $to = null, private readonly string $by = 'month')
    {
        parent::__construct($from, $to);
    }

    public function calculate(): array
    {
        $byDay = $this->by === 'day';
        $keyFormat = $byDay ? 'Y-m-d' : 'Y-m';
        $period = $byDay ? 'DATE(created_at)' : $this->getDateFormat('%Y-%m');
        $range = $this->getDayRange();
        // Nothing dated after today counts yet
        $to = $range ? $range[1]->min(now()->endOfDay()) : now()->endOfDay();

        $totals = fn ($query) => $query
            ->where('created_at', '<=', $to)
            ->selectRaw("{$period} as period, SUM(amount) as total")
            ->groupBy('period')
            ->pluck('total', 'period');
        $income = $totals(Transaction::query()->income());
        $expenses = $totals(Transaction::query()->expenses());
        $periods = $income->keys()->merge($expenses->keys())->unique();

        if ($periods->isEmpty()) {
            return [];
        }

        $from = Carbon::parse($periods->min() . ($byDay ? '' : '-01'));
        $from = $range ? $from->max($range[0]) : $from;
        $cursor = $byDay ? $from->copy()->startOfDay() : $from->copy()->startOfMonth();
        $net = fn ($key) => (float) ($income[$key] ?? 0) - (float) ($expenses[$key] ?? 0);

        $first = $cursor->format($keyFormat);
        $running = $periods->filter(fn ($key) => $key < $first)->sum($net);
        $series = [];

        while ($cursor->lte($to)) {
            $key = $cursor->format($keyFormat);
            $running += $net($key);
            $series[] = ['label' => $key, 'value' => round($running, 2)];
            $byDay ? $cursor->addDay() : $cursor->addMonthNoOverflow();
        }

        return $series;
    }
}
