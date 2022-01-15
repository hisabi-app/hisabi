<?php

namespace Tests\Feature;

use App\Models\Sms;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

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
}
