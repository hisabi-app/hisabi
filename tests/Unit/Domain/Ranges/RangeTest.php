<?php

namespace Tests\Unit\Domain\Ranges;

use App\Domain\Ranges\Range;
use Tests\TestCase;

class RangeTest extends TestCase
{
    public function test_it_has_default_name()
    {
        $sut = new FakeRange;

        $this->assertEquals('Fake Range', $sut->name());
    }

    public function test_it_has_default_key()
    {
        $sut = new FakeRange;

        $this->assertEquals('fake-range', $sut->key());
    }

    public function test_it_is_json_serializeable()
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
