<?php

namespace Tests\Unit;

use Carbon\Carbon;
use Tests\TestCase;
use App\Domain\Ranges\CurrentMonth;
use App\Contracts\HasPreviousRange;

class CurrentMonthTest extends TestCase
{
    /** @test */
    public function it_has_correct_json_serializeable()
    {
        // mock app date
        Carbon::setTestNow(Carbon::create(2021, 1, 18));

        $sut = new CurrentMonth;

        $this->assertEquals([
            'key' => 'current-month',
            'name' => 'Current Month',
            'start' => '2021-01-01',
            'end' => '2021-01-31',
        ], $sut->jsonSerialize());
    }

    /** @test */
    public function it_implements_has_previous_range()
    {
        $this->assertInstanceOf(HasPreviousRange::class, new CurrentMonth);
    }

    /** @test */
    public function it_has_previous_range_correct_start_and_end_dates()
    {
        // mock app date
        Carbon::setTestNow(Carbon::create(2021, 1, 18));

        $sut = new CurrentMonth;

        $this->assertEquals('2021-01-01', $sut->start());
        $this->assertEquals('2021-01-31', $sut->end());
        $this->assertEquals('2020-12-01', $sut->previousRangeStart());
        $this->assertEquals('2020-12-31', $sut->previousRangeEnd());
    }
}
