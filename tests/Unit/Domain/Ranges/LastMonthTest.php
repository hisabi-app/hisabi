<?php

namespace Tests\Unit\Domain\Ranges\Ranges;

use App\Domain\Ranges\LastMonth;
use Carbon\Carbon;
use Tests\TestCase;

class LastMonthTest extends TestCase
{
    /** @test */
    public function it_has_correct_json_serializeable()
    {
        // mock app date
        Carbon::setTestNow(Carbon::create(2021, 1, 18));

        $sut = new LastMonth;

        $this->assertEquals([
            'key' => 'last-month',
            'name' => 'Last Month',
            'start' => '2020-12-01',
            'end' => '2020-12-31',
        ], $sut->jsonSerialize());
    }
}
