<?php declare(strict_types=1);

namespace Bambamboole\LaravelOpenApi\Tests\Fixtures\Patch;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'PatchRequest',
    properties: [
        new OA\Property(property: 'name', type: 'string', nullable: true),
        new OA\Property(property: 'email', type: 'string', format: 'email', nullable: true),
        new OA\Property(property: 'description', type: 'string', nullable: true),
    ],
    type: 'object',
    additionalProperties: false,
)]
class PatchRequest {}
