<?php

namespace Tests\Feature;

use App\Models\Transaction;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TransactionsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_returns_correct_data()
    {
        $transaction1 = Transaction::factory()->create();
        $transaction2 = Transaction::factory()->create();

        $this->graphQL(/** @lang GraphQL */ '
            {
                transactions {
                    data {
                        id
                        amount
                        created_at
                        brand {
                            id
                            name
                            category {
                                name
                                type
                            }
                        }
                    }
                    paginatorInfo {
                        hasMorePages
                    }
                }
            }
            ')->assertJson([
                'data' => [
                    'transactions' => [
                        "data" => [
                            [
                                'id' => $transaction2->id,
                                'amount' => $transaction2->amount,
                                'brand' => [
                                    'name' => $transaction2->brand->name,
                                    "category" => [
                                        "name" => $transaction2->brand->category->name
                                    ]
                                ],
                            ],
                            [
                                'id' => $transaction1->id,
                                'amount' => $transaction1->amount,
                                'brand' => [
                                    'name' => $transaction1->brand->name,
                                    "category" => [
                                        "name" => $transaction1->brand->category->name
                                    ]
                                ],
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
