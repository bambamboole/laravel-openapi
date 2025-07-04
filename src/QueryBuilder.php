<?php declare(strict_types=1);

namespace Bambamboole\LaravelOpenApi;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\Request;

class QueryBuilder extends \Spatie\QueryBuilder\QueryBuilder
{
    public function apiPaginate(): Paginator
    {
        return $this->simplePaginate(min(100, $this->request->integer('per_page', 15)));
    }

    protected function initializeRequest(?Request $request = null): static
    {
        // We need to override the request initialization to use our own request
        $this->request = QueryBuilderRequest::fromRequest($request ?? request());

        return $this;
    }
}
