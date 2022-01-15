<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Domain\Metrics\ValueMetric;

class ValueMetricTest extends TestCase
{
    /** @test */
    public function it_is_abstract_class()
    {
        $this->expectException(\Error::class);

        new ValueMetric;
    }

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