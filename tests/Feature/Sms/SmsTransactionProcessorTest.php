<?php

namespace Tests\Feature\Sms;

use App\Contracts\SmsTransactionProcessor;
use App\Domains\Brand\Models\Brand;
use App\Domains\Sms\Models\Sms;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmsTransactionProcessorTest extends TestCase
{
    use RefreshDatabase;

    public function test_not_valid_template_should_create_sms_without_transaction()
    {
        $sms = "some sms body here";

        $sut = app(SmsTransactionProcessor::class);
        $sut->process($sms);

        $smsFromDB = Sms::first();
        $this->assertEquals($sms, $smsFromDB->body);
        $this->assertNull($smsFromDB->transaction);
        $this->assertEmpty($smsFromDB->meta);
    }

    public function test_valid_template_with_unknown_brand_should_create_sms_with_transaction_with_new_brand_with_no_category()
    {
        $sms = "Purchase of AED 106.00 with Credit Card at ENOC,";

        $sut = app(SmsTransactionProcessor::class);
        $sut->process($sms);

        $smsFromDB = Sms::first();
        $this->assertEquals($sms, $smsFromDB->body);
        $this->assertNotNull($smsFromDB->transaction);
        $this->assertNotNull($smsFromDB->transaction->brand);
        $this->assertNull($smsFromDB->transaction->brand->category);
        $this->assertEquals("ENOC", $smsFromDB->transaction->brand->name);
    }

    public function test_it_stores_processed_sms_with_known_purchase_template_and_link_with_transaction_if_brand_is_found()
    {
        $knownBrand = Brand::factory()->create(['name' => 'ENOC']);

        $sms = "Purchase of AED 106.00 with Credit Card at ENOC,";

        $sut = app(SmsTransactionProcessor::class);
        $sut->process($sms);

        $smsFromDB = Sms::first();
        $this->assertEquals($knownBrand->name, $smsFromDB->transaction->brand->name);
        $this->assertEquals('106.0', $smsFromDB->transaction->amount);
    }

    public function test_it_stores_processed_sms_with_known_payment_template_and_link_with_transaction_if_brand_is_found()
    {
        $knownBrand = Brand::factory()->create(['name' => 'someBrand']);

        $sms = "Payment of AED 38.7 to someBrand with Credit Card ending 9048. Avl Cr. Limit is AED 53,750.64.";

        $sut = app(SmsTransactionProcessor::class);
        $sut->process($sms);

        $smsFromDB = Sms::first();
        $this->assertEquals($knownBrand->name, $smsFromDB->transaction->brand->name);
        $this->assertEquals('38.7', $smsFromDB->transaction->amount);
    }

    public function test_it_stores_processed_sms_with_known_salary_template_and_link_with_transaction()
    {
        $knownBrand = Brand::factory()->create(['name' => 'Salary']);

        $sms = "Salary of AED 70,000.00 has been credited into your account XXX99XXX.";

        $sut = app(SmsTransactionProcessor::class);
        $sut->process($sms);

        $smsFromDB = Sms::first();
        $this->assertEquals($knownBrand->name, $smsFromDB->transaction->brand->name);
        $this->assertEquals('70000.0', $smsFromDB->transaction->amount);
    }

    public function test_it_process_multi_sms()
    {
        $sms = "some sms body here\nanother sms here";

        $sut = app(SmsTransactionProcessor::class);
        $sut->process($sms);

        $this->assertEquals(2, Sms::count());
    }

    public function test_it_returns_processed_sms_models()
    {
        $sms = "some sms body here\nanother sms here";

        $sut = app(SmsTransactionProcessor::class);
        $result = $sut->process($sms);

        $this->assertEquals(2, $result->count());
    }

    public function test_it_process_passed_sms_model_and_update_meta()
    {
        $smsModel = Sms::create(['body' => 'Purchase of AED 106.00 with Credit Card at ENOC,', 'meta' => []]);

        $sut = app(SmsTransactionProcessor::class);
        $result = $sut->process($smsModel);

        $this->assertEquals(1, $result->count());
        $this->assertNotNull($result[0]->meta);
    }

    public function test_it_creates_transaction_with_provided_datetime_if_passed_and_valid()
    {
        $knownBrand = Brand::factory()->create(['name' => 'someBrand']);

        $sms = "AED 5.65 has been debited from account 8118 using debit card at someBrand on 25-06-2022 13:29.";

        $sut = app(SmsTransactionProcessor::class);
        $sut->process($sms);


        $smsFromDB = Sms::first();
        $this->assertEquals($knownBrand->name, $smsFromDB->transaction->brand->name);
        $this->assertEquals('5.65', $smsFromDB->transaction->amount);
        $this->assertEquals('25-06-2022', $smsFromDB->transaction->created_at->format('d-m-Y'));
    }

    public function test_it_creates_transaction_with_provided_default_datetime_if_no_datetime_found()
    {
        $knownBrand = Brand::factory()->create(['name' => 'someBrand']);

        $sms = "Payment of AED 38.7 to someBrand with Credit Card ending 9048. Avl Cr. Limit is AED 53,750.64.";

        $sut = app(SmsTransactionProcessor::class);
        $sut->process($sms, "2026-06-01");

        $smsFromDB = Sms::first();
        $this->assertEquals($knownBrand->name, $smsFromDB->transaction->brand->name);
        $this->assertEquals('38.7', $smsFromDB->transaction->amount);
        $this->assertEquals('01-06-2026', $smsFromDB->transaction->created_at->format('d-m-Y'));
    }
}
