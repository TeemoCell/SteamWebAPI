<?php

namespace Syntax\SteamApi;

class SteamIdConverter
{
    use SteamId;

    public function __construct()
    {
        $this->setUpFormatted();
    }
}
