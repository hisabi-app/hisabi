<?php

namespace Tests\Feature\Transactions;

use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_correct_data()
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
