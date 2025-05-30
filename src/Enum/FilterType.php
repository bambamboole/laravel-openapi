<?php declare(strict_types=1);

namespace Bambamboole\LaravelOpenApi\Enum;

enum FilterType: string
{
    case EXACT = 'exact';
    case PARTIAL = 'partial';
    case BEGINS_WITH = 'begins_with';
    case ENDS_WITH = 'ends_with';
    case OPERATOR = 'operator';
}
