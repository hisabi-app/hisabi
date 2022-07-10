<?php

namespace App\BusinessLogic;

use App\Models\SmsTemplate;
use App\Contracts\SmsTemplateDetector as SmsTemplateDetectorContract;

class SmsTemplateDetector implements SmsTemplateDetectorContract
{
    public function detect($sms)
    {
        foreach(config('finance.sms_templates') as $template) {
            $templateCopy = $template;

            $templateCopy = str_replace("{amount}", "(.*?)", $templateCopy);
            $templateCopy = str_replace("{brand}", "(.*?)", $templateCopy);
            $templateCopy = str_replace("{card}", "(.*?)", $templateCopy);
            $templateCopy = str_replace("{account}", "(.*?)", $templateCopy);
            $templateCopy = str_replace("{datetime}", "(.*?(?=\.))", $templateCopy);
            
            if(preg_match("/{$templateCopy}/", $sms, $matchedParts)) {
                $partsWithValues = $this->getPartsWithValues($matchedParts, $template);
                
                return SmsTemplate::make(
                    $template,
                    $partsWithValues,
                );
            }
        }

        return null;
    }

    protected function getPartsWithValues($matchedParts, $templateBody)
    {
        $partsPositionsInTemplate = [];

        if(strpos($templateBody, "{amount}") !== false) {
            $partsPositionsInTemplate['amount'] = strpos($templateBody, "{amount}");
        }
        if(strpos($templateBody, "{brand}") !== false) {
            $partsPositionsInTemplate['brand'] = strpos($templateBody, "{brand}");
        }
        if(strpos($templateBody, "{card}") !== false) {
            $partsPositionsInTemplate['card'] = strpos($templateBody, "{card}");
        }
        if(strpos($templateBody, "{account}") !== false) {
            $partsPositionsInTemplate['account'] = strpos($templateBody, "{account}");
        }
        if(strpos($templateBody, "{datetime}") !== false) {
            $partsPositionsInTemplate['datetime'] = strpos($templateBody, "{datetime}");
        }
    
        asort($partsPositionsInTemplate);

        $index = 1;
        $partsWithValues = [];
        foreach($partsPositionsInTemplate as $part => $value) {
            if(! empty($matchedParts[$index])) {
                $partsWithValues[$part] = $matchedParts[$index];
            }
            $index++;
        }
        
        return $partsWithValues;
    }
}

