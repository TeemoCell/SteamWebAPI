<?php

namespace TeemoCell\SteamWebApi\Steam;

use TeemoCell\SteamWebApi\Client;
use TeemoCell\SteamWebApi\Containers\Group as GroupContainer;

class Group extends Client
{
    public function GetGroupSummary($group): GroupContainer
    {
        // Set up the api details
        $this->method = 'memberslistxml';

        $this->url = (is_numeric($group)) ? 'https://steamcommunity.com/gid/' : 'https://steamcommunity.com/groups/';

        $this->url .= $group;

        // Set up the arguments
        $arguments = [
            'xml' => 1,
        ];

        // Get the client
        $client = $this->setUpXml($arguments);

        // Clean up the games
        return new GroupContainer($client);
    }
}