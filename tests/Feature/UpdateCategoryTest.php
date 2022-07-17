<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UpdateCategoryTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_update_a_model()
    {
        $category = Category::factory()->create(['name' => 'oldName']);

        $this->graphQL(/** @lang GraphQL */ '
            mutation {
                updateCategory(id: 1 name: """someNewName""" type: """newType""") {
                    id
                    name
                    type
                }
            }
            ')->assertJson([
                'data' => [
                    'updateCategory' => [
                        "id" => 1,
                        "name" => "someNewName",
                        "type" => "newType",
                    ],
                ],
            ]);

        $this->assertEquals("someNewName", $category->fresh()->name);
    }
}
