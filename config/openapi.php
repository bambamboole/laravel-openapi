<?php declare(strict_types=1);

return [
    'oas_version' => '3.1.0',
    'folders' => [base_path('app')],
    'output' => base_path('openapi.yml'),
    'api' => [
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
];
