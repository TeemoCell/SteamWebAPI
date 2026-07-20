<?php

namespace TeemoCell\SteamWebApi\Steam;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Collection;
use TeemoCell\SteamWebApi\Client;
use TeemoCell\SteamWebApi\CommunityInventoryResult;
use TeemoCell\SteamWebApi\Containers\CommunityInventoryItem;
use TeemoCell\SteamWebApi\Exceptions\ApiCallFailedException;

/**
 * Experimental client for Steam's undocumented public Community inventory.
 * The endpoint may change independently of the documented Steamworks API.
 */
class CommunityInventory extends Client
{
    private const MAX_PAGES = 1000;

    public function __construct(?string $apiKey = null, ?ClientInterface $client = null)
    {
        parent::__construct($apiKey, $client);
        $this->url = 'https://steamcommunity.com/inventory/';
    }

    /**
     * @throws ApiCallFailedException
     * @throws GuzzleException
     */
    public function GetInventory(
        int|string $steamId,
        int $appId,
        int|string $contextId = 2,
        string $language = 'english',
        int $count = 2000,
    ): CommunityInventoryResult {
        if ($count < 1) {
            throw new \InvalidArgumentException('Inventory count must be greater than zero.');
        }

        $steamId = (string) $this->convertId($steamId, 'id64');
        $items = new Collection();
        $totalCount = 0;
        $startAssetId = null;
        $pages = 0;

        do {
            if (++$pages > self::MAX_PAGES) {
                throw new ApiCallFailedException('Community inventory pagination limit exceeded.', 500);
            }

            $arguments = [
                'l' => $language,
                'count' => $count,
            ];

            if ($startAssetId !== null) {
                $arguments['start_assetid'] = $startAssetId;
            }

            $url = "{$this->url}$steamId/$appId/".rawurlencode((string) $contextId).'?'.http_build_query($arguments);
            $body = $this->sendRequest(new Request('GET', $url))->body;

            if ((int) ($body->success ?? 0) !== 1) {
                throw new ApiCallFailedException('Steam Community inventory request failed.', 502);
            }

            $descriptions = $this->indexDescriptions($body->descriptions ?? []);

            foreach ($body->assets ?? [] as $asset) {
                $key = $this->descriptionKey($asset);
                $items->add(new CommunityInventoryItem($asset, $descriptions[$key] ?? null));
            }

            $totalCount = max($totalCount, (int) ($body->total_inventory_count ?? $items->count()));
            $hasMoreItems = (bool) ($body->more_items ?? false);
            $nextAssetId = isset($body->last_assetid) ? (string) $body->last_assetid : null;

            if ($hasMoreItems && ($nextAssetId === null || $nextAssetId === $startAssetId)) {
                throw new ApiCallFailedException('Steam Community inventory returned an invalid pagination cursor.', 502);
            }

            $startAssetId = $nextAssetId;
        } while ($hasMoreItems);

        return new CommunityInventoryResult($items, $totalCount, $pages);
    }

    private function indexDescriptions(array $descriptions): array
    {
        $indexed = [];

        foreach ($descriptions as $description) {
            $indexed[$this->descriptionKey($description)] = $description;
        }

        return $indexed;
    }

    private function descriptionKey(object $value): string
    {
        return (string) $value->classid.'_'.(string) ($value->instanceid ?? '0');
    }
}