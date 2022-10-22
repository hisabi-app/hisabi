<?php

namespace App\Domain\Metrics;

abstract class TrendMetric extends Metric
{
    protected $component = 'trend-metric';
    protected $showStandardDeviation = false;

    public function jsonSerialize(): mixed
    {
        return array_merge(parent::jsonSerialize(), [
            'show_standard_deviation' => $this->showStandardDeviation
        ]);
    }
}