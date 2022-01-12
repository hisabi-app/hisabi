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

    /** @test */
    public function it_parse_sms_model_using_template_without_saving_to_db()
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

    /** @test */
    public function it_parse_sms_without_template()
    {
        $smsString = "some sms string here";

        $sms = (new SmsParser)->parse($smsString, null);
        
        $this->assertEquals($smsString, $sms->body);
        $this->assertEquals([], $sms->meta);
        $this->assertFalse($sms->exists);
    }
}
