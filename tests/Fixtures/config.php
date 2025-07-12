<?php declare(strict_types=1);

return [
    'oas_version' => '3.1.0',
    'ruleset' => null,
    'folders' => [__DIR__.'/../TestClasses'],
    'name' => 'My API',
    'version' => '1.0.0',
    'description' => 'Developer API',
    'contact' => [
        'name' => 'API Support',
        'url' => 'https://.example.com',
        'email' => 'api@example.com',
    ],
    'servers' => [
        [
            'url' => 'https://.example.com',
            'description' => 'Your API environment',
        ],
    ],
    'exclude_money' => true,
];
