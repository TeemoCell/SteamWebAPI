<?php

namespace Syntax\SteamApi;

use Illuminate\Support\ServiceProvider;

class SteamApiServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->publishes([__DIR__.'/../../config/config.php' => config_path('steam-api.php')]);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/config.php', 'steam-api');

        $this->app->singleton(Client::class, function ($app): Client {
            $apiKey = $app['config']->get('steam-api.steamApiKey');

            return new Client(is_string($apiKey) ? $apiKey : null);
        });

        $this->app->alias(Client::class, 'steam-api');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return string[]
     */
    public function provides(): array
    {
        return [Client::class, 'steam-api'];
    }
}
