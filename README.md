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

