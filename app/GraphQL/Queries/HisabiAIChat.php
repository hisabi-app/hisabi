<?php

namespace App\GraphQL\Queries;

use App\Services\AI\HisabiAIService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class HisabiAIChat
{
    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     */
    public function __invoke($_, array $args): array
    {
        try {
            // Check if user is demo user and return dummy response
            if ($this->isDemoUser()) {
                return $this->getDummyResponse($args['messages']);
            }

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

    /**
     * Check if the authenticated user is a demo user
     */
    private function isDemoUser(): bool
    {
        $user = Auth::user();
        return $user && $user->email === config('hisabi.demo.email');
    }

    /**
     * Get dummy AI response for demo users
     */
    private function getDummyResponse(array $messages): array
    {
        $lastMessage = end($messages);
        $userMessage = is_array($lastMessage) && isset($lastMessage['content'])
            ? strtolower($lastMessage['content'])
            : '';

        // Generate contextual dummy responses based on keywords
        if (str_contains($userMessage, 'spending') || str_contains($userMessage, 'expense')) {
            $content = "Based on your transaction history, your spending has been well-balanced across various categories. Your top spending categories are Housing (AED 5,500/month), Groceries (AED 1,500/month), and Dining & Restaurants (AED 1,200/month). You're maintaining a healthy financial profile with consistent savings and investments.";
        } elseif (str_contains($userMessage, 'save') || str_contains($userMessage, 'saving')) {
            $content = "Great question about savings! You're currently saving approximately AED 2,700 per month across different savings goals: Emergency Fund (AED 800), Vacation Fund (AED 400), and periodic contributions to your Home Down Payment and Car Fund. This represents about 10% of your monthly income, which is a solid start. Consider gradually increasing this to 15-20% for optimal financial health.";
        } elseif (str_contains($userMessage, 'invest') || str_contains($userMessage, 'investment')) {
            $content = "Your investment portfolio is diversified across multiple asset classes including DFM stocks, NASDAQ stocks, cryptocurrency, mutual funds, gold, and real estate investments. You're making regular contributions which is excellent for long-term wealth building. Consider reviewing your portfolio quarterly to ensure it aligns with your risk tolerance and financial goals.";
        } elseif (str_contains($userMessage, 'income')) {
            $content = "Your primary income source is your monthly salary of AED 27,000, with occasional bonuses and freelance income. This provides a stable financial foundation. Your income-to-expense ratio is healthy, allowing for both savings and discretionary spending.";
        } else {
            $content = "I'm here to help you understand your finances better! I can analyze your spending patterns, track your savings goals, review your investments, and provide insights on your overall financial health. This is a demo account with sample data to showcase Hisabi's AI capabilities.";
        }

        return [
            'role' => 'assistant',
            'content' => $content,
            'charts' => [],
            'components' => [],
            'suggestions' => [
                'Show me my spending breakdown',
                'How much am I saving monthly?',
                'What are my investment trends?',
                'Analyze my income sources'
            ]
        ];
    }
}

