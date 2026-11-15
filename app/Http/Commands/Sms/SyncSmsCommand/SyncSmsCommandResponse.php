<?php

namespace App\Http\Commands\Sms\SyncSmsCommand;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

readonly class SyncSmsCommandResponse
{
    public function __construct(
        private Collection $results
    ) {}

    public function toResponse(): JsonResponse
    {
        return response()->json([
            'data' => $this->results->values(),
        ]);
    }
}
