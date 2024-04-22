<?php

namespace Tests\Unit\Models\Budgets;

use Tests\TestCase;
use App\Models\Brand;
use App\Models\Budget;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BudgetTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_name()
    {
        $sut = Budget::factory()->create(['name' => 'test']);

        $this->assertEquals("test", $sut->name);
    }

    /** @test */
    public function it_belongs_to_categories()
    {
        $categories = Category::factory()->createMany(3);
        $sut = Budget::factory()->create();
        $sut->categories()->attach($categories);

        $this->assertCount(3, $sut->categories);
    }

    /** @test */
    public function it_has_total_categories_transactions_amount()
    {
        $category = Category::factory()->create();
        $brand = Brand::factory()->create(['category_id' => $category->id]);
        $sut = Budget::factory()->create(['start_at' => now()->subDays(1), 'end_at' => now()->addDays(1)]);
        $sut->categories()->attach($category);

        $category->transactions()->create(['amount' => 100, 'brand_id' => $brand->id]);
        $category->transactions()->create(['amount' => 200, 'brand_id' => $brand->id]);
        $category->transactions()->create(['amount' => 200, 'brand_id' => $brand->id, 'created_at' => now()->addDays(2)]);

        $this->assertEquals(300, $sut->totalAccumulatedTransactionsAmount);
    }

    /** @test */
    public function it_has_start_and_end_at()
    {
        $sut = Budget::factory()->create(['start_at' => now()->subDays(1), 'end_at' => now()->addDays(1)]);

        $this->assertEquals(now()->subDays(1)->format('Y-m-d'), $sut->start_at->format('Y-m-d'));
        $this->assertEquals(now()->addDays(1)->format('Y-m-d'), $sut->end_at->format('Y-m-d'));
    }

    /** @test */
    public function it_has_start_and_end_dates_window()
    {
        $sut = Budget::factory()->create(['start_at' => now()->subDays(1), 'period' => 1, 'reoccurrence' => Budget::DAILY]);

        $this->assertEquals(now()->format('Y-m-d'), $sut->startAtDate);
        $this->assertEquals(now()->addDays(1)->format('Y-m-d'), $sut->endAtDate);
    }
}
