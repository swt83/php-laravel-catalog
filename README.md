# Catalog

A Laravel PHP package for caching API responses.

Since many APIs charge by the request, and to speed up response times, it's a good idea to cache API responses.  This package creates a database and automatically handles the caching on your behalf, for any API that you might use.

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

The name you provide doesn't need to be unique, rather it should be a name to describe the type of API call being made (this is to allow easy editing of the stored database records, if you needed to purge something).  The cache lifespan value should be something that [``strtotime()``](http://php.net/manual/en/function.strtotime.php) can recognize.

## Librarian

This package can also be used to host a central storage location that can be referenced via API.  This lets you can have multiple remote apps referencing cached API responses in a single database.

```
$response = Catalog::lookup('myapi', function()
{
	return MyAPI::run([
		'param1' => 'foo',
		'param2' => 'bar',
	]);
}, '1 year', 'https://myapp.com/librarian'); // notice new argument pointing to the API endpoint
```

The Laravel provider in this package automatically creates the route for the API endpoint.  Just point your remote apps to that endpoint and you should be good to go.