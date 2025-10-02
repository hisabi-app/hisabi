<?php

namespace Tests\Unit\Models\Sms;

use App\Models\Sms;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SmsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_has_body()
    {
        $sut = Sms::factory()->create(['body' => 'someName']);

        $this->assertEquals("someName", $sut->body);
    }

    public function test_it_has_meta()
    {
        $meta = ['key' => 'value'];

        $sut = Sms::factory()->create(['meta' => $meta]);

        $this->assertEquals($meta, $sut->meta);
    }

    public function test_it_may_not_belongs_to_transaction()
    {
        $sut = Sms::factory()
                    ->create();

        $this->assertNull($sut->transaction);
    }

    public function test_it_can_belongs_to_transaction()
    {
        $sut = Sms::factory()
                    ->forTransaction(['amount' => 1001])
                    ->create();

        $this->assertEquals(1001, $sut->transaction->amount);
    }

    public function test_is_does_search_about_amount_brand_or_note()
    {
        Sms::factory()->create(['body' => 'body of the sms']);

        $this->assertCount(0, Sms::search('goo')->get());
        $this->assertCount(1, Sms::search('dy of t')->get());
    }
}
