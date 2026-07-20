<?php

namespace TeemoCell\SteamWebApi\Steam;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use TeemoCell\SteamWebApi\Client;
use TeemoCell\SteamWebApi\Exceptions\ApiCallFailedException;

class WebApi extends Client
{
    public function __construct(?string $apiKey = null, ?ClientInterface $client = null)
    {
        parent::__construct($apiKey, $client);
        $this->interface = 'ISteamWebAPIUtil';
    }

    /**
     * @throws ApiCallFailedException
     * @throws GuzzleException
     */
    public function GetServerInfo(): mixed
    {
        $this->setApiDetails(__FUNCTION__, 'v1');

        return $this->setUpClient();
    }

    /**
     * @throws ApiCallFailedException
     * @throws GuzzleException
     */
    public function GetSupportedAPIList(): mixed
    {
        $this->setApiDetails(__FUNCTION__, 'v1');

        return $this->setUpClient();
    }
}
