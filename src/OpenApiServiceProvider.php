<?php declare(strict_types=1);

namespace Bambamboole\LaravelOpenApi;

use Illuminate\Support\ServiceProvider;

class OpenApiServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/openapi.php', 'openapi');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'openapi');
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
    }

    public function register()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Commands\GenerateOpenApiSpecCommand::class,
            ]);
        }
    }
}
