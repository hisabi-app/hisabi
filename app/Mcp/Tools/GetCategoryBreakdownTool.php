<?php

namespace App\Mcp\Tools;

use App\Models\Transaction;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Laravel\Mcp\Server\Tool;

class GetCategoryBreakdownTool extends Tool
{
    /**
     * The tool's name.
     */
    protected string $name = 'get_category_breakdown';

    /**
     * The tool's description.
     */
    protected string $description = 'Get detailed breakdown of transactions by category, including amount spent/earned per category, transaction count, and percentage of total.';

    /**
     * The tool's input schema.
     */
    protected function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'type' => [
                    'type' => 'string',
                    'enum' => ['EXPENSES', 'INCOME', 'SAVINGS', 'INVESTMENT'],
                    'description' => 'Type of categories to break down',
                    'default' => 'EXPENSES',
                ],
                'period' => [
                    'type' => 'string',
                    'enum' => ['current-month', 'last-month', 'last-3-months', 'current-year'],
                    'description' => 'Time period for the breakdown',
                    'default' => 'current-month',
                ],
                'include_brands' => [
                    'type' => 'boolean',
                    'description' => 'Include brand-level breakdown within each category',
                    'default' => false,
                ],
            ],
        ];
    }

    /**
     * Execute the tool.
     */
    public function __invoke(array $arguments): ToolResponse
    {
        $type = $arguments['type'] ?? 'EXPENSES';
        $period = $arguments['period'] ?? 'current-month';
        $includeBrands = $arguments['include_brands'] ?? false;

        $dateRange = $this->getDateRange($period);

        $query = Transaction::select(
                'categories.id as category_id',
                'categories.name as category_name',
                'categories.color as category_color',
                DB::raw('SUM(transactions.amount) as total_amount'),
                DB::raw('COUNT(transactions.id) as transaction_count'),
                DB::raw('AVG(transactions.amount) as avg_amount')
            )
            ->join('brands', 'transactions.brand_id', '=', 'brands.id')
            ->join('categories', 'brands.category_id', '=', 'categories.id')
            ->where('categories.type', $type)
            ->whereBetween('transactions.created_at', $dateRange)
            ->groupBy('categories.id', 'categories.name', 'categories.color')
            ->orderByDesc('total_amount')
            ->get();

        $totalAmount = $query->sum('total_amount');
        $currency = config('hisabi.currency');

        $categories = $query->map(function($category) use ($totalAmount, $currency, $includeBrands, $type, $dateRange) {
            $percentage = $totalAmount > 0 ? ($category->total_amount / $totalAmount) * 100 : 0;

            $result = [
                'id' => $category->category_id,
                'name' => $category->category_name,
                'color' => $category->category_color,
                'total_amount' => round($category->total_amount, 2),
                'transaction_count' => $category->transaction_count,
                'average_transaction' => round($category->avg_amount, 2),
                'percentage_of_total' => round($percentage, 2),
                'currency' => $currency,
            ];

            if ($includeBrands) {
                $result['brands'] = $this->getBrandBreakdown($category->category_id, $dateRange);
            }

            return $result;
        })->values()->toArray();

        $result = [
            'type' => $type,
            'period' => $period,
            'date_range' => [
                'start' => $dateRange[0]->format('Y-m-d'),
                'end' => $dateRange[1]->format('Y-m-d'),
            ],
            'total_amount' => round($totalAmount, 2),
            'currency' => $currency,
            'categories_count' => count($categories),
            'categories' => $categories,
        ];

        return ToolResponse::content([
            [
                'type' => 'text',
                'text' => json_encode($result, JSON_PRETTY_PRINT),
            ],
        ]);
    }

    /**
     * Get brand breakdown for a category
     */
    protected function getBrandBreakdown($categoryId, $dateRange): array
    {
        $brands = Transaction::select(
                'brands.id',
                'brands.name',
                DB::raw('SUM(transactions.amount) as total_amount'),
                DB::raw('COUNT(transactions.id) as transaction_count')
            )
            ->join('brands', 'transactions.brand_id', '=', 'brands.id')
            ->where('brands.category_id', $categoryId)
            ->whereBetween('transactions.created_at', $dateRange)
            ->groupBy('brands.id', 'brands.name')
            ->orderByDesc('total_amount')
            ->get();

        return $brands->map(function($brand) {
            return [
                'id' => $brand->id,
                'name' => $brand->name,
                'total_amount' => round($brand->total_amount, 2),
                'transaction_count' => $brand->transaction_count,
            ];
        })->toArray();
    }

    /**
     * Get date range based on period
     */
    protected function getDateRange(string $period): array
    {
        return match($period) {
            'current-month' => [now()->startOfMonth(), now()],
            'last-month' => [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()],
            'last-3-months' => [now()->subMonths(3)->startOfMonth(), now()],
            'current-year' => [now()->startOfYear(), now()],
            default => [now()->startOfMonth(), now()],
        };
    }
}
