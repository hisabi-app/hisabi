<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Brand;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BrandsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_returns_correct_data()
    {
        $brand = Brand::factory()->create();

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
                                ]
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
