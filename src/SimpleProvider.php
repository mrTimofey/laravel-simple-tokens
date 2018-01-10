<?php

namespace MrTimofey\LaravelSimpleTokens;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;
use Illuminate\Database\Eloquent\Builder;

class SimpleProvider extends EloquentUserProvider
{
    protected $config;

    public function __construct(HasherContract $hasher, string $model, array $config)
    {
        parent::__construct($hasher, $model);
        $this->config = $config;
    }

    protected function newModelQuery(): Builder
    {
        $query = $this->createModel()->newQuery();

        if (!empty($this->config['scope'])) {
            $scopes = (array)$this->config['scope'];
            foreach ($scopes as $k => $v) {
                if (\is_int($k)) {
                    $scope = $v;
                    $args = [];
                }
                else {
                    $scope = $k;
                    $args = $v;
                }
                \call_user_func_array([$query, $scope], $args);
            }
        }

        if (!empty($this->config['only'])) {
            $only = (array)$this->config['only'];
            foreach ($only as $k => $v) {
                if (\is_int($k)) {
                    $query->where($v, true);
                }
                else {
                    \is_array($v) ? $query->whereIn($k, $v) : $query->where($k, $v);
                }
            }
        }

        return $query;
    }

    /**
     * @inheritdoc
     */
    public function retrieveById($identifier)
    {
        $model = $this->createModel();

        return $this->newModelQuery()
            ->where($model->getAuthIdentifierName(), $identifier)
            ->first();
    }

    /**
     * @inheritdoc
     */
    public function retrieveByToken($identifier, $token)
    {
        $model = $this->createModel();

        $model = $this->newModelQuery()->where($model->getAuthIdentifierName(), $identifier)->first();

        if (!$model) {
            return null;
        }

        $rememberToken = $model->getRememberToken();

        return $rememberToken && hash_equals($rememberToken, $token) ? $model : null;
    }

    /**
     * @inheritdoc
     */
    public function retrieveByCredentials(array $credentials)
    {
        if (!empty($credentials['api_token'])) {
            if ($id = cache(config('simple_tokens.cache_prefix') . $credentials['api_token'])) {
                return $this->retrieveById($id);
            }
            return null;
        }

        if (empty($credentials) || (\count($credentials) === 1 && array_key_exists('password', $credentials))) {
            return;
        }

        // First we will add each credential element to the query as a where clause.
        // Then we can execute the query and, if we found a user, return it in a
        // Eloquent User "model" that will be utilized by the Guard instances.
        $query = $this->newModelQuery();

        foreach ($credentials as $key => $value) {
            if (!str_contains($key, 'password')) {
                $query->where($key, $value);
            }
        }

        return $query->first();
    }
}
