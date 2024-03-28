<?php

namespace Tests\Unit\Models\Transactions;

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

    /** @test */
    public function is_does_search_about_amount_brand_or_note()
    {
        Transaction::factory()
            ->forBrand(['name' => 'some existing brand'])
            ->create(['amount' => 20.5, 'note' => 'this is a gift']);

        $this->assertCount(1, Transaction::search('20')->get());
        $this->assertCount(0, Transaction::search('50')->get());
        $this->assertCount(1, Transaction::search('existing')->get());
        $this->assertCount(1, Transaction::search('gift')->get());
    }
}
