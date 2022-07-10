<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Support\Facades\Config;
use App\BusinessLogic\SmsTemplateDetector;

class SmsTemplateDetectorTest extends TestCase
{
    /** @test */
    public function it_returns_null_if_no_template_match()
    {
        $sut = new SmsTemplateDetector;

        $this->assertNull(
            $sut->detect("some unknown sms format")
        );
    }

    /** @test */
    public function it_returns_correct_matched_template_with_extracted_data()
    {
        Config::set('finance.sms_templates', [
            'hello this is {amount}, but this is {brand} on {datetime}.'
        ]);

        $sut = new SmsTemplateDetector;

        $smsTemplate = $sut->detect("hello this is 10, but this is someBrand on 20-06-2022 10:10.");

        $this->assertEquals('hello this is {amount}, but this is {brand} on {datetime}.', $smsTemplate->body());
        $this->assertEquals('10', $smsTemplate->data()['amount']);
        $this->assertEquals('someBrand', $smsTemplate->data()['brand']);
        $this->assertEquals('20-06-2022 10:10', $smsTemplate->data()['datetime']);
    }
}
