<?php

namespace App\Domains\Sms\Services;

use App\Domains\Sms\Models\Sms;
use App\Contracts\SmsTransactionProcessor;
use Illuminate\Support\Collection;

class SmsService
{
    public function __construct(
        private readonly SmsTransactionProcessor $smsTransactionProcessor
    ) {}

    public function create(array $data): Collection
    {
        return $this->smsTransactionProcessor->process(
            $data['body'],
            $data['created_at'] ?? null
        );
    }
}

