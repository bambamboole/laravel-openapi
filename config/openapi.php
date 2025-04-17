<?php declare(strict_types=1);

return [
    'docs' => [
        'enabled' => env('APP_ENV') !== 'production',
        'prefix' => 'api-docs',
    ],
    'apis' => [
        'default' => [
            'oas_version' => '3.1.0',
            'ruleset' => null,
            'folders' => [base_path('app')],
            'output' => base_path('openapi.yml'),
            'name' => 'My API',
            'version' => '1.0.0',
            'description' => 'Developer API',
            'contact' => [
                'name' => 'API Support',
                'url' => env('APP_URL', 'https://.example.com'),
                'email' => env('MAIL_FROM_ADDRESS', 'api@example.com'),
            ],
            'servers' => [
                [
                    'url' => env('APP_URL', 'https://.example.com'),
                    'description' => 'Your API environment',
                ],
            ],
        ],
    ],
];
