<?php declare(strict_types=1);

return [
    'paths' => [],
    'output' => base_path('docs'),
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
