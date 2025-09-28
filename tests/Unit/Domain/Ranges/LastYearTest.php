<?php

namespace Tests\Unit\Domain\Ranges\Ranges;

use App\Domain\Ranges\LastYear;
use Carbon\Carbon;
use Tests\TestCase;

class LastYearTest extends TestCase
{
    public function test_it_has_correct_json_serializeable()
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
