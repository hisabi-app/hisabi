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
        $templates = [
            'Purchase of AED {amount} with {card} at {brand},' => [
                'message' => 'Purchase of AED 500 with Visa at ElectronicsStore,',
                'expectedData' => [
                    'amount' => '500',
                    'brand' => 'ElectronicsStore'
                ]
            ],
            'Payment of AED {amount} to {brand} with {card}.' => [
                'message' => 'Payment of AED 200 to InternetProvider with MasterCard.',
                'expectedData' => [
                    'amount' => '200',
                    'brand' => 'InternetProvider',
                ]
            ],
            'AED {amount} has been debited from {account} using {card} at {brand} on {date} {time}.' => [
                'message' => 'AED 100 has been debited from SavingsAccount using DebitCard at Supermarket on 25-12-2023 14:00.',
                'expectedData' => [
                    'amount' => '100',
                    'brand' => 'Supermarket',
                    'datetime' => '25-12-2023'
                ]
            ],
        ];

        Config::set('finance.sms_templates', array_keys($templates));

        $sut = new SmsTemplateDetector;

        foreach ($templates as $template => $data) {
            $smsTemplate = $sut->detect($data['message']);

            $this->assertEquals($template, $smsTemplate->body());
            foreach ($data['expectedData'] as $key => $value) {
                $this->assertEquals($value, $smsTemplate->data()[$key]);
            }
        }
    }
}
