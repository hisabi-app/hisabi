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
            [
                'body' => 'hello this is {amount}, but this is {brand}!',
                'type' => 'someType',
            ]
        ]);

        $sut = new SmsTemplateDetector;

        $smsTemplate = $sut->detect("hello this is 10, but this is someBrand!");

        $this->assertEquals('hello this is {amount}, but this is {brand}!', $smsTemplate->body());
        $this->assertEquals('someType', $smsTemplate->type());
        $this->assertEquals('10', $smsTemplate->data()['amount']);
        $this->assertEquals('someBrand', $smsTemplate->data()['brand']);
    }
}
