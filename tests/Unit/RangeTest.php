<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Domain\Ranges\Range;

class RangeTest extends TestCase
{
    /** @test */
    public function it_is_abstract_class()
    {
        $this->expectException(\Error::class);

        new Range;
    }

    /** @test */
    public function it_has_default_name()
    {
        $sut = new FakeRange;

        $this->assertEquals('Fake Range', $sut->name());
    }

    /** @test */
    public function it_has_default_key()
    {
        $sut = new FakeRange;

        $this->assertEquals('fake-range', $sut->key());
    }

    /** @test */
    public function it_is_json_serializeable()
    {
        $sut = new FakeRange;

        $this->assertEquals([
            'key' => 'fake-range',
            'name' => 'Fake Range',
            'start' => 'startTime',
            'end' => 'endTime'
        ], $sut->jsonSerialize());
    }
}


class FakeRange extends Range
{
    public function start() {
        return "startTime";
    }
    
    public function end() {
        return "endTime";
    }
}