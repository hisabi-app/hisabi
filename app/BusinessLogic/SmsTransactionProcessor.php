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
    protected $knownBrands;

    public function __construct(SmsTemplateDetector $smsTemplateDetector, SmsParser $smsParser)
    {
        $this->smsTemplateDetector = $smsTemplateDetector;
        $this->smsParser = $smsParser;
        $this->knownBrands = Brand::get();
    }

    public function process($smsString)
    {
        foreach(explode("\n", $smsString) as $sms) {
            $template = $this->smsTemplateDetector->detect($sms);
            $sms = $this->smsParser->parse($sms, $template);

            if($transaction = $this->createTransactionFromSms($sms)) {
                $sms['transaction_id'] = $transaction->id;
            }

            $sms->save();
        }
    }

    protected function createTransactionFromSms($sms)
    {
        $brandFromSms = $sms->meta['data']['brand'] ?? '';

        if(! $brandFromSms) { return; }

        $matchedBrand = $this->detectBrand($brandFromSms);

        if(! $matchedBrand) { return; }
        
        $amount = (float) filter_var($sms->meta['data']['amount'] ?? 0, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

        return Transaction::create([
            'amount' => round($amount),
            'category_id' => $matchedBrand->category_id,
            'brand_id' => $matchedBrand->id,
        ]);
    }

    protected function detectBrand($brandFromSms) {
        // TODO: find better solution for detecting brands maybe using SQL query?
        $matchedBrand = null;
        foreach($this->knownBrands as $knownBrand) {
            if(str_contains(strtolower($brandFromSms), strtolower($knownBrand->name))) {
                $matchedBrand = $knownBrand;
                break;
            }
        }

        return $matchedBrand;
    }
}