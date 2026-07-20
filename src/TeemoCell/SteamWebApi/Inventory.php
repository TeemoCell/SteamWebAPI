<?php

namespace TeemoCell\SteamWebApi;

class Inventory
{
    public function __construct(public $numberOfBackpackSlots, public $items)
    {
    }
}