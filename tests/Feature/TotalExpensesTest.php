<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TotalExpensesTest extends TestCase
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
        Transaction::factory()->create(['brand_id' => $expensesBrand, 'amount' => 10001]);
        // Income
        Transaction::factory()->create(['brand_id' => $incomeBrand->id, 'amount' => 133]);

        $this->graphQL(/** @lang GraphQL */ '
            {
                totalExpenses(range: "current-year")
            }
            ')->assertJson([
                'data' => [
                    'totalExpenses' => '"10001.0"'
                ],
            ]);
    }
}
