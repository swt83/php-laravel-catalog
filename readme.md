# Catalog

A Laravel PHP package for caching API responses.

Since many API charge by the request, and to speed up response times, it's a good idea to cache API responses.  This package creates a database and automatically handles the caching on your behalf.

## Versions

Built for Laravel 5.1.

## Install

Normal install via Composer.

### Register

Register the service provider in your ``app/config/app.php`` file:

```php
Travis\Catalog\Provider::class,
```

### Publish

Publish the package migration:

```bash
$ php artisan vendor:publish
```

### Migrations

You will need a database connection called ``catalog``:

```bash
$ php artisan migrate
```

## Usage

Call the ``lookup`` method and pass an age limit and a closure:

```php
$response = Catalog::lookup(function()
{
	return MyAPI::run([
		'param1' => 'foo',
		'param2' => 'bar',
	]);
}, '1 year ago');
```

This will check the database for a stored response of the exact request.  If no stored responses are found, it will make the API request and store the response for future reference.

You can also pass an optional paramter of an age limit.  This value can be anything that [``strtotime()``](http://php.net/manual/en/function.strtotime.php) can recognize.  If an age limit is passed, any stored responses found older than the limit will be deleted.