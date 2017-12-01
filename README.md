Simple token and cache based user authentication and authorization.

## Install

```bash
composer require mr-timofey/laravel-simple-tokens
```

Add `MrTimofey\LaravelSimpleTokens\ServiceProvider` to your `app.providers` config.

```bash
php artisan vendor:publish --provider="MrTimofey\LaravelSimpleTokens\ServiceProvider"
```

## Usage

Set your `auth.providers.users.driver` config to `simple`.
Any authorized HTTP request must contain `Authorization: Bearer {api_token}` header.

Use `MrTimofey\LaravelSimpleTokens\AuthenticatesUsers` trait with your auth controller. This trait adds methods:
* authenticate - authenticates user with login/email/password/remember_token and returns JSON response which includes:
	```js
	{
		user: { /* user data */ },
		api_token: 'api token string',
		remember_token: 'remember token string or NULL if request does not have a "remember" flag'
	}
	```
* logout - deletes `api_token` from cache.
* user - returns user data JSON.