<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Domain\Ranges\Range;
use App\Domain\Metrics\Metric;

class MetricTest extends TestCase
{
    /** @test */
    public function it_is_abstract_class()
    {
        $this->expectException(\Error::class);

        new Metric;
    }

    /** @test */
    public function it_has_component()
    {
        $sut = new FakeMetric;

        $this->assertEquals('test-component', $sut->component());
    }

    /** @test */
    public function it_has_default_name()
    {
        $sut = new FakeMetric;

        $this->assertEquals('Fake Metric', $sut->name());
    }

    /** @test */
    public function it_has_default_width()
    {
        $sut = new FakeMetric;

        $this->assertEquals('1/2', $sut->width());
    }

     /** @test */
     public function it_has_set_width_method()
     {
         $sut = new FakeMetric;
 
         $this->assertEquals('1/4', $sut->setWidth('1/4')->width());
     }

    /** @test */
    public function it_has_default_ranges()
    {
        $sut = new FakeMetric;

        foreach($sut->ranges() as $range) {
            $this->assertInstanceOf(Range::class, $range);
        }
    }

    /** @test */
    public function it_has_default_graphql_query()
    {
        $sut = new FakeMetric;

        $this->assertEquals('fakeMetric', $sut->graphqlQuery());
    }

    /** @test */
    public function it_is_json_serializeable()
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