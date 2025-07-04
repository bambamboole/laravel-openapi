<?php declare(strict_types=1);

namespace Bambamboole\LaravelOpenApi\Tests\Fixtures\Put;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'PutResource',
    required: ['id', 'created_at', 'updated_at'],
    properties: [
        new OA\Property(property: 'id', type: 'int'),
        new OA\Property(property: 'created_at', type: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'date-time'),
    ],
    type: 'object',
    additionalProperties: false,
)]
class PutResource {}
