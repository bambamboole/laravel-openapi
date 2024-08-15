<?php declare(strict_types=1);

namespace Bambamboole\LaravelOpenApi\Commands;

use Bambamboole\LaravelOpenApi\OpenApiGeneratorFactory;
use Illuminate\Console\Command;
use Illuminate\Contracts\Config\Repository;
use OpenApi\Util;

class GenerateOpenApiSpecCommand extends Command
{
    protected $signature = 'openapi:generate';
    public function handle(OpenApiGeneratorFactory $factory, Repository $config): int
    {
        $generator = $factory->create();
        $openApi = $generator->generate(Util::finder($config->get('openapi.folders')));

        $openApi->saveAs($config->get('openapi.output'));

        return self::SUCCESS;
    }
}