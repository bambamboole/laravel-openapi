<?php declare(strict_types=1);

use Bambamboole\LaravelOpenApi\QueryBuilder;
use Bambamboole\LaravelOpenApi\Tests\TestCase;
use Bambamboole\LaravelOpenApi\Tests\TestClasses\Models\TestModel;
use Illuminate\Http\Request;

uses(TestCase::class)->in(__DIR__.'/Feature');

function createQueryFromFilterRequest(array $filters, ?string $model = null): QueryBuilder
{
    $model ??= TestModel::class;

    $request = new Request([
        'filter' => $filters,
    ]);

    return QueryBuilder::for($model, $request);
}

function fixture(string $name): string
{
    return __DIR__.'/Fixtures/'.ltrim($name, '/');
}
