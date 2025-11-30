<?php

namespace Tests\Feature\Api\V1\Metrics;

use App\Domains\Transaction\Models\Transaction;

class TotalCashMetricTest extends MetricsTestCase
{
    public function test_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/metrics/total-cash');
        $response->assertUnauthorized();
    }

    public function test_returns_correct_value(): void
    {
        $this->actingAs($this->user);

        Transaction::factory()->create(['brand_id' => $this->incomeBrand->id, 'amount' => 10000]);
        Transaction::factory()->create(['brand_id' => $this->expensesBrand->id, 'amount' => 2000]);
        Transaction::factory()->create(['brand_id' => $this->savingsBrand->id, 'amount' => 1000]);
        Transaction::factory()->create(['brand_id' => $this->investmentBrand->id, 'amount' => 500]);

        $response = $this->getJson('/api/v1/metrics/total-cash');

        $response->assertOk();
        // Cash = Income - (Expenses + Savings + Investment) = 10000 - (2000 + 1000 + 500) = 6500
        $this->assertEquals(6500, $response->json('data.value'));
    }

    public function test_returns_negative_when_expenses_exceed_income(): void
    {
        $this->actingAs($this->user);

        Transaction::factory()->create(['brand_id' => $this->incomeBrand->id, 'amount' => 1000]);
        Transaction::factory()->create(['brand_id' => $this->expensesBrand->id, 'amount' => 2000]);

        $response = $this->getJson('/api/v1/metrics/total-cash');

        $response->assertOk();
        $this->assertEquals(-1000, $response->json('data.value'));
    }

    public function test_returns_zero_when_no_data(): void
    {
        $this->actingAs($this->user);

        $response = $this->getJson('/api/v1/metrics/total-cash');

        $response->assertOk();
        $this->assertEquals(0, $response->json('data.value'));
    }
}
