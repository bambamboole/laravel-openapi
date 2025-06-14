<?php declare(strict_types=1);

namespace Bambamboole\LaravelOpenApi\Attributes;

use Bambamboole\LaravelOpenApi\Enum\FilterOperator;

#[\Attribute]
class IdFilter extends FilterProperty
{
    public function __construct(
        public string $name = 'id',
        public ?string $type = 'integer',
        public array $operators = [
            FilterOperator::EQUALS,
            FilterOperator::NOT_EQUALS,
            FilterOperator::IN,
            FilterOperator::NOT_IN,
        ],
    ) {
        parent::__construct(
            name: $name,
            type: $type,
            operators: $operators,
        );
    }
}
