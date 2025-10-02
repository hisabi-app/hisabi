<?php

namespace Tests\Feature\GraphQL\Queries;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NetWorthTrendTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_calculates_cumulative_net_worth_over_time()
    {
        // Mock the current date
        Carbon::setTestNow(Carbon::create(2024, 6, 15));

        $incomeCategory = Category::factory()->create(['type' => Category::INCOME]);
        $expensesCategory = Category::factory()->create(['type' => Category::EXPENSES]);
        
        $incomeBrand = Brand::factory()->create(['category_id' => $incomeCategory->id]);
        $expensesBrand = Brand::factory()->create(['category_id' => $expensesCategory->id]);
        
        // Create transactions in different months
        // January 2024: +1000 income
        Transaction::factory()->create([
            'brand_id' => $incomeBrand->id,
            'amount' => 1000,
            'created_at' => Carbon::create(2024, 1, 15)
        ]);
        
        // February 2024: -200 expenses
        Transaction::factory()->create([
            'brand_id' => $expensesBrand->id,
            'amount' => 200,
            'created_at' => Carbon::create(2024, 2, 10)
        ]);
        
        // March 2024: +500 income, -100 expenses
        Transaction::factory()->create([
            'brand_id' => $incomeBrand->id,
            'amount' => 500,
            'created_at' => Carbon::create(2024, 3, 5)
        ]);
        Transaction::factory()->create([
            'brand_id' => $expensesBrand->id,
            'amount' => 100,
            'created_at' => Carbon::create(2024, 3, 20)
        ]);

        $response = $this->graphQL(/** @lang GraphQL */ '
            {
                netWorthTrend(range: "current-year")
            }
        ');

        $result = json_decode($response->json("data.netWorthTrend"));

        // Should have 3 months of data
        $this->assertCount(3, $result);
        
        // January: 1000 (cumulative)
        $this->assertEquals('2024-01', $result[0]->label);
        $this->assertEquals(1000, $result[0]->value);
        
        // February: 1000 - 200 = 800 (cumulative)
        $this->assertEquals('2024-02', $result[1]->label);
        $this->assertEquals(800, $result[1]->value);
        
        // March: 800 + 500 - 100 = 1200 (cumulative)
        $this->assertEquals('2024-03', $result[2]->label);
        $this->assertEquals(1200, $result[2]->value);
    }

    public function test_it_filters_results_by_range_but_calculates_cumulative_from_all_time()
    {
        // Mock the current date
        Carbon::setTestNow(Carbon::create(2024, 6, 15));

        $incomeCategory = Category::factory()->create(['type' => Category::INCOME]);
        $expensesCategory = Category::factory()->create(['type' => Category::EXPENSES]);
        
        $incomeBrand = Brand::factory()->create(['category_id' => $incomeCategory->id]);
        $expensesBrand = Brand::factory()->create(['category_id' => $expensesCategory->id]);
        
        // Create transactions in 2023
        Transaction::factory()->create([
            'brand_id' => $incomeBrand->id,
            'amount' => 5000,
            'created_at' => Carbon::create(2023, 12, 15)
        ]);
        
        // Create transactions in 2024
        Transaction::factory()->create([
            'brand_id' => $incomeBrand->id,
            'amount' => 1000,
            'created_at' => Carbon::create(2024, 1, 15)
        ]);
        
        Transaction::factory()->create([
            'brand_id' => $expensesBrand->id,
            'amount' => 500,
            'created_at' => Carbon::create(2024, 2, 10)
        ]);

        $response = $this->graphQL(/** @lang GraphQL */ '
            {
                netWorthTrend(range: "current-year")
            }
        ');

        $result = json_decode($response->json("data.netWorthTrend"));

        // Should only show 2024 data (current-year range)
        $this->assertCount(2, $result);
        
        // But cumulative should include 2023 data
        // January 2024: 5000 (from 2023) + 1000 = 6000
        $this->assertEquals('2024-01', $result[0]->label);
        $this->assertEquals(6000, $result[0]->value);
        
        // February 2024: 6000 - 500 = 5500
        $this->assertEquals('2024-02', $result[1]->label);
        $this->assertEquals(5500, $result[1]->value);
    }

    public function test_it_handles_last_twelve_months_range()
    {
        // Mock the current date to June 15, 2024
        Carbon::setTestNow(Carbon::create(2024, 6, 15));

        $incomeCategory = Category::factory()->create(['type' => Category::INCOME]);
        $expensesCategory = Category::factory()->create(['type' => Category::EXPENSES]);
        
        $incomeBrand = Brand::factory()->create(['category_id' => $incomeCategory->id]);
        $expensesBrand = Brand::factory()->create(['category_id' => $expensesCategory->id]);
        
        // Create transactions before the 12-month window
        Transaction::factory()->create([
            'brand_id' => $incomeBrand->id,
            'amount' => 2000,
            'created_at' => Carbon::create(2023, 5, 1)
        ]);
        
        // Create transactions within the 12-month window
        Transaction::factory()->create([
            'brand_id' => $incomeBrand->id,
            'amount' => 1000,
            'created_at' => Carbon::create(2023, 7, 15)
        ]);
        
        Transaction::factory()->create([
            'brand_id' => $expensesBrand->id,
            'amount' => 300,
            'created_at' => Carbon::create(2024, 1, 10)
        ]);

        $response = $this->graphQL(/** @lang GraphQL */ '
            {
                netWorthTrend(range: "last-twelve-months")
            }
        ');

        $result = json_decode($response->json("data.netWorthTrend"));

        // Should show data from last 12 months (July 2023 onwards)
        $this->assertGreaterThanOrEqual(1, count($result));
        
        // First entry should include the historical balance
        // July 2023: 2000 (from May) + 1000 = 3000
        $this->assertEquals('2023-07', $result[0]->label);
        $this->assertEquals(3000, $result[0]->value);
    }

    public function test_it_handles_last_year_range()
    {
        // Mock the current date to 2024
        Carbon::setTestNow(Carbon::create(2024, 6, 15));

        $incomeCategory = Category::factory()->create(['type' => Category::INCOME]);
        $expensesCategory = Category::factory()->create(['type' => Category::EXPENSES]);
        
        $incomeBrand = Brand::factory()->create(['category_id' => $incomeCategory->id]);
        $expensesBrand = Brand::factory()->create(['category_id' => $expensesCategory->id]);
        
        // Create transactions in 2022 (before last year)
        Transaction::factory()->create([
            'brand_id' => $incomeBrand->id,
            'amount' => 5000,
            'created_at' => Carbon::create(2022, 12, 15)
        ]);
        
        // Create transactions in 2023 (last year)
        Transaction::factory()->create([
            'brand_id' => $incomeBrand->id,
            'amount' => 2000,
            'created_at' => Carbon::create(2023, 6, 15)
        ]);
        
        Transaction::factory()->create([
            'brand_id' => $expensesBrand->id,
            'amount' => 500,
            'created_at' => Carbon::create(2023, 8, 10)
        ]);
        
        // Create transactions in 2024 (current year)
        Transaction::factory()->create([
            'brand_id' => $incomeBrand->id,
            'amount' => 1000,
            'created_at' => Carbon::create(2024, 1, 5)
        ]);

        $response = $this->graphQL(/** @lang GraphQL */ '
            {
                netWorthTrend(range: "last-year")
            }
        ');

        $result = json_decode($response->json("data.netWorthTrend"));

        // Should only show 2023 data
        $this->assertCount(2, $result);
        
        // June 2023: 5000 (from 2022) + 2000 = 7000
        $this->assertEquals('2023-06', $result[0]->label);
        $this->assertEquals(7000, $result[0]->value);
        
        // August 2023: 7000 - 500 = 6500
        $this->assertEquals('2023-08', $result[1]->label);
        $this->assertEquals(6500, $result[1]->value);
    }

    public function test_it_handles_all_time_range()
    {
        $incomeCategory = Category::factory()->create(['type' => Category::INCOME]);
        $expensesCategory = Category::factory()->create(['type' => Category::EXPENSES]);
        
        $incomeBrand = Brand::factory()->create(['category_id' => $incomeCategory->id]);
        $expensesBrand = Brand::factory()->create(['category_id' => $expensesCategory->id]);
        
        // Create transactions across multiple years
        Transaction::factory()->create([
            'brand_id' => $incomeBrand->id,
            'amount' => 3000,
            'created_at' => Carbon::create(2022, 1, 15)
        ]);
        
        Transaction::factory()->create([
            'brand_id' => $expensesBrand->id,
            'amount' => 1000,
            'created_at' => Carbon::create(2023, 6, 10)
        ]);
        
        Transaction::factory()->create([
            'brand_id' => $incomeBrand->id,
            'amount' => 2000,
            'created_at' => Carbon::create(2024, 3, 5)
        ]);

        $response = $this->graphQL(/** @lang GraphQL */ '
            {
                netWorthTrend(range: "all-time")
            }
        ');

        $result = json_decode($response->json("data.netWorthTrend"));

        // Should show all transactions
        $this->assertCount(3, $result);
        
        // January 2022: 3000
        $this->assertEquals('2022-01', $result[0]->label);
        $this->assertEquals(3000, $result[0]->value);
        
        // June 2023: 3000 - 1000 = 2000
        $this->assertEquals('2023-06', $result[1]->label);
        $this->assertEquals(2000, $result[1]->value);
        
        // March 2024: 2000 + 2000 = 4000
        $this->assertEquals('2024-03', $result[2]->label);
        $this->assertEquals(4000, $result[2]->value);
    }

    public function test_it_handles_no_transactions()
    {
        $response = $this->graphQL(/** @lang GraphQL */ '
            {
                netWorthTrend(range: "current-year")
            }
        ');

        $result = json_decode($response->json("data.netWorthTrend"));

        $this->assertIsArray($result);
        $this->assertCount(0, $result);
    }

    public function test_it_handles_only_income_transactions()
    {
        Carbon::setTestNow(Carbon::create(2024, 6, 15));

        $incomeCategory = Category::factory()->create(['type' => Category::INCOME]);
        $incomeBrand = Brand::factory()->create(['category_id' => $incomeCategory->id]);
        
        Transaction::factory()->create([
            'brand_id' => $incomeBrand->id,
            'amount' => 1000,
            'created_at' => Carbon::create(2024, 1, 15)
        ]);
        
        Transaction::factory()->create([
            'brand_id' => $incomeBrand->id,
            'amount' => 500,
            'created_at' => Carbon::create(2024, 2, 15)
        ]);

        $response = $this->graphQL(/** @lang GraphQL */ '
            {
                netWorthTrend(range: "current-year")
            }
        ');

        $result = json_decode($response->json("data.netWorthTrend"));

        $this->assertCount(2, $result);
        $this->assertEquals(1000, $result[0]->value);
        $this->assertEquals(1500, $result[1]->value); // Cumulative
    }

    public function test_it_handles_only_expense_transactions()
    {
        Carbon::setTestNow(Carbon::create(2024, 6, 15));

        $expensesCategory = Category::factory()->create(['type' => Category::EXPENSES]);
        $expensesBrand = Brand::factory()->create(['category_id' => $expensesCategory->id]);
        
        Transaction::factory()->create([
            'brand_id' => $expensesBrand->id,
            'amount' => 300,
            'created_at' => Carbon::create(2024, 1, 15)
        ]);
        
        Transaction::factory()->create([
            'brand_id' => $expensesBrand->id,
            'amount' => 200,
            'created_at' => Carbon::create(2024, 2, 15)
        ]);

        $response = $this->graphQL(/** @lang GraphQL */ '
            {
                netWorthTrend(range: "current-year")
            }
        ');

        $result = json_decode($response->json("data.netWorthTrend"));

        $this->assertCount(2, $result);
        $this->assertEquals(-300, $result[0]->value);
        $this->assertEquals(-500, $result[1]->value); // Cumulative negative
    }

    public function test_it_handles_multiple_transactions_in_same_month()
    {
        Carbon::setTestNow(Carbon::create(2024, 6, 15));

        $incomeCategory = Category::factory()->create(['type' => Category::INCOME]);
        $expensesCategory = Category::factory()->create(['type' => Category::EXPENSES]);
        
        $incomeBrand = Brand::factory()->create(['category_id' => $incomeCategory->id]);
        $expensesBrand = Brand::factory()->create(['category_id' => $expensesCategory->id]);
        
        // Multiple transactions in January 2024
        Transaction::factory()->create([
            'brand_id' => $incomeBrand->id,
            'amount' => 1000,
            'created_at' => Carbon::create(2024, 1, 5)
        ]);
        
        Transaction::factory()->create([
            'brand_id' => $incomeBrand->id,
            'amount' => 500,
            'created_at' => Carbon::create(2024, 1, 15)
        ]);
        
        Transaction::factory()->create([
            'brand_id' => $expensesBrand->id,
            'amount' => 300,
            'created_at' => Carbon::create(2024, 1, 20)
        ]);

        $response = $this->graphQL(/** @lang GraphQL */ '
            {
                netWorthTrend(range: "current-year")
            }
        ');

        $result = json_decode($response->json("data.netWorthTrend"));

        // Should aggregate all transactions in the same month
        $this->assertCount(1, $result);
        $this->assertEquals('2024-01', $result[0]->label);
        $this->assertEquals(1200, $result[0]->value); // 1000 + 500 - 300
    }
}

