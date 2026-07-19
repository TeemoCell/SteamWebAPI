<?php

namespace Syntax\SteamApi\Steam;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Syntax\SteamApi\Client;
use Illuminate\Support\Collection;
use Syntax\SteamApi\Containers\App as AppContainer;
use Syntax\SteamApi\Exceptions\ApiCallFailedException;
use Syntax\SteamApi\Exceptions\InvalidApiKeyException;

class App extends Client
{
    /**
     * @throws InvalidApiKeyException
     */

    public function __construct(?string $apiKey = null, ?ClientInterface $client = null)
    {
        parent::__construct($apiKey, $client);

        $this->url = 'http://store.steampowered.com/';
        $this->interface = 'api';
    }

    /**
     * @param $appIds
     * @param string|null $country
     * @param string|null $language
     * @return Collection
     * @throws ApiCallFailedException
     * @throws GuzzleException
     */
    public function appDetails($appIds, $country = null, $language = null): Collection
    {
        // Set up the api details
        $this->method = 'appdetails';
        $this->version = null;

        // Set up the arguments
        $arguments = [
            'appids' => $appIds,
            'cc' => $country,
            'l' => $language,
        ];

        // Get the client
        $client = $this->setUpClient($arguments);

        return $this->convertToObjects($client);
    }

    /**
     * @throws ApiCallFailedException
     * @throws GuzzleException
     */
    public function GetAppList(array $arguments = []): array
    {
        $this->url = 'https://partner.steam-api.com/';
        $this->interface = 'IStoreService';
        $this->method = __FUNCTION__;
        $this->version = 'v1';

        $arguments['max_results'] = min(
            max((int) ($arguments['max_results'] ?? 50000), 1),
            50000,
        );

        $apps = [];
        $lastAppId = $arguments['last_appid'] ?? null;

        do {
            $response = $this->getServiceResponse($arguments);
            $page = isset($response->apps) && is_array($response->apps)
                ? $response->apps
                : [];

            if ($page === []) {
                break;
            }

            array_push($apps, ...$page);

            $lastApp = end($page);
            $nextAppId = is_object($lastApp) && isset($lastApp->appid)
                ? $lastApp->appid
                : null;
            $hasMoreResults = isset($response->have_more_results)
                ? (bool) $response->have_more_results
                : count($page) >= $arguments['max_results'];

            if (! $hasMoreResults || $nextAppId === null || $nextAppId === $lastAppId) {
                break;
            }

            $lastAppId = $nextAppId;
            $arguments['last_appid'] = $lastAppId;
        } while (true);

        return $apps;
    }

    protected function convertToObjects($apps): Collection
    {
        $convertedApps = $this->convertGames($apps);

        return $this->sortObjects($convertedApps);
    }

    /**
     * @param $apps
     *
     * @return Collection
     */
    protected function convertGames($apps): Collection
    {
        $convertedApps = new Collection();

        foreach ($apps as $app) {
            if (isset($app->data)) {
                $convertedApps->add(new AppContainer($app->data));
            }
        }

        return $convertedApps;
    }
}