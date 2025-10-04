<?php

namespace Tests\Feature\Transactions;

use App\Domains\Transaction\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteTransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_delete_a_model()
    {
        $transaction = Transaction::factory()->create();

        $this->graphQL(/** @lang GraphQL */ '
            mutation {
                deleteTransaction(id: 1) {
                    id
                }
            }
            ')->assertJson([
                'data' => [
                    'deleteTransaction' => [
                        "id" => $transaction->id,
                    ],
                ],
            ]);

        $this->assertNull($transaction->fresh());
    }
}
