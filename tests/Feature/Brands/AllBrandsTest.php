<?php

namespace Tests\Feature\Brands;

use App\Domains\Brand\Models\Brand;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AllBrandsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_correct_data()
    {
        $brand = Brand::factory()->create();

        $this->graphQL(/** @lang GraphQL */ '
            {
                allBrands {
                    id
                    name
                    category {
                        name
                    }
                }
            }
            ')->assertJson([
                'data' => [
                    'allBrands' => [
                        [
                            'id' => $brand->id,
                            'name' => $brand->name,
                            'category' => [
                                'name' => $brand->category->name
                            ],
                        ]
                    ],
                ],
            ]);
    }
}
