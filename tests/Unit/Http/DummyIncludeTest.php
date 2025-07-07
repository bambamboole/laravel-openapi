<?php declare(strict_types=1);

namespace Bambamboole\LaravelOpenApi\Tests\Unit\Http;

use Bambamboole\LaravelOpenApi\Http\DummyInclude;
use PHPUnit\Framework\TestCase;
use Spatie\QueryBuilder\AllowedInclude;

class DummyIncludeTest extends TestCase
{
    public function test_it_returns_an_allowed_include(): void
    {
        $include = DummyInclude::make('foo')->first();

        self::assertInstanceOf(AllowedInclude::class, $include);
        self::assertEquals('foo', $include->getName());
        self::assertTrue($include->isForInclude('foo'));
    }
}
