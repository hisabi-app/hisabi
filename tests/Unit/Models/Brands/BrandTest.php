<?php

namespace Tests\Unit\Models\Brands;

use App\Models\Brand;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

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

    /** @test */
    public function is_does_search_about_amount_brand_or_note()
    {
        Brand::factory()->forCategory(['name' => 'internet'])->create(['name' => 'google']);
        Brand::factory()->forCategory(['name' => 'shopping'])->create(['name' => 'ikea']);
        Brand::factory()->forCategory(['name' => 'shopping'])->create(['name' => 'lulu']);

        $this->assertCount(1, Brand::search('goo')->get());
        $this->assertCount(1, Brand::search('internet')->get());
        $this->assertCount(2, Brand::search('shopping')->get());
        $this->assertCount(0, Brand::search('other')->get());
    }
}
