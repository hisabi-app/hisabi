<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TotalInvestmentTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_returns_correct_data()
    {
        $investmentCategory = Category::factory()->create(['type' => Category::INVESTMENT]);

        $investmentBrand = Brand::factory()->create(['category_id' => $investmentCategory->id]);

        Transaction::factory()->create(['brand_id' => $investmentBrand->id, 'amount' => 133]);

        $this->graphQL(/** @lang GraphQL */ '
            {
                totalInvestment
            }
            ')->assertJson([
                'data' => [
                    'totalInvestment' => '{"value":"133.0"}'
                ],
            ]);
    }
}
