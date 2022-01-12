<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Sms;
use App\Models\SmsTemplate;
use App\BusinessLogic\SmsParser;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SmsParserTest extends TestCase
{
    use RefreshDatabase;
    
    /** @test */
    public function it_parse_sms_string_using_template_without_saving_to_db()
    {
        $template = SmsTemplate::make("someBody", "someType", ['key' => 'value']);
        $smsString = "some sms string here";

        $sms = (new SmsParser)->parse($smsString, $template);
        
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
    public function it_parse_sms_model_using_template_without_saving_to_db()
    {
        $template = SmsTemplate::make("someBody", "someType", ['key' => 'value']);
        $smsModel = Sms::make(['type' => 'unknown', 'meta' => [], 'body' => "some sms string here"]);

        $sms = (new SmsParser)->parse($smsModel, $template);
        
        $this->assertEquals("some sms string here", $sms->body);
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

        $sms = (new SmsParser)->parse($smsString, null);
        
        $this->assertEquals($smsString, $sms->body);
        $this->assertEquals(Sms::UNKNOWN, $sms->type);
        $this->assertEquals([], $sms->meta);
        $this->assertFalse($sms->exists);
    }
}
