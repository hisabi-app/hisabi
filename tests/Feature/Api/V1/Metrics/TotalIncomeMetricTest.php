<?php

namespace Tests\Feature\Api\V1\Metrics;

use App\Domains\Transaction\Models\Transaction;
use Carbon\Carbon;

class TotalIncomeMetricTest extends MetricsTestCase
{
    public function test_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/metrics/total-income');
        $response->assertUnauthorized();
    }

    public function test_returns_correct_value(): void
    {
        $this->actingAs($this->user);

        Transaction::factory()->create(['brand_id' => $this->incomeBrand->id, 'amount' => 5000]);
        Transaction::factory()->create(['brand_id' => $this->incomeBrand->id, 'amount' => 3000]);

        $response = $this->getJson('/api/v1/metrics/total-income?range=current-year');

        $response->assertOk();
        $this->assertEquals(8000, $response->json('data.value'));
    }

    public function test_excludes_expenses(): void
    {
        $this->actingAs($this->user);

        Transaction::factory()->create(['brand_id' => $this->incomeBrand->id, 'amount' => 5000]);
        Transaction::factory()->create(['brand_id' => $this->expensesBrand->id, 'amount' => 1000]);

        $response = $this->getJson('/api/v1/metrics/total-income?range=current-year');

        $response->assertOk();
        $this->assertEquals(5000, $response->json('data.value'));
    }

    public function test_returns_previous_period(): void
    {
        $this->actingAs($this->user);

        $currentMonthDate = Carbon::now()->startOfMonth()->addDays(5);
        $lastMonthDate = Carbon::now()->subMonth()->startOfMonth()->addDays(5);

        $currentTransaction = Transaction::factory()->create([
            'brand_id' => $this->incomeBrand->id,
            'amount' => 5000,
        ]);
        $currentTransaction->created_at = $currentMonthDate;
        $currentTransaction->save();

        $previousTransaction = Transaction::factory()->create([
            'brand_id' => $this->incomeBrand->id,
            'amount' => 4000,
        ]);
        $previousTransaction->created_at = $lastMonthDate;
        $previousTransaction->save();

        $response = $this->getJson('/api/v1/metrics/total-income?range=current-month');

        $response->assertOk();
        $this->assertEquals(5000, $response->json('data.value'));
        $this->assertEquals(4000, $response->json('data.previous'));
    }

    public function test_returns_zero_when_no_data(): void
    {
        $this->actingAs($this->user);

        $response = $this->getJson('/api/v1/metrics/total-income?range=current-year');

        $response->assertOk();
        $this->assertEquals(0, $response->json('data.value'));
    }

    public function test_filters_by_current_month(): void
    {
        $this->actingAs($this->user);

        $currentMonthDate = Carbon::now()->startOfMonth()->addDays(5);
        $twoMonthsAgoDate = Carbon::now()->subMonths(2)->startOfMonth()->addDays(5);

        $currentTransaction = Transaction::factory()->create([
            'brand_id' => $this->incomeBrand->id,
            'amount' => 5000,
        ]);
        $currentTransaction->created_at = $currentMonthDate;
        $currentTransaction->save();

        $oldTransaction = Transaction::factory()->create([
            'brand_id' => $this->incomeBrand->id,
            'amount' => 3000,
        ]);
        $oldTransaction->created_at = $twoMonthsAgoDate;
        $oldTransaction->save();

        $response = $this->getJson('/api/v1/metrics/total-income?range=current-month');

        $response->assertOk();
        $this->assertEquals(5000, $response->json('data.value'));
    }
}
