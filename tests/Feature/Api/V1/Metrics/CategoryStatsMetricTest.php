<?php

namespace Tests\Feature\Api\V1\Metrics;

use App\Domains\Brand\Models\Brand;
use App\Domains\Transaction\Models\Transaction;

class CategoryStatsMetricTest extends MetricsTestCase
{
    public function test_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/metrics/category-stats');
        $response->assertUnauthorized();
    }

    public function test_returns_most_used_category(): void
    {
        $this->actingAs($this->user);

        Transaction::factory()->count(3)->create(['brand_id' => $this->expensesBrand->id, 'amount' => 100]);

        $response = $this->getJson('/api/v1/metrics/category-stats?range=current-year');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertArrayHasKey('mostUsedCategory', $data);
        $this->assertEquals('Food', $data['mostUsedCategory']['name']);
        $this->assertEquals(3, $data['mostUsedCategory']['count']);
    }

    public function test_returns_highest_spending_category(): void
    {
        $this->actingAs($this->user);

        Transaction::factory()->create(['brand_id' => $this->expensesBrand->id, 'amount' => 500]);

        $response = $this->getJson('/api/v1/metrics/category-stats?range=current-year');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertArrayHasKey('highestSpendingCategory', $data);
        $this->assertEquals('Food', $data['highestSpendingCategory']['name']);
        $this->assertEquals(500, $data['highestSpendingCategory']['amount']);
    }

    public function test_returns_highest_income_category(): void
    {
        $this->actingAs($this->user);

        Transaction::factory()->create(['brand_id' => $this->incomeBrand->id, 'amount' => 5000]);

        $response = $this->getJson('/api/v1/metrics/category-stats?range=current-year');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertArrayHasKey('highestIncomeCategory', $data);
        $this->assertEquals('Salary', $data['highestIncomeCategory']['name']);
        $this->assertEquals(5000, $data['highestIncomeCategory']['amount']);
    }

    public function test_returns_nulls_when_no_data(): void
    {
        $this->actingAs($this->user);

        $response = $this->getJson('/api/v1/metrics/category-stats?range=current-year');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertNull($data['mostUsedCategory']);
        $this->assertNull($data['highestSpendingCategory']);
        $this->assertNull($data['highestIncomeCategory']);
    }
}
