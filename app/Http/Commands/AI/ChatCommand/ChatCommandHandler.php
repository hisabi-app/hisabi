<?php

namespace App\Http\Commands\AI\ChatCommand;

use App\Services\AI\HisabiAIService;
use Illuminate\Support\Facades\Log;

class ChatCommandHandler
{
    public function __construct(
        private readonly HisabiAIService $aiService
    ) {}

    public function handle(ChatCommand $command): ChatCommandResponse
    {
        try {
            $response = $this->aiService->chat($command->messages);
            return new ChatCommandResponse($response);
        } catch (\Exception $e) {
            Log::error('Hisabi AI Chat Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return new ChatCommandResponse([
                'role' => 'assistant',
                'content' => 'I apologize, but I encountered an error processing your request. Please try again in a moment.',
                'charts' => [],
                'components' => [],
                'suggestions' => [
                    'Can you show me my spending summary?',
                    'What are my top expenses this month?'
                ]
            ]);
        }
    }
}
