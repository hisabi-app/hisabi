<?php

namespace Tests\Feature\Transactions;

use App\Models\Brand;
use App\Models\User;
use App\Domains\Transaction\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateTransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_create_a_model()
    {
        $user = User::factory()->create();
        $brand = Brand::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/v1/transactions', [
                'amount' => 100,
                'brand_id' => $brand->id,
                'created_at' => '2022-05-01',
                'note' => 'someNote'
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'transaction' => [
                    "id" => 1,
                    "amount" => 100,
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
            ]);

        $this->assertCount(1, Transaction::all());
    }
}
