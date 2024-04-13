<?php

namespace Tests\Feature\Sms;

use App\Models\Sms;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateSmsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_create_a_model()
    {
        $this->graphQL(/** @lang GraphQL */ '
            mutation {
                createSms(body: """someBody""") {
                    id
                    body
                    transaction_id
                }
            }
            ')->assertJson([
                'data' => [
                    'createSms' => [
                        [
                            "id" => 1,
                            "body" => "someBody",
                            "transaction_id" => null,
                        ]
                    ],
                ],
            ]);

        $this->assertCount(1, Sms::all());
    }

    /** @test */
    public function it_process_created_at_if_provided()
    {
        $this->graphQL(/** @lang GraphQL */ '
            mutation {
                createSms(body: """Payment of AED 38.7 to someBrand with Credit Card ending 9048. Avl Cr. Limit is AED 53,750.64.""" created_at: """2022-05-01""") {
                    id
                    body
                    transaction_id
                }
            }
            ')->assertJson([
            'data' => [
                'createSms' => [
                    [
                        "id" => 1,
                        "body" => "Payment of AED 38.7 to someBrand with Credit Card ending 9048. Avl Cr. Limit is AED 53,750.64.",
                        "transaction_id" => 1,
                    ]
                ],
            ],
        ]);

        $this->assertEquals("2022-05-01", Sms::first()->transaction->created_at->format('Y-m-d'));
    }
}
