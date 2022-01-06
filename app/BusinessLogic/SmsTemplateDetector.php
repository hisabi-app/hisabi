<?php

namespace App\BusinessLogic;

use App\Models\SmsTemplate;
use App\Contracts\SmsTemplateDetector as SmsTemplateDetectorContract;

class SmsTemplateDetector implements SmsTemplateDetectorContract
{
    public function detect($sms)
    {
        foreach(config('finance.sms_templates') as $template) {
            $templateBody = $template['body'];

            $templateBody = str_replace("{amount}", "(.*?)", $templateBody);
            $templateBody = str_replace("{brand}", "(.*?)", $templateBody);
            $templateBody = str_replace("{card}", "(.*?)", $templateBody);
            
            if(preg_match("/{$templateBody}/", $sms, $matchedParts)) {
                $partsWithValues = $this->getPartsWithValues($matchedParts, $template['body']);
                
                return SmsTemplate::make(
                    $template['body'],
                    $template['type'],
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

