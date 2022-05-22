<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TotalIncomeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_returns_correct_data()
    {
        $incomeCategory = Category::factory()->create(['type' => Category::INCOME]);

        $incomeBrand = Brand::factory()->create(['category_id' => $incomeCategory->id]);

        Transaction::factory()->create(['brand_id' => $incomeBrand->id, 'amount' => 133]);

        $this->graphQL(/** @lang GraphQL */ '
            {
                totalIncome(range: "current-year")
            }
            ')->assertJson([
                'data' => [
                    'totalIncome' => '"133.0"'
                ],
            ]);
    }
}
