# Laravel Scout Sphinx Driver

## Introduction
This package is fork of constantable/laravel-scout-sphinx.
package offers advanced functionality for searching and filtering data using the [Sphinx full text search server](http://sphinxsearch.com/) for [Laravel Scout](https://laravel.com/docs/master/scout).

## Installation

### Composer

Use the following command to install this package via Composer.

```bash
composer require eliasj/laravel-scout-sphinx
```

### Configuration

Publish the Scout configuration using the `vendor:publish` Artisan command. 

```bash
php artisan vendor:publish --provider="Laravel\Scout\ScoutServiceProvider"
```

This command will publish the `scout.php` configuration file to your config directory, which you can than edit and set `sphinxsearch` as the Scout driver.

```php
'driver' => env('SCOUT_DRIVER', 'sphinxsearch'),
```


To configure the connection to Sphinx server add the following (i.e. default) connection options.

```php
    'sphinxsearch' => [
        'host' => env('SPHINX_HOST', 'localhost'),
        'port' => env('SPHINX_PORT', '9306'),
        'socket' => env('SPHINX_SOCKET'),
        'charset' => env('SPHINX_CHARSET'),
    ],
```

Override these variables in your `.env` file if required.

## Usage

- Add the `Laravel\Scout\Searchable` trait to the model you would like to make searchable. 
- Customize the index name and searchable data for the model:

```php

    public function searchableAs()
    {
        return 'posts_index';
    }
    
    public function toSearchableArray()
    {
        $array = $this->toArray();

        // Customize array...

        return $array;
    }
```

A basic search:

```php 
$orders = App\Order::search('Star Trek')->get();
``` 

Please refer to the [Scout documentation](https://laravel.com/docs/master/scout#searching) for additional information. You can run more complex queries on the index by using a callback, setting the `where` clause, `orderBy`, or `paginate` threshold. For example:

```php
$orders = App\Order::search($keyword, function (SphinxQL $query) {
        return $query->groupBy('description');
    })            
    ->where('status', 1)
    ->orderBy('date', 'DESC')
    ->paginate(20);
``` 

Note: Changes on Sphinx indexes are only allowed for RT (Real-time) indexes. If you have these and need to update/delete records please define `public $isRT = true;` in the model's property. 

## Credits
- [Hyn](https://github.com/hyn)

## License

Licensed under the MIT license

[ico-version]: https://img.shields.io/packagist/v/constantable/laravel-scout-sphinx.svg?style=flat
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat
[link-packagist]: https://packagist.org/packages/constantable/laravel-scout-sphinx
