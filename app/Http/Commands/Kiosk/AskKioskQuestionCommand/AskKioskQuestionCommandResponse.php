<?php

namespace App\Http\Commands\Kiosk\AskKioskQuestionCommand;

use Illuminate\Http\JsonResponse;

readonly class AskKioskQuestionCommandResponse
{
    /**
     * @param  array{question: string, title: string, say: string, cards: array<int, array>, conversation_id: string, speech_id: ?string}|null  $answer
     */
    public function __construct(
        private ?array $answer,
        private ?string $error = null,
        private int $status = 200
    ) {}

    public static function failed(string $error, int $status): self
    {
        return new self(null, $error, $status);
    }

    public function toResponse(): JsonResponse
    {
        return $this->error
            ? response()->json(['message' => $this->error], $this->status)
            : response()->json(['data' => $this->answer]);
    }
}
