<?php

namespace Tests\Feature\Brands;

use App\Models\Brand;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrandsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_returns_correct_data()
    {
        $brand = Brand::factory()->create();
        $brand->transactions()->create(['amount' => 1000]);
        $brand->transactions()->create(['amount' => 233]);

        $this->graphQL(/** @lang GraphQL */ '
            {
                brands(page: 1) {
                    data {
                        id
                        name
                        category {
                            id
                            name
                        }
                        transactionsCount
                    }
                    paginatorInfo {
                        hasMorePages
                    }
                }
            }
            ')->assertJson([
                'data' => [
                    'brands' => [
                        "data" => [
                            [
                                'id' => $brand->id,
                                'name' => $brand->name,
                                "category" => [
                                    "id" => $brand->category->id,
                                    "name" => $brand->category->name,
                                ],
                                "transactionsCount" => 2,
                            ],
                        ],
                        "paginatorInfo" => [
                            "hasMorePages" => false
                        ]
                    ],
                ],
            ]);
    }
}
