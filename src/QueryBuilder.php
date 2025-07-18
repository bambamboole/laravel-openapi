<?php declare(strict_types=1);

namespace Bambamboole\LaravelOpenApi;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;

class QueryBuilder extends \Spatie\QueryBuilder\QueryBuilder
{
    public function __construct(
        protected EloquentBuilder|Relation $subject,
        ?Request $request = null
    ) {
        // We need to override the request initialization to use our own request
        $this->request = $request
            ? QueryBuilderRequest::fromRequest($request)
            : app(QueryBuilderRequest::class);
    }

    public function apiPaginate(): Paginator
    {
        return $this->simplePaginate(min(100, $this->request->integer('per_page', 15)));
    }

    public function getRequest(): QueryBuilderRequest
    {
        return $this->request;
    }
}
