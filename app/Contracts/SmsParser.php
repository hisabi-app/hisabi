<?php

namespace App\Contracts;

use App\Domains\Sms\Models\Sms;

interface SmsParser 
{
    public function parse($sms, $smsTemplate): Sms;
}