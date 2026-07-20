<?php

namespace TeemoCell\SteamWebApi;

use Illuminate\Support\Collection;

class CommunityInventoryResult
{
    public function __construct(
        public Collection $items,
        public int $totalCount,
        public int $pages,
    ) {
    }
}
