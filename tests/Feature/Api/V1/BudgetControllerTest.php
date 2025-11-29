<?php

namespace Tests\Feature\Api\V1;

use App\Domains\Budget\Models\Budget;
use App\Models\User;
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
}
