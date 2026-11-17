<?php

namespace App\Http\Queries\Kiosk\GetKioskSpeechQuery;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

readonly class GetKioskSpeechQueryResponse
{
    public function __construct(
        private ?string $audio,
        private ?string $error = null,
        private int $status = 200
    ) {}

    public static function failed(string $error, int $status): self
    {
        return new self(null, $error, $status);
    }

    public function toResponse(): JsonResponse|Response
    {
        return $this->error
            ? response()->json(['message' => $this->error], $this->status)
            : response($this->audio, 200, ['Content-Type' => 'audio/mpeg', 'Cache-Control' => 'no-store']);
    }
}
