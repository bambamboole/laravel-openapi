<?php declare(strict_types=1);

return [
    'paths' => [],
    'output' => base_path('docs'),
    'commands' => [
        // {path} and {port} will be replaced with the configured/passed values
        'build' => 'docker run --rm -v {path}:/docs squidfunk/mkdocs-material build',
        'serve' => [
            'docker', 'run', '--rm', '-it',
            '-p', '{port}:{port}',
            '-v', '{path}:/docs',
            '-e', 'ADD_MODULES=mkdocs-material pymdown-extensions',
            '-e', 'LIVE_RELOAD_SUPPORT=true',
            '-e', 'FAST_MODE=true',
            '-e', 'DOCS_DIRECTORY=/docs',
            '-e', 'AUTO_UPDATE=true',
            '-e', 'UPDATE_INTERVAL=1',
            '-e', 'DEV_ADDR=0.0.0.0:{port}',
            'polinux/mkdocs',
        ],
    ],
    'config' => [
        'site_name' => 'Xentral Functional Documentation',
        'docs_dir' => 'generated',
        'theme' => [
            'name' => 'material',
            'palette' => ['scheme' => 'default', 'primary' => 'indigo', 'accent' => 'indigo'],
            'features' => [
                'navigation.instant',
                'navigation.tracking',
                'navigation.top',
                'navigation.indexes',
                'content.diagram',
            ],
        ],
        'markdown_extensions' => [
            'admonition',
            'pymdownx.details',
            'attr_list',
            ['pymdownx.highlight' => ['anchor_linenums' => true]],
            'pymdownx.inlinehilite',
            ['pymdownx.superfences' => [
                'custom_fences' => [
                    [
                        'name' => 'mermaid',
                        'class' => 'mermaid',
                        'format' => '!!python/name:pymdownx.superfences.fence_code_format',
                    ],
                ],
            ]],
        ],
    ],
];
