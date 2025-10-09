<?php

namespace App\Http\Commands\Sms\CreateSmsCommand;

use Illuminate\Support\Collection;
use Illuminate\Http\JsonResponse;

readonly class CreateSmsCommandResponse
{
    public function __construct(
        private Collection $smsCollection
    ) {}

    public function toResponse(): JsonResponse
    {
        return response()->json([
            'data' => $this->smsCollection,
        ], 201);
    }
}

