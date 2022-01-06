<?php

namespace App\Contracts;

use App\Models\Sms;

interface SmsParser 
{
    public function parse($sms, $smsTemplate): Sms;
}