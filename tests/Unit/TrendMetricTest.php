<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Domain\Metrics\TrendMetric;

class TrendMetricTest extends TestCase
{
    /** @test */
    public function it_is_abstract_class()
    {
        $this->expectException(\Error::class);

        new TrendMetric;
    }

    /** @test */
    public function it_has_correct_component()
    {
        $sut = new FakeTrendMetric;

        $this->assertEquals('trend-metric', $sut->component());
    }
}

class FakeTrendMetric extends TrendMetric
{

}
