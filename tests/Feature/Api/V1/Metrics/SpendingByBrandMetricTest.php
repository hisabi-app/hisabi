<?php

namespace Tests\Feature\Api\V1\Metrics;

use App\Domains\Brand\Models\Brand;
use App\Domains\Transaction\Models\Transaction;

class SpendingByBrandMetricTest extends MetricsTestCase
{
    public function test_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/metrics/spending-by-brand?category_id=1');
        $response->assertUnauthorized();
    }

    public function test_returns_data_for_category(): void
    {
        $this->actingAs($this->user);

        Transaction::factory()->create(['brand_id' => $this->expensesBrand->id, 'amount' => 300]);

        $response = $this->getJson('/api/v1/metrics/spending-by-brand?range=current-year&category_id=' . $this->expensesCategory->id);

        $response->assertOk();
        $data = $response->json('data');
        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
        $this->assertEquals('Restaurant', $data[0]['label']);
        $this->assertEquals(300, $data[0]['value']);
    }

    public function test_filters_by_category(): void
    {
        $this->actingAs($this->user);

        Transaction::factory()->create(['brand_id' => $this->expensesBrand->id, 'amount' => 300]);
        Transaction::factory()->create(['brand_id' => $this->incomeBrand->id, 'amount' => 5000]);

        $response = $this->getJson('/api/v1/metrics/spending-by-brand?range=current-year&category_id=' . $this->expensesCategory->id);

        $response->assertOk();
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('Restaurant', $data[0]['label']);
    }

    public function test_groups_multiple_brands(): void
    {
        $this->actingAs($this->user);

        $groceryBrand = Brand::factory()->create(['category_id' => $this->expensesCategory->id, 'name' => 'Grocery Store']);

        Transaction::factory()->create(['brand_id' => $this->expensesBrand->id, 'amount' => 300]);
        Transaction::factory()->create(['brand_id' => $groceryBrand->id, 'amount' => 500]);

        $response = $this->getJson('/api/v1/metrics/spending-by-brand?range=current-year&category_id=' . $this->expensesCategory->id);

        $response->assertOk();
        $data = $response->json('data');
        $this->assertCount(2, $data);
    }

    public function test_orders_by_value_descending(): void
    {
        $this->actingAs($this->user);

        $groceryBrand = Brand::factory()->create(['category_id' => $this->expensesCategory->id, 'name' => 'Grocery Store']);

        Transaction::factory()->create(['brand_id' => $this->expensesBrand->id, 'amount' => 300]);
        Transaction::factory()->create(['brand_id' => $groceryBrand->id, 'amount' => 500]);

        $response = $this->getJson('/api/v1/metrics/spending-by-brand?range=current-year&category_id=' . $this->expensesCategory->id);

        $response->assertOk();
        $data = $response->json('data');
        $this->assertEquals('Grocery Store', $data[0]['label']);
        $this->assertEquals('Restaurant', $data[1]['label']);
    }

    public function test_returns_empty_array_when_no_data(): void
    {
        $this->actingAs($this->user);

        $response = $this->getJson('/api/v1/metrics/spending-by-brand?range=current-year&category_id=' . $this->expensesCategory->id);

        $response->assertOk();
        $this->assertIsArray($response->json('data'));
        $this->assertEmpty($response->json('data'));
    }
}
