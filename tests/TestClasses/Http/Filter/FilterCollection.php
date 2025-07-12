<?php declare(strict_types=1);

namespace Bambamboole\LaravelOpenApi\Tests\TestClasses\Http\Filter;

use Bambamboole\LaravelOpenApi\Attributes\FilterProperty;
use Bambamboole\LaravelOpenApi\Attributes\FilterPropertyCollection;

class FilterCollection implements FilterPropertyCollection
{
    public function getFilterProperties(): array
    {
        return [
            new FilterProperty(name: 'created_at', type: 'date-time'),
            new FilterProperty(name: 'updated_at', type: 'date-time'),
        ];
    }
}
