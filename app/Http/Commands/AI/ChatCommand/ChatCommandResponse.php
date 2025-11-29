<?php

namespace App\Http\Commands\AI\ChatCommand;

use Illuminate\Http\JsonResponse;

readonly class ChatCommandResponse
{
    public function __construct(
        private array $response
    ) {}

    public function toResponse(): JsonResponse
    {
        return response()->json([
            'role' => $this->response['role'],
            'content' => $this->response['content'],
            'charts' => $this->response['charts'] ?? [],
            'components' => $this->response['components'] ?? [],
            'suggestions' => $this->response['suggestions'] ?? [],
        ]);
    }
}
