<?php declare(strict_types=1);

use Bambamboole\LaravelOpenApi\Http\DummyInclude;
use Spatie\QueryBuilder\AllowedInclude;

it('returns an allowed include', function () {
    $include = DummyInclude::make('foo')->first();

    expect($include)
        ->toBeInstanceOf(AllowedInclude::class)
        ->and($include->getName())->toBe('foo')
        ->and($include->isForInclude('foo'))->toBeTrue();
});
