<?php

namespace App\BusinessLogic;

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

    public function process($smsString)
    {
        
        foreach(explode("\n", $smsString) as $sms) {
            $template = $this->smsTemplateDetector->detect($sms);
            $sms = $this->smsParser->parse($sms, $template);

            if($transaction = $this->createTransactionFromSms($sms)) {
                $smstransaction_id['transaction_id'] = $transaction->id;
            }

            $sms->save();
        }
    }

    protected function createTransactionFromSms($sms)
    {
        // can return null

        // return Transaction::create([
        //     'amount' => $sms->amount(),
        //     'category_id' => 1,
        //     'brand_id' => 1,
        // ]);
    }
}