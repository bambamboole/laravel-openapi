<?php declare(strict_types=1);

namespace Bambamboole\LaravelOpenApi\Attributes;

use Bambamboole\LaravelOpenApi\AttributeFactory;
use OpenApi\Annotations\Put;
use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\RequestBody;
use OpenApi\Attributes\Response;
use OpenApi\Generator;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
class PutEndpoint extends Put
{
    public function __construct(
        string $path,
        string $request,
        string $resource,
        ?string $description = null,
        ?array $tags = null,
        ?array $security = null,
        ?string $summary = null,
        ?array $parameters = [],
        ?string $operationId = null,
        bool $isInternal = false,
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
            AttributeFactory::createValidationResponse($request),
            new Response(response: '401', description: 'Unauthorized'),
            new Response(response: '403', description: 'Unauthorized'),
        ];

        $parameters = array_merge($parameters, AttributeFactory::createMissingPathParameters($path, $parameters));

        $requestBody = new RequestBody(
            content: new JsonContent(ref: $request),
        );
        parent::__construct([
            'path' => $path,
            'operationId' => $operationId ?? Generator::UNDEFINED,
            'description' => $description ?? Generator::UNDEFINED,
            'summary' => $summary ?? Generator::UNDEFINED,
            'security' => $security ?? Generator::UNDEFINED,
            'servers' => Generator::UNDEFINED,
            'tags' => $tags ?? Generator::UNDEFINED,
            'callbacks' => Generator::UNDEFINED,
            'deprecated' => Generator::UNDEFINED,
            'x' => $isInternal ? ['internal' => true] : Generator::UNDEFINED,
            'value' => $this->combine($requestBody, $responses, $parameters),
        ]);
    }
}
