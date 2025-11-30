<?php

namespace Tests\Feature\Api\V1\Metrics;

use App\Domains\Brand\Models\Brand;
use App\Domains\Transaction\Models\Transaction;

class TransactionsByBrandMetricTest extends MetricsTestCase
{
    public function test_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/metrics/transactions-by-brand?id=1');
        $response->assertUnauthorized();
    }

    public function test_returns_data_for_category(): void
    {
        $this->actingAs($this->user);

        Transaction::factory()->create(['brand_id' => $this->expensesBrand->id, 'amount' => 300]);

        $response = $this->getJson('/api/v1/metrics/transactions-by-brand?range=current-year&id=' . $this->expensesCategory->id);

        $response->assertOk();
        $data = $response->json('data');
        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
        $this->assertEquals('Restaurant', $data[0]['label']);
        $this->assertEquals(1, $data[0]['value']);
    }

    public function test_groups_by_brand_within_category(): void
    {
        $this->actingAs($this->user);

        $groceryBrand = Brand::factory()->create(['category_id' => $this->expensesCategory->id, 'name' => 'Grocery Store']);

        Transaction::factory()->create(['brand_id' => $this->expensesBrand->id, 'amount' => 100]);
        Transaction::factory()->create(['brand_id' => $groceryBrand->id, 'amount' => 150]);

        $response = $this->getJson('/api/v1/metrics/transactions-by-brand?range=current-year&id=' . $this->expensesCategory->id);

        $response->assertOk();
        $data = $response->json('data');
        $this->assertCount(2, $data);
    }

    public function test_orders_by_count_descending(): void
    {
        $this->actingAs($this->user);

        $groceryBrand = Brand::factory()->create(['category_id' => $this->expensesCategory->id, 'name' => 'Grocery Store']);

        Transaction::factory()->count(3)->create(['brand_id' => $this->expensesBrand->id, 'amount' => 100]);
        Transaction::factory()->create(['brand_id' => $groceryBrand->id, 'amount' => 150]);

        $response = $this->getJson('/api/v1/metrics/transactions-by-brand?range=current-year&id=' . $this->expensesCategory->id);

        $response->assertOk();
        $data = $response->json('data');
        $this->assertEquals('Restaurant', $data[0]['label']);
        $this->assertEquals(3, $data[0]['value']);
    }

    public function test_returns_empty_array_when_no_data(): void
    {
        $this->actingAs($this->user);

        $response = $this->getJson('/api/v1/metrics/transactions-by-brand?range=current-year&id=' . $this->expensesCategory->id);

        $response->assertOk();
        $this->assertIsArray($response->json('data'));
        $this->assertEmpty($response->json('data'));
    }
}
