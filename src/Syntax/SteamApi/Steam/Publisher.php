<?php

namespace Syntax\SteamApi\Steam;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Syntax\SteamApi\Client;
use Syntax\SteamApi\Exceptions\ApiCallFailedException;

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
}
