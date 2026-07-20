<?php

namespace TeemoCell\SteamWebApi\Steam;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use TeemoCell\SteamWebApi\Client;
use Illuminate\Support\Collection;
use TeemoCell\SteamWebApi\Containers\Item as ItemContainer;
use TeemoCell\SteamWebApi\Exceptions\ApiCallFailedException;
use TeemoCell\SteamWebApi\Inventory;

class Item extends Client
{
    public function __construct(?string $apiKey = null, ?ClientInterface $client = null)
    {
        parent::__construct($apiKey, $client);

        $this->url = 'https://store.steampowered.com/';
        $this->isService = true;
        $this->interface = 'api';
    }

    /**
     * @deprecated Use communityInventory()->GetInventory() for public Community
     *             inventories or publisher()->GetInventory() for your own app.
     *
     * @throws ApiCallFailedException
     * @throws GuzzleException
     */
    public function GetPlayerItems($appId, $steamId): Inventory
    {
        // Set up the api details
        $this->url = 'https://api.steampowered.com/';
        $this->interface = 'IEconItems_'.$appId;
        $this->method = __FUNCTION__;
        $this->version = 'v1';

        $arguments = ['steamid' => $steamId];

        $client = $this->setUpClient($arguments);

        // Clean up the items
        $items = $this->convertToObjects($client->result->items);

        // Return a full inventory
        return new Inventory($client->result->num_backpack_slots, $items);
    }

    protected function convertToObjects($items): Collection
    {
        return $this->convertItems($items);
    }

    /**
     * @param array $items
     *
     * @return Collection
     */
    protected function convertItems(array $items): Collection
    {
        $convertedItems = new Collection();

        foreach ($items as $item) {
            $convertedItems->add(new ItemContainer($item));
        }

        return $convertedItems;
    }
}
