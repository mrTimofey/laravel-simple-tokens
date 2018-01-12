<?php

namespace MrTimofey\LaravelSimpleTokens;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider as Base;

class ServiceProvider extends Base
{
    public function boot(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config.php', 'simple_tokens');
        $this->publishes([__DIR__ . '/../config.php' => config_path('simple_tokens.php')], 'config');
        $this->app->make('auth')->provider('simple', function ($app, array $config) {
            /** @var Application $app */

            /** @var array $globalConfig */
            $globalConfig = $app->make('config')->get('simple_tokens');

            if (isset($config['cache_prefix'])) {
                $config['cache_prefix'] = $globalConfig['cache_prefix'] . $config['cache_prefix'];
            }

            /** @var Application $app */
            return new SimpleProvider(
                $app->make('hash'),
                $app->make('cache.store'),
                $config + $globalConfig
            );
        });
    }
}
