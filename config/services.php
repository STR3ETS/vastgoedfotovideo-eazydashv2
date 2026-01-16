<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
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

    'openai' => [
        'key' => env('OPENAI_API_KEY'),
    ],

    'mailgun' => [
        'domain'   => env('MAILGUN_DOMAIN'),
        'secret'   => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'm365' => [
        'tenant_id'     => env('M365_TENANT_ID'),
        'client_id'     => env('M365_CLIENT_ID'),
        'client_secret' => env('M365_CLIENT_SECRET'),
        'mailbox'       => env('M365_INFO_MAILBOX', 'info@eazyonline.nl'),
        'webhook_secret'=> env('M365_WEBHOOK_SECRET'),
    ],

    // ✅ OpenRouteService (geocoding + routing)
    'ors' => [
        'key' => env('ORS_API_KEY'),
        'base_url' => env('ORS_BASE_URL', 'https://api.openrouteservice.org'),
        'profile' => env('ORS_PROFILE', 'driving-car'),
    ],
];
