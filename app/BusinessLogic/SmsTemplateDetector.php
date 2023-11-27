<?php

namespace App\BusinessLogic;

use App\Models\SmsTemplate;
use App\Contracts\SmsTemplateDetector as SmsTemplateDetectorContract;

class SmsTemplateDetector implements SmsTemplateDetectorContract
{
    /**
     * @param $sms
     * @return SmsTemplate|null
     */
    public function detect($sms): ?SmsTemplate
    {
        $detectedTemplate = $this->getDetectedTemplate($sms);

        if(! $detectedTemplate) {
            return null;
        }

        $smsInformation = $this->extractSmsInformation($detectedTemplate, $sms);

        return SmsTemplate::make(
            $detectedTemplate,
            $smsInformation,
        );
    }

    /**
     * @param $template
     * @param $sms
     * @return array
     */
    private function extractSmsInformation($template, $sms): array
    {
        $keys = $this->extractPlaceholdersKeys($template);
        $maskedSmsTemplate = $this->getMaskedSmsTemplate($template);
        preg_match("/{$maskedSmsTemplate}/", $sms, $matchedParts);
        array_shift($matchedParts);

        $smsInformation = [];

        for($i = 0; $i < count($keys); $i++) {
            if(empty($matchedParts[$i])) {
                continue;
            }
            if($keys[$i] == "date") {
                $date = $matchedParts[$i];
                $date = str_replace("/", "-", $date);
                $smsInformation["datetime"] = $date;
            }

            $smsInformation[$keys[$i]] = $matchedParts[$i];
        }

        return $smsInformation;
    }

    /**
     * @param $sms
     * @return array|string|null
     */
    private function getDetectedTemplate($sms): array|string|null
    {
        $detectedTemplate = null;

        foreach(config('finance.sms_templates') as $template) {
            $maskedSmsTemplate = $this->getMaskedSmsTemplate($template);

            if(preg_match("/{$maskedSmsTemplate}/", $sms)) {
                $detectedTemplate = $template;
                break;
            }
        }

        return $detectedTemplate;
    }

    /**
     * @param $string
     * @return mixed
     */
    private function extractPlaceholdersKeys($string): mixed
    {
        // Regular expression to match content inside curly braces
        $pattern = '/\{([^}]*)}/';

        // Array to store the matches
        $matches = [];

        // Perform the regex match
        preg_match_all($pattern, $string, $matches);

        // The matches are in the second element of the result
        return $matches[1];
    }

    /**
     * @param mixed $template
     * @return array|string|string[]|null
     */
    public function getMaskedSmsTemplate(mixed $template): string|array|null
    {
        return preg_replace("/\{.*?}/", "(.*?)", $template);
    }
}

