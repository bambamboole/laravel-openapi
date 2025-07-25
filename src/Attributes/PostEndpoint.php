<?php declare(strict_types=1);

namespace Bambamboole\LaravelOpenApi\Attributes;

use Bambamboole\LaravelOpenApi\AttributeFactory;
use OpenApi\Annotations\Post;
use OpenApi\Attributes\MediaType;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\RequestBody;
use OpenApi\Attributes\Schema;
use OpenApi\Generator;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
class PostEndpoint extends Post
{
    use HasEndpointHelpers;

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
        string $contentType = 'application/json',
        bool $isInternal = false,
        ?\DateTimeInterface $deprecated = null,
    ) {
        $responses = [
            $this->response($successStatus, $description, [
                new Property('data', ref: $resource),
            ]),
            AttributeFactory::createValidationResponse($request),
            $this->response401(),
            $this->response403(),
        ];

        $parameters = array_merge($parameters, AttributeFactory::createMissingPathParameters($path, $parameters));

        $requestBody = new RequestBody(
            content: new MediaType($contentType, schema: new Schema(ref: $request))
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
            'deprecated' => $deprecated !== null ? true : Generator::UNDEFINED,
            'x' => $this->parseX($isInternal, $deprecated),
            'value' => $this->combine($requestBody, $responses, $parameters),
        ]);
    }
}
