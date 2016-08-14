<!---
title: Installation
-->

#### 1. Composer
Add the `laradic/assets` package to your composer.json dependencies.
```json
"require": {
    "laradic/assets": "1.0.*"
}
```

#### 2. Laravel
Register the `AssetsServiceProvider` in your application, preferably in your `config/app.php` file.
```php
'providers' => [
    Laradic\Assets\AssetsServiceProvider::class
]
```

###### Optional: Add the facade

```php
'facades' => [
    'Asset' => Laradic\Assets\Facades\Asset::class
]
```

#### 3. Optional: Publish config
```sh
php artisan vendor:publish --provider=Laradic\Assets\AssetsServiceProvider
```
