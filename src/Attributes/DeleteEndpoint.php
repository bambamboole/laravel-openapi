<?php declare(strict_types=1);

namespace Bambamboole\LaravelOpenApi\Attributes;

use Bambamboole\LaravelOpenApi\AttributeFactory;
use OpenApi\Annotations\Delete;
use OpenApi\Generator;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
class DeleteEndpoint extends Delete
{
    use HasEndpointHelpers;

    public function __construct(
        string $path,
        ?string $description = null,
        ?array $tags = null,
        ?array $security = null,
        ?string $summary = null,
        ?array $parameters = [],
        ?string $operationId = null,
        array $validates = [],
        bool $isInternal = false,
        ?\DateTimeInterface $deprecated = null,
    ) {
        $responses = [
            $this->response('204', 'Resource successfully deleted'),
            $this->response401(),
            $this->response403(),
        ];
        if (! empty($validates)) {
            $responses[] = AttributeFactory::createValidationResponse($validates);
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
            'x' => $this->parseX($isInternal, $deprecated),
            'value' => $this->combine($responses, $parameters),
        ]);
    }
}
