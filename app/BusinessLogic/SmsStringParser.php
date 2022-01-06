<?php

namespace App\BusinessLogic;

use App\Models\Sms;
use App\Contracts\SmsParser as SmsParserContract;

class SmsStringParser implements SmsParserContract
{
    public function parse($sms, $smsTemplate): Sms
    {
        return Sms::make([
            'body' => $sms,
            'type' => $smsTemplate ? $smsTemplate->type() : Sms::UNKNOWN,
            'meta' => $smsTemplate ? $smsTemplate->toArray() : [],
        ]);
    }
}