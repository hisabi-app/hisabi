<?php

namespace Tests\Feature\Api\V1\Metrics;

use App\Domains\Transaction\Models\Transaction;

class AverageTransactionMetricTest extends MetricsTestCase
{
    public function test_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/metrics/average-transaction');
        $response->assertUnauthorized();
    }

    public function test_returns_average_transaction_by_category(): void
    {
        $this->actingAs($this->user);

        Transaction::factory()->create(['brand_id' => $this->expensesBrand->id, 'amount' => 100]);
        Transaction::factory()->create(['brand_id' => $this->expensesBrand->id, 'amount' => 200]);
        Transaction::factory()->create(['brand_id' => $this->expensesBrand->id, 'amount' => 300]);

        $response = $this->getJson('/api/v1/metrics/average-transaction?range=current-year');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertIsArray($data);
        $foodCategory = collect($data)->firstWhere('label', 'Food');
        $this->assertEquals(200, $foodCategory['value']);
    }

    public function test_groups_by_category(): void
    {
        $this->actingAs($this->user);

        Transaction::factory()->create(['brand_id' => $this->expensesBrand->id, 'amount' => 500]);
        Transaction::factory()->create(['brand_id' => $this->incomeBrand->id, 'amount' => 5000]);

        $response = $this->getJson('/api/v1/metrics/average-transaction?range=current-year');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertCount(2, $data);
    }

    public function test_returns_empty_array_when_no_data(): void
    {
        $this->actingAs($this->user);

        $response = $this->getJson('/api/v1/metrics/average-transaction?range=current-year');

        $response->assertOk();
        $this->assertIsArray($response->json('data'));
        $this->assertEmpty($response->json('data'));
    }
}
