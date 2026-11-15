<?php

namespace Tests\Unit\Models\Brands;

use App\Domains\Brand\Models\Brand;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_has_name()
    {
        $sut = Brand::factory()->create(['name' => 'test']);

        $this->assertEquals("test", $sut->name);
    }

    public function test_it_belongs_to_category()
    {
        $sut = Brand::factory()
                    ->forCategory(['name' => 'categoryTest'])
                    ->create();

        $this->assertEquals('categoryTest', $sut->category->name);
    }

    public function test_it_has_transactions()
    {
        $sut = Brand::factory()->create();

        $sut->transactions()->create(['amount' => 3]);

        $this->assertCount(1, $sut->transactions);
    }

    public function test_it_does_search_about_amount_brand_or_note()
    {
        Brand::factory()->forCategory(['name' => 'internet'])->create(['name' => 'google']);
        Brand::factory()->forCategory(['name' => 'shopping'])->create(['name' => 'ikea']);
        Brand::factory()->forCategory(['name' => 'shopping'])->create(['name' => 'lulu']);

        $this->assertCount(1, Brand::search('goo')->get());
        $this->assertCount(1, Brand::search('internet')->get());
        $this->assertCount(2, Brand::search('shopping')->get());
        $this->assertCount(0, Brand::search('other')->get());
    }

    public function test_an_sms_merchant_goes_to_the_brand_whose_name_it_contains()
    {
        $brand = Brand::factory()->create(['name' => 'Example Mart']);

        $this->assertTrue($brand->is(Brand::findOrCreateNew('EXAMPLE MART LLC, DUBAI')));
        $this->assertEquals(1, Brand::count());
    }

    public function test_an_unknown_sms_merchant_becomes_a_new_brand()
    {
        Brand::factory()->create(['name' => 'Example Mart']);

        $brand = Brand::findOrCreateNew('SAMPLE CAFE 12');

        $this->assertEquals('SAMPLE CAFE 12', $brand->name);
        $this->assertEquals(2, Brand::count());
    }

    public function test_the_longest_match_wins_over_the_oldest_brand()
    {
        Brand::factory()->create(['name' => 'Lulu']);
        $laundry = Brand::factory()->create(['name' => 'Laundry']);

        $this->assertTrue($laundry->is(Brand::findOrCreateNew('LULU LAUNDRY CO')));
    }

    public function test_on_a_tie_the_oldest_brand_wins()
    {
        $first = Brand::factory()->create(['name' => 'Sample']);
        Brand::factory()->create(['name' => 'Sampl3']);

        $this->assertTrue($first->is(Brand::findOrCreateNew('SAMPLE SAMPL3 SHOP')));
    }

    public function test_a_brand_matches_its_aliases()
    {
        Brand::factory()->create(['name' => 'Example']);
        $now = Brand::factory()->create(['name' => 'Example Now', 'aliases' => ['EXAMPLEUFR']]);

        $this->assertTrue($now->is(Brand::findOrCreateNew('EXAMPLEUFR DI, Dubai')));
    }

    public function test_an_alias_can_use_a_star_for_any_text_in_between()
    {
        $pharmacy = Brand::factory()->create(['name' => 'Sample Pharmacy', 'aliases' => ['SAMPLE * PHY']]);

        $this->assertTrue($pharmacy->is(Brand::findOrCreateNew('1234 SAMPLE 567 PHY')));
        $this->assertNull(Brand::findForSms('SAMPLE MART'));
    }

    public function test_matching_ignores_case()
    {
        $brand = Brand::factory()->create(['name' => 'noon', 'aliases' => ['Noon Minutes']]);

        $this->assertTrue($brand->is(Brand::findOrCreateNew('NOON MINUTES LLC')));
    }
}
