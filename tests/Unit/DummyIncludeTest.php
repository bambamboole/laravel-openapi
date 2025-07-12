<?php declare(strict_types=1);

use Bambamboole\LaravelOpenApi\Http\DummyInclude;
use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\AllowedInclude;

it('returns an allowed include', function () {
    $include = DummyInclude::make('foo')->first();

    expect($include)
        ->toBeInstanceOf(AllowedInclude::class)
        ->and($include->getName())->toBe('foo')
        ->and($include->isForInclude('foo'))->toBeTrue();
});

it('is invokable and does nothing', function () {
    $include = new DummyInclude;

    expect(method_exists($include, '__invoke'))->toBeTrue();

    $include($this->createMock(Builder::class), 'foo');
});
