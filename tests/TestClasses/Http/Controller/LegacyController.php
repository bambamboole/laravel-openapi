<?php declare(strict_types=1);

namespace Bambamboole\LaravelOpenApi\Tests\TestClasses\Http\Controller;

use Bambamboole\LaravelOpenApi\Attributes\GetEndpoint;
use Bambamboole\LaravelOpenApi\Tests\TestClasses\Http\Resources\TestResource;
use Bambamboole\LaravelOpenApi\Tests\TestClasses\Models\TestModel;

class LegacyController
{
    #[GetEndpoint(
        path: '/api/legacy',
        resource: TestResource::class,
        description: 'deprecated legacy endpoint',
        deprecated: new \DateTimeImmutable('2024-01-01'),
    )]
    public function show(int $id): TestResource
    {
        return new TestResource(TestModel::query()->findOrFail($id));
    }
}
