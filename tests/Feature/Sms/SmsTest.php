<?php

namespace Tests\Feature\Sms;

use App\Domains\Sms\Models\Sms;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_correct_data()
    {
        $sms = Sms::factory()->create();

        $this->graphQL(/** @lang GraphQL */ '
            {
                sms(page: 1) {
                    data {
                        id
                        body
                        transaction_id
                    }
                    paginatorInfo {
                        hasMorePages
                    }
                }
            }
            ')->assertJson([
                'data' => [
                    'sms' => [
                        "data" => [
                            [
                                'id' => $sms->id,
                                'body' => $sms->body,
                                'transaction_id' => $sms->transaction_id,
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
