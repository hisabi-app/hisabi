<?php

namespace Tests\Unit;

use Carbon\Carbon;
use Tests\TestCase;
use App\Domain\Ranges\CurrentYear;

class CurrentYearTest extends TestCase
{
    /** @test */
    public function it_has_corrent_json_serializeable()
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
}
