<?php

namespace Tests\Unit\Domain\Ranges\Metrics\Metrics;

use App\Domain\Metrics\ValueMetric;
use Tests\TestCase;

class ValueMetricTest extends TestCase
{
    /** @test */
    public function it_has_correct_component()
    {
        $sut = new FakeValueMetric;

        $this->assertEquals('value-metric', $sut->component());
    }
}

class FakeValueMetric extends ValueMetric
{

}
