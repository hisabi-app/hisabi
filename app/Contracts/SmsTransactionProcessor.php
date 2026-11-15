<?php

namespace App\Contracts;

use App\Domains\Sms\Models\Sms;

interface SmsTransactionProcessor
{
    public function process($sms, $defaultDate = null);

    /**
     * Read one message as a whole (or read a stored one again) and store it with the given attributes.
     */
    public function processMessage(string|Sms $message, $defaultDate = null, array $attributes = []): Sms;

    /**
     * The messages in a pasted text: one per line, except lines that match a multi-line template together.
     */
    public function split(string $text): array;
}
