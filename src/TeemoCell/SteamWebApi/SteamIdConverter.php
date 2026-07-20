<?php

namespace TeemoCell\SteamWebApi;

class SteamIdConverter
{
    use SteamId;

    public function __construct()
    {
        $this->setUpFormatted();
    }
}
