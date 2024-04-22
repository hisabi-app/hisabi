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
    public function it_has_total_transactions_amount()
    {
        $category = Category::factory()->create();
        $brand = Brand::factory()->create(['category_id' => $category->id]);
        $sut = Budget::factory()->create(['start_at' => now()->subDays(1), 'end_at' => now()->addDays(1), 'amount' => 700]);
        $sut->categories()->attach($category);

        $category->transactions()->create(['amount' => 100, 'brand_id' => $brand->id]);
        $category->transactions()->create(['amount' => 200, 'brand_id' => $brand->id]);
        $category->transactions()->create(['amount' => 200, 'brand_id' => $brand->id, 'created_at' => now()->addDays(2)]);

        $this->assertEquals(300, $sut->totalTransactionsAmount);
        $this->assertEquals(42.86, $sut->totalSpentPercentage);
    }

    /** @test */
    public function it_has_isSaving()
    {
        $this->assertTrue(Budget::factory()->create(['saving' => true])->isSaving);
        $this->assertFalse(Budget::factory()->create(['saving' => false])->isSaving);
    }

    /** @test */
    public function it_has_totalMarginPerDay_should_return_remaining_if_no_more_days()
    {
        $sut = Budget::factory()->create(['start_at' => now()->subDays(1), 'end_at' => now()->addDays(1), 'amount' => 700]);
        $sut->categories()->attach(Category::factory()->create());

        $this->assertEquals(700, $sut->totalMarginPerDay);
    }

    /** @test */
    public function it_has_totalMarginPerDay_should_return_zero_if_over_budget()
    {
        $category = Category::factory()->create();
        $brand = Brand::factory()->create(['category_id' => $category->id]);
        $sut = Budget::factory()->create(['start_at' => now()->subDays(1), 'end_at' => now()->addDays(1), 'amount' => 700]);
        $sut->categories()->attach($category);

        $category->transactions()->create(['amount' => 700, 'brand_id' => $brand->id]);

        $this->assertEquals(0, $sut->totalMarginPerDay);
    }

    /** @test */
    public function it_has_totalMarginPerDay_should_return_correct_value()
    {
        $category = Category::factory()->create();
        $brand = Brand::factory()->create(['category_id' => $category->id]);
        $sut = Budget::factory()->create(['start_at' => now(), 'period' => 3, 'reoccurrence' => Budget::DAILY, 'amount' => 700]);
        $sut->categories()->attach($category);

        $category->transactions()->create(['amount' => 600, 'brand_id' => $brand->id]);

        $this->assertEquals(50, $sut->totalMarginPerDay);
    }

    /** @test */
    public function it_has_start_and_end_at()
    {
        $sut = Budget::factory()->create(['start_at' => now()->subDays(1), 'end_at' => now()->addDays(1)]);

        $this->assertEquals(now()->subDays(1)->format('Y-m-d'), $sut->start_at->format('Y-m-d'));
        $this->assertEquals(now()->addDays(1)->format('Y-m-d'), $sut->end_at->format('Y-m-d'));
    }

    /** @test */
    public function it_has_remaining_days()
    {
        $sut = Budget::factory()->create(['start_at' => now()->subDays(1), 'end_at' => now()->addDays(2), 'reoccurrence' => Budget::CUSTOM]);

        $this->assertEquals(1, $sut->remainingDays);
    }

    /** @test */
    public function it_has_remaining_to_spend()
    {
        $category = Category::factory()->create();
        $brand = Brand::factory()->create(['category_id' => $category->id]);
        $sut = Budget::factory()->create(['start_at' => now()->subDays(1), 'end_at' => now()->addDays(1), 'amount' => 700]);
        $sut->categories()->attach($category);

        $category->transactions()->create(['amount' => 100, 'brand_id' => $brand->id]);
        $category->transactions()->create(['amount' => 200, 'brand_id' => $brand->id]);

        $this->assertEquals(400, $sut->remainingToSpend);
    }

    /** @test */
    public function it_has_start_and_end_dates_window()
    {
        $sut = Budget::factory()->create(['start_at' => now()->subDays(1), 'period' => 1, 'reoccurrence' => Budget::DAILY]);

        $this->assertEquals(now()->format('Y-m-d'), $sut->startAtDate);
        $this->assertEquals(now()->addDays(1)->format('Y-m-d'), $sut->endAtDate);
    }
}
