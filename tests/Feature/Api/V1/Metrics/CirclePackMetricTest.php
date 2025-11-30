<?php

namespace Tests\Feature\Api\V1\Metrics;

use App\Domains\Category\Models\Category;
use App\Domains\Brand\Models\Brand;
use App\Domains\Transaction\Models\Transaction;

class CirclePackMetricTest extends MetricsTestCase
{
    public function test_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/metrics/circle-pack');
        $response->assertUnauthorized();
    }

    public function test_returns_hierarchical_data(): void
    {
        $this->actingAs($this->user);

        Transaction::factory()->create(['brand_id' => $this->expensesBrand->id, 'amount' => 300]);
        Transaction::factory()->create(['brand_id' => $this->incomeBrand->id, 'amount' => 5000]);

        $response = $this->getJson('/api/v1/metrics/circle-pack?range=current-year');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertIsArray($data);
        $this->assertArrayHasKey('children', $data);
    }

    public function test_groups_by_category_and_brand(): void
    {
        $this->actingAs($this->user);

        $groceryBrand = Brand::factory()->create(['category_id' => $this->expensesCategory->id, 'name' => 'Grocery Store']);

        Transaction::factory()->create(['brand_id' => $this->expensesBrand->id, 'amount' => 300]);
        Transaction::factory()->create(['brand_id' => $groceryBrand->id, 'amount' => 500]);

        $response = $this->getJson('/api/v1/metrics/circle-pack?range=current-year');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertArrayHasKey('children', $data);
    }

    public function test_returns_empty_children_when_no_data(): void
    {
        $this->actingAs($this->user);

        $response = $this->getJson('/api/v1/metrics/circle-pack?range=current-year');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertIsArray($data);
        $this->assertArrayHasKey('children', $data);
        $this->assertEmpty($data['children']);
    }
}
