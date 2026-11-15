<?php

namespace Tests\Feature\Api\V1\Metrics;

use App\Domains\Brand\Models\Brand;
use App\Domains\Transaction\Models\Transaction;
use Carbon\Carbon;

class NetWorthTrendMetricTest extends MetricsTestCase
{
    public function test_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/metrics/net-worth-trend');
        $response->assertUnauthorized();
    }

    public function test_returns_monthly_data(): void
    {
        $this->actingAs($this->user);

        Transaction::factory()->create([
            'brand_id' => $this->incomeBrand->id,
            'amount' => 5000,
            'created_at' => Carbon::now()->startOfMonth()
        ]);
        Transaction::factory()->create([
            'brand_id' => $this->expensesBrand->id,
            'amount' => 2000,
            'created_at' => Carbon::now()->startOfMonth()
        ]);

        $response = $this->getJson('/api/v1/metrics/net-worth-trend?range=current-year');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
    }

    public function test_calculates_running_net_worth(): void
    {
        $this->actingAs($this->user);

        $lastMonth = Carbon::now()->subMonth()->startOfMonth()->addDays(5);
        $thisMonth = Carbon::now()->startOfMonth()->addDays(5);

        $t1 = Transaction::factory()->create(['brand_id' => $this->incomeBrand->id, 'amount' => 5000]);
        $t1->created_at = $lastMonth;
        $t1->save();

        $t2 = Transaction::factory()->create(['brand_id' => $this->incomeBrand->id, 'amount' => 3000]);
        $t2->created_at = $thisMonth;
        $t2->save();

        $response = $this->getJson('/api/v1/metrics/net-worth-trend?range=current-year');

        $response->assertOk();
        $data = $response->json('data');

        // Should show cumulative net worth
        $this->assertCount(2, $data);
        $this->assertEquals(5000, $data[0]['value']);
        $this->assertEquals(8000, $data[1]['value']); // 5000 + 3000
    }

    public function test_returns_empty_array_when_no_data(): void
    {
        $this->actingAs($this->user);

        $response = $this->getJson('/api/v1/metrics/net-worth-trend?range=current-year');

        $response->assertOk();
        $this->assertIsArray($response->json('data'));
        $this->assertEmpty($response->json('data'));
    }

    public function test_a_month_goes_by_day_and_carries_in_what_came_before(): void
    {
        $this->travelTo('2026-11-14 18:00:00');
        $this->actingAs($this->user);
        $this->transaction($this->incomeBrand, 10000, '2026-10-20 09:00:00');
        $this->transaction($this->expensesBrand, 1000, '2026-11-02 09:00:00');
        $this->transaction($this->incomeBrand, 500, '2026-11-14 09:00:00');

        $data = $this->getJson('/api/v1/metrics/net-worth-trend?from=2026-11-01&to=2026-11-30&by=day')->assertOk()->json('data');

        // Every day up to today
        $this->assertCount(14, $data);
        $this->assertEquals(['label' => '2026-11-01', 'value' => 10000], $data[0]);
        $this->assertEquals(['label' => '2026-11-02', 'value' => 9000], $data[1]);
        $this->assertEquals(['label' => '2026-11-14', 'value' => 9500], $data[13]);
    }

    public function test_a_year_goes_by_month_from_the_first_transaction(): void
    {
        $this->travelTo('2026-11-14 18:00:00');
        $this->actingAs($this->user);
        $this->transaction($this->incomeBrand, 5000, '2026-03-05 09:00:00');
        $this->transaction($this->expensesBrand, 1000, '2026-06-10 09:00:00');

        $data = $this->getJson('/api/v1/metrics/net-worth-trend?from=2026-01-01&to=2026-12-31')->assertOk()->json('data');

        $this->assertSame(['2026-03', '2026-04', '2026-05', '2026-06', '2026-07', '2026-08', '2026-09', '2026-10', '2026-11'], array_column($data, 'label'));
        $this->assertEquals(5000, $data[1]['value']);
        $this->assertEquals(4000, $data[8]['value']);
    }

    public function test_a_range_before_the_first_transaction_is_empty(): void
    {
        $this->travelTo('2026-11-14 18:00:00');
        $this->actingAs($this->user);
        $this->transaction($this->incomeBrand, 5000, '2026-03-05 09:00:00');

        $this->getJson('/api/v1/metrics/net-worth-trend?from=2025-01-01&to=2025-12-31')->assertOk()->assertExactJson(['data' => []]);
    }

    private function transaction(Brand $brand, float $amount, string $at): void
    {
        Transaction::factory()->create(['brand_id' => $brand->id, 'amount' => $amount, 'created_at' => $at]);
    }
}
