<?php

namespace App\Http\Commands\Home\DismissSuggestionCommand;

use Illuminate\Http\JsonResponse;

readonly class DismissSuggestionCommandResponse
{
    public function __construct(
        private string $key
    ) {}

    public function toResponse(): JsonResponse
    {
        return response()->json([
            'data' => [
                'key' => $this->key,
                'until' => now()->startOfMonth()->addMonth()->format('Y-m-d'),
            ],
        ]);
    }
}
