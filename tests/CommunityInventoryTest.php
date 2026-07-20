<?php

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use TeemoCell\SteamWebApi\Containers\CommunityInventoryItem;
use TeemoCell\SteamWebApi\Steam\CommunityInventory;

class CommunityInventoryTest extends TestCase
{
    public function test_public_inventory_is_paginated_and_enriched_with_descriptions(): void
    {
        [$client, $history] = $this->clientWithResponses([
            [
                'success' => 1,
                'total_inventory_count' => 2,
                'more_items' => 1,
                'last_assetid' => '100',
                'assets' => [[
                    'appid' => 730,
                    'contextid' => '2',
                    'assetid' => '100',
                    'classid' => '10',
                    'instanceid' => '0',
                    'amount' => '1',
                ]],
                'descriptions' => [[
                    'classid' => '10',
                    'instanceid' => '0',
                    'name' => 'Test item',
                    'type' => 'Weapon',
                    'descriptions' => [['value' => '<b>First line</b>']],
                    'icon_url' => 'images/test.png',
                    'market_name' => 'Test Item',
                    'market_hash_name' => 'Test Item (Factory New)',
                    'tradable' => 1,
                    'marketable' => 1,
                    'tags' => [['category' => 'Type', 'name' => 'Weapon']],
                ]],
            ],
            [
                'success' => 1,
                'total_inventory_count' => 2,
                'more_items' => 0,
                'assets' => [[
                    'appid' => 730,
                    'contextid' => '2',
                    'assetid' => '101',
                    'classid' => '11',
                    'instanceid' => '0',
                    'amount' => '2',
                ]],
                'descriptions' => [[
                    'classid' => '11',
                    'instanceid' => '0',
                    'name' => 'Second item',
                    'icon_url' => 'https://cdn.example.test/item.png',
                    'tradable' => 0,
                    'marketable' => 0,
                ]],
            ],
        ]);

        $inventory = (new CommunityInventory('test-key', $client))->GetInventory(
            '76561198336555523',
            730,
            2,
            'german',
            100,
        );

        self::assertSame(2, $inventory->totalCount);
        self::assertSame(2, $inventory->pages);
        self::assertCount(2, $inventory->items);

        $first = $inventory->items->first();
        self::assertInstanceOf(CommunityInventoryItem::class, $first);
        self::assertSame('Test item', $first->name);
        self::assertSame('First line', $first->description);
        self::assertSame(
            'https://community.fastly.steamstatic.com/economy/image/images/test.png',
            $first->iconUrl,
        );
        self::assertSame('Test Item (Factory New)', $first->marketHashName);
        self::assertTrue($first->tradable);
        self::assertTrue($first->marketable);

        $firstRequest = $history->requests[0]['request'];
        $secondRequest = $history->requests[1]['request'];
        $firstQuery = $this->requestQuery($firstRequest);
        $secondQuery = $this->requestQuery($secondRequest);

        self::assertSame('steamcommunity.com', $firstRequest->getUri()->getHost());
        self::assertSame('/inventory/76561198336555523/730/2', $firstRequest->getUri()->getPath());
        self::assertSame('german', $firstQuery['l']);
        self::assertSame('100', $firstQuery['count']);
        self::assertArrayNotHasKey('key', $firstQuery);
        self::assertSame('100', $secondQuery['start_assetid']);
    }

    public function test_inventory_rejects_non_positive_page_size(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new CommunityInventory('test-key'))->GetInventory(
            '76561198336555523',
            730,
            count: 0,
        );
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

    private function requestQuery(RequestInterface $request): array
    {
        parse_str((string) $request->getUri()->getQuery(), $query);

        return $query;
    }
}
