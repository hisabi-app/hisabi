<?php

namespace Tests\Feature\GraphQL\Queries;

use App\Domains\Brand\Models\Brand;
use App\Models\Category;
use App\Domains\Transaction\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NumberOfTransactionsPerCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_correct_data()
    {
        $expensesCategory = Category::factory()->create(['type' => Category::EXPENSES]);
        $incomeCategory = Category::factory()->create(['type' => Category::INCOME]);

        $expensesBrand = Brand::factory()->create(['category_id' => $expensesCategory->id]);
        $incomeBrand = Brand::factory()->create(['category_id' => $incomeCategory->id]);

        Transaction::factory()->create(['brand_id' => $expensesBrand, 'amount' => 10001]);
        Transaction::factory()->create(['brand_id' => $expensesBrand, 'amount' => 23]);
        Transaction::factory()->create(['brand_id' => $incomeBrand->id, 'amount' => 133]);

        $response = $this->graphQL(/** @lang GraphQL */ '
            {
                numberOfTransactionsPerCategory(range: "current-year")
            }
            ');

        $response = json_decode($response->json("data.numberOfTransactionsPerCategory"));

        $this->assertCount(2, $response);
        $this->assertEquals($expensesCategory->name, $response[0]->label);
        $this->assertEquals(2, $response[0]->value);
        $this->assertEquals($incomeCategory->name, $response[1]->label);
        $this->assertEquals(1, $response[1]->value);
    }
}
