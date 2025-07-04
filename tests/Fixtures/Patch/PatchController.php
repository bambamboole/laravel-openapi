<?php declare(strict_types=1);

namespace Bambamboole\LaravelOpenApi\Tests\Fixtures\Patch;

use Bambamboole\LaravelOpenApi\Attributes\PatchEndpoint;

class PatchController
{
    #[PatchEndpoint(
        path: '/api/patch/{id}',
        request: PatchRequest::class,
        resource: PatchResource::class,
        description: 'patch resource',
    )]
    public function update(): PatchResource
    {
        return new PatchResource;
    }
}
