<?php

return [
    'settings' => [
        'driver' => 'file',

        'file' => [
            'disk' => 'local',
            'path' => 'settings.json',
        ],
    ],

    'auth' => [
        'require_email_verification' => (bool)env('MONET_AUTH_REQUIRE_EMAIL_VERIFICATION', true),

        'allow_username_login' => (bool)env('MONET_AUTH_ALLOW_USERNAME_LOGIN', false),

        'routes' => [
            'login' => env('MONET_AUTH_LOGIN_ROUTE', '/login'),
            'register' => env('MONET_AUTH_REGISTER_ROUTE', '/register'),
            'logout' => env('MONET_AUTH_LOGOUT_ROUTE', '/logout'),
            'password' => [
                'request' => env('MONET_AUTH_PASSWORD_REQUEST_ROUTE', '/forgot-password'),
                'reset' => env('MONET_AUTH_PASSWORD_RESET_ROUTE', '/reset-password'),
                'confirm' => env('MONET_AUTH_PASSWORD_CONFIRM_ROUTE', '/confirm-password'),
            ],
            'email' => [
                'notice' => env('MONET_AUTH_EMAIL_NOTICE_ROUTE', '/email-verification'),
                'verify' => env('MONET_AUTH_EMAIL_VERIFY_ROUTE', '/email-verification/{id}/{hash}'),
            ],
        ],
    ],

    'modules' => [
        'paths' => [
            'modules',
        ],
        'cache' => [
            'enabled' => env('MONET_MODULES_CACHE_ENABLED', true),
            'keys' => [
                'all' => env('MONET_MODULES_CACHE_KEYS_ALL', 'monet.modules.all'),
                'ordered' => env('MONET_MODULES_CACHE_KEYS_ORDERED', 'monet.modules.ordered'),
            ],
        ],
    ],

    'themes' => [
        'paths' => [
            'themes',
        ],
        'cache' => [
            'enabled' => env('MONET_THEMES_CACHE_ENABLED', true),
            'key' => env('MONET_THEMES_CACHE_KEY', 'monet.themes.all')
        ],
    ],
];
