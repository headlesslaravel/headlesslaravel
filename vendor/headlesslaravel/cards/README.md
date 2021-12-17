# Headless Cards

[![Latest Version on Packagist](https://img.shields.io/packagist/v/headlesslaravel/cards.svg?style=flat-square)](https://packagist.org/packages/headlesslaravel/cards)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/headlesslaravel/cards/run-tests?label=tests)](https://github.com/headlesslaravel/cards/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/headlesslaravel/cards/Check%20&%20fix%20styling?label=code%20style)](https://github.com/headlesslaravel/cards/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/headlesslaravel/cards.svg?style=flat-square)](https://packagist.org/packages/headlesslaravel/cards)

---

## Installation

You can install the package via composer:

```bash
composer require headlesslaravel/cards
```
Make a card class to manage multiple cards of one  type:
```bash
php artisan make:cards Dashboard
```
Then add the class to an endpoint:
```php
Route::cards('api/dashboard', Dashboard::class);
```

```php
<?php

namespace App\Http\Cards;

class Dashboard extends CardGroup
{
    public function cards()
    {
        Card::make('Total Users')
            ->link('/users')
            ->component('number-card')
            ->value(function() {
                return User::count();
            });
    }
}
```
```bash
/api/dashboard
```
```json
{
    "data": [
        {
            "key": "total_users",
            "title": "Total Users",
            "value": 5,
            "component": "number-card",
            "link": "/users",
            "endpoint": "api/dashboard/total-users"
        }
    ]
}
```
You can also reference the single method using the `key` in slug format.

This is useful when you want your ui to update / filter a single card. 
```
/api/dashboard/total-users
```

```json
{
    "key": "total_users",
    "title": "Total Users",
    "value": 5,
    "component": "number-card",
    "link": "/users",
    "endpoint": "api/dashboard/total-users"
}
```

This is only a basic example. The real power comes in the filtering multiple cards using one query string and validating that the query string is accurate.

```php
<?php

namespace App\Http\Cards;

class Dashboard extends CardGroup
{
    public function rules()
    {
        return [
            'from' => ['nullable', 'date', 'before_or_equal:to'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ];
    }
    
    public function cards()
    {
        Card::make('Total Users')
            ->link('/users')
            ->component('number-card')
            ->value(function(Params $params) {
                return User::whereBetween('created_at', [
                    $params->get('from', now()),  
                    $params->get('to', now())
                ])->count();
            });
        
        Card::make('Total Orders', 'total_orders')
            ->link('/orders')
            ->component('number-card')
            ->value(function(Params $params) {
                return Order::whereBetween('created_at', [
                    $params->get('from', now()),  
                    $params->get('to', now())
                ])->count();
            });
    }
}
```
Which results in both models  being filtered by the same query string.
```bash
/dashboard?from=...&to=...
```
```json
{
    "data": [
        {
            "key": "total_users",
            "title": "Total Users",
            "value": 5,
            "component": "number-card",
            "link": "/users",
            "endpoint": "api/dashboard/total-users"
        },
        {
            "key": "total_orders",
            "title": "Total Orders",
            "value": 50,
            "component": "number-card",
            "link": "/orders",
            "endpoint": "api/dashboard/total-orders"
        }
    ]
}
```
The filters also work on a single card request:
```bash
/dashboard/total-users?from=...&to=...
```
```bash
/dashboard/total-orders?from=...&to=...
```

You can pass a number of things as values:

### Views
```php
Card::make('Welcome')->view('cards.welcome');
```
```json
{
    "key": "welcome",
    "title": "Welcome",
    "value": "<h1>Welcome!</h1>",
    "component": null,
    "link": null,
    "endpoint": "api/dashboard/welcome"
}
```
### Http 
```php
Card::make('Weather')->http('api.com/weather', 'data.results.0');
```
```json
{
    "key": "weather",
    "title": "Weather",
    "value": {
        "today": "90 degrees",
        "tomorrow": "50 degrees"
    },
    "component": null,
    "link": null,
    "endpoint": "api/dashboard/weather"
}
```
Which is just shorthand for:
```php
Card::make('Weather')
    ->value(function() {
        return Http::get('api.com/weather')->json('data.results.0');
    });
```

### Cache
Any values in a callable can be cached: (5 mins)

```php
Card::make('Weather')
    ->cache(5)
    ->value(function() {
        return Http::get('api.com/weather')->json('data.today');
    });
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
