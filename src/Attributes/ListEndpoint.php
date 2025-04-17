<?php declare(strict_types=1);

namespace Bambamboole\LaravelOpenApi\Attributes;

use Bambamboole\LaravelOpenApi\AttributeFactory;
use Bambamboole\LaravelOpenApi\Enum\PaginationType;
use OpenApi\Annotations\Get;
use OpenApi\Attributes\Items;
use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Response;
use OpenApi\Generator;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
class ListEndpoint extends Get
{
    public function __construct(
        string $path,
        string $resource,
        ?string $description = null,
        array $filters = [],
        array $includes = [],
        array $parameters = [],
        ?array $tags = null,
        ?array $security = null,
        ?string $summary = null,
        ?string $operationId = null,
        int $defaultPageSize = 15,
        int $maxPageSize = 100,
        PaginationType $paginationType = PaginationType::SIMPLE,
    ) {

        $responses = [
            new Response(response: '200', description: $description, content: new JsonContent(properties: [
                new Property('data', type: 'array', items: new Items(ref: $resource)),
                ...$this->getPaginationProperties($paginationType),
            ])),
            new Response(response: '401', description: 'Unauthorized'),
            new Response(response: '403', description: 'Unauthorized'),
        ];
        $parameters = array_merge($parameters, AttributeFactory::createMissingPathParameters($path, $parameters));
        if (! empty($filters)) {
            $parameters[] = AttributeFactory::createFilterParameter($filters);
        }
        if (! empty($includes)) {
            $parameters[] = AttributeFactory::createIncludeParameter($includes);
        }
        $parameters = array_merge($parameters, AttributeFactory::createPaginationParameters($defaultPageSize, $maxPageSize, $paginationType));

        parent::__construct([
            'path' => $path,
            'operationId' => $operationId ?? Generator::UNDEFINED,
            'description' => $description ?? Generator::UNDEFINED,
            'summary' => $summary ?? Generator::UNDEFINED,
            'security' => $security ?? Generator::UNDEFINED,
            'servers' => $servers ?? Generator::UNDEFINED,
            'tags' => $tags ?? Generator::UNDEFINED,
            'callbacks' => $callbacks ?? Generator::UNDEFINED,
            'deprecated' => $deprecated ?? Generator::UNDEFINED,
            'x' => $x ?? Generator::UNDEFINED,
            'value' => $this->combine($responses, $parameters),
        ]);
    }

    private function getPaginationProperties(PaginationType $paginationType): array
    {
        if ($paginationType === PaginationType::SIMPLE) {
            return [
                new Property(
                    'meta',
                    properties: [
                        new Property(property: 'current_page', type: 'integer'),
                        new Property(property: 'from', type: 'integer'),
                        new Property(property: 'path', type: 'string'),
                        new Property(property: 'per_page', type: 'integer'),
                        new Property(property: 'last_page', type: 'integer'),
                        new Property(property: 'to', type: 'integer'),
                        new Property(property: 'total', type: 'integer'),
                        new Property(property: 'links', type: 'array', items: new Items(type: 'object')),
                    ],
                    type: 'object',
                ),
            ];
        }
        if ($paginationType === PaginationType::CURSOR) {
            return [
                new Property(
                    'links',
                    properties: [
                        new Property(property: 'first', type: 'string', nullable: true),
                        new Property(property: 'last', type: 'string', nullable: true),
                        new Property(property: 'prev', type: 'string', nullable: true),
                        new Property(property: 'next', type: 'string', nullable: true),
                    ],
                    type: 'object',
                ),
                new Property(
                    'meta',
                    properties: [
                        new Property(property: 'path', type: 'string'),
                        new Property(property: 'per_page', type: 'integer'),
                        new Property(property: 'next_cursor', type: 'string', nullable: true),
                        new Property(property: 'prev_cursor', type: 'string', nullable: true),
                    ],
                    type: 'object',
                ),
            ];
        }

        if ($paginationType === PaginationType::TABLE) {
            return [
                new Property(
                    'links',
                    properties: [
                        new Property(property: 'first', type: 'string', nullable: true),
                        new Property(property: 'last', type: 'string', nullable: true),
                        new Property(property: 'prev', type: 'string', nullable: true),
                        new Property(property: 'next', type: 'string', nullable: true),
                    ],
                    type: 'object',
                ),
                new Property(
                    'meta',
                    properties: [
                        new Property(property: 'current_page', type: 'integer'),
                        new Property(property: 'from', type: 'integer'),
                        new Property(property: 'last_page', type: 'integer'),
                        new Property(property: 'links', type: 'array', items: new Items(type: 'object', properties: [
                            new Property(property: 'url', type: 'string', nullable: true),
                            new Property(property: 'label', type: 'string'),
                            new Property(property: 'active', type: 'boolean'),
                        ])),
                        new Property(property: 'path', type: 'string'),
                        new Property(property: 'per_page', type: 'integer'),
                        new Property(property: 'to', type: 'integer'),
                        new Property(property: 'total', type: 'integer'),
                    ],
                    type: 'object',
                ),
            ];
        }
    }
}
