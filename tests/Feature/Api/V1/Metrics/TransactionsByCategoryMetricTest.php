<?php

namespace Tests\Feature\Api\V1\Metrics;

use App\Domains\Category\Models\Category;
use App\Domains\Brand\Models\Brand;
use App\Domains\Transaction\Models\Transaction;

class TransactionsByCategoryMetricTest extends MetricsTestCase
{
    public function test_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/metrics/transactions-by-category');
        $response->assertUnauthorized();
    }

    public function test_returns_data_grouped_by_category(): void
    {
        $this->actingAs($this->user);

        Transaction::factory()->create(['brand_id' => $this->expensesBrand->id, 'amount' => 300]);

        $response = $this->getJson('/api/v1/metrics/transactions-by-category?range=current-year');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
        $foodCategory = collect($data)->firstWhere('label', 'Food');
        $this->assertEquals(1, $foodCategory['value']);
    }

    public function test_groups_multiple_categories(): void
    {
        $this->actingAs($this->user);

        Transaction::factory()->create(['brand_id' => $this->expensesBrand->id, 'amount' => 300]);
        Transaction::factory()->create(['brand_id' => $this->incomeBrand->id, 'amount' => 5000]);

        $response = $this->getJson('/api/v1/metrics/transactions-by-category?range=current-year');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertCount(2, $data);
    }

    public function test_orders_by_count_descending(): void
    {
        $this->actingAs($this->user);

        Transaction::factory()->count(3)->create(['brand_id' => $this->expensesBrand->id, 'amount' => 100]);
        Transaction::factory()->create(['brand_id' => $this->incomeBrand->id, 'amount' => 5000]);

        $response = $this->getJson('/api/v1/metrics/transactions-by-category?range=current-year');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertEquals('Food', $data[0]['label']);
        $this->assertEquals(3, $data[0]['value']);
    }

    public function test_returns_empty_array_when_no_data(): void
    {
        $this->actingAs($this->user);

        $response = $this->getJson('/api/v1/metrics/transactions-by-category?range=current-year');

        $response->assertOk();
        $this->assertIsArray($response->json('data'));
        $this->assertEmpty($response->json('data'));
    }
}
