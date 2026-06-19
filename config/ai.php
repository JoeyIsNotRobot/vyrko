<?php

return [
    'provider' => env('AI_PROVIDER', 'gemini'),
    'api_key' => env('AI_API_KEY', env('GEMINI_API_KEY')),
    'model' => env('AI_MODEL', 'gemini-2.5-flash'),
    'endpoint' => env('GEMINI_API_ENDPOINT', 'https://generativelanguage.googleapis.com/v1beta'),
    'timeout' => (int) env('AI_TIMEOUT', 60),
];
