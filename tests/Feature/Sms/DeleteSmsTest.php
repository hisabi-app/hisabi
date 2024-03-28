<?php

namespace Tests\Feature\Sms;

use App\Models\Sms;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

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
