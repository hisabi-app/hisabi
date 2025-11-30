<?php

namespace Tests\Feature\Api\V1\Metrics;

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
}
