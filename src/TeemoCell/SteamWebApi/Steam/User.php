<?php

namespace TeemoCell\SteamWebApi\Steam;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use InvalidArgumentException;
use TeemoCell\SteamWebApi\Client;
use TeemoCell\SteamWebApi\Containers\Player as PlayerContainer;
use TeemoCell\SteamWebApi\Exceptions\ApiArgumentRequired;
use TeemoCell\SteamWebApi\Exceptions\ApiCallFailedException;
use TeemoCell\SteamWebApi\Exceptions\UnrecognizedId;

class User extends Client
{
    private array $friendRelationships = [
        'all',
        'friend',
    ];

    public function __construct($steamId, ?string $apiKey = null, ?ClientInterface $client = null)
    {
        parent::__construct($apiKey, $client);
        $this->url = 'https://partner.steam-api.com/';
        $this->interface = 'ISteamUser';
        $this->steamId = $steamId;
    }

    /**
     * Get the user_ids for a display name.
     *
     * @param null $displayName Custom name from steam profile link.
     *
     * @return mixed
     *
     * @throws ApiArgumentRequired
     * @throws ApiCallFailedException
     * @throws GuzzleException
     * @throws UnrecognizedId
     * @throws \JsonException
     */
    public function ResolveVanityURL($displayName = null, int $urlType = 1): mixed
    {
        // This only works with a display name.  Make sure we have one.
        if ($displayName == null) {
            throw new UnrecognizedId('You must pass a display name for this call.');
        }

        // Set up the api details
        $this->method = __FUNCTION__;
        $this->version = 'v1';

        $results = $this->getClientResponse([
            'vanityurl' => $displayName,
            'url_type' => $urlType,
        ]);

        // Return the full steam ID object for the display name.
        return $results->message ?? $this->convertId($results->steamid);
    }

    /**
     * @param string|null $steamId
     *
     * @return array
     */
    public function GetPlayerSummaries(?string $steamId = null): array
    {
        // Set up the api details
        $this->method = __FUNCTION__;
        $this->version = 'v2';

        if ($steamId == null) {
            $steamId = $this->steamId;
        }

        $steamId = implode(',', (array) $steamId);

        $chunks = array_chunk(explode(',', $steamId), 100);

        $map = array_map($this->getChunkedPlayerSummaries(...), $chunks);

        return $this->compressPlayerSummaries($map);
    }

    /**
     * @param $chunk
     * @return array
     * @throws ApiCallFailedException
     * @throws GuzzleException
     * @throws \JsonException
     * @throws ApiArgumentRequired
     */
    private function getChunkedPlayerSummaries($chunk): array
    {
        // Set up the arguments
        $arguments = [
            'steamids' => implode(',', $chunk),
        ];

        // Get the client
        $client = $this->setUpClient($arguments)->response;

        // Clean up the games
        return $this->convertToObjects($client->players);
    }

    private function compressPlayerSummaries($summaries): array
    {
        $result = [];
        $keys = array_keys($summaries);

        foreach ($keys as $key) {
            $result = array_merge($result, $summaries[$key]);
        }

        return $result;
    }

    /**
     * @throws ApiCallFailedException
     * @throws GuzzleException
     */
    public function GetPlayerBans($steamId = null)
    {
        // Set up the api details
        $this->method = __FUNCTION__;
        $this->version = 'v1';

        if ($steamId == null) {
            $steamId = $this->steamId;
        }

        // Set up the arguments
        $arguments = [
            'steamids' => implode(',', (array) $steamId),
        ];

        // Get the client
        $client = $this->setUpClient($arguments);

        return $client->players;
    }

    /**
     * @throws ApiCallFailedException
     * @throws GuzzleException
     */
    public function GetFriendList($relationship = 'all', $summaries = true): array
    {
        // Set up the api details
        $this->method = __FUNCTION__;
        $this->version = 'v1';

        if (! in_array($relationship, $this->friendRelationships)) {
            throw new InvalidArgumentException("Provided relationship [$relationship] is not valid.  Please select from: ".implode(', ', $this->friendRelationships));
        }

        // Set up the arguments
        $arguments = [
            'steamid' => $this->steamId,
            'relationship' => $relationship,
        ];

        // Get the client
        $client = $this->setUpClient($arguments)->friendslist;

        // Clean up the games
        $steamIds = [];

        foreach ($client->friends as $friend) {
            $steamIds[] = $friend->steamid;
        }

        if ($summaries) {
            $friends = $this->GetPlayerSummaries(implode(',', $steamIds));
        } else {
            $friends = $steamIds;
        }

        return $friends;
    }

    protected function convertToObjects($players): array
    {
        $cleanedPlayers = [];

        foreach ($players as $player) {
            if (property_exists($player, 'steamid')) {
                $cleanedPlayers[] = new PlayerContainer($player);
            }
        }

        return $cleanedPlayers;
    }
}