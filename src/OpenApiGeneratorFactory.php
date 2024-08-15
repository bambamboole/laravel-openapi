<?php declare(strict_types=1);

namespace Bambamboole\LaravelOpenApi;

use Illuminate\Contracts\Config\Repository;
use OpenApi\Analysers\AttributeAnnotationFactory;
use OpenApi\Analysers\DocBlockAnnotationFactory;
use OpenApi\Analysers\ReflectionAnalyser;
use OpenApi\Generator;
use OpenApi\Loggers\ConsoleLogger;
use OpenApi\Processors\OperationId;

class OpenApiGeneratorFactory
{
    public function __construct(private readonly Repository $config) {}

    public function create(): Generator
    {
        $generator = new Generator(new ConsoleLogger);
        $generator->getProcessorPipeline()->add(new AddMetaInfoProcessor($this->config));
        $generator->getProcessorPipeline()->remove(OperationId::class);
        $generator->getProcessorPipeline()->add(new OperationIdProcessor);

        $analyzer = new ReflectionAnalyser([new DocBlockAnnotationFactory, new AttributeAnnotationFactory]);

        return $generator
//            ->setConfig([])
            ->setVersion($this->config->get('openapi.oas_version', '3.1.0'))
            ->setAnalyser($analyzer);
    }
}
