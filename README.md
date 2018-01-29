Simple token and cache based user authentication and authorization.

You are tired of the mind-blowing, unnecessarily complex Passport OAuth?
You want something configurable, manageable and simple?
Then that is just the package you need.

## Features

* Fully Eloquent compatible auth driver
* Cache based token authorization with configurable TTL
* Additional query restriction configuration per each provider using simple `where/where in` clauses or model scopes
	(for example, you can restrict authorization only for particular user roles)
* Controller trait to maintain authentication/authorization/logout logic for your application out-of-the-box
	(but you still have to define controller and routes)
* Multiple independent guards support

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

### Auth provider configuration

```php
// config/auth.php

return [

	// ...

	'providers' => [
		// Simple example (suitable for most cases)
		'simple' => [
			'driver' => 'simple',
			'model' => App\User::class
		],
	
		// Advanced example
		'advanced' => [
			'driver' => 'simple',
			'model' => App\User::class,
			
			// Query modifiers
			'only' => [
				// only users with email = example@email.com
				'email' => 'example@email.com',
				// only users with ID 1, 2 or 3
				'id' => [1, 2, 3]
			],
			
			// Any model scope
			'scope' => 'scopeName',
			// ...or
			'scope' => [
				'scopeName',
				'scopeWithArguments' => ['arg1', 'arg2']
			],
			
			// Cache prefix can be configured if you want to use multiple independent providers.
			// This will allow clients to have multiple tokens (one per each unique prefix).
			// On the other hand, you can restrict users to have a sinlgle token by providing same prefix.
			// Default: no prefix
			// IMPORTANT: this prefix will will be appended to the `simple_tokens.cache_prefix` config entry.
			'cache_prefix' => '',

			// Token expiration time in minutes.
			// You can overwrite default value from the `simple_tokens.token_ttl` config entry here.
			'token_ttl' => 60
		]
	],

	// ...

];
```