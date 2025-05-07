<?php declare(strict_types=1);

namespace Bambamboole\LaravelOpenApi\Attributes;

interface FilterPropertyCollection
{
    public function getFilterProperties(): array;
}
