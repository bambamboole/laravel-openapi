<?php declare(strict_types=1);

namespace Bambamboole\LaravelOpenApi\Tests\Fixtures\Put;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'PutRequest',
    required: ['name', 'email'],
    properties: [
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'email', type: 'string', format: 'email'),
        new OA\Property(property: 'description', type: 'string', nullable: true),
    ],
    type: 'object',
    additionalProperties: false,
)]
class PutRequest {}
