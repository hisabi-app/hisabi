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
}
