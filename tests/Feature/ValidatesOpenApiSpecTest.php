<?php declare(strict_types=1);

use Bambamboole\LaravelOpenApi\Tests\TestClasses\Models\TestModel;
use Bambamboole\LaravelOpenApi\ValidatesOpenApiSpec;
use League\OpenAPIValidation\PSR7\Exception\NoPath;

uses(ValidatesOpenApiSpec::class);

beforeEach(function () {
    config(['openapi.schemas.default.output' => fixture('expected.yml')]);
    TestModel::factory()->count(5)->create();
});

it('validates requests against OpenAPI spec automatically: success', function () {
    $this->getJson('/api/v1/test-models')->assertOk();
});

it('validates requests against OpenAPI spec automatically:fail', function () {
    $this->expectException(NoPath::class);
    $this->getJson('/api/v1/wrong-models/1');
});
