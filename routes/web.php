<?php declare(strict_types=1);

use Bambamboole\LaravelOpenApi\Http\ApiDocsController;
use Illuminate\Support\Facades\Route;

if (config('openapi.docs.enabled') === false) {
    return;
}

Route::middleware(array_merge(
    ['web', 'auth'],
    config('openapi.docs.middlewares', [])
))->group(function () {
    Route::get('/api-docs/assets/{asset}', [ApiDocsController::class, 'assets'])->name('openapi.docs.assets');
    Route::get('/api-docs/schemas/{schema}', [ApiDocsController::class, 'schema'])->name('openapi.schema');
    Route::get('/api-docs/{schema?}', [ApiDocsController::class, 'docs'])->name('openapi.docs');
});
