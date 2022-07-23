<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreateCategoryTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_create_a_model()
    {
        $this->graphQL(
            /** @lang GraphQL */
            '
            mutation {
                createCategory(name: """someName""" type: """someType""" color: """someColor""") {
                    id
                    name
                    type
                    color
                }
            }
            '
        )->assertJson([
            'data' => [
                'createCategory' => [
                    "id" => 1,
                    "name" => "someName",
                    "type" => "someType",
                    "color" => "someColor"
                ],
            ],
        ]);

        $this->assertCount(1, Category::all());
    }
}
