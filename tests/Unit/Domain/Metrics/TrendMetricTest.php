<?php

namespace Tests\Unit\Domain\Ranges\Metrics\Metrics;

use App\Domain\Metrics\TrendMetric;
use Tests\TestCase;

class TrendMetricTest extends TestCase
{
    public function test_it_has_correct_component()
    {
        $sut = new FakeTrendMetric;

        $this->assertEquals('trend-metric', $sut->component());
    }

    public function test_it_has_show_standard_deviation_flag()
    {
        $sut = new FakeTrendMetric;

        $this->assertEquals(false, $sut->jsonSerialize()['show_standard_deviation']);
    }
}

class FakeTrendMetric extends TrendMetric
{

}
