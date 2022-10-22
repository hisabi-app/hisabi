<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AverageValueTransactionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_returns_correct_data()
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
