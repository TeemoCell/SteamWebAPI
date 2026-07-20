<?php

namespace TeemoCell\SteamWebApi\Steam;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use TeemoCell\SteamWebApi\Client;
use TeemoCell\SteamWebApi\Exceptions\ApiCallFailedException;

class News extends Client
{
    public function __construct(?string $apiKey = null, ?ClientInterface $client = null)
    {
        parent::__construct($apiKey, $client);
        $this->interface = 'ISteamNews';
    }

    /**
     * @throws ApiCallFailedException
     * @throws GuzzleException
     */
    public function GetNewsForApp(
        $appId,
        $count = 5,
        $maxLength = null,
        $endDate = null,
        array|string|null $feeds = null,
    ) {
        // Set up the api details
        $this->method = __FUNCTION__;
        $this->version = 'v2';

        // Set up the arguments
        $arguments = [
            'appid' => $appId,
            'count' => $count,
        ];

        if (! is_null($maxLength)) {
            $arguments['maxlength'] = $maxLength;
        }

        if (! is_null($endDate)) {
            $arguments['enddate'] = $endDate;
        }

        if (! is_null($feeds)) {
            $arguments['feeds'] = implode(',', (array) $feeds);
        }

        // Get the client
        $client = $this->setUpClient($arguments);

        return $client->appnews;
    }
}
