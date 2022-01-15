<?php

namespace Tests\Feature;

use App\Models\Sms;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DeleteSmsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_delete_a_model()
    {
        $sms = Sms::factory()->create();

        $this->graphQL(/** @lang GraphQL */ '
            mutation {
                deleteSms(id: 1) {
                    id
                }
            }
            ')->assertJson([
                'data' => [
                    'deleteSms' => [
                        "id" => $sms->id,
                    ],
                ],
            ]);

        $this->assertNull($sms->fresh());
    }
}
