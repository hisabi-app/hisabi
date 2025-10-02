<?php

namespace App\Mcp\Tools;

use App\Models\Transaction;
use App\Models\Category;
use Laravel\Mcp\Tool;
use Laravel\Mcp\ToolResponse;

class GetFinancialSummaryTool extends Tool
{
    /**
     * The tool's name.
     */
    protected string $name = 'get_financial_summary';

    /**
     * The tool's description.
     */
    protected string $description = 'Get a comprehensive financial summary including total income, expenses, savings, investments, net worth, and available cash for a specified time range.';

    /**
     * The tool's input schema.
     */
    protected function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'period' => [
                    'type' => 'string',
                    'enum' => ['current-month', 'last-month', 'last-3-months', 'last-6-months', 'current-year', 'all-time'],
                    'description' => 'Time period for the summary',
                    'default' => 'current-month',
                ],
            ],
        ];
    }

    /**
     * Execute the tool.
     */
    public function __invoke(array $arguments): ToolResponse
    {
        $period = $arguments['period'] ?? 'current-month';
        $dateRange = $this->getDateRange($period);
        
        $query = Transaction::query();
        
        if ($dateRange) {
            $query->whereBetween('created_at', $dateRange);
        }
        
        // Calculate totals
        $totalIncome = (clone $query)->income()->sum('amount');
        $totalExpenses = (clone $query)->expenses()->sum('amount');
        $totalSavings = (clone $query)->savings()->sum('amount');
        $totalInvestment = (clone $query)->investment()->sum('amount');
        
        // Calculate derived metrics
        $netWorth = $totalIncome - $totalExpenses;
        $totalCash = $totalIncome - ($totalExpenses + $totalSavings + $totalInvestment);
        $savingsRate = $totalIncome > 0 ? ($totalSavings / $totalIncome) * 100 : 0;
        
        $currency = config('hisabi.currency');
        
        $result = [
            'period' => $period,
            'date_range' => [
                'start' => $dateRange ? $dateRange[0]->format('Y-m-d') : null,
                'end' => $dateRange ? $dateRange[1]->format('Y-m-d') : now()->format('Y-m-d'),
            ],
            'currency' => $currency,
            'summary' => [
                'total_income' => round($totalIncome, 2),
                'total_expenses' => round($totalExpenses, 2),
                'total_savings' => round($totalSavings, 2),
                'total_investment' => round($totalInvestment, 2),
                'net_worth' => round($netWorth, 2),
                'available_cash' => round($totalCash, 2),
                'savings_rate_percentage' => round($savingsRate, 2),
            ],
            'insights' => [
                'is_profitable' => $netWorth > 0,
                'has_positive_cash_flow' => $totalCash > 0,
                'expense_to_income_ratio' => $totalIncome > 0 ? round(($totalExpenses / $totalIncome) * 100, 2) : 0,
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
     * Get date range based on period
     */
    protected function getDateRange(string $period): ?array
    {
        return match($period) {
            'current-month' => [now()->startOfMonth(), now()],
            'last-month' => [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()],
            'last-3-months' => [now()->subMonths(3)->startOfMonth(), now()],
            'last-6-months' => [now()->subMonths(6)->startOfMonth(), now()],
            'current-year' => [now()->startOfYear(), now()],
            'all-time' => null,
            default => [now()->startOfMonth(), now()],
        };
    }
}
