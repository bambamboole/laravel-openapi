<?php declare(strict_types=1);

namespace Bambamboole\LaravelOpenApi\Attributes;

use Bambamboole\LaravelOpenApi\Enum\FilterType;
use Illuminate\Support\Arr;
use OpenApi\Attributes\Items;
use OpenApi\Attributes\Property;
use OpenApi\Generator;

#[\Attribute]
class FilterProperty
{
    public function __construct(
        public string $name,
        public ?string $description = null,
        public bool $multiple = true,
        public ?string $type = null,
        public ?string $example = null,
        public FilterType $filterType = FilterType::EXACT,
        public array $operators = [],
        public array|string|null $enum = null,
        public ?string $default = null,
        public bool $partial = false,
    ) {}

    public function toProperty(): array|Property
    {
        return match ($this->multiple) {
            true => new Property(
                property: $this->name,
                description: $this->description(),
                type: 'array',
                items: new Items(type: $this->type, enum: $this->enum, example: $this->example())
            ),
            false => new Property(
                property: $this->name,
                description: $this->description(),
                enum: $this->enum,
                example: $this->example(),
            ),
        };
    }

    private function description(): ?string
    {
        if ($this->description) {
            return $this->description;
        }
        $description = 'Filter for '.$this->name.' property of the given resource.';
        if ($this->filterType === FilterType::OPERATOR) {
            $description .= 'The filter is applied using the operators: '.Arr::join($this->operators, ', ', ' and ').'.';
        }
        if ($this->default) {
            $description .= ' Default value for this filter is "'.$this->default.'".';
        }
        if ($this->partial) {
            $description .= ' Use "~" to match also partially.';
        }

        return $description;
    }

    private function example()
    {
        if ($this->example) {
            return $this->example;
        }

        return match ($this->type) {
            'integer' => 12,
            default => Generator::UNDEFINED
        };
    }
}
