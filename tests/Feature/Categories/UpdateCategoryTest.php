<?php

namespace Tests\Feature\Categories;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_update_a_model()
    {
        $category = Category::factory()->create(['name' => 'oldName']);

        $this->graphQL(
            /** @lang GraphQL */
            '
            mutation {
                updateCategory(id: 1 name: """someNewName""" type: """newType""" color: """newColor""" icon: """newIcon""") {
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
                'updateCategory' => [
                    "id" => 1,
                    "name" => "someNewName",
                    "type" => "newType",
                    "color" => "newColor",
                    "icon" => "newIcon"
                ],
            ],
        ]);

        $this->assertEquals("someNewName", $category->fresh()->name);
    }
}
