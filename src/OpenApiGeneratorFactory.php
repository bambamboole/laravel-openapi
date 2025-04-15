<?php declare(strict_types=1);

namespace Bambamboole\LaravelOpenApi;

use OpenApi\Analysers\AttributeAnnotationFactory;
use OpenApi\Analysers\DocBlockAnnotationFactory;
use OpenApi\Analysers\ReflectionAnalyser;
use OpenApi\Generator;
use OpenApi\Loggers\ConsoleLogger;
use OpenApi\Processors\OperationId;

class OpenApiGeneratorFactory
{
    public function create(array $config): Generator
    {
        $generator = new Generator(new ConsoleLogger);
        $generator->getProcessorPipeline()->add(new AddMetaInfoProcessor($config));
        $generator->getProcessorPipeline()->remove(OperationId::class);
        $generator->getProcessorPipeline()->add(new OperationIdProcessor);

        $analyzer = new ReflectionAnalyser([new DocBlockAnnotationFactory, new AttributeAnnotationFactory]);

        return $generator
            ->setVersion($config['oas_version'] ?? '3.1.0')
            ->setAnalyser($analyzer);
    }
}
