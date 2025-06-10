<?php declare(strict_types=1);

namespace Bambamboole\LaravelOpenApi\Tests\Fixtures\List;

use Bambamboole\LaravelOpenApi\Attributes\DateFilter;
use Bambamboole\LaravelOpenApi\Attributes\FilterParameter;
use Bambamboole\LaravelOpenApi\Attributes\IdFilter;
use Bambamboole\LaravelOpenApi\Attributes\ListEndpoint;
use Bambamboole\LaravelOpenApi\Attributes\StringFilter;

class ListController
{
    #[ListEndpoint(
        path: '/api/list',
        resource: ListResource::class,
        description: 'List resources',
        includes: ['foo', 'bar'],
        parameters: [
            new FilterParameter([
                new IdFilter,
                new StringFilter(name: 'status'),
                new StringFilter(name: 'name'),
                new DateFilter(name: 'created_at'),
                new FilterCollection,
            ]),
        ],
        maxPageSize: 1337,
    )]
    public function index(): ListResource
    {
        return new ListResource;
    }
}
