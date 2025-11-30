<?php

namespace Tests\Feature\Api\V1\Metrics;

use App\Domains\Transaction\Models\Transaction;
use Carbon\Carbon;

class CategoryDailyTrendMetricTest extends MetricsTestCase
{
    public function test_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/metrics/category-daily-trend?id=1');
        $response->assertUnauthorized();
    }

    public function test_returns_daily_data(): void
    {
        $this->actingAs($this->user);

        $today = Carbon::now()->startOfDay();
        Transaction::factory()->create([
            'brand_id' => $this->expensesBrand->id,
            'amount' => 100,
            'created_at' => $today
        ]);

        $response = $this->getJson('/api/v1/metrics/category-daily-trend?range=current-month&id=' . $this->expensesCategory->id);

        $response->assertOk();
        $data = $response->json('data');
        $this->assertIsArray($data);
    }

    public function test_fills_missing_days_with_zero(): void
    {
        $this->actingAs($this->user);

        $today = Carbon::now()->startOfMonth()->addDays(5);
        $t = Transaction::factory()->create(['brand_id' => $this->expensesBrand->id, 'amount' => 100]);
        $t->created_at = $today;
        $t->save();

        $response = $this->getJson('/api/v1/metrics/category-daily-trend?range=current-month&id=' . $this->expensesCategory->id);

        $response->assertOk();
        $data = $response->json('data');

        // Should have entries for all days in the month
        $daysInMonth = Carbon::now()->daysInMonth;
        $this->assertGreaterThanOrEqual($daysInMonth, count($data));
    }

    public function test_filters_by_category(): void
    {
        $this->actingAs($this->user);

        $today = Carbon::now()->startOfMonth()->addDays(5);

        $t1 = Transaction::factory()->create(['brand_id' => $this->expensesBrand->id, 'amount' => 100]);
        $t1->created_at = $today;
        $t1->save();

        $t2 = Transaction::factory()->create(['brand_id' => $this->incomeBrand->id, 'amount' => 5000]);
        $t2->created_at = $today;
        $t2->save();

        $response = $this->getJson('/api/v1/metrics/category-daily-trend?range=current-month&id=' . $this->expensesCategory->id);

        $response->assertOk();
        $data = $response->json('data');

        $dayData = collect($data)->firstWhere('label', $today->format('Y-m-d'));
        $this->assertEquals(100, $dayData['value']);
    }
}
