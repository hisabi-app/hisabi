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

class KioskControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Carbon::setTestNow('2026-11-13 18:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_it_requires_authentication(): void
    {
        $this->getJson('/api/v1/kiosk')->assertStatus(401);
    }

    public function test_it_compares_spending_with_the_same_time_last_month(): void
    {
        $expenses = Brand::factory()->create(['category_id' => Category::factory()->create()->id]);
        $income = Brand::factory()->create(['category_id' => Category::factory()->create(['type' => Category::INCOME])->id]);

        Transaction::factory()->create(['brand_id' => $expenses->id, 'amount' => 100, 'created_at' => '2026-11-02 09:00:00']);
        Transaction::factory()->create(['brand_id' => $expenses->id, 'amount' => 50, 'created_at' => '2026-11-13 10:00:00']);
        Transaction::factory()->create(['brand_id' => $income->id, 'amount' => 5000, 'created_at' => '2026-11-03 09:00:00']);

        Transaction::factory()->create(['brand_id' => $expenses->id, 'amount' => 200, 'created_at' => '2026-10-05 09:00:00']);
        Transaction::factory()->create(['brand_id' => $expenses->id, 'amount' => 30, 'created_at' => '2026-10-13 17:00:00']);
        Transaction::factory()->create(['brand_id' => $expenses->id, 'amount' => 999, 'created_at' => '2026-10-13 19:00:00']);
        Transaction::factory()->create(['brand_id' => $expenses->id, 'amount' => 500, 'created_at' => '2026-10-20 09:00:00']);

        $this->actingAs($this->user)
            ->getJson('/api/v1/kiosk')
            ->assertStatus(200)
            ->assertJsonPath('data.spent', 150)
            ->assertJsonPath('data.spent_last_month_to_date', 230);
    }

    public function test_it_returns_current_budgets_without_ended_custom_ones(): void
    {
        Budget::factory()->create(['name' => 'Groceries', 'start_at' => '2026-01-01', 'reoccurrence' => Budget::MONTHLY]);
        Budget::factory()->create(['name' => 'Trip', 'start_at' => '2026-11-01', 'end_at' => '2026-12-15', 'reoccurrence' => Budget::CUSTOM]);
        Budget::factory()->create(['name' => 'Wedding', 'start_at' => '2026-09-01', 'end_at' => '2026-10-31', 'reoccurrence' => Budget::CUSTOM]);

        $this->actingAs($this->user)
            ->getJson('/api/v1/kiosk')
            ->assertStatus(200)
            ->assertJsonCount(2, 'data.budgets')
            ->assertJsonPath('data.budgets.0.name', 'Groceries')
            ->assertJsonPath('data.budgets.1.name', 'Trip');
    }
}
