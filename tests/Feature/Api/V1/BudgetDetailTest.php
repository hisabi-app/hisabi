<?php

namespace Tests\Feature\Api\V1;

use App\Domains\Brand\Models\Brand;
use App\Domains\Budget\Models\Budget;
use App\Domains\Transaction\Models\Transaction;
use App\Models\Category;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BudgetDetailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-11-14 18:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_it_breaks_the_current_window_down_by_brand(): void
    {
        $shopping = Category::factory()->create(['name' => 'Shopping', 'color' => 'pink', 'type' => Category::EXPENSES]);
        $other = Category::factory()->create(['type' => Category::EXPENSES]);
        $noon = Brand::factory()->create(['name' => 'noon', 'category_id' => $shopping->id]);
        $ikea = Brand::factory()->create(['name' => 'IKEA', 'category_id' => $shopping->id]);
        $elsewhere = Brand::factory()->create(['category_id' => $other->id]);

        Transaction::factory()->create(['brand_id' => $noon->id, 'amount' => 449, 'created_at' => '2026-11-12 09:00:00']);
        Transaction::factory()->create(['brand_id' => $noon->id, 'amount' => 312, 'created_at' => '2026-11-05 09:00:00']);
        Transaction::factory()->create(['brand_id' => $ikea->id, 'amount' => 620, 'created_at' => '2026-11-09 09:00:00']);
        // Last month and other categories stay out
        Transaction::factory()->create(['brand_id' => $noon->id, 'amount' => 999, 'created_at' => '2026-10-20 09:00:00']);
        Transaction::factory()->create(['brand_id' => $elsewhere->id, 'amount' => 999, 'created_at' => '2026-11-10 09:00:00']);

        $budget = Budget::factory()->create(['name' => 'Shopping', 'amount' => 2000, 'start_at' => '2026-01-01', 'reoccurrence' => Budget::MONTHLY]);
        $budget->categories()->attach($shopping->id);

        $this->actingAs(User::factory()->create())
            ->getJson("/api/v1/budgets/{$budget->id}/detail")
            ->assertOk()
            ->assertJsonPath('data.budget.name', 'Shopping')
            ->assertJsonPath('data.category.color', 'pink')
            ->assertJsonPath('data.brands.0.name', 'noon')
            ->assertJsonPath('data.brands.0.total', 761)
            ->assertJsonPath('data.brands.0.count', 2)
            ->assertJsonPath('data.brands.1.name', 'IKEA')
            ->assertJsonCount(3, 'data.transactions')
            ->assertJsonPath('data.transactions.0.created_at', '2026-11-12');
    }

    public function test_a_missing_budget_is_not_found(): void
    {
        $this->actingAs(User::factory()->create())->getJson('/api/v1/budgets/999/detail')->assertNotFound();
    }
}
