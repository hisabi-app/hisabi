<?php

namespace Tests\Feature\Categories;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_create_a_model()
    {
        $this->graphQL(
            /** @lang GraphQL */
            '
            mutation {
                createCategory(name: """someName""" type: """someType""" color: """someColor""" icon: """someIcon""") {
                    id
                    name
                    type
                    color
                    icon
                }
            }
            '
        )->assertJson([
            'data' => [
                'createCategory' => [
                    "id" => 1,
                    "name" => "someName",
                    "type" => "someType",
                    "color" => "someColor",
                    "icon" => "someIcon"
                ],
            ],
        ]);

        $this->assertCount(1, Category::all());
    }
}
