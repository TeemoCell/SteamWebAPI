# Client Usage

## Create the client

```php
use TeemoCell\SteamWebApi\Client;

$steam = new Client(apiKey: $apiKey);
```

Reuse one client instance. Endpoint clients created from it share the API key and HTTP client.

## Endpoint factories

| Factory | Purpose | Steam ID required |
| --- | --- | --- |
| `$steam->news()` | App news | No |
| `$steam->player($steamId)` | Player-owned games, badges and playtime | Yes |
| `$steam->user($steamId)` | Profiles, friends, bans and vanity URLs | Yes |
| `$steam->userStats($steamId)` | User achievements and game stats | For user-specific calls |
| `$steam->app()` | Store app details and the Steam app list | No |
| `$steam->package()` | Store package details | No |
| `$steam->group()` | Steam Community group information | No |
| `$steam->item()` | Legacy player inventories | No |
| `$steam->workshop()` | Published Workshop files | No |
| `$steam->webApi()` | Web API server and interface information | No |
| `$steam->gameServers()` | Read-only game-server account queries | No |
| `$steam->publisher()` | Publisher-key ownership and news calls | Depends on method |

See [Endpoint reference](Endpoints) for every public method.

## Steam ID formats

Player and user factories accept Steam64 IDs and supported community ID formats. IDs are normalized internally.

```php
$player = $steam->player('76561197960287930');
$user = $steam->user('STEAM_0:0:11101');
```

Convert IDs explicitly with `SteamId`:

```php
use TeemoCell\SteamWebApi\SteamId;

$converter = new SteamId();
$steam64 = $converter->convertId('STEAM_0:0:11101', 'id64');
```

Supported output aliases include `id64`, `64`, `id32`, `32`, `id3` and `3`.

## Multiple users

`user()` accepts one Steam ID or an array. Methods such as `GetPlayerSummaries()` and `GetPlayerBans()` can query multiple users.

```php
$users = $steam->user([
    76561197960287930,
    76561197968575517,
])->GetPlayerSummaries();
```

## Responses

Depending on the endpoint, methods return:

- typed container objects or Laravel collections;
- arrays of typed containers;
- decoded Steam API response objects;
- scalar values such as the current player count.

Check the method return type and the [endpoint reference](Endpoints) before persisting a response shape. Steam may omit fields for private profiles or endpoints that require app ownership.

## Privacy and permissions

Steam controls which data is returned. A successful HTTP request can still contain less data than expected when:

- the profile or game details are private;
- the Web API key is not associated with the requested user or app;
- a publisher-only endpoint receives a normal user key;
- a legacy Community endpoint is unavailable.

