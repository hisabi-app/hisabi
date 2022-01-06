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
        $sut = Transaction::factory()->make(['amount' => 10]);

        $this->assertEquals(10, $sut->amount);
    }

    /** @test */
    public function it_belongs_to_category()
    {
        $sut = Transaction::factory()
                    ->forCategory(['name' => 'categoryTest'])
                    ->create();

        $this->assertEquals('categoryTest', $sut->category->name);
    }

    /** @test */
    public function it_may_not_belongs_to_brand()
    {
        $sut = Transaction::factory()
                    ->create();

        $this->assertNull($sut->brand);
    }

    /** @test */
    public function it_can_belongs_to_brand()
    {
        $sut = Transaction::factory()
                    ->forBrand(['name' => 'testName'])
                    ->create();

        $this->assertEquals('testName', $sut->brand->name);
    }
}
