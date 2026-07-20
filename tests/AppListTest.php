<?php

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use TeemoCell\SteamWebApi\Steam\App;

class AppListTest extends TestCase
{
    public function test_it_requests_and_combines_all_app_list_pages(): void
    {
        $requests = [];
        $history = Middleware::history($requests);
        $mock = new MockHandler([
            new Response(200, [], json_encode([
                'response' => [
                    'apps' => [
                        ['appid' => 10, 'name' => 'First'],
                        ['appid' => 20, 'name' => 'Second'],
                    ],
                    'have_more_results' => true,
                ],
            ], JSON_THROW_ON_ERROR)),
            new Response(200, [], json_encode([
                'response' => [
                    'apps' => [
                        ['appid' => 30, 'name' => 'Third'],
                    ],
                    'have_more_results' => false,
                ],
            ], JSON_THROW_ON_ERROR)),
        ]);
        $stack = HandlerStack::create($mock);
        $stack->push($history);

        $apps = (new App('test-key', new HttpClient(['handler' => $stack])))
            ->GetAppList(['max_results' => 2]);

        self::assertSame([10, 20, 30], array_map(
            static fn (stdClass $app): int => $app->appid,
            $apps,
        ));
        self::assertCount(2, $requests);

        $firstQuery = $this->requestQuery($requests[0]['request']);
        $secondQuery = $this->requestQuery($requests[1]['request']);

        self::assertSame('/IStoreService/GetAppList/v1/', $requests[0]['request']->getUri()->getPath());
        self::assertSame('partner.steam-api.com', $requests[0]['request']->getUri()->getHost());
        self::assertSame(['max_results' => 2], json_decode($firstQuery['input_json'], true, flags: JSON_THROW_ON_ERROR));
        self::assertSame(
            ['max_results' => 2, 'last_appid' => 20],
            json_decode($secondQuery['input_json'], true, flags: JSON_THROW_ON_ERROR),
        );
    }

    public function test_it_stops_when_steam_returns_no_more_apps(): void
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode([
                'response' => [
                    'apps' => [['appid' => 10, 'name' => 'Only']],
                ],
            ], JSON_THROW_ON_ERROR)),
        ]);

        $apps = (new App('test-key', new HttpClient([
            'handler' => HandlerStack::create($mock),
        ])))->GetAppList(['max_results' => 2]);

        self::assertCount(1, $apps);
        self::assertSame(10, $apps[0]->appid);
    }

    private function requestQuery(RequestInterface $request): array
    {
        parse_str((string) $request->getUri()->getQuery(), $query);

        return $query;
    }
}
