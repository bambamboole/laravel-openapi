<?php declare(strict_types=1);

namespace Bambamboole\LaravelOpenApi\Tests\Fixtures\List;

use Bambamboole\LaravelOpenApi\Attributes\FilterProperty;
use Bambamboole\LaravelOpenApi\Attributes\FilterPropertyCollection;

class FilterCollection implements FilterPropertyCollection
{
    public function getFilterProperties(): array
    {
        return [
            new FilterProperty(name: 'from_collection_1', type: 'integer'),
            new FilterProperty(name: 'from_collection_2', type: 'string', example: 'Test'),
        ];
    }
}
