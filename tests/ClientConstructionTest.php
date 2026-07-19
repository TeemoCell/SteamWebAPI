<?php

use GuzzleHttp\Client as HttpClient;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;
use Syntax\SteamApi\Client;
use Syntax\SteamApi\Steam\App;
use Syntax\SteamApi\Steam\Group;
use Syntax\SteamApi\Steam\Item;
use Syntax\SteamApi\Steam\News;
use Syntax\SteamApi\Steam\Package;
use Syntax\SteamApi\Steam\Player;
use Syntax\SteamApi\Steam\User;
use Syntax\SteamApi\Steam\User\Stats;

class ClientConstructionTest extends TestCase
{
    public function test_explicit_client_exposes_typed_endpoints_with_shared_dependencies(): void
    {
        $httpClient = new HttpClient();
        $client = new Client('test-key', $httpClient);

        $endpoints = [
            [$client->app(), App::class],
            [$client->news(), News::class],
            [$client->player('76561198336555523'), Player::class],
            [$client->user('76561198336555523'), User::class],
            [$client->userStats('76561198336555523'), Stats::class],
            [$client->package(), Package::class],
            [$client->group(), Group::class],
            [$client->item(), Item::class],
        ];

        foreach ($endpoints as [$endpoint, $expectedClass]) {
            $this->assertInstanceOf($expectedClass, $endpoint);
            $this->assertSame($httpClient, $this->readProperty($endpoint, 'client'));
            $this->assertSame('test-key', $this->readProperty($endpoint, 'apiKey'));
        }
    }

    public function test_endpoint_can_be_constructed_directly(): void
    {
        $httpClient = new HttpClient();
        $app = new App('test-key', $httpClient);

        $this->assertSame($httpClient, $this->readProperty($app, 'client'));
        $this->assertSame('test-key', $this->readProperty($app, 'apiKey'));
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_environment_key_works_without_a_laravel_application(): void
    {
        putenv('STEAM_API_KEY=test-key');

        $app = new App();

        $this->assertSame('test-key', $this->readProperty($app, 'apiKey'));
    }

    private function readProperty(object $object, string $property): mixed
    {
        return (new \ReflectionProperty(Client::class, $property))->getValue($object);
    }
}
