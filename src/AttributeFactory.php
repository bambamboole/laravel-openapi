<?php declare(strict_types=1);

namespace Bambamboole\LaravelOpenApi;

use Bambamboole\LaravelOpenApi\Enum\PaginationType;
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

    public static function createPaginationParameters(int $defaultPageSize, int $maxPageSize, PaginationType $type): array
    {
        $params = [
            new Parameter(
                name: 'per_page',
                description: sprintf('Number of items per page. Default: %d, Max: %d', $defaultPageSize, $maxPageSize),
                in: 'query',
                required: false,
                schema: new Schema(type: 'integer', example: $defaultPageSize),
            ),
        ];
        if ($type === PaginationType::CURSOR) {
            $params[] = new Parameter(
                name: 'cursor',
                description: 'The cursor to use for the paginated call.',
                in: 'query',
                required: false,
                schema: new Schema(type: 'string', example: 'eyJpZCI6MTUsIl9wb2ludHNUb05leHRJdGVtcyI6dHJ1ZX0'),
            );
        }
        if ($type === PaginationType::SIMPLE || $type === PaginationType::TABLE) {
            $params[] = new Parameter(
                name: 'page',
                description: 'Page number.',
                in: 'query',
                required: false,
                schema: new Schema(type: 'integer', example: 1),
            );
        }

        return $params;
    }

    public static function createValidationResponse(string|array $request): Response
    {
        if (is_string($request)) {
            $validationData = self::extractValidationInfo($request);
            $keys = $validationData['keys'];
            $rules = $validationData['rules'];

            // Generate realistic error messages
            $errorMessages = [];
            foreach ($keys as $key) {
                $rule = $rules[$key] ?? 'required';
                $errorMessages[$key] = [self::generateLaravelLikeValidationMessage($key, $rule)];
            }
        } elseif (is_array($request)) {
            $errorMessages = [];

            // Check if array is associative (field => message)
            if (array_keys($request) !== range(0, count($request) - 1)) {
                // Associative array - use keys as field names and values as custom messages
                foreach ($request as $field => $message) {
                    $errorMessages[$field] = [is_array($message) ? $message[0] : $message];
                }
            } else {
                // Indexed array - use values as field names and generate messages
                foreach ($request as $field) {
                    $errorMessages[$field] = [self::generateLaravelLikeValidationMessage($field, 'required')];
                }
            }
        }

        // Fallback if no messages could be generated
        if (empty($errorMessages)) {
            $errorMessages = [
                'email' => ['The email field is required.'],
                'street' => ['The street field is required.'],
            ];
        }

        return new Response(
            response: '422',
            description: 'Failed validation',
            content: new JsonContent(
                properties: [
                    new Property('message', type: 'string',
                        example: reset($errorMessages)[0] ?? 'The validation failed.',
                    ),
                    new Property('errors', type: 'object',
                        example: $errorMessages,
                    ),
                ],
            )
        );
    }

    private static function generateLaravelLikeValidationMessage(string $field, string|array $rule): string
    {
        // Convert array rules to string for parsing
        if (is_array($rule)) {
            $rule = array_filter($rule, fn ($v) => is_string($v));
            $rule = implode('|', array_map(function ($item) {
                return is_array($item) ? implode(':', $item) : $item;
            }, $rule));
        }

        // Parse the rules
        $rulesList = explode('|', $rule);
        $formattedField = str_replace('_', ' ', $field);

        // Generate Laravel-like messages for common rules
        if (in_array('required', $rulesList)) {
            return "The {$formattedField} field is required.";
        }

        foreach ($rulesList as $singleRule) {
            if ($singleRule === 'email') {
                return "The {$formattedField} must be a valid email address.";
            }

            if (str_starts_with($singleRule, 'min:')) {
                $min = substr($singleRule, 4);

                return "The {$formattedField} must be at least {$min} characters.";
            }

            if (str_starts_with($singleRule, 'max:')) {
                $max = substr($singleRule, 4);

                return "The {$formattedField} may not be greater than {$max} characters.";
            }

            if ($singleRule === 'numeric') {
                return "The {$formattedField} must be a number.";
            }

            if ($singleRule === 'integer') {
                return "The {$formattedField} must be an integer.";
            }

            if ($singleRule === 'date') {
                return "The {$formattedField} is not a valid date.";
            }

            if ($singleRule === 'boolean') {
                return "The {$formattedField} field must be true or false.";
            }

            if (str_starts_with($singleRule, 'in:')) {
                $values = substr($singleRule, 3);

                return "The selected {$formattedField} is invalid.";
            }

            if (str_starts_with($singleRule, 'regex:')) {
                return "The {$formattedField} format is invalid.";
            }
        }

        // Default message
        return "The {$formattedField} is invalid.";
    }

    private static function extractValidationInfo(string $requestClass): array
    {
        try {
            if (! class_exists($requestClass)) {
                return ['keys' => [], 'rules' => []];
            }

            $reflection = new ReflectionClass($requestClass);

            if (! $reflection->hasMethod('rules')) {
                return ['keys' => [], 'rules' => []];
            }

            $rulesMethod = new ReflectionMethod($requestClass, 'rules');
            $rules = $rulesMethod->invoke($reflection->newInstanceWithoutConstructor());

            // Get the first two rules
            $slicedRules = array_slice($rules, 0, 2, true);

            return [
                'keys' => array_keys($slicedRules),
                'rules' => $slicedRules,
            ];
        } catch (\Throwable) {
            // Silently fail and return empty array
            return ['keys' => [], 'rules' => []];
        }
    }
}
