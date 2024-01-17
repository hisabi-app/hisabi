<?php

use App\Domain\SectionDivider;
use App\GraphQL\Queries\NetWorth;
use App\GraphQL\Queries\TotalCash;
use App\GraphQL\Queries\TotalIncome;
use App\GraphQL\Queries\TotalSavings;
use App\GraphQL\Queries\TotalPerBrand;
use App\GraphQL\Queries\TotalExpenses;
use App\GraphQL\Queries\TotalInvestment;
use App\GraphQL\Queries\TotalIncomeTrend;
use App\GraphQL\Queries\IncomePerCategory;
use App\GraphQL\Queries\TotalPerBrandTrend;
use App\GraphQL\Queries\TotalExpensesTrend;
use App\GraphQL\Queries\ExpensesPerCategory;
use App\GraphQL\Queries\NumberOfTransactions;
use App\GraphQL\Queries\TotalPerCategoryTrend;
use App\GraphQL\Queries\LowestValueTransaction;
use App\GraphQL\Queries\AverageValueTransaction;
use App\GraphQL\Queries\ChangeRatePerBrandTrend;
use App\GraphQL\Queries\HighestValueTransaction;
use App\GraphQL\Queries\NumberOfTransactionsPerBrand;
use App\GraphQL\Queries\TransactionsStandardDeviation;
use App\GraphQL\Queries\NumberOfTransactionsPerCategory;
use App\GraphQL\Queries\FinanceVisualizationCirclePackMetric;

return [
    'currency' => 'AED',
    'sms_templates' => [
        'Purchase of AED {amount} with {card} at {brand},',
        'Payment of AED {amount} to {brand} with {card}.',
        '{brand} of AED {amount} has been credited into ',
        'AED {amount} has been debited from {account} using {card} at {brand} on {date} {time}.',
        '{brand} of AED {amount} has been credited to your {account} on {date} {time}.',
        'Your {brand} of AED {amount} has been credited to your {account} on {date} {time}.',
        'Outward {brand} of AED {amount} is debited from your {account}. Your {card} as of {date} {time}.',
        'An ATM cash {brand} of AED{amount} has been debited from your {account} on {date} {time}.',
        '{brand} PAYMENT for {card} via MOBAPP of AED {amount} was debited from {date} {time}.',
        'Your Cr.Card {card} was used for AED{amount} on {date} {time} at {brand},{ignore}. {ignore}',
    ],
    'reports' => [
        (new SectionDivider)->withTitle("🎖️ Account Overview"),
        (new NetWorth)->setWidth('1/4')->help('The total value of your assets minus your liabilities (expenses)'),
        (new TotalCash)->setWidth('1/4')->help('The available cash = income - (expenses + savings + investments)'),
        (new TotalSavings)->setWidth('1/4'),
        (new TotalInvestment)->setWidth('1/4'),
        new TotalIncome,
        new TotalExpenses,
        new TotalIncomeTrend,
        new TotalExpensesTrend,

        (new SectionDivider)->withTitle("📊 Categories Analytics"),
        new IncomePerCategory,
        new ExpensesPerCategory,
        new TotalPerCategoryTrend,
//        new ChangeRatePerBrandTrend,

        (new SectionDivider)->withTitle("📊 Brands Analytics"),
        new TotalPerBrand,
        new TotalPerBrandTrend,

        (new SectionDivider)->withTitle("💰 Facts"),
        (new NumberOfTransactions)->setWidth('1/2'),
        (new NumberOfTransactionsPerCategory)->setWidth('1/2'),
        (new NumberOfTransactionsPerBrand)->setWidth('1/2'),
        (new HighestValueTransaction)->setWidth('1/2'),
        (new LowestValueTransaction)->setWidth('1/2'),
        (new AverageValueTransaction)->setWidth('1/2'),
        (new TransactionsStandardDeviation)->setWidth('full'),
        (new SectionDivider)->withTitle("🧬 Visualization"),
        (new FinanceVisualizationCirclePackMetric)->setWidth('full'),
    ],
    'gpt' => [
        'model' => env('GPT_MODEL', 'gpt-3.5-turbo') // gpt-4, gpt-3.5-turbo
    ]
];
