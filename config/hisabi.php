<?php

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
        // (new SectionDivider)->withTitle("📊 Categories Analytics"),
        // new IncomePerCategory,
        // new ExpensesPerCategory,
        // new TotalPerCategoryTrend,
        // new TotalPerCategoryDailyTrend,
//        new ChangeRatePerBrandTrend,

        // (new SectionDivider)->withTitle("📊 Brands Analytics"),
        // new TotalPerBrand,
        // new TotalPerBrandTrend,

        // (new SectionDivider)->withTitle("💰 Facts"),
        // (new NumberOfTransactions)->setWidth('1/2'),
        // (new NumberOfTransactionsPerCategory)->setWidth('1/2'),
        // (new NumberOfTransactionsPerBrand)->setWidth('1/2'),
        // (new HighestValueTransaction)->setWidth('1/2'),
        // (new LowestValueTransaction)->setWidth('1/2'),
        // (new AverageValueTransaction)->setWidth('1/2'),
        // (new TransactionsStandardDeviation)->setWidth('full'),
    ]
];
