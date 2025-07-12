<?php declare(strict_types=1);

use Bambamboole\LaravelOpenApi\QueryBuilder;
use Bambamboole\LaravelOpenApi\QueryBuilderRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

it('uses our custom query builder request', function () {
    $qb = new QueryBuilder(
        mock(Builder::class),
        Request::capture(),
    );

    expect($qb->getRequest())->toBeInstanceOf(QueryBuilderRequest::class);
});
