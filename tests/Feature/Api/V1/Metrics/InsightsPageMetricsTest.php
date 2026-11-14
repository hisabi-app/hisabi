<?php

namespace Tests\Feature\Api\V1\Metrics;

use App\Domains\Brand\Models\Brand;
use App\Domains\Category\Models\Category;
use App\Domains\Transaction\Models\Transaction;
use Carbon\Carbon;

// The metrics behind the redesigned Insights page
class InsightsPageMetricsTest extends MetricsTestCase
{
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

    public function test_they_require_authentication(): void
    {
        foreach (['income-allocation', 'category-breakdown', 'top-brands', 'spending-over-time'] as $metric) {
            $this->getJson("/api/v1/metrics/{$metric}")->assertUnauthorized();
        }
    }

    public function test_income_allocation_splits_income_within_the_range(): void
    {
        $this->actingAs($this->user);
        $this->transaction($this->incomeBrand, 10000, '2026-11-01 09:00:00');
        $this->transaction($this->expensesBrand, 6000, '2026-11-05 09:00:00');
        $this->transaction($this->savingsBrand, 2000, '2026-11-06 09:00:00');
        $this->transaction($this->investmentBrand, 1000, '2026-11-30 22:00:00');
        $this->transaction($this->expensesBrand, 999, '2026-10-31 09:00:00');

        $this->getJson('/api/v1/metrics/income-allocation?from=2026-11-01&to=2026-11-30')
            ->assertOk()
            ->assertJsonPath('data.income', 10000)
            ->assertJsonPath('data.spent', 6000)
            ->assertJsonPath('data.saved', 2000)
            // The last day of the range counts in full
            ->assertJsonPath('data.invested', 1000);
    }

    public function test_category_breakdown_lists_spending_categories_biggest_first(): void
    {
        $this->actingAs($this->user);
        $groceries = Category::factory()->create(['type' => Category::EXPENSES, 'name' => 'Groceries', 'color' => 'green', 'icon' => 'ShoppingCart']);
        $carrefour = Brand::factory()->create(['category_id' => $groceries->id]);
        $this->transaction($carrefour, 300, '2026-11-02 09:00:00');
        $this->transaction($carrefour, 400, '2026-11-03 09:00:00');
        $this->transaction($this->expensesBrand, 100, '2026-11-04 09:00:00');
        $this->transaction($this->incomeBrand, 9000, '2026-11-01 09:00:00');

        $this->getJson('/api/v1/metrics/category-breakdown?from=2026-11-01&to=2026-11-30')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.name', 'Groceries')
            ->assertJsonPath('data.0.color', 'green')
            ->assertJsonPath('data.0.icon', 'ShoppingCart')
            ->assertJsonPath('data.0.total', 700)
            ->assertJsonPath('data.0.count', 2)
            ->assertJsonPath('data.1.name', 'Food');
    }

    public function test_top_brands_are_the_five_with_the_most_spending(): void
    {
        $this->actingAs($this->user);
        foreach ([100, 600, 300, 500, 200, 400] as $i => $amount) {
            $brand = Brand::factory()->create(['category_id' => $this->expensesCategory->id, 'name' => "Brand {$amount}"]);
            $this->transaction($brand, $amount, '2026-11-0' . ($i + 1) . ' 09:00:00');
        }
        $this->transaction($this->incomeBrand, 99999, '2026-11-01 09:00:00');

        $this->getJson('/api/v1/metrics/top-brands?from=2026-11-01&to=2026-11-30')
            ->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('data.0.name', 'Brand 600')
            ->assertJsonPath('data.0.total', 600)
            ->assertJsonPath('data.0.category.name', 'Food')
            ->assertJsonPath('data.4.name', 'Brand 200');
    }

    public function test_spending_over_time_fills_empty_months(): void
    {
        $this->actingAs($this->user);
        $this->transaction($this->expensesBrand, 100, '2026-01-10 09:00:00');
        $this->transaction($this->expensesBrand, 250, '2026-03-02 09:00:00');

        $data = $this->getJson('/api/v1/metrics/spending-over-time?from=2026-01-01&to=2026-12-31')->assertOk()->json('data');

        // Up to the current month, not into the future
        $this->assertCount(11, $data);
        $this->assertSame(['period' => '2026-01', 'total' => 100], $data[0]);
        $this->assertSame(['period' => '2026-02', 'total' => 0], $data[1]);
        $this->assertSame(['period' => '2026-03', 'total' => 250], $data[2]);
    }

    public function test_spending_over_time_can_go_by_day(): void
    {
        $this->actingAs($this->user);
        $this->transaction($this->expensesBrand, 40, '2026-11-03 09:00:00');

        $data = $this->getJson('/api/v1/metrics/spending-over-time?from=2026-11-01&to=2026-11-30&by=day')->assertOk()->json('data');

        $this->assertCount(14, $data);
        $this->assertSame(['period' => '2026-11-03', 'total' => 40], $data[2]);
    }

    private function transaction(Brand $brand, float $amount, string $at): void
    {
        Transaction::factory()->create(['brand_id' => $brand->id, 'amount' => $amount, 'created_at' => $at]);
    }
}
