# Laravel OpenApi

[![Latest Version on Packagist](https://img.shields.io/packagist/v/bambamboole/laravel-openapi.svg?style=flat-square)](https://packagist.org/packages/bambamboole/laravel-openapi)
[![Total Downloads](https://img.shields.io/packagist/dt/bambamboole/laravel-openapi.svg?style=flat-square)](https://packagist.org/packages/bambamboole/laravel-openapi)
![GitHub Actions](https://github.com/bambamboole/laravel-openapi/actions/workflows/main.yml/badge.svg)

This package provides tools around specification, documentation and implementation, that enable engineers to build a
unified API experience.

Instead of hustling around with yaml or json files, you can use strongly-typed PHP attributes to define your
API endpoints and schemas co-located to the responsible functionality. The provided attributes play hand in hand with
the extended `QueryBuilder` from the `spatie/laravel-query-builder` package to provide a straight forward way of
implementing the specified API endpoints.

There were five main goals in mind when creating this package:

* Reduce the needed boilerplate as much as possible
* Co-locate Endpoint spec to controllers, validation spec to request classes and schema spec to resource classes
* Do not generate from implementation, so that the schema can be used in tests for validation
* Provide more or less tight guard rails around the API implementation, so that its easy to onboard new developers
* Provide a way to generate OpenAPI schema files that can be used to test against in PHPUnit, for documentation and
  client generation
* Provide a web interface to easily view and try the OpenAPI documentation

## How does it work?

Laravel OpenApi is built on the shoulders of giants, namely the packages `zircote/swagger-php` and
`spatie/laravel-query-builder` and adds some additional features on top of it.

It enables you to manage multiple openapi schema files in a single project. The default configuration can be done via
the `openapi.php` config file.

The package provides opinionated and straight forward PHP 8 attributes to define OpenAPI specifications directly in your
controller methods and request/resource classes. The package provides a set of predefined attributes for common HTTP
methods (GET, POST, PUT,
PATCH, DELETE) that automatically:

- Generate endpoint documentation with proper path parameters
- Document request bodies and validation requirements
- Define response schemas and status codes
- Handle authentication and authorization responses

These attributes extract the necessary information from your code structure, reducing duplication and keeping your API
documentation in sync with your implementation.

## Installation

You can install the package via composer.

```bash
composer require bambamboole/laravel-openapi
```

## Usage

### Resource definition

You can define your API resources using the `#[OA\Schema]` attribute. This allows you to specify the properties of your
resource, including their types and whether they are required. The example below shows how to define a simple
`SalesOrder` resource.

```php
#[OA\Schema(
    schema: 'SalesOrder',
    required: ['id', 'status', 'customer', 'created_at', 'updated_at'],
    properties: [
        new OA\Property(property: 'id', type: 'integer'),
        new OA\Property(property: 'status', ref: SalesOrderStatus::class),
        new OA\Property(property: 'customer', anyOf: [
            new OA\Schema(ref: CustomerResource::class),
            new OA\Schema(properties: [new OA\Property(property: 'id', type: 'integer')], type: 'object'),
        ]
        ),
        new OA\Property(property: 'positions', type: 'array', items: new OA\Items(ref: SalesOrderPositionResource::class), nullable: true),
        new OA\Property(property: 'created_at', type: 'datetime'),
        new OA\Property(property: 'updated_at', type: 'datetime'),
    ],
    type: 'object',
    additionalProperties: false,
)]
class SalesOrderResource extends JsonResource
{
    /** @var SalesOrder */
    public $resource;

    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'status' => $this->resource->status,
            'customer' => $this->whenLoaded('customer', fn () => new CustomerResource($this->resource->customer), ['id' => $this->resource->customer_id]),
            'positions' => $this->whenLoaded('positions', fn () => SalesOrderPositionResource::collection($this->resource->positions)),
            'created_at' => $this->resource->created_at->format(DATE_ATOM),
            'updated_at' => $this->resource->updated_at->format(DATE_ATOM),
        ];
    }
}
```

### List endpoints

You can define a list endpoint using the `#[ListEndpoint]` attribute. This allows you to specify the path, resource
class, description, and any additional parameters such as filters, sorts, and includes. The example below shows how to
define a list endpoint for sales orders.

```php
    #[ListEndpoint(
        path: '/api/v1/sales-orders',
        resource: SalesOrderResource::class,
        description: 'Paginated list of sales orders',
        includes: ['customer', 'positions'],
        parameters: [
            new IdFilter(),
            new StringFilter(name: 'documentNumber'),
            new DateFilter(name: 'documentDate'),
            /// ...
            new QuerySort(['created_at', 'updated_at']),
        ],
        tags: ['SalesOrder'],
    )]
    public function index(): AnonymousResourceCollection
    {
        $salesOrders = QueryBuilder::for(SalesOrder::class)
            ->withCount('positions')
            ->defaultSort('-created_at')
            ->allowedFilters([
                QueryFilter::identifier(),
                QueryFilter::string('documentNumber'),
                QueryFilter::date('documentDate', 'datum'),
                new AllowedFilter('positions.count', new RelationCountFilter(),'positions'),
                // ...
            ])
            ->allowedSorts([
                AllowedSort::field('created_at'),
                AllowedSort::field('updated_at'),
            ])
            ->allowedIncludes([
                'customer',
                'positions',
            ])
            ->apiPaginate();

        return SalesOrderResource::collection($salesOrders);
    }
```

#### Filtering

We are leveraging the `spatie/laravel-query-builder` package to provide an easy filter implementation. Nevertheless, the
filters are adapted to our conventions. This means, that a filter in the url always contains `key`, `op` and `value`.
Examples are as follows:

```bash
/api/v1/sales-orders?filter[0][key]=documentNumber&filter[0][op]=eq&filter[0][value]=12345
/api/v1/sales-orders?filter[0][key]=documentNumber&filter[0][op]=in&filter[0][value][]=12345&filter[0][value][]=54321
/api/v1/sales-orders?filter[0][key]=documentDate&filter[0][op]=lessThan&filter[0][value]=2025-05-05
/api/v1/sales-orders?filter[0][key]=customer.name&filter[0][op]=contains&filter[0][value]=John
```

### View endpoints

You can define a view endpoint using the `#[GetEndpoint]` attribute. This allows you to specify the path, resource
class, description, and any additional parameters such as includes. The example below shows how to define a view
endpoint for a single sales order.

```php  
    #[GetEndpoint(
        path: '/api/v1/sales-orders/{id}',
        resource: SalesOrderResource::class,
        description: 'View a single sales order',
        tags: ['SalesOrder'],
        includes: ['customer', 'positions'],
    )]
    public function view(int $id): SalesOrderResource
    {
        $salesOrder = QueryBuilder::for(SalesOrder::class)
            ->allowedIncludes([
                'customer',
                'positions',
            ])
            ->findOrFail($id);

        return new SalesOrderResource($salesOrder);
    }
```

### Defining request bodies

You can define request bodies works like defining resources, using the `#[OA\Schema]` attribute. It just happens on
Laravels Form requests (or e.g. spatie/laravel-data objects) This allows you to specify the properties of your request
body, including their types and whether they are required. The example below shows how to define a request body for
creating a sales order.

```php
#[OA\Schema(
    schema: 'CreateSalesOrderRequest',
    required: ['project', 'customer', 'positions'],
    properties: [
        new OA\Property(property: 'project', type: 'object', required: ['id'], properties: [new OA\Property(property: 'id', type: 'integer')]),
        new OA\Property(property: 'customer', type: 'object', required: ['id'], properties: [new OA\Property(property: 'id', type: 'integer')]),
        new OA\Property(property: 'tags', type: 'array', items: new OA\Items(type: 'string')),
        new OA\Property(property: 'positions', type: 'array', items: new OA\Items(
            required: ['sku', 'quantity', 'price'],
            properties: [
                new OA\Property(property: 'sku', type: 'string'),
                new OA\Property(property: 'quantity', type: 'integer'),
                new OA\Property(property: 'price', ref: '#/components/schemas/Money'),
            ])),
    ],
    type: 'object',
    additionalProperties: false,
)]
class CreateSalesOrderRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'project.id' => ['required', 'integer', 'exists:projects,id'],
            'customer.id' => ['required', 'integer', 'exists:business_partners,id'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string'],
            'positions' => ['required', 'array'],
            'positions.*.sku' => ['required', 'string', 'exists:products,sku'],
            'positions.*.quantity' => ['required', 'integer'],
            'positions.*.price' => ['required'],
            'positions.*.price.amount' => ['required', 'decimal:0,2'],
            'positions.*.price.currency' => ['required', 'in:EUR,USD'],
        ];
    }
}
```

### Create endpoints

You can define a create endpoint using the `#[PostEndpoint]` attribute. This allows you to specify the path, resource
class, description, and any additional parameters such as request body and response. The example below shows how to
define a create endpoint for a sales order.

```php
    #[PostEndpoint(
        path: '/api/v1/sales-orders',
        request: CreateSalesOrderRequest::class,
        resource: SalesOrderResource::class,
        description: 'Create a new sales order',
        tags: ['SalesOrder'],
        successStatus: '201',
    )]
    public function create(CreateSalesOrderRequest $request): SalesOrderResource
    {
        $salesOrder = SalesOrder::create([
            // ...
        ]);

        return new SalesOrderResource($salesOrder);
    }


```

### Update endpoints

You can define an update endpoint using the `#[PutEndpoint]` or `#[PatchEndpoint]` attribute. In general they are
working in the same way as the `#[PostEndpoint]`. This allows you to specify the path, resource class, description, and
any additional parameters such as request body and response. The example below shows how to define an update endpoint
for a sales order.

```php
    #[PatchEndpoint(
        path: '/api/v1/sales-orders/{id}',
        request: UpdateSalesOrderRequest::class,
        resource: SalesOrderResource::class,
        description: 'Update an existing sales order',
        tags: ['SalesOrder'],
    )]
    public function update(UpdateSalesOrderRequest $request, int $id): SalesOrderResource
    {
        $salesOrder = SalesOrder::with('positions')->findOrFail($id);

        // Handle update logic here, e.g. updating positions, customer, etc.

        return new SalesOrderResource($salesOrder);
    }
```

### Delete endpoints

You can define a delete endpoint using the `#[DeleteEndpoint]` attribute. This allows you to specify the path, resource
class, description, and any additional parameters such as response. The example below shows how to define a delete
endpoint for a sales order.
The example below shows how to define a delete endpoint for a sales order. It also demonstrates how to use
custom validation to ensure that only pending sales orders can be deleted. If the sales order is not pending, a
validation exception is thrown with a custom message.

```php
    #[DeleteEndpoint(
        path: '/api/v1/sales-orders/{id}',
        description: 'Delete a sales order',
        tags: ['SalesOrder'],
        validates: ['status' => 'Only pending sales orders can be deleted.'],
    )]
    public function delete(int $id): Response
    {
        $salesOrder = SalesOrder::findOrFail($id);

        if ($salesOrder->status !== SalesOrderStatus::PENDING) {
            throw ValidationException::withMessages([
                'status' => 'Only pending sales orders can be deleted. Current status: '.$salesOrder->status->value,
            ]);
        }

        $salesOrder->positions()->delete();
        $salesOrder->delete();

        return response()->noContent();
    }
```

```bash
php artisan openapi:generate
```

### Configuration

After installation, you can publish the configuration file using:

```bash
php artisan vendor:publish --provider="Bambamboole\LaravelOpenApi\OpenApiServiceProvider"
```

This will create a `config/openapi.php` file with the following options:

```php
return [
    'docs' => [
        'enabled' => env('APP_ENV') !== 'production',
        'prefix' => 'api-docs',
    ],
    'schemas' => [
        'default' => [
            'oas_version' => '3.1.0',
            'ruleset' => null,
            'folders' => [base_path('app')],
            'output' => base_path('openapi.yml'),
            'name' => 'My API',
            'version' => '1.0.0',
            'description' => 'Developer API',
            'contact' => [
                'name' => 'API Support',
                'url' => env('APP_URL', 'https://.example.com'),
                'email' => env('MAIL_FROM_ADDRESS', 'api@example.com'),
            ],
            'servers' => [
                [
                    'url' => env('APP_URL', 'https://.example.com'),
                    'description' => 'Your API environment',
                ],
            ],
        ],
    ],
    'merge' => [
        'schemas' => ['default'],
    ],
];
```

#### Multiple Schemas

You can define multiple schemas in the configuration file. Each schema can have its own settings, including which
folders to scan, output file, and other OpenAPI information.

```php
'schemas' => [
    'v1' => [
        'folders' => [base_path('app/Http/Controllers/Api/V1')],
        'output' => base_path('openapi-v1.yml'),
        // other settings...
    ],
    'v2' => [
        'folders' => [base_path('app/Http/Controllers/Api/V2')],
        'output' => base_path('openapi-v2.yml'),
        // other settings...
    ],
],
```

To generate a specific schema, you can pass the schema name to the `openapi:generate` command:

```bash
php artisan openapi:generate v1
```

### Merging OpenAPI Schemas

If your project defines multiple OpenAPI schemas (for example, for different API versions or modules), you can merge
them into a single specification file using the provided Artisan command.

#### Configuration

In your `config/openapi.php`, specify which schemas to merge and the output file:

```php
'merge' => [
    'schemas' => ['default', 'v2'], // List the schema keys you want to merge
    'files' => [base_path('extra_spec.json')], // (Optional) Additional files to merge
    'output' => base_path('openapi_merged.yml'), // (Optional) Output file path
],
```

- `schemas`: Array of schema keys defined in the `schemas` section to merge.
- `files`: (Optional) Additional OpenAPI files to include in the merge.
- `output`: (Optional) Path for the merged output file. Defaults to `openapi_merged.yml` in the project root.

#### Merging Schemas

Run the following Artisan command to merge the specified schemas and files:

```bash
php artisan openapi:merge
```

This will generate a single merged OpenAPI file at the location specified in your configuration.

#### Example

Suppose you have two schemas, `v1` and `v2`, defined in your config:

```php
'schemas' => [
    'v1' => [
        'folders' => [base_path('app/Http/Controllers/Api/V1')],
        'output' => base_path('openapi-v1.yml'),
    ],
    'v2' => [
        'folders' => [base_path('app/Http/Controllers/Api/V2')],
        'output' => base_path('openapi-v2.yml'),
    ],
],
```

And your merge config:

```php
'merge' => [
    'schemas' => ['v1', 'v2'],
    'files' => [base_path('extra_spec.json')], // Optional additional files from other sources
    'output' => base_path('openapi_merged.yml'),
],
```

After running `php artisan openapi:merge`, you will find the merged OpenAPI spec at `openapi_merged.yml`. By changing
the file extion to `json`, it will generate a json file.

### Web Interface

The package provides a web interface for viewing the OpenAPI documentation. By default, it's available at `/api-docs`
and is protected by the `web` and `auth` middleware.

You can configure the web interface in the `docs` section of the configuration file:

```php
'docs' => [
    'enabled' => env('APP_ENV') !== 'production', // Enable or disable the web interface
    'prefix' => 'api-docs', // URL prefix for the web interface
    'middlewares' => [], // Additional middlewares to apply
],
```

### Reusing filters

It can be very useful to reuse filters across multiple endpoints. This can be done by creating a new Attribute class
that implements the `FilterSpecCollection` interface. Here's an example:

```php
<?php

namespace App\OpenApi\Filters;

use Bambamboole\LaravelOpenApi\Attributes\FilterProperty;
use Bambamboole\LaravelOpenApi\Attributes\FilterSpecCollection;
use Bambamboole\LaravelOpenApi\Enum\FilterType;

#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class UserFilters implements FilterSpecCollection
{
    public function getFilterSpecification(): array
    {
        return [
            new FilterProperty(
                name: 'name',
                description: 'Filter users by name',
                type: 'string',
                filterType: FilterType::PARTIAL
            ),
            new FilterProperty(
                name: 'email',
                description: 'Filter users by email',
                type: 'string',
                filterType: FilterType::EXACT
            ),
            new FilterProperty(
                name: 'created_at',
                description: 'Filter users by creation date',
                type: 'string',
                filterType: FilterType::OPERATOR,
                operators: ['eq', 'gt', 'lt', 'gte', 'lte']
            ),
        ];
    }
}
```

You can then use this attribute in your controller methods:

```php
#[ListEndpoint(
    path: '/api/v1/users',
    resource: UserResource::class,
    description: 'Paginated list of users',
    parameters: [
        new UserFilters(),
        new QuerySort(['created_at', 'updated_at']),
    ],
    tags: ['User'],
)]
public function index(): AnonymousResourceCollection
{
    $users = QueryBuilder::for(User::class)
        ->defaultSort('-created_at')
        ->allowedFilters([
            QueryFilter::string('name'),
            QueryFilter::string('email'),
            QueryFilter::date('created_at'),
        ])
        ->allowedSorts([
            AllowedSort::field('created_at'),
            AllowedSort::field('updated_at'),
        ])
        ->apiPaginate();

    return UserResource::collection($users);
}
```

### Testing

```bash
composer test
```

## Contributing

### Ideas/Roadmap

Here are some ideas for future development:

- Support for other OpenAPI doc tools than Swagger UI
- Support for more OpenAPI features like callbacks, webhooks, and links
- Improved documentation generation with more examples and use cases
- Support for generating client libraries in various languages

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email manuel@christlieb.eu instead of using the issue tracker.

## Credits

- [Manuel Christlieb](https://github.com/bambamboole)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
