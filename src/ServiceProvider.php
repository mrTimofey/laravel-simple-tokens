<?php

namespace MrTimofey\LaravelSimpleTokens;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider as Base;

class ServiceProvider extends Base
{
    public function boot(): void
    {
        $this->publishes([__DIR__ . '/../config.php' => config_path('simple_tokens.php')], 'config');
        $auth = $this->app->make('auth');
        $auth->provider('simple', function ($app, array $config) {
            /** @var Application $app */
            return new SimpleProvider($app->make('hash'), $config['model']);
        });
    }
}
