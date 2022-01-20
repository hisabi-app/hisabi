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
        $expensesCategory = Category::factory()->create(['type' => Category::EXPENSES]);
        $incomeCategory = Category::factory()->create(['type' => Category::INCOME]);

        $expensesBrand = Brand::factory()->create(['category_id' => $expensesCategory->id]);
        $incomeBrand = Brand::factory()->create(['category_id' => $incomeCategory->id]);

        // Expenses
        Transaction::factory()->create(['brand_id' => $expensesBrand, 'amount' => 3444]);
        // Income
        Transaction::factory()->create(['brand_id' => $incomeBrand->id, 'amount' => 10001]);

        $this->graphQL(/** @lang GraphQL */ '
            {
                totalCash
            }
            ')->assertJson([
                'data' => [
                    'totalCash' => '6557'
                ],
            ]);
    }
}
