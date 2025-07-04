<?php declare(strict_types=1);

namespace Bambamboole\LaravelOpenApi\Tests\Fixtures\Patch;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'PatchResource',
    required: ['id', 'created_at', 'updated_at'],
    properties: [
        new OA\Property(property: 'id', type: 'int'),
        new OA\Property(property: 'created_at', type: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'date-time'),
    ],
    type: 'object',
    additionalProperties: false,
)]
class PatchResource {}
