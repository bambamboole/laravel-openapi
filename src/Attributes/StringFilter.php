<?php declare(strict_types=1);

namespace Bambamboole\LaravelOpenApi\Attributes;

use Bambamboole\LaravelOpenApi\Enum\FilterOperator;

#[\Attribute]
class StringFilter extends FilterProperty
{
    public function __construct(
        public string $name,
        public ?string $type = 'string',
        public array $operators = [
            FilterOperator::EQUALS,
            FilterOperator::NOT_EQUALS,
            FilterOperator::IN,
            FilterOperator::NOT_IN,
            FilterOperator::CONTAINS,
            FilterOperator::NOT_CONTAINS,
            FilterOperator::STARTS_WITH,
            FilterOperator::ENDS_WITH,
        ],
    ) {
        parent::__construct(
            name: $name,
            type: $type,
            operators: $operators,
        );
    }
}
