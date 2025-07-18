<?php declare(strict_types=1);

namespace Bambamboole\LaravelOpenApi;

use OpenApi\Analysis;

class SortComponentsProcessor
{
    public function __invoke(Analysis $analysis)
    {
        if (is_object($analysis->openapi->components) && is_iterable($analysis->openapi->components->schemas)) {
            usort($analysis->openapi->components->schemas, function ($a, $b) {
                return strcmp($a->schema, $b->schema);
            });
        }
    }
}
