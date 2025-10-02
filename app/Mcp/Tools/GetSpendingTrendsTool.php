<?php

namespace App\Mcp\Tools;

use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Laravel\Mcp\Tool;
use Laravel\Mcp\ToolResponse;

class GetSpendingTrendsTool extends Tool
{
    /**
     * The tool's name.
     */
    protected string $name = 'get_spending_trends';

    /**
     * The tool's description.
     */
    protected string $description = 'Get time-series data showing spending and income trends over time, grouped by day, week, or month.';

    /**
     * The tool's input schema.
     */
    protected function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'group_by' => [
                    'type' => 'string',
                    'enum' => ['day', 'week', 'month'],
                    'description' => 'Time granularity for grouping',
                    'default' => 'month',
                ],
                'period' => [
                    'type' => 'string',
                    'enum' => ['last-30-days', 'last-3-months', 'last-6-months', 'last-12-months', 'current-year'],
                    'description' => 'Overall time period to analyze',
                    'default' => 'last-3-months',
                ],
                'category_id' => [
                    'type' => 'integer',
                    'description' => 'Optional: filter by specific category ID',
                ],
            ],
        ];
    }

    /**
     * Execute the tool.
     */
    public function __invoke(array $arguments): ToolResponse
    {
        $groupBy = $arguments['group_by'] ?? 'month';
        $period = $arguments['period'] ?? 'last-3-months';
        $categoryId = $arguments['category_id'] ?? null;
        
        $dateRange = $this->getDateRange($period);
        $dateFormat = $this->getDateFormat($groupBy);
        
        $query = Transaction::select(
                DB::raw("DATE_FORMAT(transactions.created_at, '{$dateFormat}') as period"),
                DB::raw('SUM(CASE WHEN categories.type = "EXPENSES" THEN transactions.amount ELSE 0 END) as expenses'),
                DB::raw('SUM(CASE WHEN categories.type = "INCOME" THEN transactions.amount ELSE 0 END) as income'),
                DB::raw('SUM(CASE WHEN categories.type = "SAVINGS" THEN transactions.amount ELSE 0 END) as savings'),
                DB::raw('SUM(CASE WHEN categories.type = "INVESTMENT" THEN transactions.amount ELSE 0 END) as investment'),
                DB::raw('COUNT(transactions.id) as transaction_count')
            )
            ->join('brands', 'transactions.brand_id', '=', 'brands.id')
            ->join('categories', 'brands.category_id', '=', 'categories.id')
            ->whereBetween('transactions.created_at', $dateRange);
        
        if ($categoryId) {
            $query->where('categories.id', $categoryId);
        }
        
        $trends = $query->groupBy('period')
            ->orderBy('period')
            ->get();
        
        $currency = config('hisabi.currency');
        
        $result = [
            'group_by' => $groupBy,
            'period' => $period,
            'date_range' => [
                'start' => $dateRange[0]->format('Y-m-d'),
                'end' => $dateRange[1]->format('Y-m-d'),
            ],
            'currency' => $currency,
            'data_points' => $trends->map(function($trend) use ($currency) {
                $netCashFlow = $trend->income - ($trend->expenses + $trend->savings + $trend->investment);
                
                return [
                    'period' => $trend->period,
                    'income' => round($trend->income, 2),
                    'expenses' => round($trend->expenses, 2),
                    'savings' => round($trend->savings, 2),
                    'investment' => round($trend->investment, 2),
                    'net_cash_flow' => round($netCashFlow, 2),
                    'transaction_count' => $trend->transaction_count,
                    'currency' => $currency,
                ];
            })->values()->toArray(),
            'summary' => [
                'total_periods' => $trends->count(),
                'average_expenses_per_period' => round($trends->avg('expenses'), 2),
                'average_income_per_period' => round($trends->avg('income'), 2),
                'highest_expense_period' => $trends->sortByDesc('expenses')->first()?->period,
                'highest_income_period' => $trends->sortByDesc('income')->first()?->period,
            ],
        ];
        
        return ToolResponse::content([
            [
                'type' => 'text',
                'text' => json_encode($result, JSON_PRETTY_PRINT),
            ],
        ]);
    }
    
    /**
     * Get date format for MySQL based on grouping
     */
    protected function getDateFormat(string $groupBy): string
    {
        return match($groupBy) {
            'day' => '%Y-%m-%d',
            'week' => '%Y-W%u',
            'month' => '%Y-%m',
            default => '%Y-%m',
        };
    }
    
    /**
     * Get date range based on period
     */
    protected function getDateRange(string $period): array
    {
        return match($period) {
            'last-30-days' => [now()->subDays(30), now()],
            'last-3-months' => [now()->subMonths(3)->startOfMonth(), now()],
            'last-6-months' => [now()->subMonths(6)->startOfMonth(), now()],
            'last-12-months' => [now()->subMonths(12)->startOfMonth(), now()],
            'current-year' => [now()->startOfYear(), now()],
            default => [now()->subMonths(3)->startOfMonth(), now()],
        };
    }
}
