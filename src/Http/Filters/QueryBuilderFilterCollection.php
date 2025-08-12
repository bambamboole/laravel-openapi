<?php declare(strict_types=1);

namespace Bambamboole\LaravelOpenApi\Http\Filters;

interface QueryBuilderFilterCollection
{
    public function getFilters(): array;
}
