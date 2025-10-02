<?php

namespace App\Services\AI;

use Prism\Prism\Prism;
use Prism\Prism\ValueObjects\Messages\UserMessage;
use Prism\Prism\ValueObjects\Messages\AssistantMessage;
use Prism\Prism\ValueObjects\Messages\SystemMessage;
use Prism\Prism\Enums\Provider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class HisabiAIService
{
    protected array $conversationHistory = [];
    
    /**
     * Chat with the AI about finances
     */
    public function chat(array $messages): array
    {
        $user = Auth::user();
        
        // Prepare system message with financial context
        $systemPrompt = $this->buildSystemPrompt($user);
        
        // Convert messages to Prism format
        $prismMessages = $this->formatMessages($messages);
        
        try {
            // Use text generation with structured instructions
            $response = Prism::text()
                ->using(Provider::Anthropic, 'claude-3-7-sonnet-latest')
                ->withSystemPrompt($systemPrompt)
                ->withMessages($prismMessages)
                ->generate();
            
            // For now, return simple text response
            // In a production app, you could parse JSON from the response
            // or use OpenAI's function calling for structured data
            return [
                'role' => 'assistant',
                'content' => $response->text,
                'charts' => [],
                'components' => [],
                'suggestions' => $this->generateSuggestions()
            ];
            
        } catch (\Exception $e) {
            Log::error('Hisabi AI Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'role' => 'assistant',
                'content' => 'I apologize, but I encountered an error processing your request. Please try again.',
                'charts' => [],
                'components' => [],
                'suggestions' => $this->generateSuggestions()
            ];
        }
    }
    
    /**
     * Generate default suggestions
     */
    protected function generateSuggestions(): array
    {
        return [
            'Show me my spending summary for this month',
            'What are my top expenses?',
            'How much can I save this month?'
        ];
    }
    
    /**
     * Stream chat responses (for future implementation)
     */
    public function streamChat(array $messages): \Generator
    {
        $user = Auth::user();
        $systemPrompt = $this->buildSystemPrompt($user);
        $prismMessages = $this->formatMessages($messages);
        
        $stream = Prism::text()
            ->using(Provider::OpenAI, config('hisabi.gpt.model', 'gpt-4o'))
            ->withMessages([
                new SystemMessage($systemPrompt),
                ...$prismMessages
            ])
            ->stream();
        
        foreach ($stream as $chunk) {
            yield $chunk->text;
        }
    }
    
    /**
     * Build system prompt with user's financial context
     */
    protected function buildSystemPrompt($user): string
    {
        $financialSummary = $this->getFinancialSummary($user);
        
        return <<<PROMPT
You are HisabiAI, a helpful personal finance assistant developed by Saleem Hadad.
Your role is to help users understand and manage their personal finances effectively.

**User's Financial Summary:**
{$financialSummary}

**Your Capabilities:**
1. Answer questions about the user's financial data
2. Provide spending insights and trends analysis
3. Offer budget recommendations and savings advice
4. Generate visual charts to illustrate financial data
5. Create custom financial widgets for important metrics

**Response Format:**
- Always provide clear, actionable insights
- When appropriate, include charts (line, bar, pie, area) to visualize data
- For important alerts or recommendations, use custom components
- Suggest 2-3 relevant follow-up questions

**Important Guidelines:**
- ONLY respond to finance and personal finance questions
- Base all advice on the user's actual transaction data
- For investment or complex financial advice, remind users to consult professional advisors
- Be encouraging and positive while being honest about financial situations
- Use the currency {$this->getCurrency()} in all financial discussions

**Chart Types Available:**
- "line": For trends over time (e.g., spending trends)
- "bar": For comparisons (e.g., category spending)
- "pie": For distributions (e.g., expense breakdown)
- "area": For cumulative data (e.g., savings growth)

**Component Types Available:**
- "budget_alert": Highlight budget warnings or successes
- "savings_card": Show savings opportunities
- "category_breakdown": Detailed category analysis
- "spending_summary": Quick overview card

Always structure your responses with appropriate charts and components to make data easy to understand.
PROMPT;
    }
    
    /**
     * Get user's financial summary
     */
    protected function getFinancialSummary($user): string
    {
        $analyzer = new FinancialAnalyzer();
        return $analyzer->generateSummary($user);
    }
    
    /**
     * Get currency symbol
     */
    protected function getCurrency(): string
    {
        return config('hisabi.currency', 'AED');
    }
    
    /**
     * Format messages for Prism
     */
    protected function formatMessages(array $messages): array
    {
        return array_map(function ($message) {
            $content = is_array($message) ? $message['content'] : $message->content;
            $role = is_array($message) ? $message['role'] : $message->role;
            
            return $role === 'user' 
                ? new UserMessage($content)
                : new AssistantMessage($content);
        }, $messages);
    }
}

