<?php

namespace App\Http\Commands\Kiosk\TranscribeKioskQuestionCommand;

use Illuminate\Http\JsonResponse;

readonly class TranscribeKioskQuestionCommandResponse
{
    public function __construct(
        private ?string $text,
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
            : response()->json(['data' => ['text' => $this->text]]);
    }
}
