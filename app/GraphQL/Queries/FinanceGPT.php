<?php

namespace App\GraphQL\Queries;

use App\Models\Transaction;
use OpenAI\Laravel\Facades\OpenAI;

class FinanceGPT
{
    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     */
    public function __invoke($_, array $args)
    {
        $transactions = Transaction::with(['brand', 'brand.category'])->where('created_at', '>=', now()->subMonths(4))
            ->get()->map(function ($transaction) {
            return [
                'amount' => config('finance.currency') . " " . $transaction->amount,
                'brand' => $transaction->brand->name,
                'category' => $transaction->brand->category->name,
                'type' => $transaction->brand->category->type,
                'date' => $transaction->created_at,
            ];
        })->toJson();

        $result = OpenAI::chat()->create([
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'system', 'content' => 'You are a helpful personal finance assistant called FinanceGPT developed by Saleem Hadad. The user might ask you questions about their finance and the transactions history of the user will be shared with you in the next message. You only reply in the finance context and based on the user data and history, you can suggest advices on savings or investment based on the user transaction data if requested but remind the user to seek for professional consultant. IMPORTANT: Do NOT answer anything other than finance.'],
                ['role' => 'system', 'content' => 'The finance transaction history of the user for the last 4 months: ' . $transactions],
                ...$args['messages'],
            ],
        ]);

        return [
            'role' => 'assistant',
            'content' => $result['choices'][0]['message']['content']
        ];
    }
}
