# Laravel ACL 🔐

![CI](https://github.com/renoki-co/laravel-acl/workflows/CI/badge.svg?branch=master)
[![codecov](https://codecov.io/gh/renoki-co/laravel-acl/branch/master/graph/badge.svg)](https://codecov.io/gh/renoki-co/laravel-acl/branch/master)
[![StyleCI](https://github.styleci.io/repos/:styleci_code/shield?branch=master)](https://github.styleci.io/repos/:styleci_code)
[![Latest Stable Version](https://poser.pugx.org/renoki-co/laravel-acl/v/stable)](https://packagist.org/packages/renoki-co/laravel-acl)
[![Total Downloads](https://poser.pugx.org/renoki-co/laravel-acl/downloads)](https://packagist.org/packages/renoki-co/laravel-acl)
[![Monthly Downloads](https://poser.pugx.org/renoki-co/laravel-acl/d/monthly)](https://packagist.org/packages/renoki-co/laravel-acl)
[![License](https://poser.pugx.org/renoki-co/laravel-acl/license)](https://packagist.org/packages/renoki-co/laravel-acl)

Simple, AWS IAM-style ACL for Laravel applications, leveraging granular permissions in your applications with strong declarations. 🔐

## 🚀 Installation

You can install the package via composer:

```bash
composer require renoki-co/laravel-acl
```

Publish the config:

```bash
php artisan vendor:publish --provider="RenokiCo\LaravelAcl\LaravelAclServiceProvider" --tag="config"
```

Publish the migrations:

```bash
php artisan vendor:publish --provider="RenokiCo\LaravelAcl\LaravelAclServiceProvider" --tag="migrations"
```

## 🙌 Usage

```php
$ //
```

## 🐛 Testing

``` bash
vendor/bin/phpunit
```

## 🤝 Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## 🔒  Security

If you discover any security related issues, please email alex@renoki.org instead of using the issue tracker.

## 🎉 Credits

- [Alex Renoki](https://github.com/rennokki)
- [All Contributors](../../contributors)
