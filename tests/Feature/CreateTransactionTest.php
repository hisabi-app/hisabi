<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Brand;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreateTransactionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_create_a_model()
    {
        $brand = Brand::factory()->create();

        $this->graphQL(/** @lang GraphQL */ '
            mutation {
                createTransaction(amount: 100 brand_id: 1 created_at: """2022-05-01""" note: """someNote""") {
                    id
                    amount
                    created_at
                    note
                    brand {
                        id
                        name
                        category {
                            name
                            type
                        }
                    }
                }
            }
            ')->assertJson([
                'data' => [
                    'createTransaction' => [
                        "id" => 1,
                        "amount" => 100,
                        "created_at" => "2022-05-01",
                        "note" => "someNote",
                        "brand" => [
                            "id" => $brand->id,
                            "name" => $brand->name,
                            "category" => [
                                "name" => $brand->category->name,
                                "type" => $brand->category->type,
                            ]
                        ]
                    ],
                ],
            ]);

        $this->assertCount(1, Transaction::all());
    }
}
