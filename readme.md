# DomainReflection

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads][ico-downloads]][link-downloads]
[![Build Status][ico-travis]][link-travis]
[![StyleCI][ico-styleci]][link-styleci]

The package for presenting information about domain models in a database. 

Use for model-driven laravel development or DDD-development, API resource creation and so on.

## Installation

Via Composer

``` bash
$ composer require shirokovnv/domain-reflection
```

## Usage

After installation type in console from the root folder: 

```php
php artisan migrate
```

This will create tables for storing meta information about your models:

- ref_models
- ref_fields
- ref_relations
- ref_fkeys
- ref_scopes
- ref_scope_args

Publish configuration: 

```php
php artisan vendor:publish --provider="Shirokovnv\DomainReflection\DomainReflectionServiceProvider" --tag=config
```

Then add paths for your domain in configuration file domain-reflection.php

By default it's App/Models

### Console commands:

1. 
    ```php
    php artisan domain:init
    ```
    This will register all your models in database

2.  ```php
    php artisan domain:reload "App\Models\User"
    ```
    Reload information about specific model
    
3.  ```php
    php artisan domain:remove "App\Models\Post"
    ```    
    Remove specific model data
        
Enjoy!
        
## Change log

Please see the [changelog](changelog.md) for more information on what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [contributing.md](contributing.md) for details and a todolist.

## Security

If you discover any security related issues, please email shirokovnv@gmail.com instead of using the issue tracker.

## Credits

- [Nickolai Shirokov][link-author]
- [All Contributors][link-contributors]

## License

MIT. Please see the [license file](license.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/shirokovnv/domain-reflection.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/shirokovnv/domain-reflection.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/shirokovnv/domain-reflection/master.svg?style=flat-square
[ico-styleci]: https://styleci.io/repos/12345678/shield

[link-packagist]: https://packagist.org/packages/shirokovnv/domain-reflection
[link-downloads]: https://packagist.org/packages/shirokovnv/domain-reflection
[link-travis]: https://travis-ci.org/shirokovnv/domain-reflection
[link-styleci]: https://styleci.io/repos/12345678
[link-author]: https://github.com/shirokovnv
[link-contributors]: ../../contributors
