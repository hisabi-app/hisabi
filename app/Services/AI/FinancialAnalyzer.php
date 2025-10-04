<?php

namespace App\Services\AI;

use App\Domains\Transaction\Models\Transaction;
use App\Models\Category;
use Illuminate\Support\Facades\DB;

class FinancialAnalyzer
{
    /**
     * Generate a comprehensive financial summary for the user
     */
    public function generateSummary($user): string
    {
        $timeRange = now()->subMonths(3);
        
        // Get basic metrics
        $totalIncome = Transaction::income()
            ->where('created_at', '>=', $timeRange)
            ->sum('amount');
            
        $totalExpenses = Transaction::expenses()
            ->where('created_at', '>=', $timeRange)
            ->sum('amount');
            
        $totalSavings = Transaction::savings()
            ->where('created_at', '>=', $timeRange)
            ->sum('amount');
            
        $totalInvestment = Transaction::investment()
            ->where('created_at', '>=', $timeRange)
            ->sum('amount');
        
        // Get category breakdown
        $expensesByCategory = $this->getExpensesByCategory($timeRange);
        $incomeByCategory = $this->getIncomeByCategory($timeRange);
        
        // Get monthly trends
        $monthlyTrends = $this->getMonthlyTrends($timeRange);
        
        // Get top spending brands
        $topBrands = $this->getTopBrands($timeRange, 5);
        
        $currency = config('hisabi.currency', 'AED');
        
        // Build summary text
        $summary = <<<SUMMARY
**Financial Overview (Last 3 Months):**
- Total Income: {$currency} {$this->formatNumber($totalIncome)}
- Total Expenses: {$currency} {$this->formatNumber($totalExpenses)}
- Total Savings: {$currency} {$this->formatNumber($totalSavings)}
- Total Investment: {$currency} {$this->formatNumber($totalInvestment)}
- Net Cash Available: {$currency} {$this->formatNumber($totalIncome - ($totalExpenses + $totalSavings + $totalInvestment))}

**Expenses by Category:**
{$expensesByCategory}

**Income by Category:**
{$incomeByCategory}

**Top 5 Spending Brands:**
{$topBrands}

**Monthly Trends:**
{$monthlyTrends}

This data represents the user's actual financial transactions and should be used to provide personalized insights.
SUMMARY;
        
        return $summary;
    }
    
    /**
     * Get expenses grouped by category
     */
    protected function getExpensesByCategory($sinceDate): string
    {
        $expenses = Transaction::select('categories.name', DB::raw('SUM(transactions.amount) as total'))
            ->join('brands', 'transactions.brand_id', '=', 'brands.id')
            ->join('categories', 'brands.category_id', '=', 'categories.id')
            ->where('categories.type', Category::EXPENSES)
            ->where('transactions.created_at', '>=', $sinceDate)
            ->groupBy('categories.name')
            ->orderByDesc('total')
            ->get();
        
        if ($expenses->isEmpty()) {
            return "No expense data available.";
        }
        
        $currency = config('hisabi.currency', 'AED');
        return $expenses->map(fn($exp) => "  - {$exp->name}: {$currency} {$this->formatNumber($exp->total)}")
            ->join("\n");
    }
    
    /**
     * Get income grouped by category
     */
    protected function getIncomeByCategory($sinceDate): string
    {
        $income = Transaction::select('categories.name', DB::raw('SUM(transactions.amount) as total'))
            ->join('brands', 'transactions.brand_id', '=', 'brands.id')
            ->join('categories', 'brands.category_id', '=', 'categories.id')
            ->where('categories.type', Category::INCOME)
            ->where('transactions.created_at', '>=', $sinceDate)
            ->groupBy('categories.name')
            ->orderByDesc('total')
            ->get();
        
        if ($income->isEmpty()) {
            return "No income data available.";
        }
        
        $currency = config('hisabi.currency', 'AED');
        return $income->map(fn($inc) => "  - {$inc->name}: {$currency} {$this->formatNumber($inc->total)}")
            ->join("\n");
    }
    
    /**
     * Get monthly spending trends
     */
    protected function getMonthlyTrends($sinceDate): string
    {
        $trends = Transaction::select(
                DB::raw('DATE_FORMAT(transactions.created_at, "%Y-%m") as month'),
                DB::raw('SUM(CASE WHEN categories.type = "EXPENSES" THEN transactions.amount ELSE 0 END) as expenses'),
                DB::raw('SUM(CASE WHEN categories.type = "INCOME" THEN transactions.amount ELSE 0 END) as income')
            )
            ->join('brands', 'transactions.brand_id', '=', 'brands.id')
            ->join('categories', 'brands.category_id', '=', 'categories.id')
            ->where('transactions.created_at', '>=', $sinceDate)
            ->groupBy('month')
            ->orderBy('month')
            ->get();
        
        if ($trends->isEmpty()) {
            return "No trend data available.";
        }
        
        $currency = config('hisabi.currency', 'AED');
        return $trends->map(function($trend) use ($currency) {
            return "  - {$trend->month}: Income {$currency} {$this->formatNumber($trend->income)}, Expenses {$currency} {$this->formatNumber($trend->expenses)}";
        })->join("\n");
    }
    
    /**
     * Get top spending brands
     */
    protected function getTopBrands($sinceDate, int $limit = 5): string
    {
        $brands = Transaction::select('brands.name', DB::raw('SUM(transactions.amount) as total'))
            ->join('brands', 'transactions.brand_id', '=', 'brands.id')
            ->join('categories', 'brands.category_id', '=', 'categories.id')
            ->where('categories.type', Category::EXPENSES)
            ->where('transactions.created_at', '>=', $sinceDate)
            ->groupBy('brands.name')
            ->orderByDesc('total')
            ->limit($limit)
            ->get();
        
        if ($brands->isEmpty()) {
            return "No brand data available.";
        }
        
        $currency = config('hisabi.currency', 'AED');
        return $brands->map(fn($brand, $index) => 
            "  " . ($index + 1) . ". {$brand->name}: {$currency} {$this->formatNumber($brand->total)}"
        )->join("\n");
    }
    
    /**
     * Format number with thousands separator
     */
    protected function formatNumber($number): string
    {
        return number_format($number, 2);
    }
}

