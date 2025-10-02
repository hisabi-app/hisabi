<?php

namespace Tests\Unit\Models\Categories;

use App\Models\Brand;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_class_has_expenses_constant()
    {
        $this->assertEquals(Category::EXPENSES, "EXPENSES");
    }

    public function test_class_has_income_constant()
    {
        $this->assertEquals(Category::INCOME, "INCOME");
    }

    public function test_class_has_investment_constant()
    {
        $this->assertEquals(Category::INVESTMENT, "INVESTMENT");
    }

    public function test_class_has_savings_constant()
    {
        $this->assertEquals(Category::SAVINGS, "SAVINGS");
    }

    public function test_it_has_name()
    {
        $sut = Category::factory()->create(['name' => 'categoryTest']);

        $this->assertEquals("categoryTest", $sut->name);
    }

    public function test_it_has_type()
    {
        $sut = Category::factory()->create(['type' => Category::EXPENSES]);

        $this->assertEquals(Category::EXPENSES, $sut->type);
    }

    public function test_it_has_color()
    {
        $sut = Category::factory()->create(['color' => 'gray']);

        $this->assertEquals('gray', $sut->color);
    }

    public function test_category_can_have_brands()
    {
        $sut = Category::factory()
            ->has(Brand::factory()->count(3))
            ->create();

        $this->assertCount(3, $sut->brands);
    }

    public function test_is_does_search_about_amount_brand_or_note()
    {
        Category::factory()->create(['name' => 'debt']);
        Category::factory()->create(['name' => 'deee']);

        $this->assertCount(0, Category::search('goo')->get());
        $this->assertCount(1, Category::search('debt')->get());
        $this->assertCount(2, Category::search('de')->get());
    }
}
