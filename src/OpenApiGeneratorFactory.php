<?php declare(strict_types=1);

namespace Bambamboole\LaravelOpenApi;

use Bambamboole\LaravelOpenApi\PostProcessors\AddMetaInfoProcessor;
use Bambamboole\LaravelOpenApi\PostProcessors\FeatureFlagProcessor;
use Bambamboole\LaravelOpenApi\PostProcessors\FilterDeprecationsProcessor;
use Bambamboole\LaravelOpenApi\PostProcessors\OperationIdProcessor;
use Bambamboole\LaravelOpenApi\PostProcessors\SortComponentsProcessor;
use Bambamboole\LaravelOpenApi\PostProcessors\TokenScopeProcessor;
use Illuminate\Support\Arr;
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
        $generator->getProcessorPipeline()->insert(new AddMetaInfoProcessor($config), fn () => 1);
        $generator->getProcessorPipeline()->remove(OperationId::class);
        $generator->getProcessorPipeline()->add(new OperationIdProcessor);
        $generator->getProcessorPipeline()->add(new TokenScopeProcessor);
        $generator->getProcessorPipeline()->add(new FeatureFlagProcessor(Arr::get($config, 'feature_flags.description_prefix', "This endpoint is only available if the feature flag `{flag}` is enabled.\n\n")));
        $generator->getProcessorPipeline()->add(new ValidationResponseStatusCodeProcessor(Arr::get($config, 'validation_status_code', 422)));
        $generator->getProcessorPipeline()->add(new SortComponentsProcessor);
        if (Arr::get($config, 'deprecation_filter.enabled', false)) {
            $generator->getProcessorPipeline()->add(new FilterDeprecationsProcessor(Arr::get($config, 'deprecation_filter.months_before_removal', 6)));
        }

        $analyzer = new ReflectionAnalyser([new DocBlockAnnotationFactory, new AttributeAnnotationFactory]);

        return $generator
            ->setVersion($config['oas_version'] ?? '3.1.0')
            ->setAnalyser($analyzer);
    }
}
