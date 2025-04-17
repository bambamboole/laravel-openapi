# Laravel OpenApi

[![Latest Version on Packagist](https://img.shields.io/packagist/v/bambamboole/laravel-openapi.svg?style=flat-square)](https://packagist.org/packages/bambamboole/laravel-openapi)
[![Total Downloads](https://img.shields.io/packagist/dt/bambamboole/laravel-openapi.svg?style=flat-square)](https://packagist.org/packages/bambamboole/laravel-openapi)
![GitHub Actions](https://github.com/bambamboole/laravel-openapi/actions/workflows/main.yml/badge.svg)

This package provides an elegant way to generate OpenAPI documentation for your Laravel API using PHP attributes.
Instead of hustling around with yaml or json files, you can use strongly-typed PHP attributes to define your
API endpoints and schemas co-located to the responsible functionality.

There were three main goals in mind when creating this package:

* Reduce the needed boilerplate as much as possible
* Co-locate Endpoint spec to controllers, validation spec to request classes and schema spec to resource classes
* Do not generate from implementation, so that the schema can be used in tests for validation

## How does it work?

Laravel OpenApi is built on the shoulders of giants, namely the package `zircote/swagger-php`, It extends given
annotations

It enables you to manage multiple openapi schema files in a single project. The default configuration can be done via
the `openapi.php` config file.

to provide opinionated and straight forward PHP 8 attributes from to define OpenAPI specifications directly in your
controller methods and
request/resource classes. The package provides a set of predefined attributes for common HTTP methods (GET, POST, PUT,
PATCH, DELETE) that automatically:

- Generate endpoint documentation with proper path parameters
- Document request bodies and validation requirements
- Define response schemas and status codes
- Handle authentication and authorization responses

These attributes extract the necessary information from your code structure, reducing duplication and keeping your API
documentation in sync with your implementation.

## Installation

## Installation

You can install the package via composer.

```bash
composer require bambamboole/laravel-openapi
```

## Usage

### Add endpoint attribute to controller

```php  
    #[GetEndpoint(
        path: '/api/v1/sales-orders/{id}',
        resource: SalesOrderResource::class,
        description: 'View a single sales order',
        tags: ['SalesOrder'],
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

Here another example with a paginated endpoint:

```php
    #[ListEndpoint(
        path: '/api/v1/sales-orders',
        resource: SalesOrderResource::class,
        description: 'Paginated list of sales orders',
        includes: ['customer', 'positions'],
        parameters: [
            new QueryFilter(name: 'id', type: 'integer', example: 1),
            new QueryFilter(name: 'customer.id',  type: 'integer', example: 1),
            new QueryFilter(name: 'positions.sku',  type: 'string', example: 'tshirt-yellow-xl'),
            new QueryFilter(name: 'positions.count',  type: 'operator'),
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
                AllowedFilter::exact('id'),
                AllowedFilter::belongsTo('customer.id', 'customer'),
                AllowedFilter::exact('positions.sku'),
                new AllowedFilter('positions.count', new RelationCountFilter(),'positions'),
                AllowedFilter::operator('created_at', FilterOperator::DYNAMIC)
            ])
            ->allowedSorts([
                AllowedSort::field('created_at'),
                AllowedSort::field('updated_at'),
            ])
            ->allowedIncludes([
                'customer',
                'positions',
            ])
            ->paginate(3);

        return SalesOrderResource::collection($salesOrders);
    }
```

An example for a Form Request:
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
        new OA\Property(property: 'positions', type: 'array', items: new OA\Items(ref: SalesOrderPositionResource::class)),
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

```bash
php artisan openapi:generate
```

### Testing

```bash
composer test
```

## Contributing

### Ideas/Roadmap

tbd

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email manuel@christlieb.eu instead of using the issue tracker.

## Credits

- [Manuel Christlieb](https://github.com/bambamboole)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

