<?php

namespace Tests\Feature\Categories;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AllCategoriesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_returns_correct_data()
    {
        $category = Category::factory()->create();

        $this->graphQL(/** @lang GraphQL */ '
            {
                allCategories {
                    id
                    name
                }
            }
            ')->assertJson([
                'data' => [
                    'allCategories' => [
                        [
                            'id' => $category->id,
                            'name' => $category->name,
                        ]
                    ],
                ],
            ]);
    }
}
