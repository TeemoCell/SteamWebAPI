<?php

namespace TeemoCell\SteamWebApi\Steam\User;

use Exception;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use TeemoCell\SteamWebApi\Client;
use TeemoCell\SteamWebApi\Containers\Achievement;
use TeemoCell\SteamWebApi\Exceptions\ApiCallFailedException;

class Stats extends Client
{
    public function __construct($steamId = null, ?string $apiKey = null, ?ClientInterface $client = null)
    {
        parent::__construct($apiKey, $client);
        $this->url = 'https://partner.steam-api.com/';
        $this->interface = 'ISteamUserStats';
        $this->steamId = $steamId;
    }

    /**
     * @param $appId
     *
     * @return array|null
     * @throws GuzzleException
     * @throws ApiCallFailedException
     * @deprecated Use GetPlayerAchievements().
     *
     */
    public function GetPlayerAchievementsAPI($appId): ?array
    {
        return $this->GetPlayerAchievements($appId);
    }

    public function GetPlayerAchievements($appId): ?array
    {
        $this->setStatsApiDetails(__FUNCTION__, 'v1');

        $response = $this->setUpClient([
            'steamid' => $this->steamId,
            'appid' => $appId,
            'l' => 'english',
        ]);

        if (! isset($response->playerstats->achievements)
            || ! is_array($response->playerstats->achievements)) {
            return null;
        }

        return $this->convertToObjects($response->playerstats->achievements);
    }

    /**
     * @deprecated Community XML is a legacy fallback. Use GetPlayerAchievements().
     */
    public function GetPlayerAchievementsFromCommunity($appId): ?array
    {
        $this->interface = null;
        $this->method = 'achievements';

        $this->url = (is_numeric($this->steamId)) ? 'https://steamcommunity.com/profiles/' : 'https://steamcommunity.com/id/';

        $this->url = $this->url.$this->steamId.'/stats/'.$appId;

        // Set up the arguments
        $arguments = [
            'xml' => 1,
        ];

        try {
            // Get the client
            $client = $this->setUpXml($arguments);

            // Clean up the games
            return $this->convertToObjects($client->achievements->achievement);
        } catch (Exception) {
            // In rare cases, games can force the use of a simplified name instead of an app ID
            // In these cases, try again by grabbing the redirected url.
            if (is_int($appId)) {
                $this->getRedirectUrl();

                try {
                    // Get the client
                    $client = $this->setUpXml($arguments);

                    // Clean up the games
                    return $this->convertToObjects($client->achievements->achievement);
                } catch (Exception) {
                    return null;
                }
            }

            // If the name and ID fail, return null.
            return null;
        }
    }

    /**
     * @throws ApiCallFailedException
     * @throws GuzzleException
     */
    public function GetGlobalAchievementPercentagesForApp($gameId)
    {
        $this->setStatsApiDetails(__FUNCTION__, 'v2');

        // Set up the arguments
        $arguments = [
            'gameid' => $gameId,
            'l' => 'english',
        ];

        // Get the client
        $response = $this->setUpClient($arguments);

        if (! is_object($response)
            || ! property_exists($response, 'achievementpercentages')
            || ! is_object($response->achievementpercentages)
            || ! property_exists($response->achievementpercentages, 'achievements')) {
            return [];
        }

        return $response->achievementpercentages->achievements;
    }

    /**
     * @param $appId int Steam 64 id
     * @param $all   bool Return all stats when true and only achievements when false
     *
     * @return mixed
     * @throws ApiCallFailedException
     * @throws GuzzleException
     */
    public function GetUserStatsForGame(int $appId, bool $all = false): mixed
    {
        $this->setStatsApiDetails(__FUNCTION__, 'v2');

        // Set up the arguments
        $arguments = [
            'steamid' => $this->steamId,
            'appid' => $appId,
            'l' => 'english',
        ];

        // Get the client
        $response = $this->setUpClient($arguments);

        if (! is_object($response)
            || ! property_exists($response, 'playerstats')
            || ! is_object($response->playerstats)) {
            return [];
        }

        $client = $response->playerstats;

        // Games like DOTA and CS:GO have additional stats here.  Return everything if they are wanted.
        if ($all === true) {
            return $client;
        }

        if (! property_exists($client, 'achievements')) {
            return [];
        }

        return $client->achievements;
    }

    /**
     * @param $appId
     *
     * @return mixed
     * @throws ApiCallFailedException
     * @throws GuzzleException
     * @link https://wiki.teamfortress.com/wiki/WebAPI/GetSchemaForGame
     *
     */
    public function GetSchemaForGame($appId): mixed
    {
        $this->setStatsApiDetails(__FUNCTION__, 'v2');

        // Set up the arguments
        $arguments = [
            'appid' => $appId,
            'l' => 'english',
        ];

        // Get the client
        return $this->setUpClient($arguments);
    }

    /**
     * Return the number of players currently connected to Steam for an app.
     *
     * @throws ApiCallFailedException
     * @throws GuzzleException
     */
    public function GetNumberOfCurrentPlayers(int $appId): int
    {
        $this->setStatsApiDetails(__FUNCTION__, 'v1');

        $response = $this->setUpClient(['appid' => $appId]);

        return (int) ($response->response->player_count ?? 0);
    }

    protected function convertToObjects($achievements): array
    {
        $cleanedAchievements = [];

        foreach ($achievements as $achievement) {
            $cleanedAchievements[] = new Achievement($achievement);
        }

        return $cleanedAchievements;
    }

    private function setStatsApiDetails(string $method, string $version): void
    {
        $this->url = 'https://partner.steam-api.com/';
        $this->interface = 'ISteamUserStats';
        $this->method = $method;
        $this->version = $version;
    }
}