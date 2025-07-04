<?php declare(strict_types=1);

namespace Bambamboole\LaravelOpenApi\Tests\Fixtures\Get;

use Bambamboole\LaravelOpenApi\Attributes\GetEndpoint;

class GetController
{
    #[GetEndpoint(
        path: '/api/list',
        resource: GetResource::class,
        description: 'get resource',
        includes: ['foo', 'bar'],
    )]
    public function index(): GetResource
    {
        return new GetResource;
    }
}
