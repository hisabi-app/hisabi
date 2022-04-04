<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Brand;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BrandTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_name()
    {
        $sut = Brand::factory()->create(['name' => 'test']);

        $this->assertEquals("test", $sut->name);
    }

    /** @test */
    public function it_belongs_to_category()
    {
        $sut = Brand::factory()
                    ->forCategory(['name' => 'categoryTest'])
                    ->create();

        $this->assertEquals('categoryTest', $sut->category->name);
    }

    /** @test */
    public function it_has_transactions()
    {
        $sut = Brand::factory()->create();

        $sut->transactions()->create(['amount' => 3]);

        $this->assertCount(1, $sut->transactions);
    }
}
