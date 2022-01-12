<?php

namespace App\BusinessLogic;

use App\Models\Sms;
use App\Contracts\SmsParser as SmsParserContract;

class SmsParser implements SmsParserContract
{
    public function parse($sms, $smsTemplate): Sms
    {
        $meta = $smsTemplate ? $smsTemplate->toArray() : [];

        if($sms instanceof Sms) {
            $sms->meta = $meta;

            return $sms;
        }

        return Sms::make([
            'body' => $sms,
            'meta' => $meta,
        ]);
    }
}