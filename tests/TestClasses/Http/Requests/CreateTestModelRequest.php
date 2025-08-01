<?php declare(strict_types=1);

namespace Bambamboole\LaravelOpenApi\Tests\TestClasses\Http\Requests;

use Bambamboole\LaravelOpenApi\Tests\TestClasses\Enum\StatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'CreateTestModelRequest',
    properties: [
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'status', enum: StatusEnum::class),
    ],
    type: 'object',
    additionalProperties: false,
)]
class CreateTestModelRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'string',
            'status' => Rule::enum(StatusEnum::class),
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
