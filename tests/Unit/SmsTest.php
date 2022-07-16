<?php

namespace Tests\Unit;

use App\Models\Sms;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SmsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_body()
    {
        $sut = Sms::factory()->create(['body' => 'someName']);

        $this->assertEquals("someName", $sut->body);
    }

    /** @test */
    public function it_has_meta()
    {
        $meta = ['key' => 'value'];

        $sut = Sms::factory()->create(['meta' => $meta]);

        $this->assertEquals($meta, $sut->meta);
    }

    /** @test */
    public function it_may_not_belongs_to_transaction()
    {
        $sut = Sms::factory()
                    ->create();

        $this->assertNull($sut->transaction);
    }

    /** @test */
    public function it_can_belongs_to_transaction()
    {
        $sut = Sms::factory()
                    ->forTransaction(['amount' => 1001])
                    ->create();

        $this->assertEquals(1001, $sut->transaction->amount);
    }
}
