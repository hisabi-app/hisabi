<?php

namespace Tests\Feature;

use App\Models\Sms;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UpdateSmsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_update_a_model()
    {
        $sms = Sms::factory()->create(['body' => 'oldBody']);

        $this->graphQL(/** @lang GraphQL */ '
            mutation {
                updateSms(id: 1 body: """newBody""") { 
                    id
                    body
                    transaction_id
                }
            }
            ')->assertJson([
                'data' => [
                    'updateSms' => [
                        "id" => 1,
                        "body" => "newBody",
                        "transaction_id" => null,
                    ],
                ],
            ]);

        $this->assertEquals("newBody", $sms->fresh()->body);
    }
}
