<?php

namespace Tests\Feature\GraphQL\Queries;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IncomePerCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_correct_data()
    {
        $expensesCategory = Category::factory()->create(['type' => Category::EXPENSES]);
        $incomeCategory = Category::factory()->create(['type' => Category::INCOME]);

        $expensesBrand = Brand::factory()->create(['category_id' => $expensesCategory->id]);
        $incomeBrand = Brand::factory()->create(['category_id' => $incomeCategory->id]);

        $expensesTransaction = Transaction::factory()->create(['brand_id' => $expensesBrand, 'amount' => 10001]);
        $incomeTransaction =Transaction::factory()->create(['brand_id' => $incomeBrand->id, 'amount' => 133]);

        $response = $this->graphQL(/** @lang GraphQL */ '
            {
                incomePerCategory(range: "current-year")
            }
            ');

        $response = json_decode($response->json("data.incomePerCategory"));

        $this->assertCount(1, $response);
        $this->assertEquals($incomeCategory->name, $response[0]->label);
        $this->assertEquals($incomeTransaction->amount, $response[0]->value);
    }
}
