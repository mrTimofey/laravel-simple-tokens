<?php

return [
	/**
	 * Cache key for '{prefix}{api_token}' => {user ID}
	 */
    'cache_prefix' => 'simple_tokens:',

	/**
	 * Token time-to-live in minutes
	 */
	'token_ttl' => 24 * 60
];