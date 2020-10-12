# An simple an elegant way to create SCIM 2.0 queries using PHP

[![Latest Version on Packagist](https://img.shields.io/packagist/v/danutavadanei/php-scim-query.svg?style=flat-square)](https://packagist.org/packages/danutavadanei/php-scim-query)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/danutavadanei/php-scim-query/run-tests?label=tests)](https://github.com/danutavadanei/php-scim-query/actions?query=workflow%3Arun-tests+branch%3Amaster)
[![Total Downloads](https://img.shields.io/packagist/dt/danutavadanei/php-scim-query.svg?style=flat-square)](https://packagist.org/packages/danutavadanei/php-scim-query)

## Installation

You can install the package via composer:

```bash
composer require danutavadanei/php-scim-query
```

## Usage

``` php
$builder = new \DanutAvadanei\PhpScimQuery\Builder;

$builder->whereEqual('userName', 'Danut');

$builder->whereNotEqual('userName', 'Danut');

$builder->whereContains('userName', 'Dan');

$builder->whereNotContains('userName', 'Dan');

$builder->whereStartsWith('userName', 'Dan');

$builder->whereNotStartsWith('userName', 'Dan');

$builder->whereEndsWith('userName', 'Dan');

$builder->whereNotEndsWith('userName', 'Dan');

$builder->whereGreaterThan('lastModified', '2020-01-01T00:00:00Z');

$builder->whereNotGreaterThan('lastModified', '2020-01-01T00:00:00Z');

$builder->whereGreaterThanOrEqualTo('lastModified', '2020-01-01T00:00:00Z');

$builder->whereNotGreaterThanOrEqualTo('lastModified', '2020-01-01T00:00:00Z');

$builder->whereLessThan('lastModified', '2020-01-01T00:00:00Z');

$builder->whereNotLessThan('lastModified', '2020-01-01T00:00:00Z');

$builder->whereLessThanOrEqualTo('lastModified', '2020-01-01T00:00:00Z');

$builder->whereNotLessThanOrEqualTo('lastModified', '2020-01-01T00:00:00Z');

$builder->wherePresent('title');

$builder->whereNotPresent('title');

$builder->whereIn('title', ['Mr.', 'Ms.']);

$builder->whereNotIn('title', ['Mr.', 'Ms.']);

$builder->whereComplex('emails', function ($builder) {
    $builder->whereEquals('work')
        ->whereContains('@example.com');
});

$builder->whereNotComplex('emails', function (Builder $builder) {
    $builder->whereEquals('work')
        ->whereContains('@example.com');
});

$builder
    ->where(function (Builder $builder) {
        $builder->whereEquals('employeeType', 'executive')
            ->whereEndsWith('email', '@example.com');
    })
    ->orWhereEquals('employeeType', 'diretor');
```

## Testing

``` bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Danut Avadanei](https://github.com/DanutAvadanei)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Resources

https://github.com/illuminate/database/blob/master/Query/Builder.php
https://tools.ietf.org/html/rfc7644
https://ldapwiki.com/wiki/SCIM%20Filtering
https://github.com/hiyosi/filter
https://github.com/pingidentity/scim2
https://apidocs.pingidentity.com/pingdatagovernance/scim/v2/api/guide/

## To-do

- add scim client abstraction and interface

whereBetween
whereDate
whereDecimal
whereComplex?
