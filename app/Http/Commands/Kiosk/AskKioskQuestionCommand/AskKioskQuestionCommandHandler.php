<?php

namespace App\Http\Commands\Kiosk\AskKioskQuestionCommand;

use App\Domains\Assistant\Services\AssistantService;
use Illuminate\Support\Facades\Cache;
use Throwable;

class AskKioskQuestionCommandHandler
{
    // Longer than an answer can take (four steps of at most 15 seconds)
    private const LOCK_SECONDS = 70;

    public function __construct(
        private readonly AssistantService $assistantService
    ) {}

    /**
     * Not wrapped in DB::transaction like other commands: it only reads, and a transaction
     * would hold the database for as long as Claude takes to answer.
     */
    public function handle(AskKioskQuestionCommand $command): AskKioskQuestionCommandResponse
    {
        if (! $this->assistantService->enabled()) {
            return AskKioskQuestionCommandResponse::failed('Claude isn’t set up on this Hisabi.', 503);
        }

        // One question at a time, so a double tap doesn't pay for two answers
        $lock = Cache::lock('kiosk-ask', self::LOCK_SECONDS);

        if (! $lock->get()) {
            return AskKioskQuestionCommandResponse::failed('Still answering the last question.', 429);
        }

        try {
            return new AskKioskQuestionCommandResponse(
                $this->assistantService->ask($command->question, $command->conversationId)
            );
        } catch (Throwable $e) {
            report($e);

            return AskKioskQuestionCommandResponse::failed('Couldn’t reach Claude. Try again in a moment.', 503);
        } finally {
            $lock->release();
        }
    }
}
