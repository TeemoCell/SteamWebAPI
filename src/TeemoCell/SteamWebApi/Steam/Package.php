<?php

namespace TeemoCell\SteamWebApi\Steam;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use TeemoCell\SteamWebApi\Client;
use Illuminate\Support\Collection;
use TeemoCell\SteamWebApi\Containers\Package as PackageContainer;
use TeemoCell\SteamWebApi\Exceptions\ApiCallFailedException;

class Package extends Client
{
    public function __construct(?string $apiKey = null, ?ClientInterface $client = null)
    {
        parent::__construct($apiKey, $client);

        $this->url = 'https://store.steampowered.com/';
        $this->interface = 'api';
    }

    /**
     * @throws ApiCallFailedException
     * @throws GuzzleException
     */
    public function packageDetails($packId, $cc = null, $language = null): Collection
    {
        // Set up the api details
        $this->method = 'packagedetails';
        $this->version = null;
        // Set up the arguments
        $arguments = [
            'packageids' => $packId,
            'cc' => $cc,
            'l' => $language,
        ];
        // Get the client
        $client = $this->setUpClient($arguments);

        return $this->convertToObjects($client, $packId);
    }

    protected function convertToObjects($package, $packId): Collection
    {
        $convertedPacks = $this->convertPacks($package, $packId);
        return $this->sortObjects($convertedPacks);
    }

    /**
     * @param $packages
     * @param $packId
     * @return Collection
     */
    protected function convertPacks($packages, $packId): Collection
    {
        $convertedPacks = new Collection();
        foreach ($packages as $package) {
            if (isset($package->data)) {
                $convertedPacks->add(new PackageContainer($package->data, $packId));
            }
        }

        return $convertedPacks;
    }
}
