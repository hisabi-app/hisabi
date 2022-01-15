<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UpdateTransactionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_update_a_model()
    {
        $transaction = Transaction::factory()->create(['amount' => 100]);

        $this->graphQL(/** @lang GraphQL */ '
            mutation {
                updateTransaction(id: 1 amount: 200 brand_id: 2 created_at: "2022-01-01") {
                    amount
                }
            }
            ')->assertJson([
                'data' => [
                    'updateTransaction' => [
                        "amount" => 200,
                    ],
                ],
            ]);

        $this->assertEquals(200, $transaction->fresh()->amount);
    }
}
