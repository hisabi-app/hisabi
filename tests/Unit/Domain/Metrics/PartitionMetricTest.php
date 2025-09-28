<?php

namespace Tests\Unit\Domain\Ranges\Metrics\Metrics;

use App\Domain\Metrics\PartitionMetric;
use Tests\TestCase;

class PartitionMetricTest extends TestCase
{
    public function test_it_has_correct_component()
    {
        $sut = new FakePartitionMetric;

        $this->assertEquals('partition-metric', $sut->component());
    }
}

class FakePartitionMetric extends PartitionMetric
{
}
