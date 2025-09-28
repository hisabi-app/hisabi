<?php

namespace Tests\Unit\Models\Sms;

use App\BusinessLogic\SmsParser;
use App\Models\Sms;
use App\Models\SmsTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmsParserTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_parse_sms_string_using_template_without_saving_to_db()
    {
        $template = SmsTemplate::make("someBody", ['key' => 'value']);
        $smsString = "some sms string here";

        $sms = (new SmsParser)->parse($smsString, $template);

        $this->assertEquals($smsString, $sms->body);
        $this->assertEquals([
            'body' => 'someBody',
            'data' => ['key' => 'value'],
        ], $sms->meta);
        $this->assertFalse($sms->exists);
    }

    public function test_it_parse_sms_model_using_template_without_saving_to_db()
    {
        $template = SmsTemplate::make("someBody", ['key' => 'value']);
        $smsModel = Sms::make(['meta' => [], 'body' => "some sms string here"]);

        $sms = (new SmsParser)->parse($smsModel, $template);

        $this->assertEquals("some sms string here", $sms->body);
        $this->assertEquals([
            'body' => 'someBody',
            'data' => ['key' => 'value'],
        ], $sms->meta);
        $this->assertFalse($sms->exists);
    }

    public function test_it_parse_sms_without_template()
    {
        $smsString = "some sms string here";

        $sms = (new SmsParser)->parse($smsString, null);

        $this->assertEquals($smsString, $sms->body);
        $this->assertEquals([], $sms->meta);
        $this->assertFalse($sms->exists);
    }
}
