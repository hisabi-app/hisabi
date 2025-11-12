# 🎯 Hisabi Dashboard Improvements - Implementation Plan

> **Purpose:** Track dashboard enhancement tasks in small, manageable slices that can be implemented independently by AI agents without losing context.

---

## 📊 Implementation Status Legend

- ❌ **Not Started** - Task not yet begun
- 🟡 **In Progress** - Currently being worked on
- ✅ **Completed** - Implementation finished and tested
- 🚫 **Blocked** - Waiting on dependencies or decisions

---

# PHASE 1: CRITICAL FEATURES 🔴

These are the most important features that will immediately improve user understanding of their finances.

---

## Task 1.1: Cash Flow Widget (This Month)

**Status:** ❌ Not Started

**Priority:** CRITICAL

**Estimated Complexity:** Low

**Description:**
Add a widget that shows the user's cash flow for the current month (Income - Expenses). This is THE most important metric for daily financial health.

**User Story:**
As a user, I want to see at a glance whether I'm earning more than I'm spending this month, so I can quickly understand if I'm financially on track.

**Acceptance Criteria:**
- [ ] Display current month income
- [ ] Display current month expenses
- [ ] Calculate and display net cash flow (income - expenses)
- [ ] Show visual indicator: green for positive, red for negative
- [ ] Show trend icon (up/down arrow)
- [ ] Include percentage comparison to last month
- [ ] Display prominently near top of dashboard

**Technical Implementation:**

### Backend:
1. Create new GraphQL query file: `/app/GraphQL/Queries/MonthlyCashFlow.php`
```php
<?php

namespace App\GraphQL\Queries;

use App\Domains\Transaction\Models\Transaction;
use App\Domain\Metrics\ValueMetric;
use App\Domain\Ranges\CurrentMonth;
use App\Domain\Ranges\LastMonth;

class MonthlyCashFlow extends ValueMetric
{
    public function ranges()
    {
        return [
            new CurrentMonth,
            new LastMonth,
        ];
    }

    public function __invoke($_, array $args)
    {
        $rangeData = app('findRangeByKey', ["key" => $args['range'] ?? 'current-month']);

        // Get income for period
        $income = Transaction::income()
            ->whereBetween('created_at', [$rangeData->start(), $rangeData->end()])
            ->sum('amount');

        // Get expenses for period
        $expenses = Transaction::expenses()
            ->whereBetween('created_at', [$rangeData->start(), $rangeData->end()])
            ->sum('amount');

        $cashFlow = $income - $expenses;

        // Get previous period for comparison if available
        $previous = null;
        if ($rangeData instanceof \App\Domain\Ranges\Contracts\HasPreviousRange) {
            $previousIncome = Transaction::income()
                ->whereBetween('created_at', [$rangeData->previousStart(), $rangeData->previousEnd()])
                ->sum('amount');

            $previousExpenses = Transaction::expenses()
                ->whereBetween('created_at', [$rangeData->previousStart(), $rangeData->previousEnd()])
                ->sum('amount');

            $previous = $previousIncome - $previousExpenses;
        }

        return [
            'value' => $cashFlow,
            'previous' => $previous,
            'income' => $income,
            'expenses' => $expenses,
        ];
    }
}
```

2. Add query to GraphQL schema: `/graphql/schema.graphql`
```graphql
type Query {
    monthlyCashFlow(range: String): Json @field(resolver: "App\\GraphQL\\Queries\\MonthlyCashFlow")
}
```

### Frontend:
3. Create new component: `/resources/js/components/Domain/CashFlowMetric.tsx`
```tsx
import React, { useEffect, useState } from 'react';
import { TrendingUpIcon, TrendingDownIcon } from '@heroicons/react/solid';
import { query } from '../../Api';
import { Card } from '@/components/ui/card';
import LoadingView from "../Global/LoadingView";
import { formatNumber, getAppCurrency } from '../../Utils';

export default function CashFlowMetric() {
    const [data, setData] = useState(null);
    const [selectedRange, setSelectedRange] = useState('current-month');

    const ranges = [
        { key: 'current-month', name: 'Current Month' },
        { key: 'last-month', name: 'Last Month' },
    ];

    useEffect(() => {
        const fetchData = async () => {
            setData(null);
            let { data: responseData } = await query('monthlyCashFlow', selectedRange);
            let parsedData = JSON.parse(responseData['monthlyCashFlow']);
            setData(parsedData);
        };

        fetchData();
    }, [selectedRange]);

    if (data == null) {
        return (
            <Card className="relative">
                <LoadingView />
            </Card>
        );
    }

    const isPositive = data.value >= 0;
    const growthPercentage = data.previous && data.previous !== 0
        ? Math.abs((((data.value - data.previous) / data.previous) * 100).toFixed(2))
        : 0;

    const isIncreasing = data.previous ? data.value > data.previous : false;

    return (
        <Card className='relative'>
            <div className="px-6 flex flex-col h-full gap-y-2">
                <div className="flex grow-0 justify-between items-center">
                    <h3 className="mr-3 text-base text-gray-600">Monthly Cash Flow</h3>
                    <select
                        className="ml-auto min-w-24 h-8 text-xs border-none appearance-none bg-gray-100 pl-2 pr-6 rounded active:outline-none active:shadow-outline focus:outline-none focus:shadow-outline"
                        name="range"
                        value={selectedRange}
                        onChange={(e) => setSelectedRange(e.target.value)}
                    >
                        {ranges.map(range => <option key={range.key} value={range.key}>{range.name}</option>)}
                    </select>
                </div>

                <p className={`flex grow-1 items-center text-3xl font-bold ${isPositive ? 'text-green-600' : 'text-red-600'}`}>
                    {isPositive ? '+' : '-'} {getAppCurrency()} {formatNumber(Math.abs(data.value))}
                </p>

                <div className="text-sm text-gray-500">
                    <p>Income: {getAppCurrency()} {formatNumber(data.income)}</p>
                    <p>Expenses: {getAppCurrency()} {formatNumber(data.expenses)}</p>
                </div>

                <div className="flex grow-0 items-center">
                    <div className={`px-3 py-1 rounded-full text-sm font-medium ${isPositive ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`}>
                        {isPositive ? '✓ Positive Cash Flow' : '⚠ Negative Cash Flow'}
                    </div>
                </div>

                {data.previous !== null && growthPercentage !== 0 && (
                    <div className="flex grow-0">
                        {isIncreasing && <TrendingUpIcon className={`mr-2 h-5 w-5 ${isPositive ? 'text-green-500' : 'text-red-500'}`} aria-hidden="true" />}
                        {!isIncreasing && <TrendingDownIcon className={`mr-2 h-5 w-5 ${isPositive ? 'text-red-500' : 'text-green-500'}`} aria-hidden="true" />}
                        <p className="text-gray-500 font-bold">
                            {growthPercentage}% {isIncreasing ? 'increase' : 'decrease'} vs last period
                        </p>
                    </div>
                )}
            </div>
        </Card>
    );
}
```

4. Add to Dashboard: `/resources/js/pages/Dashboard.tsx`
```tsx
// Add import at top
import CashFlowMetric from '@/components/Domain/CashFlowMetric';

// Add after Budgets section, before Net Worth (around line 50)
<CashFlowMetric />
```

**Files to Create/Modify:**
- CREATE: `/app/GraphQL/Queries/MonthlyCashFlow.php`
- MODIFY: `/graphql/schema.graphql`
- CREATE: `/resources/js/components/Domain/CashFlowMetric.tsx`
- MODIFY: `/resources/js/pages/Dashboard.tsx`

**Testing Checklist:**
- [ ] Widget displays on dashboard
- [ ] Shows correct current month income/expenses
- [ ] Calculates cash flow correctly (income - expenses)
- [ ] Green indicator for positive, red for negative
- [ ] Range selector works (current month / last month)
- [ ] Comparison to previous period shows correctly
- [ ] Responsive design works on mobile
- [ ] Loading state displays properly
- [ ] Handles zero/null values gracefully

**Dependencies:** None

---

## Task 1.2: Savings Rate Widget

**Status:** ❌ Not Started

**Priority:** CRITICAL

**Estimated Complexity:** Low-Medium

**Description:**
Display the user's savings rate as a percentage of income. This is one of the most important financial health indicators.

**User Story:**
As a user, I want to see what percentage of my income I'm saving, so I can compare against recommended rates (15-20%) and track my progress.

**Acceptance Criteria:**
- [ ] Calculate savings rate: (Savings / Income) × 100
- [ ] Display as percentage with clear label
- [ ] Show comparison to previous period
- [ ] Show target savings rate (20%) with progress indicator
- [ ] Show how much more needs to be saved to hit target
- [ ] Color code: Green if ≥20%, Yellow if 10-19%, Red if <10%
- [ ] Support date range selection

**Technical Implementation:**

### Backend:
1. Create new GraphQL query file: `/app/GraphQL/Queries/SavingsRate.php`
```php
<?php

namespace App\GraphQL\Queries;

use App\Domains\Transaction\Models\Transaction;
use App\Domain\Metrics\ValueMetric;
use App\Domain\Ranges\CurrentMonth;
use App\Domain\Ranges\LastMonth;
use App\Domain\Ranges\CurrentYear;
use App\Domain\Ranges\LastYear;
use App\Domain\Ranges\AllTime;

class SavingsRate extends ValueMetric
{
    public function ranges()
    {
        return [
            new CurrentMonth,
            new LastMonth,
            new CurrentYear,
            new LastYear,
            new AllTime,
        ];
    }

    public function __invoke($_, array $args)
    {
        $rangeData = app('findRangeByKey', ["key" => $args['range'] ?? 'current-month']);

        // Get income and savings for period
        $income = Transaction::income()
            ->whereBetween('created_at', [$rangeData->start(), $rangeData->end()])
            ->sum('amount');

        $savings = Transaction::savings()
            ->whereBetween('created_at', [$rangeData->start(), $rangeData->end()])
            ->sum('amount');

        // Calculate savings rate as percentage
        $savingsRate = $income > 0 ? ($savings / $income) * 100 : 0;

        // Get previous period for comparison if available
        $previous = null;
        if ($rangeData instanceof \App\Domain\Ranges\Contracts\HasPreviousRange) {
            $previousIncome = Transaction::income()
                ->whereBetween('created_at', [$rangeData->previousStart(), $rangeData->previousEnd()])
                ->sum('amount');

            $previousSavings = Transaction::savings()
                ->whereBetween('created_at', [$rangeData->previousStart(), $rangeData->previousEnd()])
                ->sum('amount');

            $previous = $previousIncome > 0 ? ($previousSavings / $previousIncome) * 100 : 0;
        }

        // Calculate how much more needs to be saved to hit 20% target
        $targetRate = 20;
        $targetSavings = ($targetRate / 100) * $income;
        $savingsGap = max(0, $targetSavings - $savings);

        return [
            'value' => round($savingsRate, 2),
            'previous' => $previous ? round($previous, 2) : null,
            'income' => $income,
            'savings' => $savings,
            'target_rate' => $targetRate,
            'savings_gap' => $savingsGap,
            'progress_to_target' => min(100, ($savingsRate / $targetRate) * 100),
        ];
    }
}
```

2. Add query to GraphQL schema: `/graphql/schema.graphql`
```graphql
type Query {
    savingsRate(range: String): Json @field(resolver: "App\\GraphQL\\Queries\\SavingsRate")
}
```

### Frontend:
3. Create new component: `/resources/js/components/Domain/SavingsRateMetric.tsx`
```tsx
import React, { useEffect, useState } from 'react';
import { TrendingUpIcon, TrendingDownIcon } from '@heroicons/react/solid';
import { query } from '../../Api';
import { Card } from '@/components/ui/card';
import LoadingView from "../Global/LoadingView";
import { formatNumber, getAppCurrency } from '../../Utils';

export default function SavingsRateMetric() {
    const [data, setData] = useState(null);
    const [selectedRange, setSelectedRange] = useState('current-month');

    const ranges = [
        { key: 'current-month', name: 'Current Month' },
        { key: 'last-month', name: 'Last Month' },
        { key: 'current-year', name: 'Current Year' },
        { key: 'last-year', name: 'Last Year' },
        { key: 'all-time', name: 'All Time' },
    ];

    useEffect(() => {
        const fetchData = async () => {
            setData(null);
            let { data: responseData } = await query('savingsRate', selectedRange);
            let parsedData = JSON.parse(responseData['savingsRate']);
            setData(parsedData);
        };

        fetchData();
    }, [selectedRange]);

    if (data == null) {
        return (
            <Card className="relative">
                <LoadingView />
            </Card>
        );
    }

    const getStatusColor = (rate) => {
        if (rate >= 20) return { bg: 'bg-green-100', text: 'text-green-800', indicator: 'text-green-500' };
        if (rate >= 10) return { bg: 'bg-yellow-100', text: 'text-yellow-800', indicator: 'text-yellow-500' };
        return { bg: 'bg-red-100', text: 'text-red-800', indicator: 'text-red-500' };
    };

    const colors = getStatusColor(data.value);
    const isIncreasing = data.previous ? data.value > data.previous : false;
    const changeAmount = data.previous ? Math.abs(data.value - data.previous).toFixed(2) : 0;

    return (
        <Card className='relative'>
            <div className="px-6 flex flex-col h-full gap-y-3">
                <div className="flex grow-0 justify-between items-center">
                    <h3 className="mr-3 text-base text-gray-600">Savings Rate</h3>
                    <select
                        className="ml-auto min-w-24 h-8 text-xs border-none appearance-none bg-gray-100 pl-2 pr-6 rounded active:outline-none active:shadow-outline focus:outline-none focus:shadow-outline"
                        name="range"
                        value={selectedRange}
                        onChange={(e) => setSelectedRange(e.target.value)}
                    >
                        {ranges.map(range => <option key={range.key} value={range.key}>{range.name}</option>)}
                    </select>
                </div>

                <div className="flex items-baseline gap-2">
                    <p className={`text-4xl font-bold ${colors.indicator}`}>
                        {data.value.toFixed(1)}%
                    </p>
                    <p className="text-sm text-gray-500">of income</p>
                </div>

                {/* Progress to Target */}
                <div>
                    <div className="flex justify-between text-xs text-gray-500 mb-1">
                        <span>Target: {data.target_rate}%</span>
                        <span>{data.progress_to_target.toFixed(0)}% of target</span>
                    </div>
                    <div className="w-full h-2 bg-gray-200 rounded-full overflow-hidden">
                        <div
                            className={`h-full ${colors.indicator === 'text-green-500' ? 'bg-green-500' : colors.indicator === 'text-yellow-500' ? 'bg-yellow-500' : 'bg-red-500'}`}
                            style={{ width: `${Math.min(100, data.progress_to_target)}%` }}
                        />
                    </div>
                </div>

                {/* Savings Gap */}
                {data.savings_gap > 0 && (
                    <div className={`px-3 py-2 rounded ${colors.bg} ${colors.text} text-sm`}>
                        Save {getAppCurrency()} {formatNumber(data.savings_gap)} more to hit {data.target_rate}% target
                    </div>
                )}

                {data.savings_gap === 0 && (
                    <div className="px-3 py-2 rounded bg-green-100 text-green-800 text-sm font-medium">
                        ✓ Target achieved!
                    </div>
                )}

                {/* Trend Comparison */}
                {data.previous !== null && changeAmount > 0 && (
                    <div className="flex grow-0 items-center text-sm">
                        {isIncreasing && <TrendingUpIcon className="mr-2 h-4 w-4 text-green-500" aria-hidden="true" />}
                        {!isIncreasing && <TrendingDownIcon className="mr-2 h-4 w-4 text-red-500" aria-hidden="true" />}
                        <p className="text-gray-500">
                            {changeAmount}% {isIncreasing ? 'increase' : 'decrease'} vs last period
                        </p>
                    </div>
                )}

                {/* Breakdown */}
                <div className="text-xs text-gray-400 pt-2 border-t">
                    <p>Savings: {getAppCurrency()} {formatNumber(data.savings)}</p>
                    <p>Income: {getAppCurrency()} {formatNumber(data.income)}</p>
                </div>
            </div>
        </Card>
    );
}
```

4. Add to Dashboard: `/resources/js/pages/Dashboard.tsx`
```tsx
// Add import at top
import SavingsRateMetric from '@/components/Domain/SavingsRateMetric';

// Add after CashFlowMetric or in the 3-column grid section
<SavingsRateMetric />
```

**Files to Create/Modify:**
- CREATE: `/app/GraphQL/Queries/SavingsRate.php`
- MODIFY: `/graphql/schema.graphql`
- CREATE: `/resources/js/components/Domain/SavingsRateMetric.tsx`
- MODIFY: `/resources/js/pages/Dashboard.tsx`

**Testing Checklist:**
- [ ] Widget displays correctly
- [ ] Calculates savings rate accurately: (Savings / Income) × 100
- [ ] Shows progress bar toward 20% target
- [ ] Displays gap amount to reach target
- [ ] Color coding works: green ≥20%, yellow 10-19%, red <10%
- [ ] Range selector functions properly
- [ ] Comparison to previous period displays
- [ ] Handles edge cases (zero income, no savings)
- [ ] Responsive on mobile
- [ ] Loading state works

**Dependencies:** None

---

## Task 1.3: Financial Health Score Widget

**Status:** ❌ Not Started

**Priority:** CRITICAL

**Estimated Complexity:** Medium

**Description:**
Create a simple financial health score (0-100) based on multiple factors to give users a quick understanding of their overall financial status.

**User Story:**
As a user, I want to see a single score that tells me if I'm "doing well" financially, so I don't have to interpret multiple metrics myself.

**Acceptance Criteria:**
- [ ] Calculate score (0-100) based on multiple factors
- [ ] Display score prominently with color coding
- [ ] Show text status: Excellent (90+), Good (70-89), Fair (50-69), Poor (<50)
- [ ] Break down score components with checkmarks/warnings
- [ ] Include: Emergency fund, Cash flow, Savings rate, Budget compliance
- [ ] Each component shows status icon and brief text
- [ ] Update in real-time as financial data changes

**Scoring Formula:**
- Emergency Fund Status: 25 points (0 months=0, 1-2=10, 3-5=20, 6+=25)
- Cash Flow: 25 points (Negative=0, Break-even=10, Positive=25)
- Savings Rate: 30 points (0%=0, 5%=10, 10%=15, 15%=20, 20%+=30)
- Budget Compliance: 20 points (Over budget=0, At budget=10, Under budget=20)

**Technical Implementation:**

### Backend:
1. Create new GraphQL query file: `/app/GraphQL/Queries/FinancialHealthScore.php`
```php
<?php

namespace App\GraphQL\Queries;

use App\Domains\Transaction\Models\Transaction;
use App\Models\Budget;
use App\Domain\Ranges\CurrentMonth;

class FinancialHealthScore
{
    public function __invoke($_, array $args)
    {
        $currentMonth = new CurrentMonth();

        // Component 1: Emergency Fund (25 points)
        $totalSavings = Transaction::savings()->sum('amount');
        $monthlyExpenses = Transaction::expenses()
            ->whereBetween('created_at', [$currentMonth->start(), $currentMonth->end()])
            ->sum('amount');

        $emergencyFundMonths = $monthlyExpenses > 0 ? $totalSavings / $monthlyExpenses : 0;
        $emergencyFundScore = $this->calculateEmergencyFundScore($emergencyFundMonths);

        // Component 2: Cash Flow (25 points)
        $income = Transaction::income()
            ->whereBetween('created_at', [$currentMonth->start(), $currentMonth->end()])
            ->sum('amount');
        $expenses = Transaction::expenses()
            ->whereBetween('created_at', [$currentMonth->start(), $currentMonth->end()])
            ->sum('amount');

        $cashFlow = $income - $expenses;
        $cashFlowScore = $this->calculateCashFlowScore($cashFlow, $income);

        // Component 3: Savings Rate (30 points)
        $savings = Transaction::savings()
            ->whereBetween('created_at', [$currentMonth->start(), $currentMonth->end()])
            ->sum('amount');

        $savingsRate = $income > 0 ? ($savings / $income) * 100 : 0;
        $savingsRateScore = $this->calculateSavingsRateScore($savingsRate);

        // Component 4: Budget Compliance (20 points)
        $budgetComplianceScore = $this->calculateBudgetComplianceScore();

        // Total Score
        $totalScore = round($emergencyFundScore + $cashFlowScore + $savingsRateScore + $budgetComplianceScore);

        return [
            'total_score' => $totalScore,
            'status' => $this->getStatusLabel($totalScore),
            'components' => [
                [
                    'name' => 'Emergency Fund',
                    'score' => round($emergencyFundScore),
                    'max_score' => 25,
                    'status' => $this->getComponentStatus($emergencyFundScore, 25),
                    'details' => number_format($emergencyFundMonths, 1) . ' months of expenses',
                ],
                [
                    'name' => 'Cash Flow',
                    'score' => round($cashFlowScore),
                    'max_score' => 25,
                    'status' => $this->getComponentStatus($cashFlowScore, 25),
                    'details' => $cashFlow >= 0 ? 'Positive' : 'Negative',
                ],
                [
                    'name' => 'Savings Rate',
                    'score' => round($savingsRateScore),
                    'max_score' => 30,
                    'status' => $this->getComponentStatus($savingsRateScore, 30),
                    'details' => number_format($savingsRate, 1) . '% of income',
                ],
                [
                    'name' => 'Budget Compliance',
                    'score' => round($budgetComplianceScore),
                    'max_score' => 20,
                    'status' => $this->getComponentStatus($budgetComplianceScore, 20),
                    'details' => $this->getBudgetComplianceDetails(),
                ],
            ],
        ];
    }

    private function calculateEmergencyFundScore($months)
    {
        if ($months >= 6) return 25;
        if ($months >= 3) return 20;
        if ($months >= 1) return 10;
        return 0;
    }

    private function calculateCashFlowScore($cashFlow, $income)
    {
        if ($cashFlow <= 0) return 0;
        if ($income == 0) return 0;

        $cashFlowRatio = ($cashFlow / $income) * 100;

        if ($cashFlowRatio >= 20) return 25;
        if ($cashFlowRatio >= 10) return 20;
        if ($cashFlowRatio >= 5) return 15;
        return 10;
    }

    private function calculateSavingsRateScore($rate)
    {
        if ($rate >= 20) return 30;
        if ($rate >= 15) return 25;
        if ($rate >= 10) return 20;
        if ($rate >= 5) return 10;
        return 0;
    }

    private function calculateBudgetComplianceScore()
    {
        $activeBudgets = Budget::where('start_at_date', '<=', now())
            ->where('end_at_date', '>=', now())
            ->get();

        if ($activeBudgets->isEmpty()) {
            return 10; // Neutral score if no budgets set
        }

        $totalCompliance = 0;
        foreach ($activeBudgets as $budget) {
            if ($budget->total_spent_percentage <= 100) {
                $totalCompliance += 1;
            }
        }

        $complianceRate = ($totalCompliance / $activeBudgets->count()) * 100;

        if ($complianceRate == 100) return 20;
        if ($complianceRate >= 80) return 15;
        if ($complianceRate >= 60) return 10;
        return 5;
    }

    private function getBudgetComplianceDetails()
    {
        $activeBudgets = Budget::where('start_at_date', '<=', now())
            ->where('end_at_date', '>=', now())
            ->get();

        if ($activeBudgets->isEmpty()) {
            return 'No active budgets';
        }

        $compliant = $activeBudgets->filter(fn($b) => $b->total_spent_percentage <= 100)->count();
        $total = $activeBudgets->count();

        return "$compliant of $total budgets on track";
    }

    private function getComponentStatus($score, $maxScore)
    {
        $percentage = ($score / $maxScore) * 100;

        if ($percentage >= 80) return 'excellent';
        if ($percentage >= 60) return 'good';
        if ($percentage >= 40) return 'fair';
        return 'poor';
    }

    private function getStatusLabel($score)
    {
        if ($score >= 90) return 'Excellent';
        if ($score >= 70) return 'Good';
        if ($score >= 50) return 'Fair';
        return 'Needs Attention';
    }
}
```

2. Add query to GraphQL schema: `/graphql/schema.graphql`
```graphql
type Query {
    financialHealthScore: Json @field(resolver: "App\\GraphQL\\Queries\\FinancialHealthScore")
}
```

### Frontend:
3. Create new component: `/resources/js/components/Domain/FinancialHealthScore.tsx`
```tsx
import React, { useEffect, useState } from 'react';
import { CheckCircleIcon, ExclamationCircleIcon, XCircleIcon } from '@heroicons/react/solid';
import { Card } from '@/components/ui/card';
import LoadingView from "../Global/LoadingView";
import { customQuery } from '../../Api/common';

export default function FinancialHealthScore() {
    const [data, setData] = useState(null);

    useEffect(() => {
        const fetchData = async () => {
            const query = `
                query {
                    financialHealthScore
                }
            `;

            try {
                const { data: responseData } = await customQuery(query);
                const parsedData = JSON.parse(responseData.financialHealthScore);
                setData(parsedData);
            } catch (error) {
                console.error('Error fetching financial health score:', error);
            }
        };

        fetchData();
    }, []);

    if (data == null) {
        return (
            <Card className="relative">
                <LoadingView />
            </Card>
        );
    }

    const getScoreColor = (score) => {
        if (score >= 90) return { bg: 'bg-green-500', text: 'text-green-600', light: 'bg-green-50' };
        if (score >= 70) return { bg: 'bg-blue-500', text: 'text-blue-600', light: 'bg-blue-50' };
        if (score >= 50) return { bg: 'bg-yellow-500', text: 'text-yellow-600', light: 'bg-yellow-50' };
        return { bg: 'bg-red-500', text: 'text-red-600', light: 'bg-red-50' };
    };

    const getStatusIcon = (status) => {
        switch (status) {
            case 'excellent':
            case 'good':
                return <CheckCircleIcon className="h-5 w-5 text-green-500" />;
            case 'fair':
                return <ExclamationCircleIcon className="h-5 w-5 text-yellow-500" />;
            case 'poor':
                return <XCircleIcon className="h-5 w-5 text-red-500" />;
            default:
                return null;
        }
    };

    const colors = getScoreColor(data.total_score);

    return (
        <Card className='relative'>
            <div className="px-6 flex flex-col gap-y-4">
                <h3 className="text-base text-gray-600">Financial Health</h3>

                {/* Score Display */}
                <div className="flex items-center gap-4">
                    <div className={`relative w-24 h-24 rounded-full ${colors.light} flex items-center justify-center`}>
                        <svg className="absolute inset-0 w-24 h-24 -rotate-90">
                            <circle
                                cx="48"
                                cy="48"
                                r="40"
                                stroke="currentColor"
                                strokeWidth="8"
                                fill="none"
                                className="text-gray-200"
                            />
                            <circle
                                cx="48"
                                cy="48"
                                r="40"
                                stroke="currentColor"
                                strokeWidth="8"
                                fill="none"
                                strokeDasharray={`${(data.total_score / 100) * 251.2} 251.2`}
                                className={colors.bg.replace('bg-', 'text-')}
                            />
                        </svg>
                        <div className="text-center z-10">
                            <p className={`text-2xl font-bold ${colors.text}`}>{data.total_score}</p>
                            <p className="text-xs text-gray-500">/ 100</p>
                        </div>
                    </div>

                    <div>
                        <p className={`text-xl font-semibold ${colors.text}`}>{data.status}</p>
                        <p className="text-sm text-gray-500">Overall financial health</p>
                    </div>
                </div>

                {/* Components Breakdown */}
                <div className="space-y-2">
                    {data.components.map((component, index) => (
                        <div key={index} className="flex items-center justify-between py-2 border-t first:border-t-0">
                            <div className="flex items-center gap-2">
                                {getStatusIcon(component.status)}
                                <div>
                                    <p className="text-sm font-medium">{component.name}</p>
                                    <p className="text-xs text-gray-500">{component.details}</p>
                                </div>
                            </div>
                            <div className="text-right">
                                <p className="text-sm font-semibold">{component.score}/{component.max_score}</p>
                            </div>
                        </div>
                    ))}
                </div>
            </div>
        </Card>
    );
}
```

4. Add to Dashboard: `/resources/js/pages/Dashboard.tsx`
```tsx
// Add import at top
import FinancialHealthScore from '@/components/Domain/FinancialHealthScore';

// Add at the very top of the dashboard (after Budgets)
<FinancialHealthScore />
```

**Files to Create/Modify:**
- CREATE: `/app/GraphQL/Queries/FinancialHealthScore.php`
- MODIFY: `/graphql/schema.graphql`
- CREATE: `/resources/js/components/Domain/FinancialHealthScore.tsx`
- MODIFY: `/resources/js/pages/Dashboard.tsx`

**Testing Checklist:**
- [ ] Score calculates correctly (0-100)
- [ ] Status label matches score: Excellent (90+), Good (70-89), Fair (50-69), Needs Attention (<50)
- [ ] Circular progress indicator displays correctly
- [ ] All 4 components show with proper icons
- [ ] Component scores sum to total score
- [ ] Emergency fund calculation works
- [ ] Cash flow status accurate
- [ ] Savings rate percentage correct
- [ ] Budget compliance considers active budgets
- [ ] Handles edge cases (no budgets, no data)
- [ ] Responsive design
- [ ] Loading state works

**Dependencies:**
- Requires Budget model with `total_spent_percentage` attribute
- Requires Transaction scopes: `income()`, `expenses()`, `savings()`

---

## Task 1.4: Spending Alerts Widget

**Status:** ❌ Not Started

**Priority:** CRITICAL

**Estimated Complexity:** Medium

**Description:**
Add a proactive alerts section that warns users about unusual spending patterns, budget overruns, and positive trends.

**User Story:**
As a user, I want to be notified about unusual financial patterns (spending spikes, budget warnings), so I can address problems before they become serious.

**Acceptance Criteria:**
- [ ] Detect spending increases >30% in any category vs previous period
- [ ] Alert when budget reaches 80% and 100%
- [ ] Alert when budget is exceeded
- [ ] Show positive alerts (spending decreased, on track with goals)
- [ ] Color code: Red (critical), Yellow (warning), Green (positive)
- [ ] Display as compact alert cards
- [ ] Show max 5 alerts, prioritized by severity
- [ ] Each alert shows: icon, message, affected category/budget

**Alert Types:**
1. **Category Spike**: "Groceries spending up 45% this month"
2. **Budget Warning**: "Budget 'Monthly Expenses' at 85%"
3. **Budget Exceeded**: "You're AED 500 over your dining budget"
4. **Positive Trend**: "Utilities spending down 12%"
5. **No Transactions**: "No expenses recorded in 7 days"

**Technical Implementation:**

### Backend:
1. Create new GraphQL query file: `/app/GraphQL/Queries/SpendingAlerts.php`
```php
<?php

namespace App\GraphQL\Queries;

use App\Domains\Transaction\Models\Transaction;
use App\Models\Budget;
use App\Models\Category;
use App\Domain\Ranges\CurrentMonth;
use App\Domain\Ranges\LastMonth;
use Carbon\Carbon;

class SpendingAlerts
{
    public function __invoke($_, array $args)
    {
        $alerts = [];
        $currentMonth = new CurrentMonth();
        $lastMonth = new LastMonth();

        // Alert 1: Category Spending Spikes
        $alerts = array_merge($alerts, $this->detectCategorySpikes($currentMonth, $lastMonth));

        // Alert 2: Budget Warnings
        $alerts = array_merge($alerts, $this->detectBudgetWarnings());

        // Alert 3: Positive Trends
        $alerts = array_merge($alerts, $this->detectPositiveTrends($currentMonth, $lastMonth));

        // Alert 4: No Recent Transactions
        $alerts = array_merge($alerts, $this->detectInactivity());

        // Sort by severity (critical > warning > info > positive) and take top 5
        usort($alerts, function($a, $b) {
            $severityOrder = ['critical' => 0, 'warning' => 1, 'info' => 2, 'positive' => 3];
            return $severityOrder[$a['severity']] <=> $severityOrder[$b['severity']];
        });

        return array_slice($alerts, 0, 5);
    }

    private function detectCategorySpikes($currentMonth, $lastMonth)
    {
        $alerts = [];
        $categories = Category::where('type', 'expense')->get();

        foreach ($categories as $category) {
            $currentSpending = Transaction::expenses()
                ->where('category_id', $category->id)
                ->whereBetween('created_at', [$currentMonth->start(), $currentMonth->end()])
                ->sum('amount');

            $lastSpending = Transaction::expenses()
                ->where('category_id', $category->id)
                ->whereBetween('created_at', [$lastMonth->start(), $lastMonth->end()])
                ->sum('amount');

            if ($lastSpending > 0) {
                $percentageChange = (($currentSpending - $lastSpending) / $lastSpending) * 100;

                if ($percentageChange > 30) {
                    $alerts[] = [
                        'severity' => 'warning',
                        'type' => 'category_spike',
                        'message' => "{$category->name} spending up " . round($percentageChange) . "% this month",
                        'category' => $category->name,
                        'percentage' => round($percentageChange),
                    ];
                }
            }
        }

        return $alerts;
    }

    private function detectBudgetWarnings()
    {
        $alerts = [];
        $activeBudgets = Budget::where('start_at_date', '<=', now())
            ->where('end_at_date', '>=', now())
            ->get();

        foreach ($activeBudgets as $budget) {
            $percentage = $budget->total_spent_percentage;

            if ($percentage > 100) {
                $overspent = $budget->amount * (($percentage - 100) / 100);
                $alerts[] = [
                    'severity' => 'critical',
                    'type' => 'budget_exceeded',
                    'message' => "You're AED " . number_format($overspent, 0) . " over your '{$budget->name}' budget",
                    'budget_name' => $budget->name,
                    'overspent_amount' => $overspent,
                ];
            } elseif ($percentage >= 90) {
                $alerts[] = [
                    'severity' => 'warning',
                    'type' => 'budget_warning',
                    'message' => "Budget '{$budget->name}' at {$percentage}%",
                    'budget_name' => $budget->name,
                    'percentage' => $percentage,
                ];
            }
        }

        return $alerts;
    }

    private function detectPositiveTrends($currentMonth, $lastMonth)
    {
        $alerts = [];
        $categories = Category::where('type', 'expense')->get();

        foreach ($categories as $category) {
            $currentSpending = Transaction::expenses()
                ->where('category_id', $category->id)
                ->whereBetween('created_at', [$currentMonth->start(), $currentMonth->end()])
                ->sum('amount');

            $lastSpending = Transaction::expenses()
                ->where('category_id', $category->id)
                ->whereBetween('created_at', [$lastMonth->start(), $lastMonth->end()])
                ->sum('amount');

            if ($lastSpending > 0 && $currentSpending < $lastSpending) {
                $percentageChange = (($lastSpending - $currentSpending) / $lastSpending) * 100;

                if ($percentageChange > 15) {
                    $alerts[] = [
                        'severity' => 'positive',
                        'type' => 'spending_decrease',
                        'message' => "{$category->name} spending down " . round($percentageChange) . "%",
                        'category' => $category->name,
                        'percentage' => round($percentageChange),
                    ];
                }
            }
        }

        return $alerts;
    }

    private function detectInactivity()
    {
        $alerts = [];
        $lastTransaction = Transaction::latest('created_at')->first();

        if ($lastTransaction) {
            $daysSinceLastTransaction = Carbon::parse($lastTransaction->created_at)->diffInDays(now());

            if ($daysSinceLastTransaction >= 7) {
                $alerts[] = [
                    'severity' => 'info',
                    'type' => 'no_activity',
                    'message' => "No transactions recorded in {$daysSinceLastTransaction} days",
                    'days' => $daysSinceLastTransaction,
                ];
            }
        }

        return $alerts;
    }
}
```

2. Add query to GraphQL schema: `/graphql/schema.graphql`
```graphql
type Query {
    spendingAlerts: Json @field(resolver: "App\\GraphQL\\Queries\\SpendingAlerts")
}
```

### Frontend:
3. Create new component: `/resources/js/components/Domain/SpendingAlerts.tsx`
```tsx
import React, { useEffect, useState } from 'react';
import { ExclamationIcon, CheckCircleIcon, InformationCircleIcon, XCircleIcon } from '@heroicons/react/solid';
import { Card } from '@/components/ui/card';
import LoadingView from "../Global/LoadingView";
import { customQuery } from '../../Api/common';

export default function SpendingAlerts() {
    const [alerts, setAlerts] = useState(null);

    useEffect(() => {
        const fetchData = async () => {
            const query = `
                query {
                    spendingAlerts
                }
            `;

            try {
                const { data: responseData } = await customQuery(query);
                const parsedData = JSON.parse(responseData.spendingAlerts);
                setAlerts(parsedData);
            } catch (error) {
                console.error('Error fetching spending alerts:', error);
            }
        };

        fetchData();
    }, []);

    if (alerts == null) {
        return (
            <Card className="relative">
                <LoadingView />
            </Card>
        );
    }

    if (alerts.length === 0) {
        return (
            <Card>
                <div className="px-6 py-4 text-center">
                    <CheckCircleIcon className="h-8 w-8 text-green-500 mx-auto mb-2" />
                    <p className="text-sm text-gray-600">All good! No alerts at this time.</p>
                </div>
            </Card>
        );
    }

    const getAlertIcon = (severity) => {
        switch (severity) {
            case 'critical':
                return <XCircleIcon className="h-5 w-5 text-red-500" />;
            case 'warning':
                return <ExclamationIcon className="h-5 w-5 text-yellow-500" />;
            case 'positive':
                return <CheckCircleIcon className="h-5 w-5 text-green-500" />;
            default:
                return <InformationCircleIcon className="h-5 w-5 text-blue-500" />;
        }
    };

    const getAlertStyles = (severity) => {
        switch (severity) {
            case 'critical':
                return 'bg-red-50 border-l-4 border-red-500';
            case 'warning':
                return 'bg-yellow-50 border-l-4 border-yellow-500';
            case 'positive':
                return 'bg-green-50 border-l-4 border-green-500';
            default:
                return 'bg-blue-50 border-l-4 border-blue-500';
        }
    };

    return (
        <Card>
            <div className="px-6 py-4">
                <h3 className="text-base text-gray-600 mb-3 flex items-center gap-2">
                    <ExclamationIcon className="h-5 w-5 text-gray-500" />
                    Alerts & Notifications
                </h3>

                <div className="space-y-2">
                    {alerts.map((alert, index) => (
                        <div
                            key={index}
                            className={`flex items-start gap-3 p-3 rounded ${getAlertStyles(alert.severity)}`}
                        >
                            <div className="flex-shrink-0 mt-0.5">
                                {getAlertIcon(alert.severity)}
                            </div>
                            <p className="text-sm text-gray-700 flex-1">{alert.message}</p>
                        </div>
                    ))}
                </div>
            </div>
        </Card>
    );
}
```

4. Add to Dashboard: `/resources/js/pages/Dashboard.tsx`
```tsx
// Add import at top
import SpendingAlerts from '@/components/Domain/SpendingAlerts';

// Add after FinancialHealthScore and before Budgets
<SpendingAlerts />
```

**Files to Create/Modify:**
- CREATE: `/app/GraphQL/Queries/SpendingAlerts.php`
- MODIFY: `/graphql/schema.graphql`
- CREATE: `/resources/js/components/Domain/SpendingAlerts.tsx`
- MODIFY: `/resources/js/pages/Dashboard.tsx`

**Testing Checklist:**
- [ ] Detects category spending spikes >30%
- [ ] Shows budget warnings at 90%+
- [ ] Shows critical alerts when budget exceeded
- [ ] Detects positive trends (spending decreases >15%)
- [ ] Shows inactivity warning after 7 days no transactions
- [ ] Displays max 5 alerts
- [ ] Alerts sorted by severity (critical first)
- [ ] Color coding correct: red (critical), yellow (warning), green (positive), blue (info)
- [ ] Icons display properly
- [ ] Shows "All good" message when no alerts
- [ ] Responsive design
- [ ] Loading state works

**Dependencies:**
- Requires Category model with `type` field
- Requires Budget model with `total_spent_percentage`
- Requires Transaction model with category relationship

---

## Task 1.5: Emergency Fund Status Widget

**Status:** ❌ Not Started

**Priority:** CRITICAL

**Estimated Complexity:** Low

**Description:**
Display the user's emergency fund status in terms of months of expenses covered. This is a cornerstone metric of financial security.

**User Story:**
As a user, I want to know if I have enough emergency savings to cover 3-6 months of expenses, so I feel financially secure.

**Acceptance Criteria:**
- [ ] Calculate months of expenses covered: Total Savings / Monthly Avg Expenses
- [ ] Display number of months prominently
- [ ] Show target range: 3-6 months
- [ ] Visual indicator: Red (<3 months), Yellow (3-6 months), Green (>6 months)
- [ ] Show actual savings amount and monthly expense amount
- [ ] Progress bar toward minimum goal (3 months)
- [ ] Show how much more needed to reach 3 and 6 month goals
- [ ] Update automatically when savings or expenses change

**Technical Implementation:**

### Backend:
1. Create new GraphQL query file: `/app/GraphQL/Queries/EmergencyFundStatus.php`
```php
<?php

namespace App\GraphQL\Queries;

use App\Domains\Transaction\Models\Transaction;
use App\Domain\Ranges\LastTwelveMonths;

class EmergencyFundStatus
{
    public function __invoke($_, array $args)
    {
        // Get total savings (all time)
        $totalSavings = Transaction::savings()->sum('amount');

        // Calculate average monthly expenses over last 12 months
        $lastTwelveMonths = new LastTwelveMonths();
        $totalExpenses = Transaction::expenses()
            ->whereBetween('created_at', [$lastTwelveMonths->start(), $lastTwelveMonths->end()])
            ->sum('amount');

        // Average over 12 months
        $averageMonthlyExpenses = $totalExpenses / 12;

        // Calculate months of expenses covered
        $monthsCovered = $averageMonthlyExpenses > 0
            ? $totalSavings / $averageMonthlyExpenses
            : 0;

        // Calculate gap to targets
        $threeMonthTarget = $averageMonthlyExpenses * 3;
        $sixMonthTarget = $averageMonthlyExpenses * 6;

        $gapToThreeMonths = max(0, $threeMonthTarget - $totalSavings);
        $gapToSixMonths = max(0, $sixMonthTarget - $totalSavings);

        // Determine status
        $status = 'poor';
        if ($monthsCovered >= 6) {
            $status = 'excellent';
        } elseif ($monthsCovered >= 3) {
            $status = 'good';
        } elseif ($monthsCovered >= 1) {
            $status = 'fair';
        }

        return [
            'months_covered' => round($monthsCovered, 1),
            'total_savings' => $totalSavings,
            'average_monthly_expenses' => round($averageMonthlyExpenses, 2),
            'status' => $status,
            'three_month_target' => $threeMonthTarget,
            'six_month_target' => $sixMonthTarget,
            'gap_to_three_months' => $gapToThreeMonths,
            'gap_to_six_months' => $gapToSixMonths,
            'progress_to_minimum' => min(100, ($monthsCovered / 3) * 100),
            'progress_to_ideal' => min(100, ($monthsCovered / 6) * 100),
        ];
    }
}
```

2. Add query to GraphQL schema: `/graphql/schema.graphql`
```graphql
type Query {
    emergencyFundStatus: Json @field(resolver: "App\\GraphQL\\Queries\\EmergencyFundStatus")
}
```

### Frontend:
3. Create new component: `/resources/js/components/Domain/EmergencyFundStatus.tsx`
```tsx
import React, { useEffect, useState } from 'react';
import { ShieldCheckIcon } from '@heroicons/react/solid';
import { Card } from '@/components/ui/card';
import LoadingView from "../Global/LoadingView";
import { formatNumber, getAppCurrency } from '../../Utils';
import { customQuery } from '../../Api/common';

export default function EmergencyFundStatus() {
    const [data, setData] = useState(null);

    useEffect(() => {
        const fetchData = async () => {
            const query = `
                query {
                    emergencyFundStatus
                }
            `;

            try {
                const { data: responseData } = await customQuery(query);
                const parsedData = JSON.parse(responseData.emergencyFundStatus);
                setData(parsedData);
            } catch (error) {
                console.error('Error fetching emergency fund status:', error);
            }
        };

        fetchData();
    }, []);

    if (data == null) {
        return (
            <Card className="relative">
                <LoadingView />
            </Card>
        );
    }

    const getStatusColor = (status) => {
        switch (status) {
            case 'excellent':
                return { bg: 'bg-green-500', text: 'text-green-600', light: 'bg-green-50' };
            case 'good':
                return { bg: 'bg-yellow-500', text: 'text-yellow-600', light: 'bg-yellow-50' };
            case 'fair':
                return { bg: 'bg-orange-500', text: 'text-orange-600', light: 'bg-orange-50' };
            default:
                return { bg: 'bg-red-500', text: 'text-red-600', light: 'bg-red-50' };
        }
    };

    const colors = getStatusColor(data.status);

    const getStatusMessage = () => {
        if (data.months_covered >= 6) return '✓ Well protected';
        if (data.months_covered >= 3) return '✓ Minimum goal met';
        if (data.months_covered >= 1) return '⚠ Building fund';
        return '⚠ Needs attention';
    };

    return (
        <Card className='relative'>
            <div className="px-6 flex flex-col gap-y-4">
                <div className="flex items-center gap-2">
                    <ShieldCheckIcon className="h-5 w-5 text-gray-500" />
                    <h3 className="text-base text-gray-600">Emergency Fund</h3>
                </div>

                {/* Main Display */}
                <div className="flex items-baseline gap-2">
                    <p className={`text-4xl font-bold ${colors.text}`}>
                        {data.months_covered.toFixed(1)}
                    </p>
                    <p className="text-sm text-gray-500">months of expenses</p>
                </div>

                <div className={`px-3 py-2 rounded ${colors.light} ${colors.text} text-sm font-medium`}>
                    {getStatusMessage()}
                </div>

                {/* Progress Bars */}
                <div className="space-y-3">
                    {/* Minimum Goal (3 months) */}
                    <div>
                        <div className="flex justify-between text-xs text-gray-500 mb-1">
                            <span>Minimum Goal (3 months)</span>
                            <span>{Math.min(100, data.progress_to_minimum).toFixed(0)}%</span>
                        </div>
                        <div className="w-full h-2 bg-gray-200 rounded-full overflow-hidden">
                            <div
                                className={`h-full transition-all ${data.progress_to_minimum >= 100 ? 'bg-green-500' : 'bg-yellow-500'}`}
                                style={{ width: `${Math.min(100, data.progress_to_minimum)}%` }}
                            />
                        </div>
                        {data.gap_to_three_months > 0 && (
                            <p className="text-xs text-gray-500 mt-1">
                                Need: {getAppCurrency()} {formatNumber(data.gap_to_three_months)}
                            </p>
                        )}
                    </div>

                    {/* Ideal Goal (6 months) */}
                    <div>
                        <div className="flex justify-between text-xs text-gray-500 mb-1">
                            <span>Ideal Goal (6 months)</span>
                            <span>{Math.min(100, data.progress_to_ideal).toFixed(0)}%</span>
                        </div>
                        <div className="w-full h-2 bg-gray-200 rounded-full overflow-hidden">
                            <div
                                className={`h-full transition-all ${data.progress_to_ideal >= 100 ? 'bg-green-500' : 'bg-blue-500'}`}
                                style={{ width: `${Math.min(100, data.progress_to_ideal)}%` }}
                            />
                        </div>
                        {data.gap_to_six_months > 0 && (
                            <p className="text-xs text-gray-500 mt-1">
                                Need: {getAppCurrency()} {formatNumber(data.gap_to_six_months)}
                            </p>
                        )}
                    </div>
                </div>

                {/* Breakdown */}
                <div className="pt-3 border-t space-y-1 text-xs text-gray-500">
                    <div className="flex justify-between">
                        <span>Your Savings:</span>
                        <span className="font-medium">{getAppCurrency()} {formatNumber(data.total_savings)}</span>
                    </div>
                    <div className="flex justify-between">
                        <span>Avg Monthly Expenses:</span>
                        <span className="font-medium">{getAppCurrency()} {formatNumber(data.average_monthly_expenses)}</span>
                    </div>
                </div>
            </div>
        </Card>
    );
}
```

4. Add to Dashboard: `/resources/js/pages/Dashboard.tsx`
```tsx
// Add import at top
import EmergencyFundStatus from '@/components/Domain/EmergencyFundStatus';

// Add in the 3-column grid section alongside Total Cash, Total Savings, Total Investment
// Or place prominently near Financial Health Score
<EmergencyFundStatus />
```

**Files to Create/Modify:**
- CREATE: `/app/GraphQL/Queries/EmergencyFundStatus.php`
- MODIFY: `/graphql/schema.graphql`
- CREATE: `/resources/js/components/Domain/EmergencyFundStatus.tsx`
- MODIFY: `/resources/js/pages/Dashboard.tsx`

**Testing Checklist:**
- [ ] Calculates months correctly: Total Savings / Avg Monthly Expenses
- [ ] Uses last 12 months for expense average
- [ ] Shows correct status: excellent (6+), good (3-6), fair (1-3), poor (<1)
- [ ] Color coding matches status
- [ ] Progress bars display correctly for 3-month and 6-month goals
- [ ] Gap amounts calculate properly
- [ ] Status message displays appropriately
- [ ] Breakdown shows savings and avg expenses
- [ ] Handles edge cases (no savings, no expenses)
- [ ] Responsive design
- [ ] Loading state works

**Dependencies:**
- Requires Transaction model with `savings()` and `expenses()` scopes
- Requires LastTwelveMonths range class

---

# PHASE 2: HIGH VALUE FEATURES 🟡

These features add significant value and improve user engagement.

---

## Task 2.1: Top Expenses List Widget

**Status:** ❌ Not Started

**Priority:** High

**Estimated Complexity:** Low-Medium

**Description:**
Display a list of the top 5-10 individual transactions (by amount) for the current period, so users can see exactly where their biggest expenses went.

**User Story:**
As a user, I want to see my largest expenses as actual transactions (not just categories), so I can identify specific money drains.

**Acceptance Criteria:**
- [ ] Show top 5-10 transactions by amount
- [ ] Display: brand/merchant name, amount, category, date
- [ ] Include brand icon/emoji if available
- [ ] Support date range filter (current month, last month, etc.)
- [ ] Click transaction to view details (optional)
- [ ] Show "View All" link to transactions page
- [ ] Distinguish between income and expense visually
- [ ] Sort by amount (highest first)

**Technical Implementation:**

### Backend:
1. Create new GraphQL query file: `/app/GraphQL/Queries/TopExpenses.php`
```php
<?php

namespace App\GraphQL\Queries;

use App\Domains\Transaction\Models\Transaction;
use App\Domain\Ranges\CurrentMonth;
use App\Domain\Ranges\LastMonth;
use App\Domain\Ranges\CurrentYear;
use App\Domain\Ranges\LastYear;
use App\Domain\Ranges\AllTime;

class TopExpenses
{
    public function ranges()
    {
        return [
            new CurrentMonth,
            new LastMonth,
            new CurrentYear,
            new LastYear,
            new AllTime,
        ];
    }

    public function __invoke($_, array $args)
    {
        $rangeData = app('findRangeByKey', ["key" => $args['range'] ?? 'current-month']);
        $limit = $args['limit'] ?? 10;

        $transactions = Transaction::expenses()
            ->with(['category', 'brand'])
            ->whereBetween('created_at', [$rangeData->start(), $rangeData->end()])
            ->orderBy('amount', 'DESC')
            ->limit($limit)
            ->get();

        return $transactions->map(function ($transaction) {
            return [
                'id' => $transaction->id,
                'amount' => $transaction->amount,
                'description' => $transaction->description,
                'date' => $transaction->created_at->format('M d, Y'),
                'category_name' => $transaction->category->name ?? 'Uncategorized',
                'category_color' => $transaction->category->color ?? '#gray',
                'brand_name' => $transaction->brand->name ?? 'Unknown',
                'brand_id' => $transaction->brand_id,
            ];
        })->toArray();
    }
}
```

2. Add query to GraphQL schema: `/graphql/schema.graphql`
```graphql
type Query {
    topExpenses(range: String, limit: Int): Json @field(resolver: "App\\GraphQL\\Queries\\TopExpenses")
}
```

### Frontend:
3. Create new component: `/resources/js/components/Domain/TopExpenses.tsx`
```tsx
import React, { useEffect, useState } from 'react';
import { TrendingUpIcon, ArrowRightIcon } from '@heroicons/react/solid';
import { Card } from '@/components/ui/card';
import LoadingView from "../Global/LoadingView";
import { formatNumber, getAppCurrency } from '../../Utils';

export default function TopExpenses() {
    const [data, setData] = useState(null);
    const [selectedRange, setSelectedRange] = useState('current-month');

    const ranges = [
        { key: 'current-month', name: 'Current Month' },
        { key: 'last-month', name: 'Last Month' },
        { key: 'current-year', name: 'Current Year' },
        { key: 'last-year', name: 'Last Year' },
        { key: 'all-time', name: 'All Time' },
    ];

    useEffect(() => {
        const fetchData = async () => {
            setData(null);

            const query = `
                query {
                    topExpenses(range: """${selectedRange}""", limit: 10)
                }
            `;

            try {
                const response = await fetch('/graphql', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'include',
                    body: JSON.stringify({ query }),
                });

                const { data: responseData } = await response.json();
                const parsedData = JSON.parse(responseData.topExpenses);
                setData(parsedData);
            } catch (error) {
                console.error('Error fetching top expenses:', error);
            }
        };

        fetchData();
    }, [selectedRange]);

    if (data == null) {
        return (
            <Card className="relative">
                <LoadingView />
            </Card>
        );
    }

    if (data.length === 0) {
        return (
            <Card>
                <div className="px-6 py-4">
                    <h3 className="text-base text-gray-600 mb-3">Top Expenses</h3>
                    <p className="text-sm text-gray-500 text-center py-8">No expenses in this period</p>
                </div>
            </Card>
        );
    }

    return (
        <Card>
            <div className="px-6 py-4">
                <div className="flex justify-between items-center mb-4">
                    <h3 className="text-base text-gray-600">Top Expenses</h3>
                    <select
                        className="min-w-24 h-8 text-xs border-none appearance-none bg-gray-100 pl-2 pr-6 rounded active:outline-none active:shadow-outline focus:outline-none focus:shadow-outline"
                        name="range"
                        value={selectedRange}
                        onChange={(e) => setSelectedRange(e.target.value)}
                    >
                        {ranges.map(range => <option key={range.key} value={range.key}>{range.name}</option>)}
                    </select>
                </div>

                <div className="space-y-3">
                    {data.map((transaction, index) => (
                        <div
                            key={transaction.id}
                            className="flex items-center gap-3 py-2 border-b last:border-b-0 hover:bg-gray-50 rounded px-2 -mx-2 transition-colors"
                        >
                            {/* Rank */}
                            <div className="flex-shrink-0 w-6 text-center">
                                <span className="text-sm font-bold text-gray-400">#{index + 1}</span>
                            </div>

                            {/* Category Color Indicator */}
                            <div
                                className="flex-shrink-0 w-1 h-10 rounded-full"
                                style={{ backgroundColor: transaction.category_color }}
                            />

                            {/* Transaction Details */}
                            <div className="flex-1 min-w-0">
                                <p className="text-sm font-medium text-gray-900 truncate">
                                    {transaction.brand_name}
                                </p>
                                <p className="text-xs text-gray-500">
                                    {transaction.category_name} · {transaction.date}
                                </p>
                                {transaction.description && (
                                    <p className="text-xs text-gray-400 truncate">{transaction.description}</p>
                                )}
                            </div>

                            {/* Amount */}
                            <div className="flex-shrink-0 text-right">
                                <p className="text-sm font-semibold text-gray-900">
                                    {getAppCurrency()} {formatNumber(transaction.amount)}
                                </p>
                            </div>
                        </div>
                    ))}
                </div>

                {/* View All Link */}
                <a
                    href="/transactions"
                    className="flex items-center justify-center gap-2 mt-4 text-sm text-blue-600 hover:text-blue-800 font-medium"
                >
                    View All Transactions
                    <ArrowRightIcon className="h-4 w-4" />
                </a>
            </div>
        </Card>
    );
}
```

4. Add to Dashboard: `/resources/js/pages/Dashboard.tsx`
```tsx
// Add import at top
import TopExpenses from '@/components/Domain/TopExpenses';

// Add after Categories Analytics section or in a 2-column grid with another widget
<TopExpenses />
```

**Files to Create/Modify:**
- CREATE: `/app/GraphQL/Queries/TopExpenses.php`
- MODIFY: `/graphql/schema.graphql`
- CREATE: `/resources/js/components/Domain/TopExpenses.tsx`
- MODIFY: `/resources/js/pages/Dashboard.tsx`

**Testing Checklist:**
- [ ] Shows top 10 expenses by amount
- [ ] Displays brand name, category, date, amount
- [ ] Category color indicator shows correctly
- [ ] Ranked 1-10
- [ ] Range selector works
- [ ] Sorted by amount (highest first)
- [ ] Handles no expenses gracefully
- [ ] View All link navigates to /transactions
- [ ] Truncates long names properly
- [ ] Hover effect works
- [ ] Responsive design
- [ ] Loading state displays

**Dependencies:**
- Requires Transaction model with `brand` and `category` relationships
- Requires Category model with `color` field
- Requires Brand model

---

## Task 2.2: Recurring Expenses Tracker

**Status:** ❌ Not Started

**Priority:** High

**Estimated Complexity:** Medium

**Description:**
Detect and display recurring expenses (subscriptions, bills) to help users identify ongoing commitments and potential savings.

**User Story:**
As a user, I want to see all my recurring expenses (Netflix, gym, etc.) in one place, so I can identify subscriptions I'm no longer using.

**Acceptance Criteria:**
- [ ] Detect recurring transactions (same brand + similar amount + monthly pattern)
- [ ] Display list of detected recurring expenses
- [ ] Show: brand name, amount, frequency, last transaction date
- [ ] Calculate total monthly recurring cost
- [ ] Highlight potentially unused subscriptions (>60 days since last transaction)
- [ ] Allow user to mark as "not recurring" (future enhancement)
- [ ] Group by frequency: monthly, quarterly, annual

**Technical Implementation:**

### Backend:
1. Create new GraphQL query file: `/app/GraphQL/Queries/RecurringExpenses.php`
```php
<?php

namespace App\GraphQL\Queries;

use App\Domains\Transaction\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RecurringExpenses
{
    public function __invoke($_, array $args)
    {
        // Get all expenses from last 12 months grouped by brand
        $twelveMonthsAgo = Carbon::now()->subMonths(12);

        $transactions = Transaction::expenses()
            ->with(['brand', 'category'])
            ->where('created_at', '>=', $twelveMonthsAgo)
            ->whereNotNull('brand_id')
            ->orderBy('created_at', 'DESC')
            ->get()
            ->groupBy('brand_id');

        $recurringExpenses = [];

        foreach ($transactions as $brandId => $brandTransactions) {
            // Need at least 3 transactions to detect pattern
            if ($brandTransactions->count() < 3) {
                continue;
            }

            // Check if amounts are similar (within 10% variance)
            $amounts = $brandTransactions->pluck('amount')->toArray();
            $avgAmount = array_sum($amounts) / count($amounts);
            $variance = $this->calculateVariance($amounts, $avgAmount);

            // If variance is low, it's likely recurring
            if ($variance < 0.15) { // 15% variance threshold
                $first = $brandTransactions->last();
                $last = $brandTransactions->first();

                // Calculate average days between transactions
                $dates = $brandTransactions->pluck('created_at')->map(fn($d) => Carbon::parse($d))->toArray();
                $daysBetween = $this->calculateAverageDaysBetween($dates);

                // Determine frequency
                $frequency = $this->determineFrequency($daysBetween);

                // Check if potentially unused
                $daysSinceLastTransaction = Carbon::now()->diffInDays($last->created_at);
                $potentiallyUnused = $daysSinceLastTransaction > 60;

                $recurringExpenses[] = [
                    'brand_id' => $brandId,
                    'brand_name' => $first->brand->name ?? 'Unknown',
                    'category_name' => $first->category->name ?? 'Uncategorized',
                    'category_color' => $first->category->color ?? '#gray',
                    'average_amount' => round($avgAmount, 2),
                    'frequency' => $frequency,
                    'frequency_days' => round($daysBetween),
                    'transaction_count' => $brandTransactions->count(),
                    'first_transaction_date' => $first->created_at->format('M d, Y'),
                    'last_transaction_date' => $last->created_at->format('M d, Y'),
                    'days_since_last' => $daysSinceLastTransaction,
                    'potentially_unused' => $potentiallyUnused,
                    'monthly_cost' => $this->calculateMonthlyCost($avgAmount, $frequency),
                ];
            }
        }

        // Sort by monthly cost (highest first)
        usort($recurringExpenses, fn($a, $b) => $b['monthly_cost'] <=> $a['monthly_cost']);

        // Calculate totals
        $totalMonthly = array_sum(array_column($recurringExpenses, 'monthly_cost'));
        $unusedCount = count(array_filter($recurringExpenses, fn($e) => $e['potentially_unused']));

        return [
            'recurring_expenses' => $recurringExpenses,
            'total_monthly_cost' => round($totalMonthly, 2),
            'total_count' => count($recurringExpenses),
            'potentially_unused_count' => $unusedCount,
        ];
    }

    private function calculateVariance($amounts, $average)
    {
        if ($average == 0) return 0;

        $squaredDiffs = array_map(fn($amount) => pow($amount - $average, 2), $amounts);
        $variance = sqrt(array_sum($squaredDiffs) / count($amounts));

        return $variance / $average; // Coefficient of variation
    }

    private function calculateAverageDaysBetween($dates)
    {
        if (count($dates) < 2) return 0;

        sort($dates);
        $daysBetween = [];

        for ($i = 1; $i < count($dates); $i++) {
            $daysBetween[] = $dates[$i]->diffInDays($dates[$i - 1]);
        }

        return array_sum($daysBetween) / count($daysBetween);
    }

    private function determineFrequency($days)
    {
        if ($days <= 35) return 'monthly';
        if ($days <= 100) return 'quarterly';
        if ($days <= 380) return 'annual';
        return 'irregular';
    }

    private function calculateMonthlyCost($amount, $frequency)
    {
        switch ($frequency) {
            case 'monthly':
                return $amount;
            case 'quarterly':
                return $amount / 3;
            case 'annual':
                return $amount / 12;
            default:
                return $amount;
        }
    }
}
```

2. Add query to GraphQL schema: `/graphql/schema.graphql`
```graphql
type Query {
    recurringExpenses: Json @field(resolver: "App\\GraphQL\\Queries\\RecurringExpenses")
}
```

### Frontend:
3. Create new component: `/resources/js/components/Domain/RecurringExpenses.tsx`
```tsx
import React, { useEffect, useState } from 'react';
import { RefreshIcon, ExclamationIcon } from '@heroicons/react/solid';
import { Card } from '@/components/ui/card';
import LoadingView from "../Global/LoadingView";
import { formatNumber, getAppCurrency } from '../../Utils';
import { customQuery } from '../../Api/common';

export default function RecurringExpenses() {
    const [data, setData] = useState(null);

    useEffect(() => {
        const fetchData = async () => {
            const query = `
                query {
                    recurringExpenses
                }
            `;

            try {
                const { data: responseData } = await customQuery(query);
                const parsedData = JSON.parse(responseData.recurringExpenses);
                setData(parsedData);
            } catch (error) {
                console.error('Error fetching recurring expenses:', error);
            }
        };

        fetchData();
    }, []);

    if (data == null) {
        return (
            <Card className="relative">
                <LoadingView />
            </Card>
        );
    }

    if (data.total_count === 0) {
        return (
            <Card>
                <div className="px-6 py-4">
                    <h3 className="text-base text-gray-600 mb-3">Recurring Expenses</h3>
                    <p className="text-sm text-gray-500 text-center py-8">
                        No recurring expenses detected
                    </p>
                </div>
            </Card>
        );
    }

    const getFrequencyBadge = (frequency) => {
        const styles = {
            monthly: 'bg-blue-100 text-blue-800',
            quarterly: 'bg-purple-100 text-purple-800',
            annual: 'bg-green-100 text-green-800',
            irregular: 'bg-gray-100 text-gray-800',
        };

        return styles[frequency] || styles.irregular;
    };

    return (
        <Card>
            <div className="px-6 py-4">
                <div className="flex items-center justify-between mb-4">
                    <div className="flex items-center gap-2">
                        <RefreshIcon className="h-5 w-5 text-gray-500" />
                        <h3 className="text-base text-gray-600">Recurring Expenses</h3>
                    </div>
                    <div className="text-right">
                        <p className="text-xs text-gray-500">Monthly Total</p>
                        <p className="text-lg font-bold text-gray-900">
                            {getAppCurrency()} {formatNumber(data.total_monthly_cost)}
                        </p>
                    </div>
                </div>

                {/* Warning for unused subscriptions */}
                {data.potentially_unused_count > 0 && (
                    <div className="mb-4 p-3 bg-yellow-50 border-l-4 border-yellow-500 rounded">
                        <div className="flex items-center gap-2">
                            <ExclamationIcon className="h-5 w-5 text-yellow-600" />
                            <p className="text-sm text-yellow-800">
                                {data.potentially_unused_count} potentially unused subscription{data.potentially_unused_count > 1 ? 's' : ''} detected
                            </p>
                        </div>
                    </div>
                )}

                {/* List of recurring expenses */}
                <div className="space-y-3 max-h-96 overflow-y-auto">
                    {data.recurring_expenses.map((expense, index) => (
                        <div
                            key={index}
                            className={`p-3 rounded border ${expense.potentially_unused ? 'border-yellow-300 bg-yellow-50' : 'border-gray-200'}`}
                        >
                            <div className="flex items-start justify-between gap-3">
                                {/* Left: Brand & Details */}
                                <div className="flex-1 min-w-0">
                                    <div className="flex items-center gap-2 mb-1">
                                        <div
                                            className="w-1 h-6 rounded-full flex-shrink-0"
                                            style={{ backgroundColor: expense.category_color }}
                                        />
                                        <p className="text-sm font-semibold text-gray-900 truncate">
                                            {expense.brand_name}
                                        </p>
                                        <span className={`px-2 py-0.5 text-xs rounded-full ${getFrequencyBadge(expense.frequency)}`}>
                                            {expense.frequency}
                                        </span>
                                    </div>
                                    <p className="text-xs text-gray-500 ml-3">
                                        {expense.category_name}
                                    </p>
                                    <p className="text-xs text-gray-400 ml-3">
                                        Last: {expense.last_transaction_date} ({expense.days_since_last} days ago)
                                    </p>
                                    {expense.potentially_unused && (
                                        <p className="text-xs text-yellow-700 ml-3 mt-1 font-medium">
                                            ⚠ Not used in {expense.days_since_last} days
                                        </p>
                                    )}
                                </div>

                                {/* Right: Amount */}
                                <div className="text-right flex-shrink-0">
                                    <p className="text-sm font-bold text-gray-900">
                                        {getAppCurrency()} {formatNumber(expense.average_amount)}
                                    </p>
                                    <p className="text-xs text-gray-500">
                                        ~{getAppCurrency()} {formatNumber(expense.monthly_cost)}/mo
                                    </p>
                                    <p className="text-xs text-gray-400">
                                        {expense.transaction_count}× charged
                                    </p>
                                </div>
                            </div>
                        </div>
                    ))}
                </div>
            </div>
        </Card>
    );
}
```

4. Add to Dashboard: `/resources/js/pages/Dashboard.tsx`
```tsx
// Add import at top
import RecurringExpenses from '@/components/Domain/RecurringExpenses';

// Add after Top Expenses or in a dedicated section
<RecurringExpenses />
```

**Files to Create/Modify:**
- CREATE: `/app/GraphQL/Queries/RecurringExpenses.php`
- MODIFY: `/graphql/schema.graphql`
- CREATE: `/resources/js/components/Domain/RecurringExpenses.tsx`
- MODIFY: `/resources/js/pages/Dashboard.tsx`

**Testing Checklist:**
- [ ] Detects recurring patterns (3+ transactions, similar amounts)
- [ ] Calculates average amount correctly
- [ ] Determines frequency: monthly, quarterly, annual
- [ ] Identifies potentially unused subscriptions (>60 days)
- [ ] Displays total monthly recurring cost
- [ ] Shows warning badge for unused subscriptions
- [ ] Lists all recurring expenses sorted by monthly cost
- [ ] Shows frequency badge with correct color
- [ ] Displays last transaction date and days since
- [ ] Handles no recurring expenses gracefully
- [ ] Scrollable list for many subscriptions
- [ ] Responsive design
- [ ] Loading state works

**Dependencies:**
- Requires Transaction model with `brand` and `category` relationships
- Requires at least 12 months of transaction history for accurate detection
- Requires Brand and Category models

---

## Task 2.3: Quick Actions Widget

**Status:** ❌ Not Started

**Priority:** High

**Estimated Complexity:** Low

**Description:**
Add action buttons to dashboard for common tasks like adding transactions, creating budgets, setting goals, etc.

**User Story:**
As a user, I want to quickly add a transaction or create a budget directly from the dashboard, without navigating to different pages.

**Acceptance Criteria:**
- [ ] Display 4-6 quick action buttons
- [ ] Actions: Add Transaction, Create Budget, Set Goal, Export Report
- [ ] Buttons clearly labeled with icons
- [ ] Clicking opens modal or navigates to appropriate page
- [ ] Responsive layout (stacked on mobile, row on desktop)
- [ ] Visually distinct from other widgets

**Technical Implementation:**

### Frontend:
1. Create new component: `/resources/js/components/Domain/QuickActions.tsx`
```tsx
import React from 'react';
import { PlusIcon, ChartBarIcon, TargetIcon, DocumentDownloadIcon } from '@heroicons/react/outline';
import { Card } from '@/components/ui/card';
import { router } from '@inertiajs/react';

export default function QuickActions() {
    const actions = [
        {
            label: 'Add Transaction',
            icon: PlusIcon,
            href: '/transactions/create',
            color: 'blue',
        },
        {
            label: 'Create Budget',
            icon: ChartBarIcon,
            href: '/budgets/create',
            color: 'green',
        },
        {
            label: 'View Reports',
            icon: DocumentDownloadIcon,
            href: '/dashboard', // Or reports page
            color: 'purple',
        },
    ];

    const getColorClasses = (color) => {
        const colors = {
            blue: 'bg-blue-50 text-blue-600 hover:bg-blue-100 border-blue-200',
            green: 'bg-green-50 text-green-600 hover:bg-green-100 border-green-200',
            purple: 'bg-purple-50 text-purple-600 hover:bg-purple-100 border-purple-200',
            orange: 'bg-orange-50 text-orange-600 hover:bg-orange-100 border-orange-200',
        };
        return colors[color] || colors.blue;
    };

    return (
        <Card>
            <div className="px-6 py-4">
                <h3 className="text-base text-gray-600 mb-4">Quick Actions</h3>

                <div className="grid grid-cols-1 md:grid-cols-3 gap-3">
                    {actions.map((action, index) => {
                        const Icon = action.icon;
                        return (
                            <button
                                key={index}
                                onClick={() => router.visit(action.href)}
                                className={`flex flex-col items-center justify-center gap-2 p-4 rounded-lg border-2 transition-all ${getColorClasses(action.color)}`}
                            >
                                <Icon className="h-8 w-8" />
                                <span className="text-sm font-medium">{action.label}</span>
                            </button>
                        );
                    })}
                </div>
            </div>
        </Card>
    );
}
```

2. Add to Dashboard: `/resources/js/pages/Dashboard.tsx`
```tsx
// Add import at top
import QuickActions from '@/components/Domain/QuickActions';

// Add after alerts or at strategic location
<QuickActions />
```

**Files to Create/Modify:**
- CREATE: `/resources/js/components/Domain/QuickActions.tsx`
- MODIFY: `/resources/js/pages/Dashboard.tsx`

**Testing Checklist:**
- [ ] All action buttons display correctly
- [ ] Icons render properly
- [ ] Clicking buttons navigates to correct pages
- [ ] Hover effects work
- [ ] Responsive: single column on mobile, 3 columns on desktop
- [ ] Color coding works for each action
- [ ] Accessible (keyboard navigation, screen readers)

**Dependencies:**
- Requires transaction creation route
- Requires budget creation route
- Optional: Goal creation route (future feature)

---

# PHASE 3: ENHANCEMENTS 🟢

Features that enhance the experience but are not critical.

---

## Task 3.1: Cash Runway / Burn Rate Widget

**Status:** ❌ Not Started

**Priority:** Medium

**Estimated Complexity:** Medium

**Description:**
Calculate and display how many months the user's current cash will last at their current spending rate.

**User Story:**
As a user, I want to know how long my money will last at my current spending rate, so I can plan accordingly or adjust my expenses.

**Acceptance Criteria:**
- [ ] Calculate monthly burn rate (average monthly expenses)
- [ ] Calculate runway: Available Cash / Monthly Burn Rate
- [ ] Display months of runway prominently
- [ ] Show warning if runway <3 months
- [ ] Display breakdown: available cash, monthly burn rate
- [ ] Compare to previous period's burn rate
- [ ] Visual indicator: green (>6 months), yellow (3-6), red (<3)

**Implementation Notes:**
- Similar structure to Emergency Fund widget
- Use last 3 months average for burn rate (more recent data)
- Consider only liquid cash, not savings/investments

---

## Task 3.2: Income Stability Indicator

**Status:** ❌ Not Started

**Priority:** Medium

**Estimated Complexity:** Medium

**Description:**
Show users if their income is stable or volatile using coefficient of variation.

**User Story:**
As a user, I want to know if my income is stable month-to-month, so I can adjust my financial planning.

**Acceptance Criteria:**
- [ ] Calculate income coefficient of variation over last 12 months
- [ ] Display stability score: Stable, Moderate, Variable, Highly Variable
- [ ] Show visual indicator (progress bar or gauge)
- [ ] Display min, max, average income
- [ ] Show standard deviation
- [ ] Color code: green (stable), yellow (moderate), red (variable)

**Implementation Notes:**
- Coefficient of Variation = Standard Deviation / Mean
- CV < 0.15 = Stable
- CV 0.15-0.30 = Moderate
- CV 0.30-0.50 = Variable
- CV > 0.50 = Highly Variable

---

## Task 3.3: Transaction Patterns Widget

**Status:** ❌ Not Started

**Priority:** Medium

**Estimated Complexity:** Low

**Description:**
Display interesting transaction statistics using the existing unused GraphQL queries.

**User Story:**
As a user, I want to see interesting patterns in my transactions (average transaction, highest expense, etc.), so I can better understand my spending behavior.

**Acceptance Criteria:**
- [ ] Use existing queries: numberOfTransactions, averageValueTransaction, highestValueTransaction, lowestValueTransaction
- [ ] Display as stat cards or single widget
- [ ] Show transaction count for period
- [ ] Show average transaction amount
- [ ] Show highest single expense
- [ ] Support date range filter

**Implementation Notes:**
- These queries already exist in codebase but aren't used
- Low effort, high insight value
- Could be combined into single "Transaction Stats" widget

---

# PHASE 4: ADVANCED FEATURES ⚪

Future enhancements for comprehensive financial management.

---

## Task 4.1: Goal Tracking System

**Status:** ❌ Not Started

**Priority:** Low

**Estimated Complexity:** High

**Description:**
Full goal tracking system for savings targets, investment goals, debt payoff, etc.

**User Story:**
As a user, I want to set financial goals (vacation fund, house down payment) and track my progress, so I stay motivated to save.

**Requirements:**
- Goal types: Savings, Investment, Debt Payoff, Purchase
- Goal attributes: name, target amount, target date, current amount
- Progress calculation and visualization
- Milestone tracking
- Automatic updates based on transactions
- Goal recommendations based on financial health

---

## Task 4.2: Debt Tracking System

**Status:** ❌ Not Started

**Priority:** Low

**Estimated Complexity:** High

**Description:**
Track debts, loans, credit cards with payoff schedules and interest calculations.

**User Story:**
As a user, I want to track all my debts and see payoff timelines, so I can become debt-free.

**Requirements:**
- Debt types: Credit Card, Loan, Mortgage, Personal Loan
- Attributes: balance, interest rate, minimum payment, due date
- Payoff calculator
- Interest paid tracking
- Debt-to-income ratio
- Snowball/avalanche method recommendations

---

## Task 4.3: Budget Allocation Recommendations (50/30/20 Rule)

**Status:** ❌ Not Started

**Priority:** Low

**Estimated Complexity:** Medium

**Description:**
Analyze user's spending and compare against recommended budget allocation (50% needs, 30% wants, 20% savings).

**User Story:**
As a user, I want to see how my spending compares to recommended allocations, so I know if I'm on the right track.

**Requirements:**
- Calculate current allocation percentages
- Compare against 50/30/20 rule
- Visual comparison (pie charts)
- Recommendations for rebalancing
- Category classification: needs vs wants

---

## Task 4.4: Financial Forecasting

**Status:** ❌ Not Started

**Priority:** Low

**Estimated Complexity:** High

**Description:**
Project future financial position based on current trends.

**User Story:**
As a user, I want to see where I'll be financially in 6-12 months at my current rate, so I can plan ahead.

**Requirements:**
- Project net worth trajectory
- Project savings accumulation
- Project expense trends
- Scenario modeling ("what if" analysis)
- Machine learning for better predictions
- Confidence intervals

---

## Task 4.5: Export & Reporting System

**Status:** ❌ Not Started

**Priority:** Low

**Estimated Complexity:** Medium

**Description:**
Generate and export financial reports in various formats.

**User Story:**
As a user, I want to export my financial data for tax purposes or to share with my accountant.

**Requirements:**
- Export formats: PDF, CSV, Excel
- Report types: Monthly Summary, Annual Report, Tax Report
- Customizable date ranges
- Include charts and visualizations in PDF
- Email delivery option
- Scheduled reports

---

# IMPLEMENTATION GUIDELINES

## For AI Agents Implementing These Tasks:

### Before Starting:
1. Read the entire task description carefully
2. Understand acceptance criteria
3. Check dependencies are met
4. Review related code in codebase

### During Implementation:
1. Follow the code structure exactly as specified
2. Match existing code style and patterns
3. Test edge cases (zero values, no data, etc.)
4. Add error handling
5. Ensure responsive design

### After Implementation:
1. Test all acceptance criteria
2. Update task status to ✅ Completed
3. Add notes about any deviations from spec
4. Document any new dependencies
5. Update related documentation

### Code Quality Standards:
- Follow existing naming conventions
- Use TypeScript types properly
- Handle loading states
- Handle error states
- Add helpful comments
- Keep functions focused and small

### Testing Requirements:
- Manual testing of all features
- Test on mobile and desktop
- Test with edge cases (empty data, large numbers, etc.)
- Test all date ranges
- Verify calculations are correct

---

# TRACKING

## Summary Status

### Phase 1 (Critical): 0/5 completed
- ❌ Task 1.1: Cash Flow Widget
- ❌ Task 1.2: Savings Rate Widget
- ❌ Task 1.3: Financial Health Score Widget
- ❌ Task 1.4: Spending Alerts Widget
- ❌ Task 1.5: Emergency Fund Status Widget

### Phase 2 (High Value): 0/3 completed
- ❌ Task 2.1: Top Expenses List Widget
- ❌ Task 2.2: Recurring Expenses Tracker
- ❌ Task 2.3: Quick Actions Widget

### Phase 3 (Enhancements): 0/3 completed
- ❌ Task 3.1: Cash Runway / Burn Rate Widget
- ❌ Task 3.2: Income Stability Indicator
- ❌ Task 3.3: Transaction Patterns Widget

### Phase 4 (Advanced): 0/5 completed
- ❌ Task 4.1: Goal Tracking System
- ❌ Task 4.2: Debt Tracking System
- ❌ Task 4.3: Budget Allocation Recommendations
- ❌ Task 4.4: Financial Forecasting
- ❌ Task 4.5: Export & Reporting System

---

**Total Progress: 0/16 tasks completed (0%)**

---

## Notes

- This document will be updated as tasks are completed
- Each task is designed to be independent and can be implemented in any order within its phase
- Priority phases should be completed in order (Phase 1 before Phase 2, etc.)
- All tasks include complete implementation code to prevent context loss
- Status emojis will be updated as work progresses: ❌ → 🟡 → ✅

---

*Last Updated: 2025-11-12*
*Document Version: 1.0*
