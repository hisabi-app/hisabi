<?php

namespace Tests\Feature\GraphQL\Queries;

use App\Domains\Brand\Models\Brand;
use App\Models\Category;
use App\Domains\Transaction\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrandStatsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_most_used_brand()
    {
        $category = Category::factory()->create(['type' => Category::EXPENSES]);
        $brand1 = Brand::factory()->create(['category_id' => $category->id, 'name' => 'Brand A']);
        $brand2 = Brand::factory()->create(['category_id' => $category->id, 'name' => 'Brand B']);

        // Brand A has 3 transactions
        Transaction::factory()->count(3)->create([
            'brand_id' => $brand1->id,
            'amount' => 100,
            'created_at' => now()
        ]);

        // Brand B has 1 transaction
        Transaction::factory()->create([
            'brand_id' => $brand2->id,
            'amount' => 500,
            'created_at' => now()
        ]);

        $response = $this->graphQL(/** @lang GraphQL */ '
            {
                brandStats(range: "current-month")
            }
        ');

        $data = json_decode($response->json("data.brandStats"), true);

        $this->assertEquals('Brand A', $data['mostUsedBrand']['name']);
        $this->assertEquals(3, $data['mostUsedBrand']['count']);
    }

    public function test_it_returns_highest_spending_brand()
    {
        $expenseCategory = Category::factory()->create(['type' => Category::EXPENSES]);
        $incomeCategory = Category::factory()->create(['type' => Category::INCOME]);
        
        $expenseBrand1 = Brand::factory()->create(['category_id' => $expenseCategory->id, 'name' => 'Expense Brand A']);
        $expenseBrand2 = Brand::factory()->create(['category_id' => $expenseCategory->id, 'name' => 'Expense Brand B']);
        $incomeBrand = Brand::factory()->create(['category_id' => $incomeCategory->id, 'name' => 'Income Brand']);

        // Expense Brand A: 3 transactions, total 300
        Transaction::factory()->count(3)->create([
            'brand_id' => $expenseBrand1->id,
            'amount' => 100,
            'created_at' => now()
        ]);

        // Expense Brand B: 1 transaction, total 500 (highest expense)
        Transaction::factory()->create([
            'brand_id' => $expenseBrand2->id,
            'amount' => 500,
            'created_at' => now()
        ]);

        // Income Brand: 1 transaction, total 1000 (should be ignored)
        Transaction::factory()->create([
            'brand_id' => $incomeBrand->id,
            'amount' => 1000,
            'created_at' => now()
        ]);

        $response = $this->graphQL(/** @lang GraphQL */ '
            {
                brandStats(range: "current-month")
            }
        ');

        $data = json_decode($response->json("data.brandStats"), true);

        // Should return the highest EXPENSE brand, not the income brand
        $this->assertEquals('Expense Brand B', $data['highestSpendingBrand']['name']);
        $this->assertEquals(500, $data['highestSpendingBrand']['amount']);
    }

    public function test_it_returns_highest_income_brand()
    {
        $incomeCategory = Category::factory()->create(['type' => Category::INCOME]);
        $expenseCategory = Category::factory()->create(['type' => Category::EXPENSES]);
        
        $incomeBrand1 = Brand::factory()->create(['category_id' => $incomeCategory->id, 'name' => 'Income Brand A']);
        $incomeBrand2 = Brand::factory()->create(['category_id' => $incomeCategory->id, 'name' => 'Income Brand B']);
        $expenseBrand = Brand::factory()->create(['category_id' => $expenseCategory->id, 'name' => 'Expense Brand']);

        // Income Brand A: total 300
        Transaction::factory()->count(3)->create([
            'brand_id' => $incomeBrand1->id,
            'amount' => 100,
            'created_at' => now()
        ]);

        // Income Brand B: total 800 (highest income)
        Transaction::factory()->create([
            'brand_id' => $incomeBrand2->id,
            'amount' => 800,
            'created_at' => now()
        ]);

        // Expense Brand: total 1000 (should be ignored)
        Transaction::factory()->create([
            'brand_id' => $expenseBrand->id,
            'amount' => 1000,
            'created_at' => now()
        ]);

        $response = $this->graphQL(/** @lang GraphQL */ '
            {
                brandStats(range: "current-month")
            }
        ');

        $data = json_decode($response->json("data.brandStats"), true);

        // Should return the highest INCOME brand, not the expense brand
        $this->assertEquals('Income Brand B', $data['highestIncomeBrand']['name']);
        $this->assertEquals(800, $data['highestIncomeBrand']['amount']);
    }

    public function test_it_filters_by_range()
    {
        $category = Category::factory()->create(['type' => Category::EXPENSES]);
        $brand1 = Brand::factory()->create(['category_id' => $category->id, 'name' => 'Current Month Brand']);
        $brand2 = Brand::factory()->create(['category_id' => $category->id, 'name' => 'Last Month Brand']);

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
                brandStats(range: "current-month")
            }
        ');

        $data = json_decode($response->json("data.brandStats"), true);

        $this->assertEquals('Current Month Brand', $data['mostUsedBrand']['name']);

        // Query for last month
        $response = $this->graphQL(/** @lang GraphQL */ '
            {
                brandStats(range: "last-month")
            }
        ');

        $data = json_decode($response->json("data.brandStats"), true);

        $this->assertEquals('Last Month Brand', $data['mostUsedBrand']['name']);
    }

    public function test_it_returns_null_when_no_brands_have_transactions()
    {
        $response = $this->graphQL(/** @lang GraphQL */ '
            {
                brandStats(range: "current-month")
            }
        ');

        $data = json_decode($response->json("data.brandStats"), true);

        $this->assertNull($data['mostUsedBrand']);
        $this->assertNull($data['highestSpendingBrand']);
        $this->assertNull($data['highestIncomeBrand']);
    }
}

