<?php

namespace Tests\Feature\Api\V1\Metrics;

use App\Domains\Transaction\Models\Transaction;
use Carbon\Carbon;

class TotalExpensesTrendMetricTest extends MetricsTestCase
{
    public function test_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/metrics/total-expenses-trend');
        $response->assertUnauthorized();
    }

    public function test_returns_monthly_data(): void
    {
        $this->actingAs($this->user);

        Transaction::factory()->create([
            'brand_id' => $this->expensesBrand->id,
            'amount' => 1500,
            'created_at' => Carbon::now()->startOfMonth()
        ]);

        $response = $this->getJson('/api/v1/metrics/total-expenses-trend?range=current-year');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
        $this->assertEquals(1500, $data[0]['value']);
    }

    public function test_groups_by_month(): void
    {
        $this->actingAs($this->user);

        $thisMonth = Carbon::now()->startOfMonth()->addDays(5);

        $t1 = Transaction::factory()->create(['brand_id' => $this->expensesBrand->id, 'amount' => 500]);
        $t1->created_at = $thisMonth;
        $t1->save();

        $t2 = Transaction::factory()->create(['brand_id' => $this->expensesBrand->id, 'amount' => 300]);
        $t2->created_at = $thisMonth->copy()->addDays(5);
        $t2->save();

        $response = $this->getJson('/api/v1/metrics/total-expenses-trend?range=current-year');

        $response->assertOk();
        $data = $response->json('data');

        $this->assertCount(1, $data);
        $this->assertEquals(800, $data[0]['value']);
    }

    public function test_excludes_income(): void
    {
        $this->actingAs($this->user);

        Transaction::factory()->create([
            'brand_id' => $this->expensesBrand->id,
            'amount' => 500,
            'created_at' => Carbon::now()->startOfMonth()
        ]);
        Transaction::factory()->create([
            'brand_id' => $this->incomeBrand->id,
            'amount' => 5000,
            'created_at' => Carbon::now()->startOfMonth()
        ]);

        $response = $this->getJson('/api/v1/metrics/total-expenses-trend?range=current-year');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertEquals(500, $data[0]['value']);
    }

    public function test_returns_empty_array_when_no_data(): void
    {
        $this->actingAs($this->user);

        $response = $this->getJson('/api/v1/metrics/total-expenses-trend?range=current-year');

        $response->assertOk();
        $this->assertIsArray($response->json('data'));
        $this->assertEmpty($response->json('data'));
    }
}
