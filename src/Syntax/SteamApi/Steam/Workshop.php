<?php

namespace Syntax\SteamApi\Steam;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Syntax\SteamApi\Client;
use Syntax\SteamApi\Exceptions\ApiArgumentRequired;
use Syntax\SteamApi\Exceptions\ApiCallFailedException;

class Workshop extends Client
{
    public function __construct(?string $apiKey = null, ?ClientInterface $client = null)
    {
        parent::__construct($apiKey, $client);
        $this->url = 'https://api.steampowered.com/';
        $this->interface = 'IPublishedFileService';
        $this->isService = true;
    }

    /**
     * Query Steam Workshop items. Pass the official QueryFiles filters as an
     * associative array. Cursor pagination starts at "*" by default.
     *
     * @throws ApiArgumentRequired
     * @throws ApiCallFailedException
     * @throws GuzzleException
     * @throws \JsonException
     */
    public function QueryFiles(array $arguments): mixed
    {
        $this->method = __FUNCTION__;
        $this->version = 'v1';
        $arguments['cursor'] ??= '*';

        return $this->getServiceResponse($arguments);
    }
}
