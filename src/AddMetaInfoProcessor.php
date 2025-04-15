<?php declare(strict_types=1);

namespace Bambamboole\LaravelOpenApi;

use OpenApi\Analysis;
use OpenApi\Annotations as OA;

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

        $analysis->openapi->servers = array_map(fn ($server) => new OA\Server($server), $this->config['servers']);
    }
}
