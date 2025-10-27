<?php

namespace Tests\Feature\GraphQL\Queries;

use App\Domains\Brand\Models\Brand;
use App\Models\Category;
use App\Domains\Transaction\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TotalInvestmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_correct_data()
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
                    'totalInvestment' => '{"value":133}'
                ],
            ]);
    }
}
