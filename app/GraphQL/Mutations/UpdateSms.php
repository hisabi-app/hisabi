<?php

namespace App\GraphQL\Mutations;

use App\Models\Sms;
use App\Contracts\SmsTransactionProcessor;

class UpdateSms
{
    protected $smsTransactionProcessor;

    public function __construct(SmsTransactionProcessor $smsTransactionProcessor)
    {
        $this->smsTransactionProcessor = $smsTransactionProcessor;
    }

    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     */
    public function __invoke($_, array $args)
    {
        $sms = Sms::findOrFail($args['id']);

        $sms->update(['body' => $args['body']]);

        $this->smsTransactionProcessor->process($sms);

        return $sms->fresh();
    }
}
