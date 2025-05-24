<?php declare(strict_types=1);

namespace Bambamboole\LaravelOpenApi\Attributes;

use Illuminate\Support\Arr;
use OpenApi\Annotations\Parameter;
use OpenApi\Attributes\Attachable;
use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\Schema;
use OpenApi\Attributes\XmlContent;
use OpenApi\Generator;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::TARGET_PROPERTY | \Attribute::TARGET_PARAMETER | \Attribute::IS_REPEATABLE)]
class FilterParameter extends Parameter
{
    public function __construct(
        array $filters = [],
        ?bool $deprecated = null,
        ?bool $allowEmptyValue = null,
        string|object|null $ref = null,
        ?array $examples = null,
        array|JsonContent|XmlContent|Attachable|null $content = null,
        ?bool $allowReserved = null,
        // annotation
        ?array $x = null,
        ?array $attachables = null,
    ) {
        $filters = collect($filters)
            ->flatMap(fn (mixed $f) => $f instanceof FilterPropertyCollection ? $f->getFilterProperties() : Arr::wrap($f))
            ->flatMap(fn (FilterProperty $f) => Arr::wrap($f->toProperty()))
            ->all();

        $schema = new Schema(
            properties: $filters,
            type: 'object',
        );

        parent::__construct([
            'parameter' => Generator::UNDEFINED,
            'name' => 'filter',
            'description' => 'The filter parameter is used to filter the results of the given endpoint',
            'in' => 'query',
            'required' => false,
            'deprecated' => $deprecated ?? Generator::UNDEFINED,
            'allowEmptyValue' => $allowEmptyValue ?? Generator::UNDEFINED,
            'ref' => $ref ?? Generator::UNDEFINED,
            'example' => Generator::UNDEFINED,
            'style' => 'deepObject',
            'explode' => Generator::UNDEFINED,
            'allowReserved' => $allowReserved ?? Generator::UNDEFINED,
            'spaceDelimited' => Generator::UNDEFINED,
            'pipeDelimited' => Generator::UNDEFINED,
            'x' => $x ?? Generator::UNDEFINED,
            'value' => $this->combine($schema, $examples, $content, $attachables),
        ]);
    }
}
