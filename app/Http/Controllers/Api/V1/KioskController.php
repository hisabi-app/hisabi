<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Commands\Kiosk\AskKioskQuestionCommand\AskKioskQuestionCommand;
use App\Http\Commands\Kiosk\AskKioskQuestionCommand\AskKioskQuestionCommandHandler;
use App\Http\Commands\Kiosk\TranscribeKioskQuestionCommand\TranscribeKioskQuestionCommand;
use App\Http\Commands\Kiosk\TranscribeKioskQuestionCommand\TranscribeKioskQuestionCommandHandler;
use App\Http\Controllers\Controller;
use App\Http\Queries\Kiosk\GetKioskSpeechQuery\GetKioskSpeechQuery;
use App\Http\Queries\Kiosk\GetKioskSpeechQuery\GetKioskSpeechQueryHandler;
use App\Http\Queries\Kiosk\GetKioskSummaryQuery\GetKioskSummaryQuery;
use App\Http\Queries\Kiosk\GetKioskSummaryQuery\GetKioskSummaryQueryHandler;
use App\Http\Requests\Api\V1\AskKioskQuestionRequest;
use App\Http\Requests\Api\V1\TranscribeKioskQuestionRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class KioskController extends Controller
{
    public function __construct(
        private readonly GetKioskSummaryQueryHandler $getKioskSummaryQueryHandler,
        private readonly TranscribeKioskQuestionCommandHandler $transcribeKioskQuestionCommandHandler,
        private readonly AskKioskQuestionCommandHandler $askKioskQuestionCommandHandler,
        private readonly GetKioskSpeechQueryHandler $getKioskSpeechQueryHandler
    ) {}

    public function index(): JsonResponse
    {
        $query = new GetKioskSummaryQuery();

        return $this->getKioskSummaryQueryHandler->handle($query)->toResponse();
    }

    public function transcribe(TranscribeKioskQuestionRequest $request): JsonResponse
    {
        $command = new TranscribeKioskQuestionCommand($request->file('audio'));

        return $this->transcribeKioskQuestionCommandHandler->handle($command)->toResponse();
    }

    public function ask(AskKioskQuestionRequest $request): JsonResponse
    {
        $command = new AskKioskQuestionCommand(
            question: $request->validated('question'),
            conversationId: $request->validated('conversation_id')
        );

        return $this->askKioskQuestionCommandHandler->handle($command)->toResponse();
    }

    public function speech(string $id): JsonResponse|Response
    {
        $query = new GetKioskSpeechQuery($id);

        return $this->getKioskSpeechQueryHandler->handle($query)->toResponse();
    }
}
