# Headless Laravel

A frontend agnostic backend layer for Laravel.

### Install

```
composer require headlesslaravel/headlesslaravel
```
```
php artisan headless:install
```
and just add 1 route to web.php for a quick start:
```php
Headless::routes();
```
- adds routes all cards in app/Http/Cards
- adds routes all formations in app/Http/Formations
- adds routes for formation imports
- adds routes for formation global search
- adds routes for notification endpoints

> Note: each feature can be routed manually instead.

[Read the documentation](https://github.com/headlesslaravel/docs)
