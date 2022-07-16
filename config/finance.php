<?php

use App\GraphQL\Queries\TotalCash;
use App\GraphQL\Queries\TotalIncome;
use App\GraphQL\Queries\TotalSavings;
use App\GraphQL\Queries\TotalExpenses;
use App\GraphQL\Queries\TotalPerBrand;
use App\GraphQL\Queries\TotalInvestment;
use App\GraphQL\Queries\TotalIncomeTrend;
use App\GraphQL\Queries\IncomePerCategory;
use App\GraphQL\Queries\TotalExpensesTrend;
use App\GraphQL\Queries\TotalPerBrandTrend;
use App\GraphQL\Queries\ExpensesPerCategory;
use App\GraphQL\Queries\TotalPerCategoryTrend;

return [
    'currency' => 'AED',
    'sms_templates' => [
        'Purchase of AED {amount} with {card} at {brand},',
        'Payment of AED {amount} to {brand} with {card}.',
        '{brand} of AED {amount} has been credited into ',
        'AED {amount} has been debited from {account} using {card} at {brand} on {datetime}.',
        '{brand} of AED {amount} has been credited to your {account} on {datetime}.',
        'Your {brand} of AED {amount} has been credited to your {account} on {datetime}.',
        'Outward {brand} of AED {amount} is debited from your {account}. Your {card} as of {datetime}.',
        'An ATM cash {brand} of AED{amount} has been debited from your {account} on {datetime}.',
        '{brand} PAYMENT for {card} via MOBAPP of AED {amount} was debited from {account}.'
    ],
    'reports' => [
        new TotalCash,
        new TotalSavings,
        new TotalInvestment,
        new TotalIncome,
        new TotalExpenses,
        new IncomePerCategory,
        new ExpensesPerCategory,
        new TotalPerBrand,
        new TotalIncomeTrend,
        new TotalExpensesTrend,
        new TotalPerCategoryTrend,
        new TotalPerBrandTrend,
    ]
];