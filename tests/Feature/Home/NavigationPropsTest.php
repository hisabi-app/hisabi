<?php

namespace Tests\Feature\Home;

use App\Domains\Brand\Models\Brand;
use App\Domains\Budget\Models\Budget;
use App\Domains\Category\Models\Category;
use App\Domains\Transaction\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class NavigationPropsTest extends TestCase
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

    public function test_a_new_user_is_at_the_new_stage(): void
    {
        $this->assertNav(fn (AssertableInertia $page) => $page
            ->where('nav.stage', 'new')
            ->where('nav.has_budgets', false)
            ->where('nav.counts.brands', 0)
            ->where('nav.counts.budgets', 0));
    }

    public function test_less_than_a_month_of_transactions_is_the_early_stage(): void
    {
        Transaction::factory()->create(['created_at' => '2026-11-01 10:00:00']);

        $this->assertNav(fn (AssertableInertia $page) => $page->where('nav.stage', 'early'));
    }

    public function test_more_than_a_month_of_transactions_is_the_full_stage(): void
    {
        Transaction::factory()->create(['created_at' => '2026-09-20 10:00:00']);

        $this->assertNav(fn (AssertableInertia $page) => $page->where('nav.stage', 'full'));
    }

    public function test_it_counts_brands_with_transactions_but_no_category(): void
    {
        Transaction::factory()->create(['brand_id' => Brand::factory()->create(['category_id' => null])->id]);
        Transaction::factory()->create(['brand_id' => Brand::factory()->create(['category_id' => null])->id]);
        // Without transactions it isn't waiting on anything
        Brand::factory()->create(['category_id' => null]);
        Transaction::factory()->create(['brand_id' => Brand::factory()->create(['category_id' => Category::factory()->create()->id])->id]);

        $this->assertNav(fn (AssertableInertia $page) => $page->where('nav.counts.brands', 2));
    }

    public function test_it_counts_budgets_that_are_over_or_ahead_of_pace(): void
    {
        $category = Category::factory()->create();
        $brand = Brand::factory()->create(['category_id' => $category->id]);
        Transaction::factory()->create(['brand_id' => $brand->id, 'amount' => 900, 'created_at' => '2026-11-10 10:00:00']);

        $over = Budget::factory()->create(['amount' => 500, 'start_at' => '2026-01-01', 'reoccurrence' => Budget::MONTHLY]);
        $ahead = Budget::factory()->create(['amount' => 1000, 'start_at' => '2026-01-01', 'reoccurrence' => Budget::MONTHLY]);
        $onTrack = Budget::factory()->create(['amount' => 5000, 'start_at' => '2026-01-01', 'reoccurrence' => Budget::MONTHLY]);
        foreach ([$over, $ahead, $onTrack] as $budget) {
            $budget->categories()->attach($category->id);
        }

        $this->assertNav(fn (AssertableInertia $page) => $page
            ->where('nav.has_budgets', true)
            ->where('nav.counts.budgets', 2));
    }

    public function test_guests_get_no_navigation(): void
    {
        $this->get('/login')->assertInertia(fn (AssertableInertia $page) => $page->where('nav', null));
    }

    private function assertNav(callable $assert): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/budgets')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $assert($page));
    }
}
