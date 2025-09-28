<?php

namespace Tests\Unit\Domain\Ranges\Metrics;

use App\Domain\Metrics\Metric;
use App\Domain\Ranges\Range;
use Tests\TestCase;

class MetricTest extends TestCase
{
    public function test_it_has_component()
    {
        $sut = new FakeMetric;

        $this->assertEquals('test-component', $sut->component());
    }

    public function test_it_has_default_name()
    {
        $sut = new FakeMetric;

        $this->assertEquals('Fake Metric', $sut->name());
    }

    public function test_it_has_default_width()
    {
        $sut = new FakeMetric;

        $this->assertEquals('1/2', $sut->width());
    }

     public function test_it_has_set_width_method()
     {
         $sut = new FakeMetric;

         $this->assertEquals('1/4', $sut->setWidth('1/4')->width());
     }

    public function test_it_has_default_ranges()
    {
        $sut = new FakeMetric;

        foreach($sut->ranges() as $range) {
            $this->assertInstanceOf(Range::class, $range);
        }
    }

    public function test_it_has_default_graphql_query()
    {
        $sut = new FakeMetric;

        $this->assertEquals('fakeMetric', $sut->graphqlQuery());
    }

    public function test_it_is_json_serializeable()
    {
        $sut = new FakeMetric;

        $this->assertArrayHasKey('component', $sut->jsonSerialize());
        $this->assertArrayHasKey('name', $sut->jsonSerialize());
        $this->assertArrayHasKey('width', $sut->jsonSerialize());
        $this->assertArrayHasKey('ranges', $sut->jsonSerialize());
        $this->assertArrayHasKey('graphql_query', $sut->jsonSerialize());
    }
}


class FakeMetric extends Metric
{
    protected $component = 'test-component';
}
