# Catalog

A Laravel PHP package for caching API responses.

Since many APIs charge by the request, and to speed up response times, it's a good idea to cache API responses.  This package creates a database and automatically handles the caching on your behalf, for any API that you might use.

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

Call the ``lookup`` method, give the lookup a name, pass a closure, and optionally provide a cache lifespan:

```php
$response = Catalog::lookup('myapi', function()
{
	return MyAPI::run([
		'param1' => 'foo',
		'param2' => 'bar',
	]);
}, '1 year');
```

The cache lifespan value should be something that [``strtotime()``](http://php.net/manual/en/function.strtotime.php) can recognize.