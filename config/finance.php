<?php

use App\GraphQL\Queries\TotalIncome;
use App\GraphQL\Queries\TotalExpenses;
use App\GraphQL\Queries\TotalPerBrand;
use App\GraphQL\Queries\IncomePerCategory;
use App\GraphQL\Queries\ExpensesPerCategory;
use App\GraphQL\Queries\TotalPerCategoryTrend;
use App\GraphQL\Queries\TotalExpensesTrend;
use App\GraphQL\Queries\TotalIncomeTrend;
use App\GraphQL\Queries\TotalPerBrandTrend;

return [
    'currency' => 'AED',
    'sms_templates' => [
        'Purchase of AED {amount} with {card} at {brand},',
        'Payment of AED {amount} to {brand} with {card}.',
        '{brand} of AED {amount} has been credited ',
    ],
    'reports' => [
        new TotalIncome,
        new TotalExpenses,
        new IncomePerCategory,
        new ExpensesPerCategory,
        new TotalIncomeTrend,
        new TotalExpensesTrend,
        new TotalPerCategoryTrend,
        new TotalPerBrandTrend,
        new TotalPerBrand,
    ]
];