<?php

use App\Models\Sms;

return [
    'sms_templates' => [
        [
            'body' => 'Purchase of AED {amount} with {card} at {brand},',
            'type' => Sms::EXPENSES
        ],
        [
            'body' => 'Payment of AED {amount} to {brand} with {card}.',
            'type' => Sms::EXPENSES
        ],
        [
            'body' => '{brand} of AED {amount} has been credited ',
            'type' => Sms::INCOME
        ]
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
        // Total Income graph for the last 12 months
        // Total Expenses graph for the last 12 months
        // Graph for expenses per category for the last 12 months
        // Brand graph for the last 12 months
        // Expenses per category this month
        // Expenses per category last month
        // Expenses per category this year
        // Expenses per category last year
        // Brand per category partition for this month
        // Brand per category partition for last month
    ]
];