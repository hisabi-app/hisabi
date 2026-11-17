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

    public function test_it_returns_this_months_spending_by_category_biggest_first(): void
    {
        $groceries = Category::factory()->create(['name' => 'Groceries', 'color' => 'green', 'icon' => 'ShoppingCart']);
        $housing = Category::factory()->create(['name' => 'Housing', 'color' => 'blue']);
        $carrefour = Brand::factory()->create(['category_id' => $groceries->id]);
        $lulu = Brand::factory()->create(['category_id' => $groceries->id]);
        $rent = Brand::factory()->create(['category_id' => $housing->id]);
        $salary = Brand::factory()->create(['category_id' => Category::factory()->create(['type' => Category::INCOME])->id]);
        $uncategorized = Brand::factory()->create(['category_id' => null]);

        Transaction::factory()->create(['brand_id' => $carrefour->id, 'amount' => 100, 'created_at' => '2026-11-02 09:00:00']);
        Transaction::factory()->create(['brand_id' => $lulu->id, 'amount' => 50.5, 'created_at' => '2026-11-10 09:00:00']);
        Transaction::factory()->create(['brand_id' => $rent->id, 'amount' => 4000, 'created_at' => '2026-11-01 00:00:00']);
        Transaction::factory()->create(['brand_id' => $salary->id, 'amount' => 20000, 'created_at' => '2026-11-01 00:00:00']);
        Transaction::factory()->create(['brand_id' => $uncategorized->id, 'amount' => 70, 'created_at' => '2026-11-03 09:00:00']);
        // Last month, and later today
        Transaction::factory()->create(['brand_id' => $carrefour->id, 'amount' => 999, 'created_at' => '2026-10-20 09:00:00']);
        Transaction::factory()->create(['brand_id' => $carrefour->id, 'amount' => 999, 'created_at' => '2026-11-13 20:00:00']);

        $this->actingAs($this->user)
            ->getJson('/api/v1/kiosk')
            ->assertStatus(200)
            ->assertJsonCount(2, 'data.categories')
            ->assertJsonPath('data.categories.0.name', 'Housing')
            ->assertJsonPath('data.categories.0.total', 4000)
            ->assertJsonPath('data.categories.1', [
                'id' => $groceries->id,
                'name' => 'Groceries',
                'color' => 'green',
                'icon' => 'ShoppingCart',
                'total' => 150.5,
            ]);
    }

    public function test_it_includes_the_latest_transaction_and_daily_running_totals(): void
    {
        $brand = Brand::factory()->create(['name' => 'Carrefour', 'category_id' => Category::factory()->create()->id]);
        Transaction::factory()->create(['brand_id' => $brand->id, 'amount' => 100, 'created_at' => '2026-11-02 09:00:00']);
        // Added last, though dated earlier: an SMS pasted late
        Transaction::factory()->create(['brand_id' => $brand->id, 'amount' => 50, 'created_at' => '2026-11-01 00:00:00', 'updated_at' => '2026-11-13 16:00:00']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/kiosk')
            ->assertStatus(200)
            ->assertJsonPath('data.latest.brand.name', 'Carrefour')
            ->assertJsonPath('data.latest.amount', 50)
            ->assertJsonCount(13, 'data.daily')
            ->assertJsonCount(31, 'data.last_month_daily');

        $this->assertEquals(150, $response->json('data.daily.12'));
        // When it was added, not its date
        $this->assertStringStartsWith('2026-11-13T16:00:00', $response->json('data.latest_at'));
    }

    public function test_the_ask_button_stays_hidden_without_an_anthropic_key(): void
    {
        config(['ai.providers.anthropic.key' => null, 'ai.providers.openai.key' => 'test']);

        $this->actingAs($this->user)
            ->getJson('/api/v1/kiosk')
            ->assertJsonPath('data.assistant', ['enabled' => false, 'voice' => false, 'suggestions' => []]);
    }

    public function test_it_suggests_questions_from_this_months_numbers(): void
    {
        config(['ai.providers.anthropic.key' => 'test', 'ai.providers.openai.key' => null]);
        $groceries = Brand::factory()->create(['category_id' => Category::factory()->create(['name' => 'Groceries'])->id]);
        Transaction::factory()->create(['brand_id' => $groceries->id, 'amount' => 100, 'created_at' => '2026-11-02 09:00:00']);
        Transaction::factory()->create(['brand_id' => $groceries->id, 'amount' => 100, 'created_at' => '2026-10-02 09:00:00']);

        $this->actingAs($this->user)
            ->getJson('/api/v1/kiosk')
            ->assertJsonPath('data.assistant.enabled', true)
            // No OpenAI key: questions are tapped, not spoken
            ->assertJsonPath('data.assistant.voice', false)
            ->assertJsonPath('data.assistant.suggestions', ['Groceries so far', 'Biggest spends this week', 'What’s different from October?']);
    }
}
