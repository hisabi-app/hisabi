<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Domains\Metrics\Metrics\TotalIncomeMetric;
use App\Domains\Metrics\Metrics\TotalExpensesMetric;
use App\Domains\Metrics\Metrics\TotalSavingsMetric;
use App\Domains\Metrics\Metrics\TotalInvestmentMetric;
use App\Domains\Metrics\Metrics\TotalCashMetric;
use App\Domains\Metrics\Metrics\NetWorthMetric;
use App\Domains\Metrics\Metrics\NetWorthTrendMetric;
use App\Domains\Metrics\Metrics\TotalIncomeTrendMetric;
use App\Domains\Metrics\Metrics\TotalExpensesTrendMetric;
use App\Domains\Metrics\Metrics\CategoryTrendMetric;
use App\Domains\Metrics\Metrics\CategoryDailyTrendMetric;
use App\Domains\Metrics\Metrics\BrandTrendMetric;
use App\Domains\Metrics\Metrics\BrandChangeRateMetric;
use App\Domains\Metrics\Metrics\ExpensesByCategoryMetric;
use App\Domains\Metrics\Metrics\IncomeByCategoryMetric;
use App\Domains\Metrics\Metrics\SpendingByBrandMetric;
use App\Domains\Metrics\Metrics\TransactionsCountMetric;
use App\Domains\Metrics\Metrics\TransactionsByCategoryMetric;
use App\Domains\Metrics\Metrics\TransactionsByBrandMetric;
use App\Domains\Metrics\Metrics\HighestTransactionMetric;
use App\Domains\Metrics\Metrics\LowestTransactionMetric;
use App\Domains\Metrics\Metrics\AverageTransactionMetric;
use App\Domains\Metrics\Metrics\TransactionsStdDevMetric;
use App\Domains\Metrics\Metrics\BrandStatsMetric;
use App\Domains\Metrics\Metrics\CategoryStatsMetric;
use App\Domains\Metrics\Metrics\CirclePackMetric;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MetricsController extends Controller
{
    public function totalIncome(Request $request): JsonResponse
    {
        $metric = new TotalIncomeMetric($request->query('range'));
        return response()->json(['data' => $metric->calculate()]);
    }

    public function totalExpenses(Request $request): JsonResponse
    {
        $metric = new TotalExpensesMetric($request->query('range'));
        return response()->json(['data' => $metric->calculate()]);
    }

    public function totalSavings(): JsonResponse
    {
        $metric = new TotalSavingsMetric();
        return response()->json(['data' => $metric->calculate()]);
    }

    public function totalInvestment(): JsonResponse
    {
        $metric = new TotalInvestmentMetric();
        return response()->json(['data' => $metric->calculate()]);
    }

    public function totalCash(): JsonResponse
    {
        $metric = new TotalCashMetric();
        return response()->json(['data' => $metric->calculate()]);
    }

    public function netWorth(): JsonResponse
    {
        $metric = new NetWorthMetric();
        return response()->json(['data' => $metric->calculate()]);
    }

    public function netWorthTrend(Request $request): JsonResponse
    {
        $metric = new NetWorthTrendMetric($request->query('range'));
        return response()->json(['data' => $metric->calculate()]);
    }

    public function totalIncomeTrend(Request $request): JsonResponse
    {
        $metric = new TotalIncomeTrendMetric($request->query('range'));
        return response()->json(['data' => $metric->calculate()]);
    }

    public function totalExpensesTrend(Request $request): JsonResponse
    {
        $metric = new TotalExpensesTrendMetric($request->query('range'));
        return response()->json(['data' => $metric->calculate()]);
    }

    public function categoryTrend(Request $request): JsonResponse
    {
        $metric = new CategoryTrendMetric(
            $request->query('range'),
            (int) $request->query('id')
        );
        return response()->json(['data' => $metric->calculate()]);
    }

    public function categoryDailyTrend(Request $request): JsonResponse
    {
        $metric = new CategoryDailyTrendMetric(
            $request->query('range'),
            (int) $request->query('id')
        );
        return response()->json(['data' => $metric->calculate()]);
    }

    public function brandTrend(Request $request): JsonResponse
    {
        $metric = new BrandTrendMetric(
            $request->query('range'),
            (int) $request->query('id')
        );
        return response()->json(['data' => $metric->calculate()]);
    }

    public function brandChangeRate(Request $request): JsonResponse
    {
        $metric = new BrandChangeRateMetric(
            $request->query('range'),
            (int) $request->query('id')
        );
        return response()->json(['data' => $metric->calculate()]);
    }

    public function expensesByCategory(Request $request): JsonResponse
    {
        $metric = new ExpensesByCategoryMetric($request->query('range'));
        return response()->json(['data' => $metric->calculate()]);
    }

    public function incomeByCategory(Request $request): JsonResponse
    {
        $metric = new IncomeByCategoryMetric($request->query('range'));
        return response()->json(['data' => $metric->calculate()]);
    }

    public function spendingByBrand(Request $request): JsonResponse
    {
        $metric = new SpendingByBrandMetric(
            $request->query('range'),
            (int) $request->query('category_id')
        );
        return response()->json(['data' => $metric->calculate()]);
    }

    public function transactionsCount(Request $request): JsonResponse
    {
        $metric = new TransactionsCountMetric($request->query('range'));
        return response()->json(['data' => $metric->calculate()]);
    }

    public function transactionsByCategory(Request $request): JsonResponse
    {
        $metric = new TransactionsByCategoryMetric($request->query('range'));
        return response()->json(['data' => $metric->calculate()]);
    }

    public function transactionsByBrand(Request $request): JsonResponse
    {
        $metric = new TransactionsByBrandMetric(
            $request->query('range'),
            (int) $request->query('id')
        );
        return response()->json(['data' => $metric->calculate()]);
    }

    public function highestTransaction(Request $request): JsonResponse
    {
        $metric = new HighestTransactionMetric($request->query('range'));
        return response()->json(['data' => $metric->calculate()]);
    }

    public function lowestTransaction(Request $request): JsonResponse
    {
        $metric = new LowestTransactionMetric($request->query('range'));
        return response()->json(['data' => $metric->calculate()]);
    }

    public function averageTransaction(Request $request): JsonResponse
    {
        $metric = new AverageTransactionMetric($request->query('range'));
        return response()->json(['data' => $metric->calculate()]);
    }

    public function transactionsStdDev(Request $request): JsonResponse
    {
        $metric = new TransactionsStdDevMetric(
            $request->query('range'),
            (int) $request->query('id')
        );
        return response()->json(['data' => $metric->calculate()]);
    }

    public function brandStats(Request $request): JsonResponse
    {
        $metric = new BrandStatsMetric($request->query('range'));
        return response()->json(['data' => $metric->calculate()]);
    }

    public function categoryStats(Request $request): JsonResponse
    {
        $metric = new CategoryStatsMetric($request->query('range'));
        return response()->json(['data' => $metric->calculate()]);
    }

    public function circlePack(Request $request): JsonResponse
    {
        $metric = new CirclePackMetric($request->query('range'));
        return response()->json(['data' => $metric->calculate()]);
    }
}
