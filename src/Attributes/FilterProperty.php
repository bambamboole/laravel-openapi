<?php declare(strict_types=1);

namespace Bambamboole\LaravelOpenApi\Attributes;

use Bambamboole\LaravelOpenApi\Enum\FilterType;
use OpenApi\Attributes\Items;
use OpenApi\Attributes\Property;

#[\Attribute]
class FilterProperty
{
    public function __construct(
        public string            $name,
        public string            $description = '',
        public bool              $multiple = true,
        public ?string           $type = null,
        public ?string           $example = null,
        public FilterType        $filterType = FilterType::EXACT,
        public array             $operators = ['>=', '<=', '>', '<', '='],
        public array|string|null $enum = null,
    )
    {
    }

    public function toProperty(): Property
    {
        return match ($this->multiple) {
            true => new Property(
                property: $this->name,
                description: $this->description,
                type: 'array',
                items: new Items(type: $this->type, example: $this->example(), enum: $this->enum)
            ),
            false => new Property(
                property: $this->name,
                description: $this->description,
                enum: $this->enum,
                example: $this->example(),
            ),
        };
    }

    private function example()
    {
        if ($this->example) {
            return $this->example;
        }

        return match ($this->type) {
            'integer' => 12,
            'string' => 'foobar',
            default => null
        };
    }
}
