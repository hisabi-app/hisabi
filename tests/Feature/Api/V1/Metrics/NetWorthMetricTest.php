<?php

namespace Tests\Feature\Api\V1\Metrics;

use App\Domains\Brand\Models\Brand;
use App\Domains\Transaction\Models\Transaction;

class NetWorthMetricTest extends MetricsTestCase
{
    public function test_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/metrics/net-worth');
        $response->assertUnauthorized();
    }

    public function test_returns_correct_value(): void
    {
        $this->actingAs($this->user);

        Transaction::factory()->create(['brand_id' => $this->incomeBrand->id, 'amount' => 10000]);
        Transaction::factory()->create(['brand_id' => $this->expensesBrand->id, 'amount' => 3000]);

        $response = $this->getJson('/api/v1/metrics/net-worth');

        $response->assertOk();
        $this->assertEquals(7000, $response->json('data.value'));
    }

    public function test_returns_negative_when_expenses_exceed_income(): void
    {
        $this->actingAs($this->user);

        Transaction::factory()->create(['brand_id' => $this->incomeBrand->id, 'amount' => 1000]);
        Transaction::factory()->create(['brand_id' => $this->expensesBrand->id, 'amount' => 3000]);

        $response = $this->getJson('/api/v1/metrics/net-worth');

        $response->assertOk();
        $this->assertEquals(-2000, $response->json('data.value'));
    }

    public function test_excludes_savings_and_investments(): void
    {
        $this->actingAs($this->user);

        Transaction::factory()->create(['brand_id' => $this->incomeBrand->id, 'amount' => 10000]);
        Transaction::factory()->create(['brand_id' => $this->expensesBrand->id, 'amount' => 3000]);
        Transaction::factory()->create(['brand_id' => $this->savingsBrand->id, 'amount' => 1000]);
        Transaction::factory()->create(['brand_id' => $this->investmentBrand->id, 'amount' => 500]);

        $response = $this->getJson('/api/v1/metrics/net-worth');

        $response->assertOk();
        // Net Worth = Income - Expenses (savings and investments are not counted)
        $this->assertEquals(7000, $response->json('data.value'));
    }

    public function test_returns_zero_when_no_data(): void
    {
        $this->actingAs($this->user);

        $response = $this->getJson('/api/v1/metrics/net-worth');

        $response->assertOk();
        $this->assertEquals(0, $response->json('data.value'));
    }

    public function test_with_a_range_it_is_where_it_stood_at_the_end_and_what_the_range_changed(): void
    {
        $this->travelTo('2026-12-15 12:00:00');
        $this->actingAs($this->user);
        $this->transaction($this->incomeBrand, 10000, '2026-01-10 09:00:00');
        $this->transaction($this->expensesBrand, 3000, '2026-11-05 09:00:00');
        // The last day of the range counts in full
        $this->transaction($this->incomeBrand, 2000, '2026-11-30 22:00:00');
        $this->transaction($this->expensesBrand, 500, '2026-12-01 09:00:00');

        $this->getJson('/api/v1/metrics/net-worth?from=2026-11-01&to=2026-11-30')
            ->assertOk()
            ->assertJsonPath('data.value', 9000)
            ->assertJsonPath('data.change', -1000);
    }

    public function test_without_a_range_there_is_no_change(): void
    {
        $this->actingAs($this->user);
        $this->transaction($this->incomeBrand, 10000, '2026-01-10 09:00:00');

        $this->getJson('/api/v1/metrics/net-worth')
            ->assertOk()
            ->assertJsonPath('data.value', 10000)
            ->assertJsonPath('data.change', null);
    }

    public function test_nothing_dated_after_today_counts(): void
    {
        $this->travelTo('2026-11-14 18:00:00');
        $this->actingAs($this->user);
        $this->transaction($this->incomeBrand, 10000, '2026-11-01 09:00:00');
        $this->transaction($this->expensesBrand, 500, '2026-12-01 09:00:00');

        $this->getJson('/api/v1/metrics/net-worth?from=2026-01-01&to=2026-12-31')
            ->assertOk()
            ->assertJsonPath('data.value', 10000)
            ->assertJsonPath('data.change', 10000);
    }

    private function transaction(Brand $brand, float $amount, string $at): void
    {
        Transaction::factory()->create(['brand_id' => $brand->id, 'amount' => $amount, 'created_at' => $at]);
    }
}
