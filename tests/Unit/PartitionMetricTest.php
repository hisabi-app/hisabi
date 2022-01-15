<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Domain\Metrics\PartitionMetric;

class PartitionMetricTest extends TestCase
{
    /** @test */
    public function it_is_abstract_class()
    {
        $this->expectException(\Error::class);

        new PartitionMetric;
    }

    /** @test */
    public function it_has_correct_component()
    {
        $sut = new FakePartitionMetric;

        $this->assertEquals('partition-metric', $sut->component());
    }
}

class FakePartitionMetric extends PartitionMetric
{

}