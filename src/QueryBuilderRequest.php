<?php declare(strict_types=1);

namespace Bambamboole\LaravelOpenApi;

use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class QueryBuilderRequest extends \Spatie\QueryBuilder\QueryBuilderRequest
{
    public function filters(): Collection
    {
        $filterParameterName = config('query-builder.parameters.filter', 'filter');

        $filterParts = $this->getRequestData($filterParameterName, []);

        if (is_string($filterParts)) {
            // If the filter is a JSON string, decode it. This is needed to support SwaggerUI properly
            if (! is_array($filterParts = json_decode($filterParts, true))) {
                return collect();
            }
            if (isset($filterParts['key'])) {
                // If the filter is a single filter object, convert it to an array
                $filterParts = [$filterParts];
            }
        }

        try {
            return collect($filterParts)->mapWithKeys(fn ($value) => [$value['key'] => [
                'operator' => $value['op'],
                'value' => $this->getFilterValue($value['value']),
            ]]);
        } catch (\Throwable) {
            throw ValidationException::withMessages(['filter' => 'Invalid filter format.']);
        }
    }
}
