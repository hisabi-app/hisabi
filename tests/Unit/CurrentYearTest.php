<?php

namespace Tests\Unit;

use Carbon\Carbon;
use Tests\TestCase;
use App\Domain\Ranges\CurrentYear;
use App\Contracts\HasPreviousRange;

class CurrentYearTest extends TestCase
{
    /** @test */
    public function it_has_correct_json_serializeable()
    {
        // mock app date
        Carbon::setTestNow(Carbon::create(2021, 1, 18));

        $sut = new CurrentYear;

        $this->assertEquals([
            'key' => 'current-year',
            'name' => 'Current Year',
            'start' => '2021-01-01',
            'end' => '2021-12-31',
        ], $sut->jsonSerialize());
    }

    /** @test */
    public function it_implements_has_previous_range()
    {
        $this->assertInstanceOf(HasPreviousRange::class, new CurrentYear);
    }

    /** @test */
    public function it_has_previous_range_correct_start_and_end_dates()
    {
        // mock app date
        Carbon::setTestNow(Carbon::create(2021, 1, 18));

        $sut = new CurrentYear;

        $this->assertEquals('2021-01-01', $sut->start());
        $this->assertEquals('2021-12-31', $sut->end());
        $this->assertEquals('2020-01-01', $sut->previousRangeStart());
        $this->assertEquals('2020-12-31', $sut->previousRangeEnd());
    }
}
