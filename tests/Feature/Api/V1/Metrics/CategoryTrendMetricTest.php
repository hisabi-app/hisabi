<?php

namespace Tests\Feature\Api\V1\Metrics;

use App\Domains\Transaction\Models\Transaction;
use Carbon\Carbon;

class CategoryTrendMetricTest extends MetricsTestCase
{
    public function test_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/metrics/category-trend?id=1');
        $response->assertUnauthorized();
    }

    public function test_returns_data_for_category(): void
    {
        $this->actingAs($this->user);

        Transaction::factory()->create([
            'brand_id' => $this->expensesBrand->id,
            'amount' => 500,
            'created_at' => Carbon::now()->startOfMonth()
        ]);

        $response = $this->getJson('/api/v1/metrics/category-trend?range=current-year&id=' . $this->expensesCategory->id);

        $response->assertOk();
        $data = $response->json('data');
        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
        $this->assertEquals(500, $data[0]['value']);
    }

    public function test_filters_by_category(): void
    {
        $this->actingAs($this->user);

        Transaction::factory()->create([
            'brand_id' => $this->expensesBrand->id,
            'amount' => 500,
            'created_at' => Carbon::now()->startOfMonth()
        ]);
        Transaction::factory()->create([
            'brand_id' => $this->incomeBrand->id,
            'amount' => 5000,
            'created_at' => Carbon::now()->startOfMonth()
        ]);

        $response = $this->getJson('/api/v1/metrics/category-trend?range=current-year&id=' . $this->expensesCategory->id);

        $response->assertOk();
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals(500, $data[0]['value']);
    }

    public function test_returns_empty_array_when_no_data(): void
    {
        $this->actingAs($this->user);

        $response = $this->getJson('/api/v1/metrics/category-trend?range=current-year&id=' . $this->expensesCategory->id);

        $response->assertOk();
        $this->assertIsArray($response->json('data'));
        $this->assertEmpty($response->json('data'));
    }
}
