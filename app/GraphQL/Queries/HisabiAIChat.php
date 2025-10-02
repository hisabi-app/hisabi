<?php

namespace App\GraphQL\Queries;

use App\Services\AI\HisabiAIService;
use Illuminate\Support\Facades\Log;

class HisabiAIChat
{
    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     */
    public function __invoke($_, array $args): array
    {
        try {
            $aiService = new HisabiAIService();
            $response = $aiService->chat($args['messages']);
            
            return $response;
            
        } catch (\Exception $e) {
            Log::error('Hisabi AI Chat Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'role' => 'assistant',
                'content' => 'I apologize, but I encountered an error processing your request. Please try again in a moment.',
                'charts' => [],
                'components' => [],
                'suggestions' => [
                    'Can you show me my spending summary?',
                    'What are my top expenses this month?'
                ]
            ];
        }
    }
}

