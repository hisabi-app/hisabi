<?php

namespace Tests\Feature\Categories;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_delete_a_model()
    {
        $category = Category::factory()->create();

        $this->graphQL(/** @lang GraphQL */ '
            mutation {
                deleteCategory(id: 1) {
                    id
                }
            }
            ')->assertJson([
                'data' => [
                    'deleteCategory' => [
                        "id" => $category->id,
                    ],
                ],
            ]);

        $this->assertNull($category->fresh());
    }
}
