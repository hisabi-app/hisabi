<?php

namespace App\GraphQL\Queries;

use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use OpenAI\Laravel\Facades\OpenAI;

class FinanceGPT
{
    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     */
    public function __invoke($_, array $args): array
    {
        // refactor this code to use service and separate layers, for now it's MVP.
        // also allow users to invoke queries (we can use the existing queries..)
        $transactionsSummary = json_encode($this->getTransactionSummary());

        $assistantResponse = $this->getGptResponse($transactionsSummary, $args['messages']);

        return [
            'role' => 'assistant',
            'content' => $assistantResponse
        ];
    }

    /**
     * @return array
     */
    private function getTransactionSummary(): array
    {
        $summary = $this->getTransactionsSummary();

        return $this->formatTransactionsSummary($summary);
    }

    /**
     * @param $summary
     * @return array
     */
    private function formatTransactionsSummary($summary): array
    {
        $result = [];

        foreach ($summary as $monthYear => $records) {
            $result[$monthYear] = [];
            foreach ($records as $record) {
                $category = $record->category_name;
                $brand = $record->brand_name;
                $totalAmount = $record->total_amount;

                if (!isset($result[$monthYear][$category])) {
                    $result[$monthYear][$category] = [];
                }

                $result[$monthYear][$category][$brand] = config('finance.currency') . ' ' . $totalAmount;
            }
        }

        return $result;
    }

    /**
     * @return mixed
     */
    private function getTransactionsSummary()
    {
        return Transaction::select(
            DB::raw('DATE_FORMAT(transactions.created_at, "%Y-%m") as month_year'),
            'categories.name as category_name',
            'brands.name as brand_name',
            DB::raw('SUM(transactions.amount) as total_amount'),
            DB::raw('count(transactions.id) as transactions_count'),
        )
            ->join('brands', 'transactions.brand_id', '=', 'brands.id')
            ->join('categories', 'brands.category_id', '=', 'categories.id')
            ->groupBy('month_year', 'category_name', 'brand_name')
            ->orderBy('month_year', 'asc')
            ->orderBy('category_name', 'asc')
            ->orderBy('brand_name', 'asc')
            ->whereBetween('transactions.created_at', [now()->subMonthsNoOverflow(3)->startOfMonth()->format("Y-m-d"), now()])
            ->get()
            ->groupBy('month_year');
    }

    /**
     * @param $transactionsSummary
     * @param $messages
     * @return mixed
     */
    public function getGptResponse($transactionsSummary, $messages)
    {
        $result = OpenAI::chat()->create([
            'model' => config('finance.gpt.model'),
            'messages' => [
                ['role' => 'system', 'content' => 'You are a helpful personal finance assistant called FinanceGPT developed by Saleem Hadad (Software Engineer). The user might ask you questions about their finance and the transactionsSummary history of the user will be shared with you in the next message. You only reply in the finance context and based on the user data and history, you can suggest advices on savings or investment based on the user transaction data if requested but remind the user to seek for professional consultant.'],
                ['role' => 'system', 'content' => 'The transactions json summary of the user for the last 3 months: ' . $transactionsSummary],
                ['role' => 'system', 'content' => 'IMPORTANT: Do NOT answer anything other than finance and personal finance.'],
                ...$messages,
            ],
        ]);

        return $result['choices'][0]['message']['content'];
    }
}
