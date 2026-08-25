<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'vietnam_address' => [
        'base_url' => env('VIETNAM_ADDRESS_API_URL', 'https://provinces.open-api.vn/api/v2'),
        'ca_bundle' => env('VIETNAM_ADDRESS_API_CA_BUNDLE'),
    ],

    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-3.6-flash'),
        'base_url' => env('GEMINI_API_URL', 'https://generativelanguage.googleapis.com'),
        'ca_bundle' => env('GEMINI_API_CA_BUNDLE'),
        'thinking_level' => env('GEMINI_THINKING_LEVEL', 'low'),
        'connect_timeout' => (int) env('GEMINI_CONNECT_TIMEOUT', 5),
        'timeout' => (int) env('GEMINI_TIMEOUT', 45),
    ],

];
