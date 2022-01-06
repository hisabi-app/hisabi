<?php

namespace Tests\Unit;

use App\Models\Sms;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SmsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function class_has_expenses_constant()
    {
        $this->assertEquals(Sms::EXPENSES, "EXPENSES");
    }

    /** @test */
    public function class_has_income_constant()
    {
        $this->assertEquals(Sms::INCOME, "INCOME");
    }

    /** @test */
    public function class_has_unknown_constant()
    {
        $this->assertEquals(Sms::UNKNOWN, "UNKNOWN");
    }

    /** @test */
    public function it_has_body()
    {
        $sut = Sms::factory()->make(['name' => 'someName']);

        $this->assertEquals("someName", $sut->name);
    }

    /** @test */
    public function it_has_type()
    {
        $sut = Sms::factory()->make(['type' => Sms::EXPENSES]);

        $this->assertEquals(Sms::EXPENSES, $sut->type);
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
