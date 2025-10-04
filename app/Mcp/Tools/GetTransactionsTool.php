<?php

namespace App\Mcp\Tools;

use App\Domains\Transaction\Models\Transaction;
use Laravel\Mcp\Tool;
use Laravel\Mcp\ToolResponse;

class GetTransactionsTool extends Tool
{
    /**
     * The tool's name.
     */
    protected string $name = 'get_transactions';

    /**
     * The tool's description.
     */
    protected string $description = 'Fetch user transactions with optional filtering by date range, category, brand, or search query. Returns transaction details including amount, brand, category, date, and notes.';

    /**
     * The tool's input schema.
     */
    protected function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'limit' => [
                    'type' => 'integer',
                    'description' => 'Maximum number of transactions to return (default: 50, max: 100)',
                    'default' => 50,
                ],
                'start_date' => [
                    'type' => 'string',
                    'description' => 'Start date for filtering (YYYY-MM-DD format)',
                ],
                'end_date' => [
                    'type' => 'string',
                    'description' => 'End date for filtering (YYYY-MM-DD format)',
                ],
                'category_type' => [
                    'type' => 'string',
                    'enum' => ['INCOME', 'EXPENSES', 'SAVINGS', 'INVESTMENT'],
                    'description' => 'Filter by transaction category type',
                ],
                'search' => [
                    'type' => 'string',
                    'description' => 'Search query for amount, note, or brand name',
                ],
            ],
        ];
    }

    /**
     * Execute the tool.
     */
    public function __invoke(array $arguments): ToolResponse
    {
        $limit = min($arguments['limit'] ?? 50, 100);
        
        $query = Transaction::with(['brand.category'])
            ->orderBy('created_at', 'desc');
        
        // Apply date filters
        if (isset($arguments['start_date'])) {
            $query->where('created_at', '>=', $arguments['start_date']);
        }
        
        if (isset($arguments['end_date'])) {
            $query->where('created_at', '<=', $arguments['end_date']);
        }
        
        // Apply category type filter
        if (isset($arguments['category_type'])) {
            $type = $arguments['category_type'];
            $query->whereHas('brand.category', function($q) use ($type) {
                $q->where('type', $type);
            });
        }
        
        // Apply search filter
        if (isset($arguments['search'])) {
            $search = $arguments['search'];
            $query->where(function($q) use ($search) {
                $q->where('amount', 'LIKE', "%{$search}%")
                  ->orWhere('note', 'LIKE', "%{$search}%")
                  ->orWhereHas('brand', function($brandQuery) use ($search) {
                      $brandQuery->where('name', 'LIKE', "%{$search}%");
                  });
            });
        }
        
        $transactions = $query->limit($limit)->get();
        
        $result = [
            'count' => $transactions->count(),
            'transactions' => $transactions->map(function($transaction) {
                return [
                    'id' => $transaction->id,
                    'amount' => $transaction->amount,
                    'currency' => config('hisabi.currency'),
                    'date' => $transaction->created_at->format('Y-m-d'),
                    'note' => $transaction->note,
                    'brand' => [
                        'id' => $transaction->brand->id,
                        'name' => $transaction->brand->name,
                    ],
                    'category' => [
                        'id' => $transaction->brand->category->id,
                        'name' => $transaction->brand->category->name,
                        'type' => $transaction->brand->category->type,
                        'color' => $transaction->brand->category->color,
                    ],
                ];
            })->values()->toArray(),
        ];
        
        return ToolResponse::content([
            [
                'type' => 'text',
                'text' => json_encode($result, JSON_PRETTY_PRINT),
            ],
        ]);
    }
}
