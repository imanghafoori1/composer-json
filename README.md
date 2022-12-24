# Composer Json:

[![Latest Version on Packagist](https://img.shields.io/packagist/v/imanghafoori/composer-json.svg?style=flat-square)](https://packagist.org/packages/:vendor_slug/:package_slug)
[![Tests](https://img.shields.io/github/actions/workflow/status/imanghafoori/composer-json/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/imanghafoori/composer-json/actions/workflows/run-tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/imanghafoori/composer-json.svg?style=flat-square)](https://packagist.org/packages/imanghafoori/composer-json)
<!--delete-->
---
This package can be used as to scaffold a framework agnostic package. Follow these steps to get started:

1. Press the "Use template" button at the top of this repo to create a new repo with the contents of this skeleton
2. Run "php ./configure.php" to run a script that will replace all placeholders throughout all the files
3. Have fun creating your package.
4. If you need help creating a package, consider picking up our <a href="https://laravelpackage.training">Laravel Package Training</a> video course.
---
<!--/delete-->
This is where your description should go. Try and limit it to a paragraph or two. Consider adding a small example.

## Support us

[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/:package_name.jpg?t=1" width="419px" />](https://spatie.be/github-ad-click/:package_name)


## Installation

You can install the package via composer:

```bash
composer require imanghafoori/composer-json
```

## Usage

You have to pass the absolute path to the composer.json file to the make method.

```php
$composer = 'ImanGhafoori\ComposerJson\ComposerJson::make(__DIR__);
```
Then you will have access to a handful of methods.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Credits

- [:author_name](https://github.com/imanghafoori1)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
