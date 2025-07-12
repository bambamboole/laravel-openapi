<?php declare(strict_types=1);

namespace Bambamboole\LaravelOpenApi\Attributes;

use Bambamboole\LaravelOpenApi\AttributeFactory;
use OpenApi\Annotations\Delete;
use OpenApi\Attributes\Response;
use OpenApi\Generator;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
class DeleteEndpoint extends Delete
{
    public function __construct(
        string $path,
        ?string $description = null,
        ?array $tags = null,
        ?array $security = null,
        ?string $summary = null,
        ?array $parameters = [],
        ?string $operationId = null,
        array $validates = [],
        ?string $x = null,
    ) {
        $responses = [
            new Response(response: '204', description: 'Resource successfully deleted'),
            new Response(response: '401', description: 'Unauthorized'),
            new Response(response: '403', description: 'Unauthorized'),
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
            'deprecated' => Generator::UNDEFINED,
            'x' => $x ?? Generator::UNDEFINED,
            'value' => $this->combine($responses, $parameters),
        ]);
    }
}
