<?php declare(strict_types=1);

namespace Bambamboole\LaravelOpenApi\Http;

use Bambamboole\LaravelOpenApi\QueryBuilderRequest;
use Illuminate\Http\Resources\Json\JsonResource;

abstract class ApiResource extends JsonResource
{
    protected function wantsToInclude(string $include): bool
    {
        return app(QueryBuilderRequest::class)->includes()->contains($include);
    }
}
