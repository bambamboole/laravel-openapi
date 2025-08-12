<?php declare(strict_types=1);

namespace Bambamboole\LaravelOpenApi\Attributes;

interface FilterSpecCollection
{
    public function getFilterSpecification(): array;
}
