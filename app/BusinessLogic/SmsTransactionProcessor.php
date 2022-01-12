<?php

namespace App\BusinessLogic;

use App\Models\Sms;
use App\Models\Transaction;
use App\Contracts\SmsParser;
use App\Contracts\SmsTemplateDetector;
use App\Contracts\SmsTransactionProcessor as SmsTransactionProcessorContract;

class SmsTransactionProcessor implements SmsTransactionProcessorContract
{
    protected SmsTemplateDetector $smsTemplateDetector;
    protected SmsParser $smsParser;

    public function __construct(SmsTemplateDetector $smsTemplateDetector, SmsParser $smsParser)
    {
        $this->smsTemplateDetector = $smsTemplateDetector;
        $this->smsParser = $smsParser;
    }

    public function process($sms)
    {
        $processedSmsModels = collect();

        $smsString = $sms instanceof Sms ? $sms->body : $sms;

        foreach(explode("\n", $smsString) as $smsBody) {
            $template = $this->smsTemplateDetector->detect($smsBody);
            $smsModel = $this->smsParser->parse($sms instanceof Sms ? $sms : $smsBody, $template);

            if($template && $transaction = Transaction::tryCreateFromSms($smsModel)) {
                $smsModel['transaction_id'] = $transaction->id;
            }

            $smsModel->save();

            $processedSmsModels->push($smsModel);
        }

        return $processedSmsModels;
    }
}