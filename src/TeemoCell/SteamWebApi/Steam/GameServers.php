<?php

namespace TeemoCell\SteamWebApi\Steam;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use stdClass;
use TeemoCell\SteamWebApi\Client;
use TeemoCell\SteamWebApi\Exceptions\ApiArgumentRequired;
use TeemoCell\SteamWebApi\Exceptions\ApiCallFailedException;

class GameServers extends Client
{
    public function __construct(?string $apiKey = null, ?ClientInterface $client = null)
    {
        parent::__construct($apiKey, $client);
        $this->interface = 'IGameServersService';
        $this->isService = true;
    }

    /**
     * @throws ApiArgumentRequired
     * @throws ApiCallFailedException
     * @throws GuzzleException
     * @throws \JsonException
     */
    public function GetAccountList(): mixed
    {
        return $this->call(__FUNCTION__, new stdClass());
    }

    public function GetAccountPublicInfo(int|string $steamId): mixed
    {
        return $this->call(__FUNCTION__, ['steamid' => $steamId]);
    }

    public function QueryLoginToken(string $loginToken): mixed
    {
        return $this->call(__FUNCTION__, ['login_token' => $loginToken]);
    }

    public function GetServerSteamIDsByIP(array|string $serverIps): mixed
    {
        return $this->call(__FUNCTION__, [
            'server_ips' => implode(',', (array) $serverIps),
        ]);
    }

    public function GetServerIPsBySteamID(array|string $serverSteamIds): mixed
    {
        return $this->call(__FUNCTION__, [
            'server_steamids' => implode(',', (array) $serverSteamIds),
        ]);
    }

    private function call(string $method, array|stdClass $arguments): mixed
    {
        $this->setApiDetails($method, 'v1');

        return $this->getServiceResponse($arguments);
    }
}
