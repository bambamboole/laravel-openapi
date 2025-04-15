<?php declare(strict_types=1);

namespace Bambamboole\LaravelOpenApi\Attributes;

use OpenApi\Annotations\Get;
use OpenApi\Attributes\Items;
use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\Parameter;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Response;
use OpenApi\Attributes\Schema;
use OpenApi\Generator;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class ListEndpoint extends Get
{
    public function __construct(
        string $path,
        string $resource,
        ?string $description = null,
        array $filters = [],
        ?array $tags = null,
        ?array $security = null,
        ?string $summary = null,
        int $defaultPageSize = 15,
        int $maxPageSize = 100,
    ) {
        $responses = [
            new Response(response: '200', description: $description, content: new JsonContent(properties: [
                new Property('data', type: 'array', items: new Items(ref: $resource)),
                new Property('meta', properties: [
                    new Property(property: 'current_page', type: 'integer'),
                    new Property(property: 'from', type: 'integer'),
                    new Property(property: 'path', type: 'string'),
                    new Property(property: 'per_page', type: 'integer'),
                    new Property(property: 'last_page', type: 'integer'),
                    new Property(property: 'to', type: 'integer'),
                    new Property(property: 'total', type: 'integer'),
                    new Property(property: 'links', type: 'array', items: new Items(type: 'object')),
                ], type: 'object'),
            ])),
            new Response(response: '401', description: 'Unauthorized'),
            new Response(response: '403', description: 'Unauthorized'),
        ];
        $parameters = [];
        if (! empty($filters)) {
            $parameters[] = new Parameter(
                name: 'filter',
                in: 'query',
                required: false,
                schema: new Schema(
                    properties: $filters,
                    type: 'object',
                ),
                style: 'deepObject',
            );
        }
        $parameters[] = new Parameter(
            name: 'page',
            description: 'Page number.',
            in: 'query',
            required: false,
            schema: new Schema(type: 'integer', example: 1),
        );
        $parameters[] = new Parameter(
            name: 'per_page',
            description: sprintf('Number of items per page. Default: %d, Max: %d', $defaultPageSize, $maxPageSize),
            in: 'query',
            required: false,
            schema: new Schema(type: 'integer', example: $defaultPageSize),
        );

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
}
