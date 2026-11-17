<?php

namespace App\Http\Queries\Kiosk\GetKioskSpeechQuery;

use App\Domains\Assistant\Services\AssistantService;
use Throwable;

class GetKioskSpeechQueryHandler
{
    public function __construct(
        private readonly AssistantService $assistantService
    ) {}

    public function handle(GetKioskSpeechQuery $query): GetKioskSpeechQueryResponse
    {
        if (! $this->assistantService->hasVoice()) {
            return GetKioskSpeechQueryResponse::failed('Speaking isn’t set up on this Hisabi.', 503);
        }

        try {
            $audio = $this->assistantService->speech($query->speechId);
        } catch (Throwable $e) {
            report($e);

            return GetKioskSpeechQueryResponse::failed('Couldn’t read the answer out.', 503);
        }

        return $audio === null
            ? GetKioskSpeechQueryResponse::failed('That answer is gone.', 404)
            : new GetKioskSpeechQueryResponse($audio);
    }
}
