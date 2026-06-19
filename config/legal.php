<?php

return [
    'terms_version' => env('LEGAL_TERMS_VERSION', '2026-06-19'),
    'privacy_version' => env('LEGAL_PRIVACY_VERSION', '2026-06-19'),
    'data_consent_version' => env('LEGAL_DATA_CONSENT_VERSION', '2026-06-19'),
    'social_data_version' => env('LEGAL_SOCIAL_DATA_VERSION', '2026-06-19'),

    'required' => [
        'terms_of_use' => 'terms_version',
        'privacy_policy' => 'privacy_version',
        'ai_data_processing' => 'data_consent_version',
    ],

    'social_required' => [
        'terms_of_use' => 'terms_version',
        'privacy_policy' => 'privacy_version',
        'ai_data_processing' => 'data_consent_version',
        'social_data_usage' => 'social_data_version',
    ],
];
