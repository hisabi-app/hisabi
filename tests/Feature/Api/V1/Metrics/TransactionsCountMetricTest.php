<?php

namespace Tests\Feature\Api\V1\Metrics;

use App\Domains\Category\Models\Category;
use App\Domains\Transaction\Models\Transaction;

class TransactionsCountMetricTest extends MetricsTestCase
{
    public function test_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/metrics/transactions-count');
        $response->assertUnauthorized();
    }

    public function test_returns_count_by_type(): void
    {
        $this->actingAs($this->user);

        Transaction::factory()->count(3)->create(['brand_id' => $this->incomeBrand->id]);
        Transaction::factory()->count(5)->create(['brand_id' => $this->expensesBrand->id]);

        $response = $this->getJson('/api/v1/metrics/transactions-count?range=current-year');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertIsArray($data);

        $incomeCount = collect($data)->firstWhere('label', Category::INCOME);
        $expensesCount = collect($data)->firstWhere('label', Category::EXPENSES);

        $this->assertEquals(3, $incomeCount['value']);
        $this->assertEquals(5, $expensesCount['value']);
    }

    public function test_orders_by_count_descending(): void
    {
        $this->actingAs($this->user);

        Transaction::factory()->count(2)->create(['brand_id' => $this->incomeBrand->id]);
        Transaction::factory()->count(5)->create(['brand_id' => $this->expensesBrand->id]);

        $response = $this->getJson('/api/v1/metrics/transactions-count?range=current-year');

        $response->assertOk();
        $data = $response->json('data');

        $this->assertEquals(Category::EXPENSES, $data[0]['label']);
        $this->assertEquals(Category::INCOME, $data[1]['label']);
    }

    public function test_returns_empty_array_when_no_data(): void
    {
        $this->actingAs($this->user);

        $response = $this->getJson('/api/v1/metrics/transactions-count?range=current-year');

        $response->assertOk();
        $this->assertIsArray($response->json('data'));
        $this->assertEmpty($response->json('data'));
    }
}
