<?php

namespace Tests\Feature\Api\V1\Metrics;

use App\Domains\Transaction\Models\Transaction;

class TotalInvestmentMetricTest extends MetricsTestCase
{
    public function test_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/metrics/total-investment');
        $response->assertUnauthorized();
    }

    public function test_returns_correct_value(): void
    {
        $this->actingAs($this->user);

        Transaction::factory()->create(['brand_id' => $this->investmentBrand->id, 'amount' => 2000]);
        Transaction::factory()->create(['brand_id' => $this->investmentBrand->id, 'amount' => 1000]);

        $response = $this->getJson('/api/v1/metrics/total-investment');

        $response->assertOk();
        $this->assertEquals(3000, $response->json('data.value'));
    }

    public function test_excludes_other_types(): void
    {
        $this->actingAs($this->user);

        Transaction::factory()->create(['brand_id' => $this->investmentBrand->id, 'amount' => 2000]);
        Transaction::factory()->create(['brand_id' => $this->incomeBrand->id, 'amount' => 5000]);
        Transaction::factory()->create(['brand_id' => $this->savingsBrand->id, 'amount' => 1000]);

        $response = $this->getJson('/api/v1/metrics/total-investment');

        $response->assertOk();
        $this->assertEquals(2000, $response->json('data.value'));
    }

    public function test_returns_zero_when_no_data(): void
    {
        $this->actingAs($this->user);

        $response = $this->getJson('/api/v1/metrics/total-investment');

        $response->assertOk();
        $this->assertEquals(0, $response->json('data.value'));
    }
}
