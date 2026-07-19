<?php

use Orchestra\Testbench\TestCase;
use Syntax\SteamApi\Client;
use Syntax\SteamApi\Facades\SteamApi;
use Syntax\SteamApi\SteamApiServiceProvider;

class LaravelClientBindingTest extends TestCase
{
    public function test_typed_client_and_legacy_facade_share_the_same_singleton(): void
    {
        $this->app['config']->set('steam-api.steamApiKey', 'test-key');

        $client = $this->app->make(Client::class);

        $this->assertSame($client, $this->app->make('steam-api'));
        $this->assertSame($client, SteamApi::getFacadeRoot());
    }

    protected function getPackageProviders($app): array
    {
        return [SteamApiServiceProvider::class];
    }
}
