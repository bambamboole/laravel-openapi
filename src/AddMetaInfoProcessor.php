<?php declare(strict_types=1);

namespace Bambamboole\LaravelOpenApi;

use Illuminate\Contracts\Config\Repository;
use OpenApi\Analysis;
use OpenApi\Annotations as OA;

class AddMetaInfoProcessor
{
    public function __construct(private readonly Repository $config) {}

    public function __invoke(Analysis $analysis)
    {
        $analysis->openapi->info = new OA\Info([
            'title' => $this->config->get('openapi.api.name', $this->config->get('app.name')),
            'version' => $this->config->get('openapi.api.version', '1.0.0'),
            'description' => $this->config->get('openapi.api.description'),
            'contact' => new OA\Contact($this->config->get('openapi.api.contact')),
        ]);
        if (empty($analysis->openapi->servers ?? [])) {
            $analysis->openapi->servers = array_map(
                fn ($server) => new OA\Server($server),
                $this->config->get('openapi.api.servers')
            );
        }
        $analysis->openapi->servers = $this->config->get('openapi.api.servers');
    }
}
