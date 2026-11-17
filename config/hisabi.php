<?php

return [
    'currency' => 'AED',
    // "Ask Claude" on the kiosk. The API keys live in config/ai.php (ANTHROPIC_API_KEY, OPENAI_API_KEY).
    'assistant' => [
        'model' => env('HISABI_ASSISTANT_MODEL', 'claude-opus-5'),
        // How much Claude thinks before answering; low keeps the wall quick. Empty for models without effort.
        'effort' => env('HISABI_ASSISTANT_EFFORT', 'low'),
        'voice' => env('HISABI_ASSISTANT_VOICE', 'sage'),
    ],
    // The first template that matches wins. A template without {brand} recognises a message
    // without creating a transaction. Templates can span several lines ("\n") for banks that
    // send multi-line messages; the lines of a pasted message are then read together.
    'sms_templates' => [
        'Purchase of AED {amount} with {card} at {brand},',
        'Payment of AED {amount} to {brand} with {card}.',
        '{brand} of AED {amount} has been credited into ',
        'AED {amount} has been debited from {account} using {card} at {brand} on {date} {time}.',
        // FAB account credits (profit, transfers in) don't say where the money came from,
        // so keep them from reaching the next template as a brand called "An amount"
        'An amount of AED {amount} has been credited to your FAB account',
        '{brand} of AED {amount} has been credited to your {account} on {date} {time}.',
        'Your {brand} of AED {amount} has been credited to your {account} on {date} {time}.',
        'Outward {brand} of AED {amount} is debited from your {account}. Your {card} as of {date} {time}.',
        'An ATM cash {brand} of AED{amount} has been debited from your {account} on {date} {time}.',
        '{brand} PAYMENT for {card} via MOBAPP of AED {amount} was debited from {date} {time}.',
        'Your Cr.Card {card} was used for AED{amount} on {date} {time} at {brand},{ignore}. {ignore}',
        // Emirates NBD
        'Funds Transfer of AED {amount} has been made using your {card} at {brand}. Your current balance',
        // ADCB credit cards (AED only: foreign-currency messages don't give the amount in AED)
        'Credit Card {card} was used for AED{amount} on {date} {time} at {brand},',
        'Credit Card {card} used for AED{amount} {ignore} foreign txn fee{ignore} on {date} {time} at {brand},',
        // Emirates Islamic credit cards
        "Credit Card Purchase\nCard Ending: {card}\nAt: {brand}, {ignore}\nAmount: AED {amount}\nDate: {date}, {time}\nAvailable Limit",
        // Dubai Islamic Bank
        'Online Purchase of AED {amount} from {account} on {date} {time} from {brand},',
        '{brand} amount of AED {amount} credited to your {account} on {date}. Available balance',
        // Moving money between your own accounts isn't spending
        'AED {amount} has been drawn from {account} as OWN ACCOUNT',
        'AED {amount} has been drawn from {account} as {brand}. Available balance',
    ]
];
