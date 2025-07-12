<?php declare(strict_types=1);

use Bambamboole\LaravelOpenApi\AttributeFactory;
use Bambamboole\LaravelOpenApi\Tests\TestClasses\Http\Requests\CreateTestModelRequest;
use Illuminate\Validation\Rules\Enum;

it('can extract validation rules from a form request', function () {
    $request = CreateTestModelRequest::class;

    $rules = AttributeFactory::extractValidationInfo($request);

    expect($rules)
        ->toHaveKeys(['name', 'status'])
        ->and($rules['status'])
        ->toBeInstanceOf(Enum::class);
});
