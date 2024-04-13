<?php

namespace App\Contracts;

interface SmsTransactionProcessor
{
    public function process($sms, $defaultDate = null);
}
