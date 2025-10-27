<?php

namespace Tests\Feature\Brands;

use App\Domains\Brand\Models\Brand;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteBrandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_delete_a_model()
    {
        $brand = Brand::factory()->create();

        $this->graphQL(/** @lang GraphQL */ '
            mutation {
                deleteBrand(id: 1) {
                    id
                }
            }
            ')->assertJson([
                'data' => [
                    'deleteBrand' => [
                        "id" => $brand->id,
                    ],
                ],
            ]);

        $this->assertNull($brand->fresh());
    }
}
