<?php

namespace App\Contracts;

interface SmsTemplateDetector
{
    public function detect($sms);
}