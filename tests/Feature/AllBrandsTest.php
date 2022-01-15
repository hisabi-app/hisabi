<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Brand;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AllBrandsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_returns_correct_data()
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
