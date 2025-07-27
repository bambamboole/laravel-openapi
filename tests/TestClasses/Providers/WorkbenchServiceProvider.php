<?php declare(strict_types=1);

namespace Bambamboole\LaravelOpenApi\Tests\TestClasses\Providers;

use Illuminate\Support\ServiceProvider;

class WorkbenchServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        config(['mkdocs.paths' => [dirname(__DIR__, 3).'/src/Commands']]);
    }
}
