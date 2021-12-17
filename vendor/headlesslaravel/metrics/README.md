# Headless Metrics

[![Latest Version on Packagist](https://img.shields.io/packagist/v/dillingham/headless-admin.svg?style=flat-square)](https://packagist.org/packages/dillingham/headless-admin)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/dillingham/headless-admin/run-tests?label=tests)](https://github.com/dillingham/headless-admin/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/dillingham/headless-admin/Check%20&%20fix%20styling?label=code%20style)](https://github.com/dillingham/headless-admin/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/dillingham/headless-admin.svg?style=flat-square)](https://packagist.org/packages/dillingham/headless-admin)

---

## Installation

You can install the package via composer:

```bash
composer require headlesslaravel/metrics
```

```php
Metric::make(Post::class)
    ->from(now()->subDays(7))
    ->count()
```
or add the `HasMetrics` trait to a model and use like so:
```php
Post::metrics()
    ->from(now()->subDays(7))
    ->count()
```
## Credits

Special thanks to @Larsklopstra for his [Flowframe/laravel-trend](https://github.com/Flowframe/laravel-trend) package. 

The implementation outlined in that package paved the way for this package.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
