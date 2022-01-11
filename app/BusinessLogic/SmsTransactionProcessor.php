<?php

namespace App\BusinessLogic;

use App\Models\Brand;
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

            if($template) {
                $transaction = $this->createTransactionFromSms($sms);
                $sms['transaction_id'] = $transaction->id;
            }

            $sms->save();
        }
    }

    protected function createTransactionFromSms($sms)
    {
        $brandFromSms = $sms->meta['data']['brand'] ?? null;
        $amountFromSms = $sms->meta['data']['amount'] ?? null;
        
        if(! $brandFromSms || ! $amountFromSms) {
            return;
        }

        $brand = $this->findOrCreateNewBrand($brandFromSms);
        
        $amount = (float) filter_var($amountFromSms, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

        return Transaction::create([
            'amount' => round($amount),
            'category_id' => $brand->category_id,
            'brand_id' => $brand->id,
        ]);
    }

    protected function findOrCreateNewBrand($brandFromSms) {
        // TODO: find better solution for detecting brands maybe using SQL query?
        foreach(Brand::get() as $knownBrand) {
            if(str_contains(strtolower($brandFromSms), strtolower($knownBrand->name))) {
                return $knownBrand;
            }
        }

        return Brand::create(['name' => $brandFromSms]);
    }
}