<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TotalCashTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_returns_correct_data()
    {
        $incomeCategory = Category::factory()->create(['type' => Category::INCOME]);
        $expensesCategory = Category::factory()->create(['type' => Category::EXPENSES]);
        $investmentCategory = Category::factory()->create(['type' => Category::INVESTMENT]);
        $savingsCategory = Category::factory()->create(['type' => Category::SAVINGS]);

        $incomeBrand = Brand::factory()->create(['category_id' => $incomeCategory->id]);
        $expensesBrand = Brand::factory()->create(['category_id' => $expensesCategory->id]);
        $investmentBrand = Brand::factory()->create(['category_id' => $investmentCategory->id]);
        $savingsBrand = Brand::factory()->create(['category_id' => $savingsCategory->id]);

        // Income
        Transaction::factory()->create(['brand_id' => $incomeBrand->id, 'amount' => 1000]);
        // Expenses
        Transaction::factory()->create(['brand_id' => $expensesBrand, 'amount' => 200]);
        // Investment
        Transaction::factory()->create(['brand_id' => $investmentBrand->id, 'amount' => 300]);
        // Savings
        Transaction::factory()->create(['brand_id' => $savingsBrand->id, 'amount' => 100]);


        $this->graphQL(/** @lang GraphQL */ '
            {
                totalCash
            }
            ')->assertJson([
                'data' => [
                    'totalCash' => '{"value":400}'
                ],
            ]);
    }
}
