<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TotalPerBrandTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_returns_correct_data()
    {
        $expensesCategory = Category::factory()->create(['type' => Category::EXPENSES]);
        $incomeCategory = Category::factory()->create(['type' => Category::INCOME]);

        $expensesBrand = Brand::factory()->create(['category_id' => $expensesCategory->id]);
        $incomeBrand = Brand::factory()->create(['category_id' => $incomeCategory->id]);

        $expensesTransaction = Transaction::factory()->create(['brand_id' => $expensesBrand, 'amount' => 10001]);
        $incomeTransaction =Transaction::factory()->create(['brand_id' => $incomeBrand->id, 'amount' => 133]);

        $response = $this->graphQL(/** @lang GraphQL */ '
            {
                totalPerBrand(range: "current-year" category_id: 1)
            }
            ');

        $response = json_decode($response->json("data.totalPerBrand"));

        $this->assertCount(1, $response);
        $this->assertEquals($expensesBrand->name, $response[0]->label);
        $this->assertEquals($expensesTransaction->amount, $response[0]->value);
    }
}
