<?php

namespace Tests\Unit;

use Carbon\Carbon;
use Tests\TestCase;
use App\Domain\Ranges\CurrentMonth;

class CurrentMonthTest extends TestCase
{
    /** @test */
    public function it_has_corrent_json_serializeable()
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
}
