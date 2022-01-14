<?php

return [
    'sms_templates' => [
        'Purchase of AED {amount} with {card} at {brand},',
        'Payment of AED {amount} to {brand} with {card}.',
        '{brand} of AED {amount} has been credited ',
    ],
    'currency' => 'AED',
    'reports' => [
        [
            'name' => 'Total Income',
            'graphql_query' => 'totalIncome',
            'component' => 'value-metric',
            'width' => '1/2',
        ],
        [
            'name' => 'Total Expenses',
            'graphql_query' => 'totalExpenses',
            'component' => 'value-metric',
            'width' => '1/2',
        ],
        [
            'name' => 'Expenses per Category',
            'graphql_query' => 'expensesPerCategory',
            'component' => 'partition-metric',
            'width' => '1/2',
            'ranges' => [
                \App\Domain\Ranges\CurrentMonth::class,
                \App\Domain\Ranges\LastMonth::class,
                \App\Domain\Ranges\CurrentYear::class,
                \App\Domain\Ranges\LastYear::class,
            ],
        ],

        // Brand per category partition for this month
        // Brand per category partition for last month

        // Total Income graph for the last 12 months
        // Total Expenses graph for the last 12 months
        // Graph for expenses per category for the last 12 months
        // Brand graph for the last 12 months
    ]
];