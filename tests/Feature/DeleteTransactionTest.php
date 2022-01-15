<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DeleteTransactionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_delete_a_model()
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
