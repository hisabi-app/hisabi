<?php

namespace Tests\Unit;

use Carbon\Carbon;
use Tests\TestCase;
use App\Domain\Ranges\LastTwelveMonths;

class LastTwelveMonthsTest extends TestCase
{
    /** @test */
    public function it_has_corrent_json_serializeable()
    {
        // mock app date
        Carbon::setTestNow(Carbon::create(2021, 1, 1));

        $sut = new LastTwelveMonths;

        $this->assertEquals([
            'key' => 'last-twelve-months',
            'name' => 'Last Twelve Months',
            'start' => '2020-01-01',
            'end' => '2021-01-01',
        ], $sut->jsonSerialize());
    }
}
