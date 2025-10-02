<?php

namespace App\Mcp\Tools;

use App\Models\Brand;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Laravel\Mcp\Tool;
use Laravel\Mcp\ToolResponse;

class SearchBrandsTool extends Tool
{
    /**
     * The tool's name.
     */
    protected string $name = 'search_brands';

    /**
     * The tool's description.
     */
    protected string $description = 'Search for brands by name and get spending statistics for each brand including total spent, transaction count, and average transaction amount.';

    /**
     * The tool's input schema.
     */
    protected function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'query' => [
                    'type' => 'string',
                    'description' => 'Search query for brand name',
                ],
                'category_id' => [
                    'type' => 'integer',
                    'description' => 'Optional: filter by category ID',
                ],
                'period' => [
                    'type' => 'string',
                    'enum' => ['current-month', 'last-3-months', 'last-6-months', 'current-year', 'all-time'],
                    'description' => 'Time period for statistics',
                    'default' => 'last-3-months',
                ],
                'limit' => [
                    'type' => 'integer',
                    'description' => 'Maximum number of brands to return',
                    'default' => 20,
                ],
            ],
            'required' => ['query'],
        ];
    }

    /**
     * Execute the tool.
     */
    public function __invoke(array $arguments): ToolResponse
    {
        $query = $arguments['query'];
        $categoryId = $arguments['category_id'] ?? null;
        $period = $arguments['period'] ?? 'last-3-months';
        $limit = min($arguments['limit'] ?? 20, 50);
        
        $dateRange = $this->getDateRange($period);
        
        $brandsQuery = Brand::with('category')
            ->where('name', 'LIKE', "%{$query}%");
        
        if ($categoryId) {
            $brandsQuery->where('category_id', $categoryId);
        }
        
        $brands = $brandsQuery->limit($limit)->get();
        
        if ($brands->isEmpty()) {
            return ToolResponse::content([
                [
                    'type' => 'text',
                    'text' => json_encode([
                        'message' => 'No brands found matching your search.',
                        'query' => $query,
                    ], JSON_PRETTY_PRINT),
                ],
            ]);
        }
        
        $currency = config('hisabi.currency');
        
        $brandsWithStats = $brands->map(function($brand) use ($dateRange, $currency, $period) {
            $statsQuery = Transaction::where('brand_id', $brand->id);
            
            if ($dateRange) {
                $statsQuery->whereBetween('created_at', $dateRange);
            }
            
            $stats = $statsQuery->select(
                DB::raw('SUM(amount) as total_amount'),
                DB::raw('COUNT(id) as transaction_count'),
                DB::raw('AVG(amount) as avg_amount'),
                DB::raw('MAX(amount) as max_amount'),
                DB::raw('MIN(amount) as min_amount')
            )->first();
            
            // Get last transaction
            $lastTransaction = Transaction::where('brand_id', $brand->id)
                ->orderBy('created_at', 'desc')
                ->first();
            
            return [
                'id' => $brand->id,
                'name' => $brand->name,
                'category' => [
                    'id' => $brand->category->id,
                    'name' => $brand->category->name,
                    'type' => $brand->category->type,
                    'color' => $brand->category->color,
                ],
                'statistics' => [
                    'period' => $period,
                    'total_spent' => round($stats->total_amount ?? 0, 2),
                    'transaction_count' => $stats->transaction_count ?? 0,
                    'average_transaction' => round($stats->avg_amount ?? 0, 2),
                    'highest_transaction' => round($stats->max_amount ?? 0, 2),
                    'lowest_transaction' => round($stats->min_amount ?? 0, 2),
                    'last_transaction_date' => $lastTransaction?->created_at->format('Y-m-d'),
                    'currency' => $currency,
                ],
            ];
        })->sortByDesc('statistics.total_spent')->values()->toArray();
        
        $result = [
            'search_query' => $query,
            'period' => $period,
            'brands_found' => count($brandsWithStats),
            'currency' => $currency,
            'brands' => $brandsWithStats,
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
            'last-3-months' => [now()->subMonths(3)->startOfMonth(), now()],
            'last-6-months' => [now()->subMonths(6)->startOfMonth(), now()],
            'current-year' => [now()->startOfYear(), now()],
            'all-time' => null,
            default => [now()->subMonths(3)->startOfMonth(), now()],
        };
    }
}
