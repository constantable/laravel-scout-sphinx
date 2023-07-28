# Laravel Scout Sphinx Driver
# This is used in Platform Manager

[![Build Status](https://travis-ci.org/constantable/laravel-scout-sphinx.svg?branch=master)](https://travis-ci.org/constantable/laravel-scout-sphinx)
[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE)

## Introduction
This package offers advanced functionality for searching and filtering data using [Sphinx search engine](http://sphinxsearch.com/) for Laravel Scout.

## Installation
### Composer
Use the following command to install package via composer
```bash
composer require constantable/laravel-scout-sphinx
```
### Configuration
Publish the Scout configuration using the `vendor:publish` Artisan command. 
```bash
php artisan vendor:publish --provider="Laravel\Scout\ScoutServiceProvider"
```
This command will publish the scout.php configuration file to your config directory. 
Edit this file to set 'sphinxsearch' as a Scout driver:
```php
'driver' => env('SCOUT_DRIVER', 'sphinxsearch'),
```
And add default Sphinx connection options
```php
    'sphinxsearch' => [
        'host' => env('SPHINX_HOST', 'localhost'),
        'port' => env('SPHINX_PORT', '9306'),
        'socket' => env('SPHINX_SOCKET'),
        'charset' => env('SPHINX_CHARSET'),
    ],
```

If needed to add any ranker options.
```
Add `sphinx_ranker =  ranker` in your config.app file.
For details on ranker, you can visit `http://sphinxsearch.com/docs/archives/2.1.1/weighting.html`
```

Override these variables in your .env file if need

## Usage
- Add the `Laravel\Scout\Searchable` trait to the model you would like to make searchable. 
- Customize index name and searchable data for the model:
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

The basic search:
```php 
$orders = App\Order::search('Star Trek')->get();
``` 

Please refer to the [Scout documentation](https://laravel.com/docs/master/scout#searching) for additional information.
You can run more complex queries on index using callback, set the where clause, orderBy or paginate, for example:
```php
$oorders = App\Order::search($keyword, function (SphinxQL $query) {
        return $query->groupBy('description');
    })            
    ->where('status', 1)
    ->orderBy('date', 'DESC')
    ->paginate(20);
``` 
> Note: Changes on Sphinx indexes are only allowed for RT (Real Time) indexes. If you have ones and you need to update/delete records please define `public $isRT = true;` model's property. 

## Credits
- [Hyn](https://github.com/hyn)

## License

Licensed under the MIT license

[ico-version]: https://img.shields.io/packagist/v/constantable/laravel-scout-sphinx.svg?style=flat
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat
[link-packagist]: https://packagist.org/packages/constantable/laravel-scout-sphinx
