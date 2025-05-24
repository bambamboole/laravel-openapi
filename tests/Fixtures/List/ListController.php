<?php declare(strict_types=1);

namespace Bambamboole\LaravelOpenApi\Tests\Fixtures\List;

use Bambamboole\LaravelOpenApi\Attributes\FilterParameter;
use Bambamboole\LaravelOpenApi\Attributes\FilterProperty;
use Bambamboole\LaravelOpenApi\Attributes\ListEndpoint;

class ListController
{
    #[ListEndpoint(
        path: '/api/list',
        resource: ListResource::class,
        description: 'List resources',
        maxPageSize: 1337,
        includes: ['foo', 'bar'],
        parameters: [
            new FilterParameter([
                new FilterProperty(name: 'id', type: 'integer'),
                new FilterProperty(name: 'status', enum: StatusEnum::class),
                new FilterProperty(name: 'name', type: 'string', example: 'something'),
                new FilterCollection,
            ]),
        ],
    )]
    public function index(): ListResource
    {
        return new ListResource;
    }
}
