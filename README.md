# Composer Json Reader

[![Latest Version on Packagist](https://img.shields.io/packagist/v/imanghafoori/composer-json.svg?style=flat-square)](https://packagist.org/packages/imanghafoori/composer-json)
[![Tests](https://img.shields.io/github/actions/workflow/status/imanghafoori1/composer-json/run-tests-phpunit.yml?branch=main&label=tests&style=flat-square)](https://github.com/imanghafoori1/composer-json/actions/workflows/run-tests-phpunit.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/imanghafoori/composer-json.svg?style=flat-square)](https://packagist.org/packages/imanghafoori/composer-json)
[![MIT Licensed](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)

This package helps you read data in your composer.json file.

## Installation

You can install the package via composer:

```bash
composer require imanghafoori/composer-json
```

## Usage

You have to pass the absolute path to the composer.json file to the make method.

```php
$composer = \ImanGhafoori\ComposerJson\ComposerJson::make(__DIR__);
```
Then you will have access to a handful of methods.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Credits

- [Iman Ghafoori](https://github.com/imanghafoori1)


## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
