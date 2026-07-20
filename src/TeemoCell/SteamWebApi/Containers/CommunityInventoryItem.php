<?php

namespace TeemoCell\SteamWebApi\Containers;

use stdClass;

class CommunityInventoryItem
{
    public string $assetId;

    public string $classId;

    public string $instanceId;

    public int $appId;

    public string $contextId;

    public int $amount;

    public ?string $name;

    public ?string $type;

    public ?string $description;

    public ?string $iconUrl;

    public ?string $iconUrlLarge;

    public ?string $marketName;

    public ?string $marketHashName;

    public bool $tradable;

    public bool $marketable;

    public array $tags;

    public function __construct(stdClass $asset, ?stdClass $description = null)
    {
        $this->assetId = (string) $asset->assetid;
        $this->classId = (string) $asset->classid;
        $this->instanceId = (string) ($asset->instanceid ?? '0');
        $this->appId = (int) $asset->appid;
        $this->contextId = (string) $asset->contextid;
        $this->amount = (int) ($asset->amount ?? 1);
        $this->name = isset($description->name) ? (string) $description->name : null;
        $this->type = isset($description->type) ? (string) $description->type : null;
        $this->description = $this->descriptionText($description);
        $this->iconUrl = $this->imageUrl($description->icon_url ?? null);
        $this->iconUrlLarge = $this->imageUrl($description->icon_url_large ?? null);
        $this->marketName = isset($description->market_name) ? (string) $description->market_name : null;
        $this->marketHashName = isset($description->market_hash_name) ? (string) $description->market_hash_name : null;
        $this->tradable = (bool) ($description->tradable ?? false);
        $this->marketable = (bool) ($description->marketable ?? false);
        $this->tags = isset($description->tags) && is_array($description->tags) ? $description->tags : [];
    }

    private function imageUrl(mixed $path): ?string
    {
        if (! is_string($path) || $path === '') {
            return null;
        }

        if (str_starts_with($path, 'https://') || str_starts_with($path, 'http://')) {
            return $path;
        }

        return 'https://community.fastly.steamstatic.com/economy/image/'.ltrim($path, '/');
    }

    private function descriptionText(?stdClass $description): ?string
    {
        if (! isset($description->descriptions) || ! is_array($description->descriptions)) {
            return null;
        }

        $lines = array_values(array_filter(array_map(
            static fn (mixed $line): ?string => isset($line->value) && is_string($line->value) ? trim(strip_tags($line->value)) : null,
            $description->descriptions,
        )));

        return $lines === [] ? null : implode("\n", $lines);
    }
}
