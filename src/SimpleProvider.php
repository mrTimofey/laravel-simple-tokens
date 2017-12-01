<?php

namespace MrTimofey\LaravelSimpleTokens;

use Illuminate\Auth\EloquentUserProvider;

class SimpleProvider extends EloquentUserProvider
{
    public function retrieveByCredentials(array $credentials)
    {
        if (!empty($credentials['api_token'])) {
            if ($id = cache(config('simple_tokens.cache_prefix') . $credentials['api_token'])) {
                return $this->retrieveById($id);
            }
            return null;
        }
        return parent::retrieveByCredentials($credentials);
    }
}
