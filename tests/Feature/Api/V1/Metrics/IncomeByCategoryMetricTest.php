<?php

namespace Tests\Feature\Api\V1\Metrics;

use App\Domains\Category\Models\Category;
use App\Domains\Brand\Models\Brand;
use App\Domains\Transaction\Models\Transaction;

class IncomeByCategoryMetricTest extends MetricsTestCase
{
    public function test_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/metrics/income-by-category');
        $response->assertUnauthorized();
    }

    public function test_returns_grouped_data(): void
    {
        $this->actingAs($this->user);

        Transaction::factory()->create(['brand_id' => $this->incomeBrand->id, 'amount' => 5000]);

        $response = $this->getJson('/api/v1/metrics/income-by-category?range=current-year');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
        $this->assertEquals('Salary', $data[0]['label']);
        $this->assertEquals(5000, $data[0]['value']);
    }

    public function test_excludes_expenses(): void
    {
        $this->actingAs($this->user);

        Transaction::factory()->create(['brand_id' => $this->incomeBrand->id, 'amount' => 5000]);
        Transaction::factory()->create(['brand_id' => $this->expensesBrand->id, 'amount' => 500]);

        $response = $this->getJson('/api/v1/metrics/income-by-category?range=current-year');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('Salary', $data[0]['label']);
    }

    public function test_groups_multiple_categories(): void
    {
        $this->actingAs($this->user);

        $freelanceCategory = Category::factory()->create(['type' => Category::INCOME, 'name' => 'Freelance']);
        $freelanceBrand = Brand::factory()->create(['category_id' => $freelanceCategory->id]);

        Transaction::factory()->create(['brand_id' => $this->incomeBrand->id, 'amount' => 5000]);
        Transaction::factory()->create(['brand_id' => $freelanceBrand->id, 'amount' => 2000]);

        $response = $this->getJson('/api/v1/metrics/income-by-category?range=current-year');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertCount(2, $data);
    }

    public function test_returns_empty_array_when_no_data(): void
    {
        $this->actingAs($this->user);

        $response = $this->getJson('/api/v1/metrics/income-by-category?range=current-year');

        $response->assertOk();
        $this->assertIsArray($response->json('data'));
        $this->assertEmpty($response->json('data'));
    }
}
