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
    'reports' => []
];