<?php declare(strict_types=1);

namespace Bambamboole\LaravelOpenApi\Commands;

use Bambamboole\LaravelOpenApi\OpenApiGeneratorFactory;
use Illuminate\Console\Command;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Support\Facades\Process;
use OpenApi\Util;

class GenerateOpenApiSpecCommand extends Command
{
    protected $signature = 'openapi:generate {api?}';

    public function handle(OpenApiGeneratorFactory $factory, Repository $config): int
    {
        $apis = $config->get('openapi.apis');
        if ($api = $this->argument('api')) {
            $specified = $config->get('openapi.apis.'.$api);
            if (! $specified) {
                $this->error('API not found: '.$api);

                return self::FAILURE;
            }
            $apis = [$api => $specified];
        }

        foreach ($apis as $config) {

            $generator = $factory->create($config);
            $openApi = $generator->generate(Util::finder($config['folders']));

            $openApi->saveAs($config['output']);
            foreach ($config['validation_commands'] as $cmd) {
                $result = Process::run($cmd, function (string $type, string $output) {
                    echo $output;
                });
                if ($result->failed()) {
                    return self::FAILURE;
                }
            }
        }

        return self::SUCCESS;
    }
}
