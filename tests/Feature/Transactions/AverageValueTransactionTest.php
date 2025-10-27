<?php

namespace Tests\Feature\Transactions;

use App\Domains\Brand\Models\Brand;
use App\Models\Category;
use App\Domains\Transaction\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AverageValueTransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_correct_data()
    {
        $expensesCategory = Category::factory()->create(['type' => Category::EXPENSES]);

        $expensesBrand = Brand::factory()->create(['category_id' => $expensesCategory->id]);

        Transaction::factory()->create(['brand_id' => $expensesBrand, 'amount' => 200]);
        Transaction::factory()->create(['brand_id' => $expensesBrand, 'amount' => 60]);
        Transaction::factory()->create(['brand_id' => $expensesBrand, 'amount' => 40]);

        $response = $this->graphQL(/** @lang GraphQL */ '
            {
                averageValueTransaction(range: "current-year")
            }
            ');

        $response = json_decode($response->json("data.averageValueTransaction"));

        $this->assertCount(1, $response);
        $this->assertEquals(100, $response[0]->value);
    }
}
