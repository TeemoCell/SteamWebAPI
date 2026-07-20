# Endpoint Reference

This page lists the public endpoint clients exposed by `TeemoCell\SteamWebApi\Client`. Method names intentionally follow Steam's Web API naming where practical.

## News

```php
$news = $steam->news();
```

| Method | Description |
| --- | --- |
| `GetNewsForApp(int $appId, int $count = 5, ?int $maxLength = null, ?int $endDate = null, array|string|null $feeds = null)` | Returns app news from `ISteamNews/GetNewsForApp/v2`. |

```php
$items = $steam->news()->GetNewsForApp(
    appId: 620,
    count: 10,
    maxLength: 500,
    feeds: ['steam_community_announcements'],
)->newsitems;
```

## Player service

```php
$player = $steam->player($steamId);
```

| Method | Description |
| --- | --- |
| `GetSteamLevel()` | Returns the user's Steam level. |
| `GetPlayerLevelDetails()` | Returns the level plus XP details. |
| `GetBadges()` | Returns the user's badges. |
| `GetCommunityBadgeProgress($badgeId = null)` | Returns community badge progress. |
| `GetOwnedGames(bool $includeAppInfo = true, bool $includePlayedFreeGames = false, array $appIdsFilter = [])` | Returns owned games when profile privacy permits it. |
| `GetRecentlyPlayedGames(?int $count = null)` | Returns games played during the recent Steam activity window. |
| `GetSingleGamePlaytime(int $appId)` | Returns playtime for an app when the key is associated with that app. |

`IsPlayingSharedGame()` remains for compatibility but is no longer documented by Steam. See [Legacy and deprecated methods](Legacy-and-Deprecated-Methods).

## Steam user

```php
$user = $steam->user($steamId);
```

| Method | Description |
| --- | --- |
| `ResolveVanityURL(?string $displayName = null, int $urlType = 1)` | Resolves a profile, group or game-group vanity URL. |
| `GetPlayerSummaries(?string $steamId = null)` | Returns summaries for one or multiple users. |
| `GetPlayerBans($steamId = null)` | Returns ban information. |
| `GetFriendList(string $relationship = 'all', bool $summaries = true)` | Returns friend IDs or full player summaries. |

`urlType` values are `1` for an individual profile, `2` for a group and `3` for an official game group.

## User stats

User-specific calls require a Steam ID:

```php
$stats = $steam->userStats($steamId);
```

| Method | Description |
| --- | --- |
| `GetPlayerAchievements(int $appId)` | Returns user achievements from the official Web API. |
| `GetGlobalAchievementPercentagesForApp(int $appId)` | Returns global unlock percentages. |
| `GetUserStatsForGame(int $appId, bool $all = false)` | Returns user stats, optionally including the full response. |
| `GetSchemaForGame(int $appId)` | Returns the game's stat and achievement schema. |

App-wide calls do not require a Steam ID:

```php
$count = $steam->userStats()->GetNumberOfCurrentPlayers(620);
```

## Store apps

```php
$apps = $steam->app();
```

| Method | Description |
| --- | --- |
| `appDetails(int|array $appIds, ?string $country = null, ?string $language = null)` | Returns legacy Steam Store app details. |
| `GetAppList(array $arguments = [])` | Reads all matching pages from `IStoreService/GetAppList/v1`. |

Arguments supported by Steam, such as `include_dlc`, `if_modified_since`, `last_appid` and `max_results`, can be passed to `GetAppList()`.

```php
$apps = $steam->app()->GetAppList([
    'include_dlc' => true,
    'max_results' => 50000,
]);
```

## Store packages

```php
$packages = $steam->package()->packageDetails(
    packId: 469,
    cc: 'de',
    language: 'german',
);
```

`packageDetails()` uses the legacy Steam Store endpoint.

## Workshop

`QueryFiles(array $arguments)` forwards documented `IPublishedFileService/QueryFiles/v1` filters through `input_json`. Cursor pagination starts at `*` when no cursor is supplied.

```php
$files = $steam->workshop()->QueryFiles([
    'query_type' => 1,
    'appid' => 620,
    'return_tags' => true,
]);
```

## Web API utilities

| Method | Description |
| --- | --- |
| `GetServerInfo()` | Returns Steam Web API server information. |
| `GetSupportedAPIList()` | Returns interfaces and methods available to the key. |

```php
$interfaces = $steam->webApi()->GetSupportedAPIList();
```

## Game servers

Only read-only `IGameServersService` operations are exposed.

| Method | Description |
| --- | --- |
| `GetAccountList()` | Lists game-server accounts available to the key. |
| `GetAccountPublicInfo(int|string $steamId)` | Returns public account information. |
| `QueryLoginToken(string $loginToken)` | Queries a login token. |
| `GetServerSteamIDsByIP(array|string $serverIps)` | Resolves server IPs to Steam IDs. |
| `GetServerIPsBySteamID(array|string $serverSteamIds)` | Resolves server Steam IDs to IPs. |

Account creation, token reset and deletion are deliberately not exposed because they mutate server credentials.

## Publisher API

These methods require a Steamworks publisher key on a trusted server.

```php
use TeemoCell\SteamWebApi\Client;

$publisher = (new Client(apiKey: $publisherKey))->publisher();
```

| Method | Description |
| --- | --- |
| `CheckAppOwnership(int|string $steamId, int $appId)` | Checks publisher-side app ownership. |
| `GetNewsForAppAuthed(...)` | Returns authenticated app news. |

## Legacy community services

The group, inventory and Community achievement clients remain available for compatibility. See [Legacy and deprecated methods](Legacy-and-Deprecated-Methods) before using them in a new application.

