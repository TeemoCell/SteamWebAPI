<?php

namespace TeemoCell\SteamWebApi\Exceptions;

class InvalidApiKeyException extends \Exception
{
    public function __construct()
    {
        parent::__construct('You must use a valid API key to connect to steam.');
    }
}
