<?php

namespace Tests\Feature\Api\V1;

use App\Domains\Brand\Models\Brand;
use App\Domains\Budget\Models\Budget;
use App\Domains\Sms\Models\Sms;
use App\Domains\Transaction\Models\Transaction;
use App\Models\Category;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Carbon::setTestNow('2026-11-14 18:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_it_requires_authentication(): void
    {
        $this->getJson('/api/v1/home')->assertStatus(401);
    }

    public function test_a_new_user_gets_an_empty_home(): void
    {
        $this->actingAs($this->user)
            ->getJson('/api/v1/home')
            ->assertOk()
            ->assertJsonPath('data.stage', 'new')
            ->assertJsonPath('data.needs_you.brands.count', 0)
            ->assertJsonPath('data.month.spent', 0)
            ->assertJsonPath('data.month.has_last_month', false)
            ->assertJsonPath('data.month.started_at', null)
            ->assertJsonPath('data.insights', [])
            ->assertJsonPath('data.recent', [])
            ->assertJsonPath('data.suggestion', null);
    }

    public function test_it_lists_what_needs_the_user(): void
    {
        $uncategorized = Brand::factory()->create(['name' => 'LIFE PHARMACY', 'category_id' => null]);
        Transaction::factory()->create(['brand_id' => $uncategorized->id, 'created_at' => '2026-11-13 10:00:00']);
        Sms::factory()->create(['transaction_id' => null]);

        $category = Category::factory()->create(['type' => Category::EXPENSES]);
        $brand = Brand::factory()->create(['category_id' => $category->id]);
        Transaction::factory()->create(['brand_id' => $brand->id, 'amount' => 900, 'created_at' => '2026-11-10 10:00:00']);
        $over = Budget::factory()->create(['name' => 'Shopping', 'amount' => 500, 'start_at' => '2026-01-01', 'reoccurrence' => Budget::MONTHLY]);
        $fine = Budget::factory()->create(['name' => 'Big', 'amount' => 9000, 'start_at' => '2026-01-01', 'reoccurrence' => Budget::MONTHLY]);
        $over->categories()->attach($category->id);
        $fine->categories()->attach($category->id);

        $this->actingAs($this->user)
            ->getJson('/api/v1/home')
            ->assertOk()
            ->assertJsonPath('data.needs_you.brands.count', 1)
            ->assertJsonPath('data.needs_you.brands.items.0.name', 'LIFE PHARMACY')
            ->assertJsonPath('data.needs_you.brands.items.0.transactions', 1)
            ->assertJsonPath('data.needs_you.unparsed_sms', 1)
            ->assertJsonCount(1, 'data.needs_you.budgets')
            ->assertJsonPath('data.needs_you.budgets.0.name', 'Shopping')
            ->assertJsonPath('data.needs_you.budgets.0.status', 'over')
            ->assertJsonCount(2, 'data.budgets');
    }

    public function test_it_compares_the_month_with_the_same_time_last_month(): void
    {
        $brand = $this->expenseBrand();
        Transaction::factory()->create(['brand_id' => $brand->id, 'amount' => 100, 'created_at' => '2026-11-02 09:00:00']);
        Transaction::factory()->create(['brand_id' => $brand->id, 'amount' => 50, 'created_at' => '2026-11-14 10:00:00']);
        Transaction::factory()->create(['brand_id' => $brand->id, 'amount' => 200, 'created_at' => '2026-10-05 09:00:00']);
        Transaction::factory()->create(['brand_id' => $brand->id, 'amount' => 999, 'created_at' => '2026-10-20 09:00:00']);

        $response = $this->actingAs($this->user)->getJson('/api/v1/home')->assertOk();

        $response->assertJsonPath('data.month.spent', 150)
            ->assertJsonPath('data.month.spent_last_month_to_date', 200)
            ->assertJsonPath('data.month.has_last_month', true);

        $daily = $response->json('data.month.daily');
        $this->assertCount(14, $daily);
        $this->assertEquals(0, $daily[0]);
        $this->assertEquals(100, $daily[1]);
        $this->assertEquals(150, $daily[13]);

        $lastMonth = $response->json('data.month.last_month_daily');
        $this->assertCount(31, $lastMonth);
        $this->assertEquals(1199, end($lastMonth));
    }

    public function test_the_first_month_reports_when_it_started(): void
    {
        Transaction::factory()->create(['brand_id' => $this->expenseBrand()->id, 'created_at' => '2026-11-08 09:00:00']);

        $this->actingAs($this->user)
            ->getJson('/api/v1/home')
            ->assertJsonPath('data.stage', 'early')
            ->assertJsonPath('data.month.started_at', '2026-11-08')
            ->assertJsonPath('data.insights', []);
    }

    public function test_it_explains_where_the_money_went(): void
    {
        $groceries = Category::factory()->create(['name' => 'Groceries', 'type' => Category::EXPENSES]);
        $rent = Category::factory()->create(['name' => 'Housing', 'type' => Category::EXPENSES]);
        $savings = Category::factory()->create(['name' => 'Savings', 'type' => Category::SAVINGS]);
        $carrefour = Brand::factory()->create(['name' => 'Carrefour', 'category_id' => $groceries->id]);
        $landlord = Brand::factory()->create(['name' => 'Rent', 'category_id' => $rent->id]);
        $bank = Brand::factory()->create(['name' => 'Savings account', 'category_id' => $savings->id]);

        // A month of history so insights are unlocked
        Transaction::factory()->create(['brand_id' => $carrefour->id, 'amount' => 100, 'created_at' => '2026-09-01 09:00:00']);
        Transaction::factory()->create(['brand_id' => $carrefour->id, 'amount' => 200, 'created_at' => '2026-10-05 09:00:00']);

        Transaction::factory()->create(['brand_id' => $landlord->id, 'amount' => 6000, 'created_at' => '2026-11-01 09:00:00']);
        Transaction::factory()->create(['brand_id' => $carrefour->id, 'amount' => 300, 'created_at' => '2026-11-03 09:00:00']);
        Transaction::factory()->create(['brand_id' => $carrefour->id, 'amount' => 250, 'created_at' => '2026-11-12 09:00:00']);
        Transaction::factory()->create(['brand_id' => $bank->id, 'amount' => 4000, 'created_at' => '2026-11-02 09:00:00']);

        $response = $this->actingAs($this->user)->getJson('/api/v1/home')->assertOk();

        $insights = collect($response->json('data.insights'))->keyBy('kind');
        // Rent is the biggest, but it's a single payment
        $this->assertSame('Carrefour', $insights['top_brand']['brand']['name']);
        $this->assertEquals(550, $insights['top_brand']['total']);
        $this->assertSame(2, $insights['top_brand']['count']);
        $this->assertSame('Housing', $insights['category_up']['category']['name']);
        $this->assertEquals(6000, $insights['category_up']['difference']);
        $this->assertEquals(4000, $insights['saved']['total']);
        $this->assertSame('2026-11-02', $insights['saved']['last_at']);
    }

    public function test_it_suggests_a_first_budget_until_dismissed_for_the_month(): void
    {
        $groceries = Category::factory()->create(['name' => 'Groceries', 'type' => Category::EXPENSES]);
        Transaction::factory()->create(['brand_id' => Brand::factory()->create(['category_id' => $groceries->id])->id, 'amount' => 640, 'created_at' => '2026-11-10 09:00:00']);

        $this->actingAs($this->user)
            ->getJson('/api/v1/home')
            ->assertJsonPath('data.suggestion.category.name', 'Groceries')
            ->assertJsonPath('data.suggestion.spent', 640);

        $this->actingAs($this->user)
            ->postJson('/api/v1/home/dismissals', ['key' => 'budget-suggestion'])
            ->assertOk()
            ->assertJsonPath('data.until', '2026-12-01');

        $this->actingAs($this->user)->getJson('/api/v1/home')->assertJsonPath('data.suggestion', null);

        // It comes back next month
        Carbon::setTestNow('2026-12-03 10:00:00');
        Transaction::factory()->create(['brand_id' => Brand::factory()->create(['category_id' => $groceries->id])->id, 'amount' => 80, 'created_at' => '2026-12-02 09:00:00']);

        $this->actingAs($this->user->fresh())->getJson('/api/v1/home')->assertJsonPath('data.suggestion.category.name', 'Groceries');
    }

    public function test_there_is_no_suggestion_once_a_budget_exists(): void
    {
        Transaction::factory()->create(['brand_id' => $this->expenseBrand()->id, 'created_at' => '2026-11-10 09:00:00']);
        Budget::factory()->create(['start_at' => '2026-01-01', 'reoccurrence' => Budget::MONTHLY]);

        $this->actingAs($this->user)->getJson('/api/v1/home')->assertJsonPath('data.suggestion', null);
    }

    public function test_only_known_suggestions_can_be_dismissed(): void
    {
        $this->actingAs($this->user)
            ->postJson('/api/v1/home/dismissals', ['key' => 'something-else'])
            ->assertStatus(422);
    }

    public function test_recent_shows_the_latest_five_transactions(): void
    {
        $brand = $this->expenseBrand();
        foreach (range(1, 7) as $day) {
            Transaction::factory()->create(['brand_id' => $brand->id, 'amount' => $day, 'created_at' => "2026-11-0{$day} 09:00:00"]);
        }

        $this->actingAs($this->user)
            ->getJson('/api/v1/home')
            ->assertJsonCount(5, 'data.recent')
            ->assertJsonPath('data.recent.0.created_at', '2026-11-07')
            ->assertJsonPath('data.recent.0.brand.category.type', Category::EXPENSES);
    }

    private function expenseBrand(): Brand
    {
        return Brand::factory()->create(['category_id' => Category::factory()->create(['type' => Category::EXPENSES])->id]);
    }
}
