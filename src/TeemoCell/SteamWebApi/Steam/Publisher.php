<?php

namespace TeemoCell\SteamWebApi\Steam;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use JsonException;
use TeemoCell\SteamWebApi\Client;
use TeemoCell\SteamWebApi\Exceptions\ApiCallFailedException;

/**
 * Endpoints in this client require a Steamworks publisher key and must only be
 * called from a trusted server.
 */
class Publisher extends Client
{
    public function __construct(?string $apiKey = null, ?ClientInterface $client = null)
    {
        parent::__construct($apiKey, $client);
        $this->url = 'https://partner.steam-api.com/';
    }

    /**
     * @throws ApiCallFailedException
     * @throws GuzzleException
     */
    public function CheckAppOwnership(int|string $steamId, int $appId): mixed
    {
        $this->url = 'https://partner.steam-api.com/';
        $this->interface = 'ISteamUser';
        $this->method = __FUNCTION__;
        $this->version = 'v4';

        return $this->setUpClient([
            'steamid' => $steamId,
            'appid' => $appId,
        ]);
    }

    /**
     * @throws ApiCallFailedException
     * @throws GuzzleException
     */
    public function GetNewsForAppAuthed(
        int $appId,
        int $count = 20,
        ?int $maxLength = null,
        ?int $endDate = null,
        array|string|null $feeds = null,
    ): mixed {
        $this->url = 'https://partner.steam-api.com/';
        $this->interface = 'ISteamNews';
        $this->method = __FUNCTION__;
        $this->version = 'v2';

        $arguments = [
            'appid' => $appId,
            'count' => $count,
        ];

        if ($maxLength !== null) {
            $arguments['maxlength'] = $maxLength;
        }

        if ($endDate !== null) {
            $arguments['enddate'] = $endDate;
        }

        if ($feeds !== null) {
            $arguments['feeds'] = implode(',', (array) $feeds);
        }

        return $this->setUpClient($arguments)->appnews;
    }

    /**
     * @return array<int, mixed>
     *
     * @throws ApiCallFailedException
     * @throws GuzzleException
     */
    public function GetInventory(int $appId, int|string $steamId): array
    {
        $this->setInventoryServiceDetails('GetInventory');

        $response = $this->getServiceResponse([
            'appid' => $appId,
            'steamid' => (string) $steamId,
        ]);

        return $this->decodeInventoryJson($response->item_json ?? null, 'item_json');
    }

    /**
     * @return array<int, mixed>
     *
     * @throws ApiCallFailedException
     * @throws GuzzleException
     */
    public function GetItemDefs(int $appId, array $filters = []): array
    {
        $this->setInventoryServiceDetails('GetItemDefs');

        $response = $this->getServiceResponse(['appid' => $appId] + $filters);

        return $this->decodeInventoryJson($response->itemdef_json ?? null, 'itemdef_json');
    }

    /**
     * Returns Steam Inventory Service store prices, not Community Market resale
     * prices.
     *
     * @throws ApiCallFailedException
     * @throws GuzzleException
     */
    public function GetPriceSheet(int $currency): mixed
    {
        $this->url = 'https://api.steampowered.com/';
        $this->interface = 'IInventoryService';
        $this->setApiDetails('GetPriceSheet', 'v1');

        return $this->getServiceResponse(['ecurrency' => $currency]);
    }

    private function setInventoryServiceDetails(string $method): void
    {
        $this->url = 'https://partner.steam-api.com/';
        $this->interface = 'IInventoryService';
        $this->setApiDetails($method, 'v1');
    }

    /**
     * @return array<int, mixed>
     *
     * @throws ApiCallFailedException
     */
    private function decodeInventoryJson(mixed $json, string $field): array
    {
        if (! is_string($json) || $json === '') {
            throw new ApiCallFailedException("Steam Inventory Service did not return $field.", 502);
        }

        try {
            $decoded = json_decode($json, false, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new ApiCallFailedException(
                "Steam Inventory Service returned invalid $field.",
                502,
                $exception,
            );
        }

        if (! is_array($decoded)) {
            throw new ApiCallFailedException("Steam Inventory Service returned invalid $field.", 502);
        }

        return $decoded;
    }
}
