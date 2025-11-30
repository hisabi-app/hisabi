<?php

namespace Tests\Feature\Api\V1\Metrics;

use App\Domains\Transaction\Models\Transaction;

class TransactionsStdDevMetricTest extends MetricsTestCase
{
    public function test_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/metrics/transactions-std-dev?id=1');
        $response->assertUnauthorized();
    }

    public function test_returns_standard_deviation(): void
    {
        $this->actingAs($this->user);

        Transaction::factory()->create(['brand_id' => $this->expensesBrand->id, 'amount' => 100]);
        Transaction::factory()->create(['brand_id' => $this->expensesBrand->id, 'amount' => 200]);
        Transaction::factory()->create(['brand_id' => $this->expensesBrand->id, 'amount' => 300]);

        $response = $this->getJson('/api/v1/metrics/transactions-std-dev?range=current-year&id=' . $this->expensesCategory->id);

        $response->assertOk();
        $data = $response->json('data');
        $this->assertArrayHasKey('value', $data);
        $this->assertIsNumeric($data['value']);
        $this->assertGreaterThan(0, $data['value']);
    }

    public function test_returns_zero_when_no_data(): void
    {
        $this->actingAs($this->user);

        $response = $this->getJson('/api/v1/metrics/transactions-std-dev?range=current-year&id=' . $this->expensesCategory->id);

        $response->assertOk();
        $this->assertEquals(0, $response->json('data.value'));
    }

    public function test_returns_zero_when_single_transaction(): void
    {
        $this->actingAs($this->user);

        Transaction::factory()->create(['brand_id' => $this->expensesBrand->id, 'amount' => 100]);

        $response = $this->getJson('/api/v1/metrics/transactions-std-dev?range=current-year&id=' . $this->expensesCategory->id);

        $response->assertOk();
        $this->assertEquals(0, $response->json('data.value'));
    }
}
