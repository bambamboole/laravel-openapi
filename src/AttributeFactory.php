<?php declare(strict_types=1);

namespace Bambamboole\LaravelOpenApi;

use OpenApi\Attributes\Items;
use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\Parameter;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Response;
use OpenApi\Attributes\Schema;
use ReflectionClass;
use ReflectionMethod;

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

    public static function createValidationResponse(string $request): Response
    {
        $rules = array_keys(self::extractValidationRules($request));
        $rules = empty($rules) ? ['email', 'street'] : $rules;
        $firstKey = $rules[0] ?? 'email';

        return new Response(
            response: '422',
            description: 'Failed validation',
            content: new JsonContent(
                properties: [
                    new Property('message', type: 'string',
                        example: 'The '.$firstKey.' is required',
                    ),
                    new Property('errors', type: 'object',
                        example: [
                            $rules[0] => sprintf('The %s field is required', $rules[0]),
                            $rules[1] => sprintf('The %s field is required', $rules[1]),
                        ]
                    ),
                ],
            )
        );
    }

    private static function extractValidationRules(string $requestClass): array
    {
        try {
            if (! class_exists($requestClass)) {
                return [];
            }

            $reflection = new ReflectionClass($requestClass);

            if (! $reflection->hasMethod('rules')) {
                return [];
            }

            $rulesMethod = new ReflectionMethod($requestClass, 'rules');
            $rules = $rulesMethod->invoke($reflection->newInstanceWithoutConstructor());

            // Get the first two rules
            return array_slice($rules, 0, 2, true);
        } catch (\Throwable) {
            // Silently fail and return empty array
            return [];
        }
    }
}
