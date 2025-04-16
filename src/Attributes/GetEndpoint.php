<?php declare(strict_types=1);

namespace Bambamboole\LaravelOpenApi\Attributes;

use Bambamboole\LaravelOpenApi\AttributeFactory;
use OpenApi\Annotations\Get;
use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Response;
use OpenApi\Generator;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
class GetEndpoint extends Get
{
    public function __construct(
        string $path,
        string $resource,
        ?string $description = null,
        ?array $tags = null,
        ?array $security = null,
        ?string $summary = null,
        ?array $parameters = [],
        ?string $operationId = null,
        array $includes = [],
    ) {
        $responses = [
            new Response(
                response: '200',
                description: $description,
                content: new JsonContent(
                    properties: [
                        new Property('data', ref: $resource),
                    ]
                )
            ),
            new Response(response: '401', description: 'Unauthorized'),
            new Response(response: '403', description: 'Unauthorized'),
        ];
        if (! empty($includes)) {
            $parameters[] = AttributeFactory::createIncludeParameter($includes);
        }

        $parameters = array_merge($parameters, AttributeFactory::createMissingPathParameters($path, $parameters));

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
