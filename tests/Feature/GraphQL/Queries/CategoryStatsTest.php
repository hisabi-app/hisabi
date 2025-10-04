<?php

namespace Tests\Feature\GraphQL\Queries;

use App\Models\Brand;
use App\Models\Category;
use App\Domains\Transaction\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryStatsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_most_used_category()
    {
        $category1 = Category::factory()->create(['type' => Category::EXPENSES, 'name' => 'Category A']);
        $category2 = Category::factory()->create(['type' => Category::EXPENSES, 'name' => 'Category B']);
        
        $brand1 = Brand::factory()->create(['category_id' => $category1->id]);
        $brand2 = Brand::factory()->create(['category_id' => $category2->id]);

        // Category A has 3 transactions
        Transaction::factory()->count(3)->create([
            'brand_id' => $brand1->id,
            'amount' => 100,
            'created_at' => now()
        ]);

        // Category B has 1 transaction
        Transaction::factory()->create([
            'brand_id' => $brand2->id,
            'amount' => 500,
            'created_at' => now()
        ]);

        $response = $this->graphQL(/** @lang GraphQL */ '
            {
                categoryStats(range: "current-month")
            }
        ');

        $data = json_decode($response->json("data.categoryStats"), true);

        $this->assertEquals('Category A', $data['mostUsedCategory']['name']);
        $this->assertEquals(3, $data['mostUsedCategory']['count']);
    }

    public function test_it_returns_highest_spending_category()
    {
        $expenseCategory1 = Category::factory()->create(['type' => Category::EXPENSES, 'name' => 'Expense Category A']);
        $expenseCategory2 = Category::factory()->create(['type' => Category::EXPENSES, 'name' => 'Expense Category B']);
        $incomeCategory = Category::factory()->create(['type' => Category::INCOME, 'name' => 'Income Category']);
        
        $expenseBrand1 = Brand::factory()->create(['category_id' => $expenseCategory1->id]);
        $expenseBrand2 = Brand::factory()->create(['category_id' => $expenseCategory2->id]);
        $incomeBrand = Brand::factory()->create(['category_id' => $incomeCategory->id]);

        // Expense Category A: 3 transactions, total 300
        Transaction::factory()->count(3)->create([
            'brand_id' => $expenseBrand1->id,
            'amount' => 100,
            'created_at' => now()
        ]);

        // Expense Category B: 1 transaction, total 500 (highest expense)
        Transaction::factory()->create([
            'brand_id' => $expenseBrand2->id,
            'amount' => 500,
            'created_at' => now()
        ]);

        // Income Category: 1 transaction, total 1000 (should be ignored)
        Transaction::factory()->create([
            'brand_id' => $incomeBrand->id,
            'amount' => 1000,
            'created_at' => now()
        ]);

        $response = $this->graphQL(/** @lang GraphQL */ '
            {
                categoryStats(range: "current-month")
            }
        ');

        $data = json_decode($response->json("data.categoryStats"), true);

        // Should return the highest EXPENSE category, not the income category
        $this->assertEquals('Expense Category B', $data['highestSpendingCategory']['name']);
        $this->assertEquals(500, $data['highestSpendingCategory']['amount']);
    }

    public function test_it_returns_highest_income_category()
    {
        $incomeCategory1 = Category::factory()->create(['type' => Category::INCOME, 'name' => 'Income Category A']);
        $incomeCategory2 = Category::factory()->create(['type' => Category::INCOME, 'name' => 'Income Category B']);
        $expenseCategory = Category::factory()->create(['type' => Category::EXPENSES, 'name' => 'Expense Category']);
        
        $incomeBrand1 = Brand::factory()->create(['category_id' => $incomeCategory1->id]);
        $incomeBrand2 = Brand::factory()->create(['category_id' => $incomeCategory2->id]);
        $expenseBrand = Brand::factory()->create(['category_id' => $expenseCategory->id]);

        // Income Category A: total 300
        Transaction::factory()->count(3)->create([
            'brand_id' => $incomeBrand1->id,
            'amount' => 100,
            'created_at' => now()
        ]);

        // Income Category B: total 800 (highest income)
        Transaction::factory()->create([
            'brand_id' => $incomeBrand2->id,
            'amount' => 800,
            'created_at' => now()
        ]);

        // Expense Category: total 1000 (should be ignored)
        Transaction::factory()->create([
            'brand_id' => $expenseBrand->id,
            'amount' => 1000,
            'created_at' => now()
        ]);

        $response = $this->graphQL(/** @lang GraphQL */ '
            {
                categoryStats(range: "current-month")
            }
        ');

        $data = json_decode($response->json("data.categoryStats"), true);

        // Should return the highest INCOME category, not the expense category
        $this->assertEquals('Income Category B', $data['highestIncomeCategory']['name']);
        $this->assertEquals(800, $data['highestIncomeCategory']['amount']);
    }

    public function test_it_filters_by_range()
    {
        $category1 = Category::factory()->create(['type' => Category::EXPENSES, 'name' => 'Current Month Category']);
        $category2 = Category::factory()->create(['type' => Category::EXPENSES, 'name' => 'Last Month Category']);
        
        $brand1 = Brand::factory()->create(['category_id' => $category1->id]);
        $brand2 = Brand::factory()->create(['category_id' => $category2->id]);

        // Transaction in current month
        Transaction::factory()->create([
            'brand_id' => $brand1->id,
            'amount' => 100,
            'created_at' => now()->startOfMonth()
        ]);

        // Transaction in last month
        Transaction::factory()->create([
            'brand_id' => $brand2->id,
            'amount' => 200,
            'created_at' => now()->subMonth()->startOfMonth()
        ]);

        // Query for current month
        $response = $this->graphQL(/** @lang GraphQL */ '
            {
                categoryStats(range: "current-month")
            }
        ');

        $data = json_decode($response->json("data.categoryStats"), true);

        $this->assertEquals('Current Month Category', $data['mostUsedCategory']['name']);

        // Query for last month
        $response = $this->graphQL(/** @lang GraphQL */ '
            {
                categoryStats(range: "last-month")
            }
        ');

        $data = json_decode($response->json("data.categoryStats"), true);

        $this->assertEquals('Last Month Category', $data['mostUsedCategory']['name']);
    }

    public function test_it_returns_null_when_no_categories_have_transactions()
    {
        $response = $this->graphQL(/** @lang GraphQL */ '
            {
                categoryStats(range: "current-month")
            }
        ');

        $data = json_decode($response->json("data.categoryStats"), true);

        $this->assertNull($data['mostUsedCategory']);
        $this->assertNull($data['highestSpendingCategory']);
        $this->assertNull($data['highestIncomeCategory']);
    }
}

