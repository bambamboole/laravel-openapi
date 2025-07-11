<?php declare(strict_types=1);

namespace Bambamboole\LaravelOpenApi\Http\Filters;

use Bambamboole\LaravelOpenApi\Enum\FilterOperator;
use Spatie\QueryBuilder\AllowedFilter;

class QueryFilter
{
    public function __construct(
        public string $name,
        public array $allowedOperators = [FilterOperator::EQUALS],
        public ?string $internalName = null,
    ) {}

    public static function identifier(string $name = 'id', ?string $internalName = null): AllowedFilter
    {
        return AllowedFilter::custom(
            $name,
            new CustomOperatorFilter([
                FilterOperator::EQUALS,
                FilterOperator::NOT_EQUALS,
                FilterOperator::IN,
                FilterOperator::NOT_IN,
            ]),
            $internalName,
        );
    }

    public static function date(string $name, ?string $internalName = null): AllowedFilter
    {
        return AllowedFilter::custom(
            $name,
            new CustomOperatorFilter([
                FilterOperator::EQUALS,
                FilterOperator::NOT_EQUALS,
                FilterOperator::LESS_THAN,
                FilterOperator::LESS_THAN_OR_EQUALS,
                FilterOperator::GREATER_THAN,
                FilterOperator::GREATER_THAN_OR_EQUALS,
            ]),
            $internalName,
        );
    }

    public static function string(string $name, ?string $internalName = null, ?string $enum = null): AllowedFilter
    {
        return AllowedFilter::custom(
            $name,
            new CustomOperatorFilter([
                FilterOperator::EQUALS,
                FilterOperator::NOT_EQUALS,
                FilterOperator::IN,
                FilterOperator::NOT_IN,
                FilterOperator::CONTAINS,
                FilterOperator::STARTS_WITH,
                FilterOperator::ENDS_WITH,
            ], $enum),
            $internalName,
        );
    }

    public static function number(string $name, ?string $internalName = null): AllowedFilter
    {
        return AllowedFilter::custom(
            $name,
            new CustomOperatorFilter([
                FilterOperator::EQUALS,
                FilterOperator::NOT_EQUALS,
                FilterOperator::LESS_THAN,
                FilterOperator::LESS_THAN_OR_EQUALS,
                FilterOperator::GREATER_THAN,
                FilterOperator::GREATER_THAN_OR_EQUALS,
            ]),
            $internalName,
        );
    }

    public static function boolean(string $name, ?string $internalName = null): AllowedFilter
    {
        return AllowedFilter::custom(
            $name,
            new CustomOperatorFilter([
                FilterOperator::EQUALS,
                FilterOperator::NOT_EQUALS,
            ]),
            $internalName,
        );
    }
}
