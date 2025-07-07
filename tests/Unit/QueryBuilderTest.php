<?php declare(strict_types=1);

namespace Bambamboole\LaravelOpenApi\Tests\Unit;

use Bambamboole\LaravelOpenApi\QueryBuilder;
use Bambamboole\LaravelOpenApi\QueryBuilderRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;

class QueryBuilderTest extends TestCase
{
    public function test_it_uses_our_custom_query_builder_request(): void
    {
        $qb = new QueryBuilder(
            $this->createMock(Builder::class),
            Request::capture(),
        );

        self::assertInstanceOf(QueryBuilderRequest::class, $qb->getRequest());
    }
}
