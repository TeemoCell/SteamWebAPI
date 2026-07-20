<?php

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use TeemoCell\SteamWebApi\Containers\Achievement;
use TeemoCell\SteamWebApi\Steam\News;
use TeemoCell\SteamWebApi\Steam\GameServers;
use TeemoCell\SteamWebApi\Steam\Player;
use TeemoCell\SteamWebApi\Steam\Publisher;
use TeemoCell\SteamWebApi\Steam\User;
use TeemoCell\SteamWebApi\Steam\User\Stats;
use TeemoCell\SteamWebApi\Steam\WebApi;
use TeemoCell\SteamWebApi\Steam\Workshop;

class CurrentApiEndpointsTest extends TestCase
{
    public function test_news_uses_https_v2_and_all_documented_filters(): void
    {
        [$client, $history] = $this->clientWithResponses([
            ['appnews' => ['appid' => 620, 'newsitems' => []]],
        ]);

        (new News('test-key', $client))->GetNewsForApp(
            620,
            10,
            500,
            1_700_000_000,
            ['steam_community_announcements', 'steam_updates'],
        );

        $request = $history->requests[0]['request'];
        $query = $this->requestQuery($request);

        self::assertSame('https', $request->getUri()->getScheme());
        self::assertSame('/ISteamNews/GetNewsForApp/v2/', $request->getUri()->getPath());
        self::assertSame('1700000000', $query['enddate']);
        self::assertSame('steam_community_announcements,steam_updates', $query['feeds']);
    }

    public function test_player_service_uses_v1_and_documented_input_names(): void
    {
        [$client, $history] = $this->clientWithResponses([
            ['response' => ['playtime' => 120]],
        ]);

        $response = (new Player('76561198336555523', 'test-key', $client))
            ->GetSingleGamePlaytime(620);

        $request = $history->requests[0]['request'];
        $input = $this->requestInput($request);

        self::assertSame(120, $response->playtime);
        self::assertSame('partner.steam-api.com', $request->getUri()->getHost());
        self::assertSame('/IPlayerService/GetSingleGamePlaytime/v1/', $request->getUri()->getPath());
        self::assertSame([
            'steamid' => '76561198336555523',
            'appid' => 620,
        ], $input);
    }

    public function test_achievements_use_the_official_web_api_method(): void
    {
        [$client, $history] = $this->clientWithResponses([
            ['playerstats' => ['achievements' => [[
                'apiname' => 'ACH_TEST',
                'achieved' => 1,
                'unlocktime' => 123,
                'name' => 'Test achievement',
                'description' => 'Description',
            ]]]],
        ]);

        $achievements = (new Stats('76561198336555523', 'test-key', $client))
            ->GetPlayerAchievements(620);

        self::assertInstanceOf(Achievement::class, $achievements[0]);
        self::assertSame('ACH_TEST', $achievements[0]->apiName);
        self::assertSame(1, $achievements[0]->achieved);
        self::assertSame(
            '/ISteamUserStats/GetPlayerAchievements/v1/',
            $history->requests[0]['request']->getUri()->getPath(),
        );
    }

    public function test_current_player_count_uses_the_current_stats_endpoint(): void
    {
        [$client, $history] = $this->clientWithResponses([
            ['response' => ['player_count' => 42]],
        ]);

        $count = (new Stats(null, 'test-key', $client))->GetNumberOfCurrentPlayers(620);

        self::assertSame(42, $count);
        self::assertSame(
            '/ISteamUserStats/GetNumberOfCurrentPlayers/v1/',
            $history->requests[0]['request']->getUri()->getPath(),
        );
    }

    public function test_workshop_query_uses_service_input_and_default_cursor(): void
    {
        [$client, $history] = $this->clientWithResponses([
            ['response' => ['publishedfiledetails' => []]],
        ]);

        (new Workshop('test-key', $client))->QueryFiles([
            'query_type' => 1,
            'appid' => 620,
        ]);

        self::assertSame('/IPublishedFileService/QueryFiles/v1/', $history->requests[0]['request']->getUri()->getPath());
        self::assertSame([
            'query_type' => 1,
            'appid' => 620,
            'cursor' => '*',
        ], $this->requestInput($history->requests[0]['request']));
    }

    public function test_user_and_game_server_calls_use_current_endpoints(): void
    {
        [$userClient, $userHistory] = $this->clientWithResponses([
            ['response' => ['steamid' => '76561198336555523', 'success' => 1]],
        ]);
        [$serverClient, $serverHistory] = $this->clientWithResponses([
            ['response' => ['is_banned' => false]],
        ]);

        (new User('76561198336555523', 'test-key', $userClient))
            ->ResolveVanityURL('valve', 3);
        (new GameServers('test-key', $serverClient))->QueryLoginToken('token');

        self::assertSame('/ISteamUser/ResolveVanityURL/v1/', $userHistory->requests[0]['request']->getUri()->getPath());
        self::assertSame('3', $this->requestQuery($userHistory->requests[0]['request'])['url_type']);
        self::assertSame('/IGameServersService/QueryLoginToken/v1/', $serverHistory->requests[0]['request']->getUri()->getPath());
        self::assertSame(
            ['login_token' => 'token'],
            $this->requestInput($serverHistory->requests[0]['request']),
        );
    }

    public function test_publisher_and_utility_endpoints_are_explicitly_separated(): void
    {
        [$publisherClient, $publisherHistory] = $this->clientWithResponses([
            ['ownsapp' => true],
        ]);
        [$utilityClient, $utilityHistory] = $this->clientWithResponses([
            ['apilist' => ['interfaces' => []]],
        ]);

        (new Publisher('publisher-key', $publisherClient))
            ->CheckAppOwnership('76561198336555523', 620);
        (new WebApi('test-key', $utilityClient))->GetSupportedAPIList();

        self::assertSame('partner.steam-api.com', $publisherHistory->requests[0]['request']->getUri()->getHost());
        self::assertSame('/ISteamUser/CheckAppOwnership/v4/', $publisherHistory->requests[0]['request']->getUri()->getPath());
        self::assertSame('/ISteamWebAPIUtil/GetSupportedAPIList/v1/', $utilityHistory->requests[0]['request']->getUri()->getPath());
    }

    private function clientWithResponses(array $responses): array
    {
        $history = new stdClass();
        $history->requests = [];
        $middleware = Middleware::history($history->requests);
        $mock = new MockHandler(array_map(
            static fn (array $response): Response => new Response(
                200,
                [],
                json_encode($response, JSON_THROW_ON_ERROR),
            ),
            $responses,
        ));
        $stack = HandlerStack::create($mock);
        $stack->push($middleware);

        return [new HttpClient(['handler' => $stack]), $history];
    }

    private function requestInput(RequestInterface $request): array
    {
        $query = $this->requestQuery($request);

        return json_decode($query['input_json'], true, flags: JSON_THROW_ON_ERROR);
    }

    private function requestQuery(RequestInterface $request): array
    {
        parse_str((string) $request->getUri()->getQuery(), $query);

        return $query;
    }
}
