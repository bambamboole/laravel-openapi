<?php declare(strict_types=1);

namespace Bambamboole\LaravelOpenApi\Tests\TestClasses\Http\Filter;

use Bambamboole\LaravelOpenApi\Attributes\FilterProperty;
use Bambamboole\LaravelOpenApi\Attributes\FilterSpecCollection;
use Bambamboole\LaravelOpenApi\Http\Filters\QueryBuilderFilterCollection;
use Bambamboole\LaravelOpenApi\Http\Filters\QueryFilter;

class FilterCollection implements FilterSpecCollection, QueryBuilderFilterCollection
{
    public function getFilterSpecification(): array
    {
        return [
            new FilterProperty(name: 'created_at', type: 'date-time'),
            new FilterProperty(name: 'updated_at', type: 'date-time'),
        ];
    }

    public function getFilters(): array
    {
        return [
            QueryFilter::date('updated_at'),
            QueryFilter::date('created_at'),
        ];
    }
}
