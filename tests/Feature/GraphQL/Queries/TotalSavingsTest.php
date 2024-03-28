<?php

namespace Tests\Feature\GraphQL\Queries;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TotalSavingsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_returns_correct_data()
    {
        $savingsCategory = Category::factory()->create(['type' => Category::SAVINGS]);

        $savingsBrand = Brand::factory()->create(['category_id' => $savingsCategory->id]);

        Transaction::factory()->create(['brand_id' => $savingsBrand->id, 'amount' => 133]);

        $this->graphQL(/** @lang GraphQL */ '
            {
                totalSavings
            }
            ')->assertJson([
                'data' => [
                    'totalSavings' => '{"value":133}'
                ],
            ]);
    }
}
