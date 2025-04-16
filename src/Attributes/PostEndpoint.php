<?php declare(strict_types=1);

namespace Bambamboole\LaravelOpenApi\Attributes;

use Bambamboole\LaravelOpenApi\AttributeFactory;
use OpenApi\Annotations\Post;
use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\RequestBody;
use OpenApi\Attributes\Response;
use OpenApi\Generator;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
class PostEndpoint extends Post
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
        string $successStatus = '200',
    ) {
        $responses = [
            new Response(
                response: $successStatus,
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
            'path' => $path ?? Generator::UNDEFINED,
            'operationId' => $operationId ?? Generator::UNDEFINED,
            'description' => $description ?? Generator::UNDEFINED,
            'summary' => $summary ?? Generator::UNDEFINED,
            'security' => $security ?? Generator::UNDEFINED,
            'servers' => $servers ?? Generator::UNDEFINED,
            'tags' => $tags ?? Generator::UNDEFINED,
            'callbacks' => $callbacks ?? Generator::UNDEFINED,
            'deprecated' => $deprecated ?? Generator::UNDEFINED,
            'x' => $x ?? Generator::UNDEFINED,
            'value' => $this->combine($requestBody, $responses, $parameters),
        ]);
    }
}
