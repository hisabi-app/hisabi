<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Tests\TestCase;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TotalIncomeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_returns_correct_value()
    {
        $incomeCategory = Category::factory()->create(['type' => Category::INCOME]);

        $incomeBrand = Brand::factory()->create(['category_id' => $incomeCategory->id]);

        Transaction::factory()->create(['brand_id' => $incomeBrand->id, 'amount' => 133]);

        $this->graphQL(/** @lang GraphQL */ '
            {
                totalIncome(range: "current-year")
            }
            ')->assertJson([
                'data' => [
                    'totalIncome' => '{"value":"133.0","previous":0}'
                ],
            ]);
    }

    /** @test */
    public function it_returns_correct_previous_value()
    {
        // mock app date
        Carbon::setTestNow(Carbon::create(2021, 1, 18));

        $incomeCategory = Category::factory()->create(['type' => Category::INCOME]);

        $incomeBrand = Brand::factory()->create(['category_id' => $incomeCategory->id]);

        Transaction::factory()->create(['brand_id' => $incomeBrand->id, 'amount' => 133, 'created_at' => now()->subMonth()]);

        $this->graphQL(/** @lang GraphQL */ '
            {
                totalIncome(range: "current-month")
            }
            ')->assertJson([
                'data' => [
                    'totalIncome' => '{"value":0,"previous":"133.0"}'
                ],
            ]);
    }
}
