<?php

namespace Tests\Feature\Api\V1\Metrics;

use App\Domains\Brand\Models\Brand;
use App\Domains\Transaction\Models\Transaction;

class BrandStatsMetricTest extends MetricsTestCase
{
    public function test_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/metrics/brand-stats');
        $response->assertUnauthorized();
    }

    public function test_returns_most_used_brand(): void
    {
        $this->actingAs($this->user);

        Transaction::factory()->count(3)->create(['brand_id' => $this->expensesBrand->id, 'amount' => 100]);

        $response = $this->getJson('/api/v1/metrics/brand-stats?range=current-year');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertArrayHasKey('mostUsedBrand', $data);
        $this->assertEquals('Restaurant', $data['mostUsedBrand']['name']);
        $this->assertEquals(3, $data['mostUsedBrand']['count']);
    }

    public function test_returns_highest_spending_brand(): void
    {
        $this->actingAs($this->user);

        Transaction::factory()->create(['brand_id' => $this->expensesBrand->id, 'amount' => 500]);

        $response = $this->getJson('/api/v1/metrics/brand-stats?range=current-year');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertArrayHasKey('highestSpendingBrand', $data);
        $this->assertEquals('Restaurant', $data['highestSpendingBrand']['name']);
        $this->assertEquals(500, $data['highestSpendingBrand']['amount']);
    }

    public function test_returns_highest_income_brand(): void
    {
        $this->actingAs($this->user);

        Transaction::factory()->create(['brand_id' => $this->incomeBrand->id, 'amount' => 5000]);

        $response = $this->getJson('/api/v1/metrics/brand-stats?range=current-year');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertArrayHasKey('highestIncomeBrand', $data);
        $this->assertEquals('Company A', $data['highestIncomeBrand']['name']);
        $this->assertEquals(5000, $data['highestIncomeBrand']['amount']);
    }

    public function test_returns_nulls_when_no_data(): void
    {
        $this->actingAs($this->user);

        $response = $this->getJson('/api/v1/metrics/brand-stats?range=current-year');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertNull($data['mostUsedBrand']);
        $this->assertNull($data['highestSpendingBrand']);
        $this->assertNull($data['highestIncomeBrand']);
    }
}
