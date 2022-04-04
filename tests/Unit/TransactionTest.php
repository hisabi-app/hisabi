<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_amount()
    {
        $sut = Transaction::factory()->create(['amount' => 10]);

        $this->assertEquals(10, $sut->amount);
    }

    /** @test */
    public function it_can_have_note()
    {
        $sut = Transaction::factory()->create(['note' => 'some note']);

        $this->assertEquals('some note', $sut->note);
    }

    /** @test */
    public function it_belongs_to_brand()
    {
        $sut = Transaction::factory()
                    ->forBrand(['name' => 'testName'])
                    ->create();

        $this->assertEquals('testName', $sut->brand->name);
    }
}
