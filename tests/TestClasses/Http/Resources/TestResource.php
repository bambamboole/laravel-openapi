<?php declare(strict_types=1);

namespace Bambamboole\LaravelOpenApi\Tests\TestClasses\Http\Resources;

use Bambamboole\LaravelOpenApi\Http\ApiResource;
use Bambamboole\LaravelOpenApi\Tests\TestClasses\Enum\StatusEnum;
use Bambamboole\LaravelOpenApi\Tests\TestClasses\Models\TestModel;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'TestResource',
    required: ['id', 'name', 'created_at', 'updated_at'],
    properties: [
        new OA\Property(property: 'id', type: 'int'),
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'status', enum: StatusEnum::class),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ],
    type: 'object',
    additionalProperties: false,
)]
class TestResource extends ApiResource
{
    /** @var TestModel */
    public $resource;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'status' => $this->resource->status->value,
            'updated_at' => $this->resource->updated_at,
            'created_at' => $this->resource->created_at,
        ];
    }
}
