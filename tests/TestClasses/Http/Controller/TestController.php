<?php declare(strict_types=1);

namespace Bambamboole\LaravelOpenApi\Tests\TestClasses\Http\Controller;

use Bambamboole\LaravelOpenApi\Attributes\ActionEndpoint;
use Bambamboole\LaravelOpenApi\Attributes\DeleteEndpoint;
use Bambamboole\LaravelOpenApi\Attributes\FilterParameter;
use Bambamboole\LaravelOpenApi\Attributes\GetEndpoint;
use Bambamboole\LaravelOpenApi\Attributes\IdFilter;
use Bambamboole\LaravelOpenApi\Attributes\ListEndpoint;
use Bambamboole\LaravelOpenApi\Attributes\PatchEndpoint;
use Bambamboole\LaravelOpenApi\Attributes\PostEndpoint;
use Bambamboole\LaravelOpenApi\Attributes\StringFilter;
use Bambamboole\LaravelOpenApi\Http\Filters\QueryFilter;
use Bambamboole\LaravelOpenApi\QueryBuilder;
use Bambamboole\LaravelOpenApi\Tests\TestClasses\Http\Filter\FilterCollection;
use Bambamboole\LaravelOpenApi\Tests\TestClasses\Http\Requests\CreateTestModelRequest;
use Bambamboole\LaravelOpenApi\Tests\TestClasses\Http\Requests\UpdateTestModelRequest;
use Bambamboole\LaravelOpenApi\Tests\TestClasses\Http\Resources\TestResource;
use Bambamboole\LaravelOpenApi\Tests\TestClasses\Models\TestModel;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class TestController
{
    #[ListEndpoint(
        path: '/api/v1/test-models',
        resource: TestResource::class,
        description: 'List test resources',
        includes: ['test model'],
        parameters: [
            new FilterParameter([
                new IdFilter,
                new StringFilter(name: 'name'),
                new StringFilter(name: 'status'),
                new FilterCollection,
            ]),
        ],
        maxPageSize: 1337,
        featureFlag: 'beta-users',
        scopes: 'test-models:read',
    )]
    public function index(): AnonymousResourceCollection
    {
        return TestResource::collection(
            QueryBuilder::for(TestModel::class)
                ->allowedFilters(
                    QueryFilter::identifier(),
                    QueryFilter::string('name'),
                    QueryFilter::string('status'),
                    QueryFilter::date('updated_at'),
                    QueryFilter::date('created_at'),
                )
                ->get()
        );
    }

    #[GetEndpoint(
        path: '/api/v1/test-models/{id}',
        resource: TestResource::class,
        description: 'get test resource',
        includes: ['test resource'],
    )]
    public function show(int $id): TestResource
    {
        return new TestResource(TestModel::query()->findOrFail($id));
    }

    #[PostEndpoint(
        path: '/api/v1/test-models',
        request: CreateTestModelRequest::class,
        resource: TestResource::class,
        description: 'update test resource',
    )]
    public function create(CreateTestModelRequest $request): TestResource
    {
        $testModel = TestModel::query()->create($request->validated());

        return new TestResource($testModel);
    }

    #[PatchEndpoint(
        path: '/api/v1/test-models/{id}',
        request: UpdateTestModelRequest::class,
        resource: TestResource::class,
        description: 'update test resource',
    )]
    public function update(UpdateTestModelRequest $request, int $id): TestResource
    {
        $testModel = TestModel::query()->findOrFail($id);
        $testModel->update($request->validated());

        return new TestResource($testModel);
    }

    #[DeleteEndpoint(
        path: '/api/v1/test-models/{id}',
        description: 'delete test resource',
    )]
    public function delete(int $id): Response
    {
        $testModel = TestModel::query()->findOrFail($id);
        $testModel->delete();

        return response()->noContent();
    }

    #[ActionEndpoint(
        path: '/api/v1/test-models/{id}/actions/test',
        description: 'Execute test action on test resource',
    )]
    public function testAction(int $id): Response
    {
        return response()->noContent();
    }
}
