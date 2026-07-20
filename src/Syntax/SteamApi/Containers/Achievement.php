<?php

namespace Syntax\SteamApi\Containers;

class Achievement
{
    public $apiName;

    public $achieved;

    public $name;

    public $description;

    public $icon;

    public $iconGray;

    public $unlockTimestamp;

    public function __construct($achievement)
    {
        if (! $achievement instanceof \SimpleXMLElement) {
            $this->apiName = (string) ($achievement->apiname ?? '');
            $this->achieved = (int) ($achievement->achieved ?? 0);
            $this->name = isset($achievement->name) ? (string) $achievement->name : null;
            $this->description = isset($achievement->description) ? (string) $achievement->description : null;
            $this->icon = isset($achievement->icon) ? (string) $achievement->icon : null;
            $this->iconGray = isset($achievement->icongray) ? (string) $achievement->icongray : null;
            $this->unlockTimestamp = isset($achievement->unlocktime)
                ? (int) $achievement->unlocktime
                : null;

            return;
        }

        $this->apiName = (string) $achievement->apiname;
        $this->achieved = (int) (string) $achievement['closed'];
        $this->name = (string) $achievement->name;
        $this->description = (string) $achievement->description;
        $this->icon = (string) $achievement->iconClosed;
        $this->iconGray = (string) $achievement->iconOpen;
        $this->unlockTimestamp = isset($achievement->unlockTimestamp) ? (int) (string) $achievement->unlockTimestamp : null;
    }
}
