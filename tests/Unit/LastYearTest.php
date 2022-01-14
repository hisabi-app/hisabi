<?php

namespace Tests\Unit;

use Carbon\Carbon;
use Tests\TestCase;
use App\Domain\Ranges\LastYear;

class LastYearTest extends TestCase
{
    /** @test */
    public function it_has_corrent_json_serializeable()
    {
        // mock app date
        Carbon::setTestNow(Carbon::create(2021, 5, 18));

        $sut = new LastYear;

        $this->assertEquals([
            'key' => 'last-year',
            'name' => 'Last Year',
            'start' => '2020-01-01',
            'end' => '2020-12-31',
        ], $sut->jsonSerialize());
    }
}
