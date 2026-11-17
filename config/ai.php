<?php

// The Laravel AI SDK, used by "Ask Claude" on the kiosk: Claude answers (Anthropic), OpenAI turns
// the question into text and reads the answer out. The model Claude uses is in config/hisabi.php.
return [

    'default' => 'anthropic',
    'default_for_audio' => 'openai',
    'default_for_transcription' => 'openai',

    'providers' => [
        'anthropic' => [
            'driver' => 'anthropic',
            'key' => env('ANTHROPIC_API_KEY'),
            'url' => env('ANTHROPIC_URL', 'https://api.anthropic.com/v1'),
        ],

        'openai' => [
            'driver' => 'openai',
            'key' => env('OPENAI_API_KEY'),
            'url' => env('OPENAI_URL', 'https://api.openai.com/v1'),
            'models' => [
                'audio' => ['default' => 'gpt-4o-mini-tts'],
                // The SDK's default model splits speakers apart, which a single question doesn't need
                'transcription' => ['default' => 'gpt-4o-mini-transcribe'],
            ],
        ],
    ],

];
