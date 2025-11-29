<?php

namespace App\Http\Commands\Sms\UpdateSmsCommand;

use App\Domains\Sms\Models\Sms;
use Illuminate\Http\JsonResponse;

readonly class UpdateSmsCommandResponse
{
    public function __construct(
        private Sms $sms
    ) {}

    public function toResponse(): JsonResponse
    {
        return response()->json([
            'sms' => $this->sms,
        ]);
    }
}
