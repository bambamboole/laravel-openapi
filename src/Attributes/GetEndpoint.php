<?php declare(strict_types=1);

namespace Bambamboole\LaravelOpenApi\Attributes;

use Bambamboole\LaravelOpenApi\AttributeFactory;
use OpenApi\Annotations\Get;
use OpenApi\Attributes\Property;
use OpenApi\Generator;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
class GetEndpoint extends Get
{
    use HasEndpointHelpers;

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
        bool $isInternal = false,
        ?\DateTimeInterface $deprecated = null,
        \BackedEnum|string|null $featureFlag = null,
        string|array|null $scopes = null,
    ) {
        $responses = [
            $this->response('200', $description, [
                new Property('data', ref: $resource),
            ]),
            $this->response401(),
            $this->response403(),
            $this->response404(),
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
            'servers' => Generator::UNDEFINED,
            'tags' => $tags ?? Generator::UNDEFINED,
            'callbacks' => Generator::UNDEFINED,
            'deprecated' => $deprecated !== null ? true : Generator::UNDEFINED,
            'x' => $this->compileX($isInternal, $deprecated, $featureFlag, $scopes),
            'value' => $this->combine($responses, $parameters),
        ]);
    }
}
