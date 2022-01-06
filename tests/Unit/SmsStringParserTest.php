<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Sms;
use App\Models\SmsTemplate;
use App\BusinessLogic\SmsStringParser;

class SmsStringParserTest extends TestCase
{
    /** @test */
    public function it_parse_sms_using_template_without_saving_to_db()
    {
        $template = SmsTemplate::make("someBody", "someType", ['key' => 'value']);
        $smsString = "some sms string here";

        $sms = (new SmsStringParser)->parse($smsString, $template);
        
        $this->assertEquals($smsString, $sms->body);
        $this->assertEquals('someType', $sms->type);
        $this->assertEquals([
            'body' => 'someBody',
            'type' => 'someType',
            'data' => ['key' => 'value'],
        ], $sms->meta);
        $this->assertFalse($sms->exists);
    }

    /** @test */
    public function it_parse_sms_without_template_and_unknown_type()
    {
        $smsString = "some sms string here";

        $sms = (new SmsStringParser)->parse($smsString, null);
        
        $this->assertEquals($smsString, $sms->body);
        $this->assertEquals(Sms::UNKNOWN, $sms->type);
        $this->assertEquals([], $sms->meta);
        $this->assertFalse($sms->exists);
    }
}
