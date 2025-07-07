<?php declare(strict_types=1);

use Bambamboole\LaravelOpenApi\Http\ApiDocsController;
use Illuminate\Support\Facades\Route;

if (config('openapi.docs.enabled') === false) {
    return;
}
Route::middleware(array_merge(
    ['web', 'auth'],
    config('openapi.docs.middlewares', [])
))
    ->prefix(config('openapi.docs.prefix', 'api-docs'))
    ->group(function () {
        Route::get('/assets/{asset}', [ApiDocsController::class, 'assets'])->name('openapi.docs.assets');
        Route::get('/schemas/{schema}', [ApiDocsController::class, 'schema'])->name('openapi.schema');
        Route::get('/{schema?}', [ApiDocsController::class, 'docs'])->name('openapi.docs');
    });
