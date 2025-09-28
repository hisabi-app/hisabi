<?php

namespace Tests\Feature\Brands;

use App\Models\Brand;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateBrandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_update_a_model()
    {
        $brand = Brand::factory()->create(['name' => 'oldName']);

        $this->graphQL(/** @lang GraphQL */ '
            mutation {
                updateBrand(id: 1 name: "someNewName" category_id: 1) {
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
                    'updateBrand' => [
                        "id" => 1,
                        "name" => "someNewName",
                        "category" => [
                            "id" => $brand->category->id,
                            "name" => $brand->category->name,
                        ]
                    ],
                ],
            ]);

        $this->assertEquals("someNewName", $brand->fresh()->name);
    }
}
