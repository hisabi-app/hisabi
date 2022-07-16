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
        
        $expensesBrand = Brand::factory()->create(['category_id' => $expensesCategory->id]);
        
        Transaction::factory()->create(['brand_id' => $expensesBrand, 'amount' => 10001]);
        
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
