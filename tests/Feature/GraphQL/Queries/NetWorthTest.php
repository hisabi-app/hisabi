<?php

namespace Tests\Feature\GraphQL\Queries;

use Tests\TestCase;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class NetWorthTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_returns_correct_data()
    {
        $incomeCategory = Category::factory()->create(['type' => Category::INCOME]);
        $expensesCategory = Category::factory()->create(['type' => Category::EXPENSES]);
        
        $incomeBrand = Brand::factory()->create(['category_id' => $incomeCategory->id]);
        $expensesBrand = Brand::factory()->create(['category_id' => $expensesCategory->id]);
        
        // Income
        Transaction::factory()->create(['brand_id' => $incomeBrand->id, 'amount' => 1000]);
        // Expenses
        Transaction::factory()->create(['brand_id' => $expensesBrand, 'amount' => 200]);

        $this->graphQL(/** @lang GraphQL */ '
            {
                netWorth
            }
            ')->assertJson([
                'data' => [
                    'netWorth' => '{"value":800}'
                ],
            ]);
    }
}
