<?php declare(strict_types=1);

namespace Bambamboole\LaravelOpenApi\Tests;

use Bambamboole\LaravelOpenApi\OpenApiServiceProvider;
use Bambamboole\LaravelOpenApi\QueryBuilderRequest;
use Bambamboole\LaravelOpenApi\Tests\TestClasses\Http\Controller\TestController;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase($this->app);

        QueryBuilderRequest::resetDelimiters();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Bambamboole\\LaravelOpenApi\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function setUpDatabase(Application $app): void
    {
        $app['db']->connection()->getSchemaBuilder()->create('test_models', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('status');
            $table->timestamps();
        });

        $app['router']->get('/api/v1/test-models', [TestController::class, 'index']);
        $app['router']->get('/api/v1/test-models/{id}', [TestController::class, 'show']);
        $app['router']->post('/api/v1/test-models', [TestController::class, 'create']);
        $app['router']->patch('/api/v1/test-models/{id}', [TestController::class, 'update']);
        $app['router']->delete('/api/v1/test-models/{id}', [TestController::class, 'delete']);
    }

    protected function getPackageProviders($app): array
    {
        return [
            OpenApiServiceProvider::class,
        ];
    }
}
