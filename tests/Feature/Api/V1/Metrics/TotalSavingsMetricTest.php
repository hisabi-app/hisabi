<?php

namespace Tests\Feature\Api\V1\Metrics;

use App\Domains\Transaction\Models\Transaction;

class TotalSavingsMetricTest extends MetricsTestCase
{
    public function test_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/metrics/total-savings');
        $response->assertUnauthorized();
    }

    public function test_returns_correct_value(): void
    {
        $this->actingAs($this->user);

        Transaction::factory()->create(['brand_id' => $this->savingsBrand->id, 'amount' => 1000]);
        Transaction::factory()->create(['brand_id' => $this->savingsBrand->id, 'amount' => 500]);

        $response = $this->getJson('/api/v1/metrics/total-savings');

        $response->assertOk();
        $this->assertEquals(1500, $response->json('data.value'));
    }

    public function test_excludes_other_types(): void
    {
        $this->actingAs($this->user);

        Transaction::factory()->create(['brand_id' => $this->savingsBrand->id, 'amount' => 1000]);
        Transaction::factory()->create(['brand_id' => $this->incomeBrand->id, 'amount' => 5000]);
        Transaction::factory()->create(['brand_id' => $this->expensesBrand->id, 'amount' => 500]);

        $response = $this->getJson('/api/v1/metrics/total-savings');

        $response->assertOk();
        $this->assertEquals(1000, $response->json('data.value'));
    }

    public function test_returns_zero_when_no_data(): void
    {
        $this->actingAs($this->user);

        $response = $this->getJson('/api/v1/metrics/total-savings');

        $response->assertOk();
        $this->assertEquals(0, $response->json('data.value'));
    }
}
