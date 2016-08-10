Laravel Blockable Plugin
============


[![Build Status](https://travis-ci.org/rtconner/laravel-blockable.svg?branch=master)](https://travis-ci.org/rtconner/laravel-likeable)
[![Latest Stable Version](https://poser.pugx.org/rtconner/laravel-blockable/v/stable.svg)](https://packagist.org/packages/rtconner/laravel-likeable)
[![License](https://poser.pugx.org/rtconner/laravel-blockable/license.svg)](https://packagist.org/packages/rtconner/laravel-likeable)

Trait for Laravel Eloquent models to allow easy implementation of a "block" or "ignore" feature.

[Laravel 5 Documentation](https://github.com/racashmoney/laravel-blockable/tree/laravel-5)  

#### Composer Install (for Laravel 5)

	composer require racashmoney/laravel-blockable "~1.2"

#### Install and then run the migrations

```php
'providers' => [
	\Racashmoney\Blockable\BlockableServiceProvider::class,
],
```

```bash
php artisan vendor:publish --provider="Racashmoney\Blockable\BlockableServiceProvider" --tag=migrations
php artisan migrate
```

#### Setup your models

```php
class Article extends \Illuminate\Database\Eloquent\Model {
	use \Racashmoney\Blockable\Blockable;
}
```

#### Sample Usage

```php
$article->block(); // block the article for current user
$article->block($myUserId); // pass in your own user id
$article->block(0); // just add blocks to the count, and don't track by user

$article->unblock(); // remove block from the article
$article->unblock($myUserId); // pass in your own user id
$article->unblock(0); // remove blocks from the count -- does not check for user

$article->blockCount; // get count of blocks

$article->blocks; // Iterable Illuminate\Database\Eloquent\Collection of existing blocks 

$article->blocked(); // check if currently logged in user blocked the article
$article->blocked($myUserId);

Article::whereBlockedBy($myUserId) // find only articles where user blocked them
	->with('blockCounter') // highly suggested to allow eager load
	->get();
```

#### Credits

 - Robert Conner - http://smartersoftware.net
