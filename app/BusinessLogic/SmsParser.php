<?php

namespace App\BusinessLogic;

use App\Models\Sms;
use App\Contracts\SmsParser as SmsParserContract;

class SmsParser implements SmsParserContract
{
    public function parse($sms, $smsTemplate): Sms
    {
        $type = $smsTemplate ? $smsTemplate->type() : Sms::UNKNOWN;
        $meta = $smsTemplate ? $smsTemplate->toArray() : [];

        if($sms instanceof Sms) {
            $sms->type = $type;
            $sms->meta = $meta;

            return $sms;
        }

        return Sms::make([
            'body' => $sms,
            'type' => $type,
            'meta' => $meta,
        ]);
    }
}