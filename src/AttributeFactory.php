<?php declare(strict_types=1);

namespace Bambamboole\LaravelOpenApi;

use OpenApi\Attributes\Items;
use OpenApi\Attributes\Parameter;
use OpenApi\Attributes\Schema;

class AttributeFactory
{
    public static function createIncludeParameter(array $includes): Parameter
    {
        return new Parameter(
            name: 'include',
            in: 'query',
            required: false,
            schema: new Schema(
                type: 'array',
                items: new Items(type: 'string', enum: $includes),
            ),
            explode: false,
        );
    }

    public static function createFilterParameter(array $filters): Parameter
    {
        return new Parameter(
            name: 'filter',
            in: 'query',
            required: false,
            schema: new Schema(
                properties: $filters,
                type: 'object',
            ),
            style: 'deepObject',
        );
    }

    public static function createMissingPathParameters(string $path, array $parameters): array
    {
        preg_match_all('/{([^}]+)}/', $path, $matches);
        $missing = [];
        foreach ($matches[1] as $match) {
            $hasParam = count(
                array_filter($parameters ?? [], fn (Parameter $parameter) => $parameter->name === $match)
            ) > 0;
            if ($hasParam) {
                continue;
            }
            $missing[] = new Parameter(
                name: $match,
                in: 'path',
                required: true,
                schema: new Schema(type: 'string')
            );
        }

        return $missing;
    }

    public static function createPaginationParameters(int $defaultPageSize, int $maxPageSize): array
    {
        return [
            new Parameter(
                name: 'page',
                description: 'Page number.',
                in: 'query',
                required: false,
                schema: new Schema(type: 'integer', example: 1),
            ),
            new Parameter(
                name: 'per_page',
                description: sprintf('Number of items per page. Default: %d, Max: %d', $defaultPageSize, $maxPageSize),
                in: 'query',
                required: false,
                schema: new Schema(type: 'integer', example: $defaultPageSize),
            ),
        ];
    }
}
