<?php

namespace Tests\Feature\Api\V1\Metrics;

use App\Domains\Brand\Models\Brand;
use App\Domains\Category\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

abstract class MetricsTestCase extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Category $incomeCategory;
    protected Category $expensesCategory;
    protected Category $savingsCategory;
    protected Category $investmentCategory;
    protected Brand $incomeBrand;
    protected Brand $expensesBrand;
    protected Brand $savingsBrand;
    protected Brand $investmentBrand;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->incomeCategory = Category::factory()->create(['type' => Category::INCOME, 'name' => 'Salary']);
        $this->expensesCategory = Category::factory()->create(['type' => Category::EXPENSES, 'name' => 'Food']);
        $this->savingsCategory = Category::factory()->create(['type' => Category::SAVINGS, 'name' => 'Emergency Fund']);
        $this->investmentCategory = Category::factory()->create(['type' => Category::INVESTMENT, 'name' => 'Stocks']);

        $this->incomeBrand = Brand::factory()->create(['category_id' => $this->incomeCategory->id, 'name' => 'Company A']);
        $this->expensesBrand = Brand::factory()->create(['category_id' => $this->expensesCategory->id, 'name' => 'Restaurant']);
        $this->savingsBrand = Brand::factory()->create(['category_id' => $this->savingsCategory->id, 'name' => 'Bank']);
        $this->investmentBrand = Brand::factory()->create(['category_id' => $this->investmentCategory->id, 'name' => 'Broker']);
    }
}
