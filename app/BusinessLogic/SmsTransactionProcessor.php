<?php

namespace App\BusinessLogic;

use App\Domains\Sms\Models\Sms;
use App\Domains\Transaction\Models\Transaction;
use App\Contracts\SmsParser;
use Illuminate\Support\Collection;
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

    public function process($sms, $defaultDate = null): Collection
    {
        $processedSmsModels = collect();

        $smsString = $sms instanceof Sms ? $sms->body : $sms;

        foreach($this->splitMessages($smsString) as $smsBody) {
            $template = $this->smsTemplateDetector->detect($smsBody);
            $smsModel = $this->smsParser->parse($sms instanceof Sms ? $sms : $smsBody, $template);
            $smsModel->setDefaultDateIfNotFound($defaultDate);

            if($template && $transaction = Transaction::tryCreateFromSms($smsModel)) {
                $smsModel['transaction_id'] = $transaction->id;
            }

            $smsModel->save();

            $processedSmsModels->push($smsModel);
        }

        return $processedSmsModels;
    }

    /**
     * One message per line, except where the next lines match a multi-line template together.
     */
    protected function splitMessages(string $text): array
    {
        $lines = collect(preg_split('/\r\n|\r|\n/', $text))
            ->map(fn ($line) => trim($line))
            ->filter(fn ($line) => $line !== '')
            ->values()
            ->all();

        $blockSizes = collect(config('hisabi.sms_templates'))
            ->map(fn ($template) => substr_count($template, "\n") + 1)
            ->filter(fn ($size) => $size > 1)
            ->unique()
            ->sortDesc();

        $messages = [];

        for($i = 0; $i < count($lines); $i++) {
            foreach($blockSizes as $size) {
                $block = implode("\n", array_slice($lines, $i, $size));
                $template = $this->smsTemplateDetector->detect($block);

                if($template && substr_count($template->body(), "\n") + 1 === $size) {
                    $messages[] = $block;
                    $i += $size - 1;

                    continue 2;
                }
            }

            $messages[] = $lines[$i];
        }

        return $messages;
    }
}
