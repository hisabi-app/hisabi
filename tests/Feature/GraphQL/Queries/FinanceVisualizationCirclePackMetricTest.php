<?php

namespace Tests\Feature\GraphQL\Queries;

use Tests\TestCase;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FinanceVisualizationCirclePackMetricTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_returns_correct_data()
    {
        $expensesCategory = Category::factory()->create(['type' => Category::EXPENSES, 'name' => 'expCat']);
        $incomeCategory = Category::factory()->create(['type' => Category::INCOME, 'name' => 'incCat']);

        $expensesBrand = Brand::factory()->create(['category_id' => $expensesCategory->id, 'name' => 'expBr']);
        $incomeBrand = Brand::factory()->create(['category_id' => $incomeCategory->id, 'name' => 'inBra']);

        Transaction::factory()->create(['brand_id' => $expensesBrand, 'amount' => 10001]);
        Transaction::factory()->create(['brand_id' => $expensesBrand, 'amount' => 23]);
        Transaction::factory()->create(['brand_id' => $incomeBrand->id, 'amount' => 133]);

        $response = $this->graphQL(/** @lang GraphQL */ '
            {
                financeVisualizationCirclePackMetric(range: "current-year")
            }
            ');

        $response = json_decode($response->json("data.financeVisualizationCirclePackMetric"));

        $this->assertCount(2, $response->children);
        $this->assertEquals($expensesCategory->type, $response->children[0]->label);
        $this->assertEquals($incomeCategory->type, $response->children[1]->label);
        $this->assertEquals($expensesCategory->name, $response->children[0]->children[0]->label);
        $this->assertEquals($incomeCategory->name, $response->children[1]->children[0]->label);
        $this->assertEquals(10024, $response->children[0]->children[0]->children[0]->value);
        $this->assertEquals(133, $response->children[1]->children[0]->children[0]->value);
    }
}
