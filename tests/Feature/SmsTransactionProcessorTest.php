<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Sms;
use App\Models\Brand;
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
    public function it_stores_processed_sms_with_known_purchase_template_and_link_with_transaction_if_brand_is_detected()
    {
        $knownBrand = Brand::factory()->create(['name' => 'ENOC']);

        $sms = "Purchase of AED 106.00 with Credit Card at ENOC,";

        $sut = app(SmsTransactionProcessor::class);
        $sut->process($sms);

        $smsFromDB = Sms::first();
        $this->assertEquals($knownBrand->name, $smsFromDB->transaction->brand->name);
        $this->assertEquals('106.0', $smsFromDB->transaction->amount);
    }

    /** @test */
    public function it_stores_processed_sms_with_known_payment_template_and_link_with_transaction_if_brand_is_detected()
    {
        $knownBrand = Brand::factory()->create(['name' => 'someBrand']);

        $sms = "Payment of AED 38.7 to someBrand with Credit Card ending 9048. Avl Cr. Limit is AED 53,750.64.";

        $sut = app(SmsTransactionProcessor::class);
        $sut->process($sms);

        $smsFromDB = Sms::first();
        $this->assertEquals($knownBrand->name, $smsFromDB->transaction->brand->name);
        $this->assertEquals('39.0', $smsFromDB->transaction->amount);
    }

    /** @test */
    public function it_stores_processed_sms_with_known_salary_template_and_link_with_transaction()
    {
        $knownBrand = Brand::factory()->create(['name' => 'Salary']);

        $sms = "Salary of AED 70,000.00 has been credited into your account XXX99XXX.";

        $sut = app(SmsTransactionProcessor::class);
        $sut->process($sms);

        $smsFromDB = Sms::first();
        $this->assertEquals($knownBrand->name, $smsFromDB->transaction->brand->name);
        $this->assertEquals('70000.0', $smsFromDB->transaction->amount);
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
