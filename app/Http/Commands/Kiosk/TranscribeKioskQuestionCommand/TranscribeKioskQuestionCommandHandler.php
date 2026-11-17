<?php

namespace App\Http\Commands\Kiosk\TranscribeKioskQuestionCommand;

use App\Domains\Assistant\Services\AssistantService;
use Throwable;

class TranscribeKioskQuestionCommandHandler
{
    public function __construct(
        private readonly AssistantService $assistantService
    ) {}

    public function handle(TranscribeKioskQuestionCommand $command): TranscribeKioskQuestionCommandResponse
    {
        if (! $this->assistantService->hasVoice()) {
            return TranscribeKioskQuestionCommandResponse::failed('Speaking isn’t set up on this Hisabi.', 503);
        }

        try {
            $text = $this->assistantService->transcribe($command->audio);
        } catch (Throwable $e) {
            report($e);

            return TranscribeKioskQuestionCommandResponse::failed('Couldn’t hear that. Try again in a moment.', 503);
        }

        return $text === ''
            ? TranscribeKioskQuestionCommandResponse::failed('Didn’t catch that.', 422)
            : new TranscribeKioskQuestionCommandResponse($text);
    }
}
