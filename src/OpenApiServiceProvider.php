<?php declare(strict_types=1);

namespace Bambamboole\LaravelOpenApi;

use Illuminate\Support\ServiceProvider;

class OpenApiServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/openapi.php', 'openapi');
    }
}
