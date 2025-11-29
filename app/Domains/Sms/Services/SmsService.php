<?php

namespace App\Domains\Sms\Services;

use App\Domains\Sms\Models\Sms;
use App\Contracts\SmsTransactionProcessor;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SmsService
{
    public function __construct(
        private readonly SmsTransactionProcessor $smsTransactionProcessor
    ) {}

    public function getPaginated(int $perPage = 100): LengthAwarePaginator
    {
        return Sms::orderBy('transaction_id')->paginate($perPage);
    }

    public function create(array $data): Collection
    {
        return $this->smsTransactionProcessor->process(
            $data['body'],
            $data['created_at'] ?? null
        );
    }

    public function update(int $id, array $data): Sms
    {
        $sms = Sms::findOrFail($id);
        $sms->update($data);
        return $sms->fresh();
    }
}

