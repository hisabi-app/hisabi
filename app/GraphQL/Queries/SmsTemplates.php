<?php

namespace App\GraphQL\Queries;

class SmsTemplates
{
    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     */
    public function __invoke($_, array $args)
    {
        return config('finance.sms_templates');
    }
}
