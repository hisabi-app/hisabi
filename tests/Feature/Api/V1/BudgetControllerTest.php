<?php

namespace Tests\Feature\Api\V1;

use App\Domains\Brand\Models\Brand;
use App\Domains\Budget\Models\Budget;
use App\Domains\Category\Models\Category;
use App\Domains\Transaction\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
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

    public function test_history_requires_authentication(): void
    {
        $this->getJson('/api/v1/budgets/history')->assertStatus(401);
    }

    public function test_history_returns_spending_per_window_for_recurring_budgets(): void
    {
        Carbon::setTestNow('2026-09-28 12:00:00');

        $category = Category::factory()->create();
        $brand = Brand::factory()->create(['category_id' => $category->id]);

        $budget = Budget::factory()->create([
            'name' => 'Dining out',
            'amount' => 1000,
            'start_at' => '2026-06-01',
            'reoccurrence' => Budget::MONTHLY,
            'period' => 1,
        ]);
        $budget->categories()->attach($category);

        Budget::factory()->create([
            'start_at' => '2026-01-01',
            'end_at' => '2026-12-31',
            'reoccurrence' => Budget::CUSTOM,
        ]);

        Transaction::factory()->create(['brand_id' => $brand->id, 'amount' => 1100, 'created_at' => '2026-06-15 10:00:00']);
        Transaction::factory()->create(['brand_id' => $brand->id, 'amount' => 900, 'created_at' => '2026-07-31 23:00:00']);
        Transaction::factory()->create(['brand_id' => $brand->id, 'amount' => 250, 'created_at' => '2026-09-10 10:00:00']);

        $response = $this->actingAs($this->user)->getJson('/api/v1/budgets/history?from=2026-06-01');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Dining out')
            ->assertJsonPath('data.0.reoccurrence', Budget::MONTHLY)
            ->assertJsonCount(4, 'data.0.periods')
            ->assertJsonPath('data.0.periods.0', ['start_date' => '2026-06-01', 'end_date' => '2026-07-01', 'spent' => 1100, 'percentage' => 110, 'is_current' => false])
            ->assertJsonPath('data.0.periods.1.percentage', 90)
            ->assertJsonPath('data.0.periods.2.spent', 0)
            ->assertJsonPath('data.0.periods.3', ['start_date' => '2026-09-01', 'end_date' => '2026-10-01', 'spent' => 250, 'percentage' => 25, 'is_current' => true]);

        Carbon::setTestNow();
    }

    public function test_history_includes_windows_before_the_budget_started(): void
    {
        Carbon::setTestNow('2026-09-28 12:00:00');

        $category = Category::factory()->create();
        $brand = Brand::factory()->create(['category_id' => $category->id]);
        $budget = Budget::factory()->create([
            'amount' => 1000,
            'start_at' => '2026-06-01',
            'reoccurrence' => Budget::MONTHLY,
            'period' => 1,
        ]);
        $budget->categories()->attach($category);

        Transaction::factory()->create(['brand_id' => $brand->id, 'amount' => 500, 'created_at' => '2026-04-20 10:00:00']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/budgets/history?from=2026-03-01&to=2026-07-31');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data.0.periods')
            ->assertJsonPath('data.0.periods.0.start_date', '2026-03-01')
            ->assertJsonPath('data.0.periods.1', ['start_date' => '2026-04-01', 'end_date' => '2026-05-01', 'spent' => 500, 'percentage' => 50, 'is_current' => false])
            ->assertJsonPath('data.0.periods.3.start_date', '2026-06-01')
            ->assertJsonPath('data.0.periods.4.start_date', '2026-07-01');

        Carbon::setTestNow();
    }

    public function test_history_is_limited_to_the_most_recent_windows(): void
    {
        Carbon::setTestNow('2026-09-28 12:00:00');

        Budget::factory()->create([
            'start_at' => '2024-01-01',
            'reoccurrence' => Budget::MONTHLY,
            'period' => 1,
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/v1/budgets/history');

        $response->assertStatus(200)
            ->assertJsonCount(24, 'data.0.periods')
            ->assertJsonPath('data.0.periods.0.start_date', '2024-10-01')
            ->assertJsonPath('data.0.periods.23.start_date', '2026-09-01');

        Carbon::setTestNow();
    }

    public function test_history_only_returns_windows_in_the_requested_range(): void
    {
        Carbon::setTestNow('2026-09-28 12:00:00');

        $category = Category::factory()->create();
        $brand = Brand::factory()->create(['category_id' => $category->id]);
        $budget = Budget::factory()->create([
            'start_at' => '2025-01-01',
            'reoccurrence' => Budget::MONTHLY,
            'period' => 1,
        ]);
        $budget->categories()->attach($category);

        Transaction::factory()->create(['brand_id' => $brand->id, 'amount' => 10, 'created_at' => '2024-03-05 10:00:00']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/budgets/history?from=2025-03-01&to=2025-05-31');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data.0.periods')
            ->assertJsonPath('data.0.periods.0.start_date', '2025-03-01')
            ->assertJsonPath('data.0.periods.2.start_date', '2025-05-01')
            ->assertJsonPath('meta.from', '2025-03-01')
            ->assertJsonPath('meta.to', '2025-05-31')
            ->assertJsonPath('meta.earliest_date', '2021-10-01')
            ->assertJsonPath('meta.first_transaction_date', '2024-03-05');

        Carbon::setTestNow();
    }

    public function test_history_range_is_limited_to_the_last_five_years(): void
    {
        Carbon::setTestNow('2026-09-28 12:00:00');

        Budget::factory()->create([
            'start_at' => '2015-01-01',
            'reoccurrence' => Budget::MONTHLY,
            'period' => 1,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/budgets/history?from=2010-01-01&to=2030-01-01');

        $response->assertStatus(200)
            ->assertJsonPath('meta.from', '2021-10-01')
            ->assertJsonPath('meta.to', '2026-09-28')
            ->assertJsonCount(60, 'data.0.periods')
            ->assertJsonPath('data.0.periods.59.start_date', '2026-09-01');

        Carbon::setTestNow();
    }

    public function test_history_queries_transactions_once_regardless_of_budgets_and_windows(): void
    {
        Carbon::setTestNow('2026-09-28 12:00:00');

        $category = Category::factory()->create();
        $otherCategory = Category::factory()->create();
        $brand = Brand::factory()->create(['category_id' => $category->id]);
        $otherBrand = Brand::factory()->create(['category_id' => $otherCategory->id]);

        $monthly = Budget::factory()->create(['start_at' => '2025-01-01', 'reoccurrence' => Budget::MONTHLY, 'period' => 1]);
        $monthly->categories()->attach([$category->id, $otherCategory->id]);
        $weekly = Budget::factory()->create(['start_at' => '2025-01-06', 'reoccurrence' => Budget::WEEKLY, 'period' => 1]);
        $weekly->categories()->attach($category);
        $daily = Budget::factory()->create(['start_at' => '2025-01-01', 'reoccurrence' => Budget::DAILY, 'period' => 1]);
        $daily->categories()->attach($otherCategory);

        Transaction::factory()->create(['brand_id' => $brand->id, 'amount' => 100, 'created_at' => '2026-09-21 10:00:00']);
        Transaction::factory()->create(['brand_id' => $otherBrand->id, 'amount' => 40, 'created_at' => '2026-09-27 23:00:00']);

        DB::enableQueryLog();
        $response = $this->actingAs($this->user)->getJson('/api/v1/budgets/history');
        $transactionQueries = collect(DB::getQueryLog())->filter(fn ($query) => str_contains($query['query'], 'transactions'));

        $response->assertStatus(200)
            ->assertJsonPath('data.0.periods.23.spent', 140)
            ->assertJsonPath('data.1.periods.58.start_date', '2026-09-21')
            ->assertJsonPath('data.1.periods.58.spent', 100)
            ->assertJsonPath('data.1.periods.59.spent', 0)
            ->assertJsonPath('data.2.periods.58.start_date', '2026-09-27')
            ->assertJsonPath('data.2.periods.58.spent', 40);

        // One query for the daily totals of all budgets, one for the first transaction date.
        $this->assertCount(2, $transactionQueries);

        Carbon::setTestNow();
    }

    public function test_index_queries_each_budget_spending_once(): void
    {
        $budgets = Budget::factory()->count(3)->create();

        DB::enableQueryLog();
        $this->actingAs($this->user)->getJson('/api/v1/budgets')->assertStatus(200);
        $transactionQueries = collect(DB::getQueryLog())->filter(fn ($query) => str_contains($query['query'], 'transactions'));

        $this->assertCount($budgets->count(), $transactionQueries);
    }

    public function test_history_validates_the_range(): void
    {
        $this->actingAs($this->user)
            ->getJson('/api/v1/budgets/history?from=2026-05-01&to=2026-01-01')
            ->assertStatus(422)
            ->assertJsonValidationErrors(['to']);

        $this->actingAs($this->user)
            ->getJson('/api/v1/budgets/history?from=not-a-date')
            ->assertStatus(422)
            ->assertJsonValidationErrors(['from']);
    }

    public function test_update_requires_authentication(): void
    {
        $budget = Budget::factory()->create();

        $this->putJson("/api/v1/budgets/{$budget->id}", ['amount' => 1500])->assertStatus(401);
    }

    public function test_it_updates_the_budget_amount(): void
    {
        $budget = Budget::factory()->create(['name' => 'Dining out', 'amount' => 1200]);

        $response = $this->actingAs($this->user)
            ->putJson("/api/v1/budgets/{$budget->id}", ['amount' => 1450]);

        $response->assertStatus(200)
            ->assertJsonPath('budget.id', $budget->id)
            ->assertJsonPath('budget.name', 'Dining out');

        $this->assertEquals(1450, $budget->fresh()->amount);
    }

    public function test_update_validates_the_amount(): void
    {
        $budget = Budget::factory()->create(['amount' => 1200]);

        $this->actingAs($this->user)
            ->putJson("/api/v1/budgets/{$budget->id}", ['amount' => 0])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);

        $this->actingAs($this->user)
            ->putJson("/api/v1/budgets/{$budget->id}", [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);

        $this->assertEquals(1200, $budget->fresh()->amount);
    }

    public function test_update_returns_404_for_missing_budget(): void
    {
        $this->actingAs($this->user)
            ->putJson('/api/v1/budgets/999', ['amount' => 100])
            ->assertStatus(404);
    }
}
