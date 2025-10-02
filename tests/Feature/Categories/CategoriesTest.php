<?php

namespace Tests\Feature\Categories;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoriesTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_correct_data()
    {
        $category = Category::factory()->create();

        $this->graphQL(
            /** @lang GraphQL */
            '
            {
                categories(page: 1) {
                    data {
                        id
                        name
                        type
                        color
                    }
                    paginatorInfo {
                        hasMorePages
                    }
                }
            }
            '
        )->assertJson([
            'data' => [
                'categories' => [
                    "data" => [
                        [
                            'id' => $category->id,
                            'name' => $category->name,
                            'type' => $category->type,
                            'color' => 'gray',
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
