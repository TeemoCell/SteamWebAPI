<?php

namespace TeemoCell\SteamWebApi\Exceptions;

class ApiArgumentRequired extends \Exception
{
    public function __construct()
    {
        parent::__construct('Arguments are required for this service.');
    }
}
