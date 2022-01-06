<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Sms;
use App\Contracts\SmsTransactionProcessor;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SmsTransactionProcessorTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_stores_processed_sms_with_unkown_template()
    {
        $sms = "some sms body here";

        $sut = app(SmsTransactionProcessor::class);
        $sut->process($sms);

        $smsFromDB = Sms::first();
        $this->assertEquals($sms, $smsFromDB->body);
        $this->assertEquals(Sms::UNKNOWN, $smsFromDB->type);
        $this->assertNull($smsFromDB->transaction);
        $this->assertEmpty($smsFromDB->meta);
    }

    /** @test */
    public function it_stores_processed_sms_with_known_template()
    {
        $sms = "Purchase of AED 106.00 with Credit Card at ENOC,";

        $sut = app(SmsTransactionProcessor::class);
        $sut->process($sms);

        $smsFromDB = Sms::first();
        $this->assertEquals($sms, $smsFromDB->body);
        $this->assertEquals(Sms::EXPENSES, $smsFromDB->type);
        $this->assertNull($smsFromDB->transaction);
        $this->assertEquals([
            'body' => 'Purchase of AED {amount} with {card} at {brand},',
            'type' => Sms::EXPENSES,
            'data' => [
                'amount' => '106.00',
                'card' => 'Credit Card',
                'brand' => 'ENOC'
            ]
        ], $smsFromDB->meta);
    }

    /** @test */
    public function it_process_multi_sms()
    {
        $sms = "some sms body here\nanother sms here";

        $sut = app(SmsTransactionProcessor::class);
        $sut->process($sms);

        $this->assertEquals(2, Sms::count());
    }
}
