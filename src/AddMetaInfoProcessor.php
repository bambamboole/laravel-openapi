<?php declare(strict_types=1);

namespace Bambamboole\LaravelOpenApi;

use OpenApi\Analysis;
use OpenApi\Annotations as OA;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Schema;

class AddMetaInfoProcessor
{
    public function __construct(private readonly array $config) {}

    public function __invoke(Analysis $analysis)
    {
        $analysis->openapi->info = new OA\Info([
            'title' => $this->config['name'],
            'version' => $this->config['version'] ?? '1.0.0',
            'description' => $this->config['description'] ?? '',
            'contact' => $this->config['contact'] ?? '',
        ]);
        $schemas = is_array($analysis->openapi->components->schemas)
            ? $analysis->openapi->components->schemas
            : [];

        $analysis->openapi->components->schemas = array_merge(
            $schemas,
            [
                new Schema(
                    schema: 'Money',
                    properties: [
                        new Property(property: 'amount', type: 'string'),
                        new Property(property: 'currency', type: 'string'),
                    ],
                    type: 'object',
                    additionalProperties: false,
                ),
            ]
        );

        $analysis->openapi->servers = array_map(fn ($server) => new OA\Server($server), $this->config['servers']);
    }
}
