<?php

namespace Tests\Feature\GraphQL\Queries;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TotalIncomeTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_correct_value()
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
                    'totalIncome' => '{"value":133,"previous":0}'
                ],
            ]);
    }

    public function test_it_returns_correct_previous_value()
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
                    'totalIncome' => '{"value":0,"previous":133}'
                ],
            ]);
    }
}
