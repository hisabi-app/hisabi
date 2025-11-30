<?php

namespace Tests\Feature\Api\V1\Metrics;

use App\Domains\Transaction\Models\Transaction;
use Carbon\Carbon;

class BrandChangeRateMetricTest extends MetricsTestCase
{
    public function test_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/metrics/brand-change-rate?id=1');
        $response->assertUnauthorized();
    }

    public function test_returns_rate_data(): void
    {
        $this->actingAs($this->user);

        $lastMonth = Carbon::now()->subMonth()->startOfMonth()->addDays(5);
        $thisMonth = Carbon::now()->startOfMonth()->addDays(5);

        $t1 = Transaction::factory()->create(['brand_id' => $this->expensesBrand->id, 'amount' => 100]);
        $t1->created_at = $lastMonth;
        $t1->save();

        $t2 = Transaction::factory()->create(['brand_id' => $this->expensesBrand->id, 'amount' => 150]);
        $t2->created_at = $thisMonth;
        $t2->save();

        $response = $this->getJson('/api/v1/metrics/brand-change-rate?range=current-year&id=' . $this->expensesBrand->id);

        $response->assertOk();
        $data = $response->json('data');
        $this->assertIsArray($data);
        $this->assertCount(2, $data);
    }

    public function test_calculates_change_rate(): void
    {
        $this->actingAs($this->user);

        $lastMonth = Carbon::now()->subMonth()->startOfMonth()->addDays(5);
        $thisMonth = Carbon::now()->startOfMonth()->addDays(5);

        $t1 = Transaction::factory()->create(['brand_id' => $this->expensesBrand->id, 'amount' => 100]);
        $t1->created_at = $lastMonth;
        $t1->save();

        $t2 = Transaction::factory()->create(['brand_id' => $this->expensesBrand->id, 'amount' => 150]);
        $t2->created_at = $thisMonth;
        $t2->save();

        $response = $this->getJson('/api/v1/metrics/brand-change-rate?range=current-year&id=' . $this->expensesBrand->id);

        $response->assertOk();
        $data = $response->json('data');

        // First month should have 0% change rate
        $this->assertEquals(0, $data[0]['value']);
        // Second month: (150 - 100) / 100 * 100 = 50%
        $this->assertEquals(50, $data[1]['value']);
    }

    public function test_returns_empty_array_when_no_data(): void
    {
        $this->actingAs($this->user);

        $response = $this->getJson('/api/v1/metrics/brand-change-rate?range=current-year&id=' . $this->expensesBrand->id);

        $response->assertOk();
        $this->assertIsArray($response->json('data'));
        $this->assertEmpty($response->json('data'));
    }
}
