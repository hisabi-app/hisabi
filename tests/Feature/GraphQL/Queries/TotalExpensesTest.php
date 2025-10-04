<?php

namespace Tests\Feature\GraphQL\Queries;

use App\Models\Brand;
use App\Models\Category;
use App\Domains\Transaction\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TotalExpensesTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_correct_value()
    {
        $expensesCategory = Category::factory()->create(['type' => Category::EXPENSES]);

        $expensesBrand = Brand::factory()->create(['category_id' => $expensesCategory->id]);

        Transaction::factory()->create(['brand_id' => $expensesBrand, 'amount' => 10001]);

        $this->graphQL(/** @lang GraphQL */ '
            {
                totalExpenses(range: "current-year")
            }
            ')->assertJson([
                'data' => [
                    'totalExpenses' => '{"value":10001,"previous":0}'
                ],
            ]);
    }

    public function test_it_returns_correct_previous_value()
    {
        // mock app date
        Carbon::setTestNow(Carbon::create(2021, 1, 18));

        $expensesCategory = Category::factory()->create(['type' => Category::EXPENSES]);

        $expensesBrand = Brand::factory()->create(['category_id' => $expensesCategory->id]);

        Transaction::factory()->create(['brand_id' => $expensesBrand, 'amount' => 10001, 'created_at' => now()->subYear()]);

        $this->graphQL(/** @lang GraphQL */ '
            {
                totalExpenses(range: "current-year")
            }
            ')->assertJson([
                'data' => [
                    'totalExpenses' => '{"value":0,"previous":10001}'
                ],
            ]);
    }
}
