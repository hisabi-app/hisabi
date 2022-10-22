<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LowestValueTransactionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_returns_correct_data()
    {
        $expensesCategory = Category::factory()->create(['type' => Category::EXPENSES]);
        $incomeCategory = Category::factory()->create(['type' => Category::INCOME]);

        $expensesBrand = Brand::factory()->create(['category_id' => $expensesCategory->id]);
        $incomeBrand = Brand::factory()->create(['category_id' => $incomeCategory->id]);

        Transaction::factory()->create(['brand_id' => $expensesBrand, 'amount' => 10001]);
        Transaction::factory()->create(['brand_id' => $expensesBrand, 'amount' => 30]);
        Transaction::factory()->create(['brand_id' => $incomeBrand->id, 'amount' => 133]);
        Transaction::factory()->create(['brand_id' => $incomeBrand->id, 'amount' => 3]);

        $response = $this->graphQL(/** @lang GraphQL */ '
            {
                lowestValueTransaction(range: "current-year")
            }
            ');

        $response = json_decode($response->json("data.lowestValueTransaction"));

        $this->assertCount(2, $response);
        $this->assertEquals(30, $response[0]->value);
        $this->assertEquals(3, $response[1]->value);
    }
}
