# Headless Laravel

```bash
composer require headlesslaravel/packages
```

Then read each package's docs for how to use each feature.

For a quick example, here is how to use formations:

```bash
php artisan make:formation ArticleFormation
```
```php
Route::formation('articles', ArticleFormation::class);
```


### Frontend assets via @craniums

For the Vue lovers:
```bash
npm i @craniums/vue
```
For the React lovers:
```bash
npm i @craniums/react
```
