<?php

namespace Tests\Feature\Api\V1;

use App\Domains\Brand\Models\Brand;
use App\Domains\Budget\Models\Budget;
use App\Domains\Category\Models\Category;
use App\Domains\Transaction\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BudgetControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_it_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/budgets');
        $response->assertStatus(401);
    }

    public function test_it_returns_all_budgets(): void
    {
        Budget::factory()->count(3)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/budgets');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'amount',
                        'total_spent_percentage',
                        'start_at_date',
                        'end_at_date',
                        'remaining_to_spend',
                        'total_margin_per_day',
                        'remaining_days',
                        'elapsed_days_percentage',
                        'is_saving',
                        'total_transactions_amount',
                    ],
                ],
            ]);

        $this->assertCount(3, $response->json('data'));
    }

    public function test_it_returns_empty_array_when_no_budgets(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/budgets');

        $response->assertStatus(200)
            ->assertJson([
                'data' => [],
            ]);
    }

    public function test_it_returns_budget_with_computed_fields(): void
    {
        $budget = Budget::factory()->create([
            'name' => 'Test Budget',
            'amount' => 1000,
            'start_at' => now()->subDays(10),
            'reoccurrence' => Budget::MONTHLY,
            'period' => 1,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/budgets');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.name', 'Test Budget')
            ->assertJsonPath('data.0.amount', 1000);
    }

    public function test_daily_spending_requires_authentication(): void
    {
        $budget = Budget::factory()->create();

        $this->getJson("/api/v1/budgets/{$budget->id}/daily-spending")->assertStatus(401);
    }

    public function test_daily_spending_returns_404_for_missing_budget(): void
    {
        $this->actingAs($this->user)
            ->getJson('/api/v1/budgets/999/daily-spending')
            ->assertStatus(404);
    }

    public function test_daily_spending_returns_one_entry_per_day_up_to_today(): void
    {
        Carbon::setTestNow('2026-09-28 12:00:00');

        $category = Category::factory()->create();
        $otherCategory = Category::factory()->create();
        $brand = Brand::factory()->create(['category_id' => $category->id]);
        $otherBrand = Brand::factory()->create(['category_id' => $otherCategory->id]);

        $budget = Budget::factory()->create([
            'amount' => 1000,
            'start_at' => '2026-01-01',
            'reoccurrence' => Budget::MONTHLY,
            'period' => 1,
        ]);
        $budget->categories()->attach($category);

        Transaction::factory()->create(['brand_id' => $brand->id, 'amount' => 100, 'created_at' => '2026-09-01 09:00:00']);
        Transaction::factory()->create(['brand_id' => $brand->id, 'amount' => 50, 'created_at' => '2026-09-01 18:00:00']);
        Transaction::factory()->create(['brand_id' => $brand->id, 'amount' => 25, 'created_at' => '2026-09-27 10:00:00']);
        // Previous window and unrelated category are excluded
        Transaction::factory()->create(['brand_id' => $brand->id, 'amount' => 999, 'created_at' => '2026-08-31 10:00:00']);
        Transaction::factory()->create(['brand_id' => $otherBrand->id, 'amount' => 999, 'created_at' => '2026-09-02 10:00:00']);

        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/budgets/{$budget->id}/daily-spending");

        $response->assertStatus(200)
            ->assertJsonPath('data.budget_id', $budget->id)
            ->assertJsonPath('data.start_date', '2026-09-01')
            ->assertJsonPath('data.end_date', '2026-10-01')
            ->assertJsonPath('data.total_days', 30)
            ->assertJsonCount(28, 'data.days')
            ->assertJsonPath('data.days.0', ['date' => '2026-09-01', 'amount' => 150])
            ->assertJsonPath('data.days.1', ['date' => '2026-09-02', 'amount' => 0])
            ->assertJsonPath('data.days.26', ['date' => '2026-09-27', 'amount' => 25])
            ->assertJsonPath('data.days.27', ['date' => '2026-09-28', 'amount' => 0]);

        Carbon::setTestNow();
    }
}
