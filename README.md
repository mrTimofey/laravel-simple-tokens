Simple token and cache based user authentication and authorization.

## Features

* Cache based token authorization with configurable TTL
* Additional query restriction configuration for user provider using simple `where/where in` clauses or model scopes
	(for example, you can restrict authorization only for particular user roles)
* Controller trait to maintain authentication/authorization/logout logic for your application out-of-the-box
	(but you still have to define routes)
* Multiple guards support

## Requirements

* PHP 7.1
* Laravel 5

## Install

```bash
composer require mr-timofey/laravel-simple-tokens
```

**For Laravel <= 5.4** add `MrTimofey\LaravelSimpleTokens\ServiceProvider` to your `app.providers` config.

```bash
php artisan vendor:publish --provider="MrTimofey\LaravelSimpleTokens\ServiceProvider"
```

## Usage

Set your `auth.providers.users.driver` (replace `users` to other provider if needed) config to `simple`.
Any authorized HTTP request must contain `Authorization: Bearer {api_token}` header.

Configure a guard (`api` by default) if necessary.

Use `MrTimofey\LaravelSimpleTokens\AuthenticatesUsers` trait with your auth controller. This trait adds methods:
* authenticate - authenticates user with login/email/password/remember_token and returns JSON response which includes:
	```js
	{
		user: { /* user data */ },
		api_token: 'api token string',
		remember_token: 'remember token string or NULL if request does not have a "remember" flag'
	}
	```
	This method generates `api_token` and puts it to cache with `cache()->set('prefix:' . $token, $user_id, $ttl)`.
	Also regenerates user `remember_token`.
	TTL is configured in `simple_tokens.ttl`.
* logout - deletes `api_token` from cache.
* user - returns user data JSON.

Also you can define a `$guard` class field to use any guard other than default one (`api`).

### User query restrictions

You can add query restrictions to a provider configuration in `auth.providers` config:

```php
// config/auth.php

return [

	// ...

	'providers' => [
		'poviderName' => [
			'driver' => 'simple',
			'model' => App\User::class,
			'only' => [
				// only users with email = example@email.com
				'email' => 'example@email.com',
				// only users with ID 1, 2 or 3
				'id' => [1, 2, 3]
			],
			// any eloquent model scope
			'scope' => 'scopeName',
			// ...or
			'scope' => [
				'someScope',
				'scopeWithArguments' => ['arg1', 'arg2']
			]
		]
	],

	// ...

];
```