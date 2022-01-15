<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreateBrandTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_create_a_model()
    {
        $category = Category::factory()->create();

        $this->graphQL(/** @lang GraphQL */ '
            mutation {
                createBrand(name: "brandName" category_id: 1) {
                    id
                    name
                    category {
                        id
                        name
                    }
                }
            }
            ')->assertJson([
                'data' => [
                    'createBrand' => [
                        "id" => 1,
                        "name" => "brandName",
                        "category" => [
                            "id" => $category->id,
                            "name" => $category->name,
                        ]
                    ],
                ],
            ]);

        $this->assertCount(1, Brand::all());
    }
}
