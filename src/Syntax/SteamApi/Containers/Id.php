<?php

namespace Syntax\SteamApi\Containers;

use Syntax\SteamApi\SteamIdConverter;

class Id
{
    public $id32;

    public $id64;

    public $id3;

    public $communityId;

    public $steamId;

    /**
     * @param integer $id
     */
    function __construct(int $id)
    {
        $steamIds = (new SteamIdConverter())->convertId($id);

        $this->id32 = $steamIds->id32;
        $this->id64 = $steamIds->id64;
        $this->id3 = $steamIds->id3;

        $this->steamId = $this->id64;
        $this->communityId = $this->id32;
    }
}
