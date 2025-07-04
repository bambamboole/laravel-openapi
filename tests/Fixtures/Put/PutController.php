<?php declare(strict_types=1);

namespace Bambamboole\LaravelOpenApi\Tests\Fixtures\Put;

use Bambamboole\LaravelOpenApi\Attributes\PutEndpoint;

class PutController
{
    #[PutEndpoint(
        path: '/api/put/{id}',
        request: PutRequest::class,
        resource: PutResource::class,
        description: 'put resource',
    )]
    public function update(): PutResource
    {
        return new PutResource;
    }
}
