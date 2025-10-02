<?php

namespace App\Mcp\Tools;

use App\Models\Budget;
use Laravel\Mcp\Tool;
use Laravel\Mcp\ToolResponse;

class GetBudgetStatusTool extends Tool
{
    /**
     * The tool's name.
     */
    protected string $name = 'get_budget_status';

    /**
     * The tool's description.
     */
    protected string $description = 'Get current status of all budgets including amount spent, remaining budget, days left, and savings status.';

    /**
     * The tool's input schema.
     */
    protected function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'budget_id' => [
                    'type' => 'integer',
                    'description' => 'Optional: get status for a specific budget ID',
                ],
            ],
        ];
    }

    /**
     * Execute the tool.
     */
    public function __invoke(array $arguments): ToolResponse
    {
        $budgetId = $arguments['budget_id'] ?? null;
        
        $query = Budget::with('categories');
        
        if ($budgetId) {
            $query->where('id', $budgetId);
        }
        
        $budgets = $query->get();
        
        if ($budgets->isEmpty()) {
            return ToolResponse::content([
                [
                    'type' => 'text',
                    'text' => json_encode(['message' => 'No budgets found'], JSON_PRETTY_PRINT),
                ],
            ]);
        }
        
        $currency = config('hisabi.currency');
        
        $result = [
            'budgets_count' => $budgets->count(),
            'currency' => $currency,
            'budgets' => $budgets->map(function($budget) use ($currency) {
                $spent = $budget->total_transactions_amount;
                $remaining = $budget->amount - $spent;
                $percentage = ($spent / $budget->amount) * 100;
                $remainingDays = $budget->remaining_days;
                $dailyBudget = $budget->total_margin_per_day;
                
                $status = 'on_track';
                if ($percentage >= 100) {
                    $status = 'exceeded';
                } elseif ($percentage >= 80) {
                    $status = 'warning';
                }
                
                return [
                    'id' => $budget->id,
                    'name' => $budget->name,
                    'type' => $budget->is_saving ? 'savings' : 'spending',
                    'budget_amount' => round($budget->amount, 2),
                    'spent_amount' => round($spent, 2),
                    'remaining_amount' => round($remaining, 2),
                    'percentage_used' => round($percentage, 2),
                    'status' => $status,
                    'period' => [
                        'start_date' => $budget->start_at_date,
                        'end_date' => $budget->end_at_date,
                        'remaining_days' => $remainingDays,
                    ],
                    'daily_budget' => round($dailyBudget, 2),
                    'categories' => $budget->categories->map(function($category) {
                        return [
                            'id' => $category->id,
                            'name' => $category->name,
                            'type' => $category->type,
                        ];
                    })->toArray(),
                    'currency' => $currency,
                    'recommendations' => $this->generateRecommendations($budget, $percentage, $remainingDays),
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
    
    /**
     * Generate budget recommendations
     */
    protected function generateRecommendations($budget, $percentage, $remainingDays): array
    {
        $recommendations = [];
        
        if ($percentage >= 100) {
            $recommendations[] = [
                'type' => 'critical',
                'message' => 'Budget exceeded! Consider reviewing your spending in the associated categories.',
            ];
        } elseif ($percentage >= 80) {
            $recommendations[] = [
                'type' => 'warning',
                'message' => 'You\'ve used 80% or more of your budget. Monitor spending carefully.',
            ];
        } elseif ($percentage < 50 && $remainingDays < 7) {
            $recommendations[] = [
                'type' => 'positive',
                'message' => 'Great job! You\'re well within budget with only a few days remaining.',
            ];
        }
        
        if ($remainingDays > 0 && $percentage < 100) {
            $dailyBudget = $budget->total_margin_per_day;
            $recommendations[] = [
                'type' => 'info',
                'message' => "You have " . config('hisabi.currency') . " {$dailyBudget} per day remaining in your budget.",
            ];
        }
        
        return $recommendations;
    }
}
