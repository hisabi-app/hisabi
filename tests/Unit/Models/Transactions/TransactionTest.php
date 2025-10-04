<?php

namespace Tests\Unit\Models\Transactions;

use Tests\TestCase;
use App\Domains\Transaction\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_has_amount()
    {
        $sut = Transaction::factory()->create(['amount' => 10]);

        $this->assertEquals(10, $sut->amount);
    }

    public function test_it_can_have_note()
    {
        $sut = Transaction::factory()->create(['note' => 'some note']);

        $this->assertEquals('some note', $sut->note);
    }

    public function test_it_belongs_to_brand()
    {
        $sut = Transaction::factory()
                    ->forBrand(['name' => 'testName'])
                    ->create();

        $this->assertEquals('testName', $sut->brand->name);
    }
}
