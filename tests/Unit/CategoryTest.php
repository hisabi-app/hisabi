<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function class_has_expenses_constant()
    {
        $this->assertEquals(Category::EXPENSES, "EXPENSES");
    }

    /** @test */
    public function class_has_income_constant()
    {
        $this->assertEquals(Category::INCOME, "INCOME");
    }
    
    /** @test */
    public function it_has_name()
    {
        $sut = Category::factory()->create(['name' => 'categoryTest']);

        $this->assertEquals("categoryTest", $sut->name);
    }

    /** @test */
    public function it_has_type()
    {
        $sut = Category::factory()->create(['type' => Category::EXPENSES]);

        $this->assertEquals(Category::EXPENSES, $sut->type);
    }

    /** @test */
    public function category_can_have_brands()
    {
        $sut = Category::factory()
                    ->has(Brand::factory()->count(3))
                    ->create();

        $this->assertCount(3, $sut->brands);
    }
}
