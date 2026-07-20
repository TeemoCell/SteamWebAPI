# Migration Guide

## Package rename

The maintained Composer package is:

```text
teemocell/steam-web-api
```

Remove the previously installed package requirement, then install the maintained package:

```bash
composer require teemocell/steam-web-api
```

The package uses the following PHP namespace:

```php
use TeemoCell\SteamWebApi\Client;
```

## API key environment variable

Use:

```dotenv
STEAM_API_KEY=your-key-here
```

Publish or refresh `config/steam-api.php` in Laravel applications if necessary.

## Client construction

Prefer the explicit constructor and typed factory methods:

```php
$steam = new Client(apiKey: $apiKey);

$steam->news();
$steam->player($steamId);
$steam->user($steamId);
$steam->userStats($steamId);
```

## Achievement endpoint

Replace the deprecated alias:

```php
$steam->userStats($steamId)->GetPlayerAchievementsAPI($appId);
```

with:

```php
$steam->userStats($steamId)->GetPlayerAchievements($appId);
```

The corrected method uses `ISteamUserStats/GetPlayerAchievements/v1`. Use `GetPlayerAchievementsFromCommunity()` only when the legacy Community XML response is explicitly required.

## Shared-game ownership

`IsPlayingSharedGame()` is no longer documented by Steam. Publisher integrations can migrate to:

```php
$publisher = (new Client(apiKey: $publisherKey))->publisher();
$ownership = $publisher->CheckAppOwnership($steamId, $appId);
```

This call requires a publisher key and must run on a trusted server.

## Current services

New typed clients are available for:

- `workshop()`
- `webApi()`
- `gameServers()`
- `publisher()`

See [Endpoint reference](Endpoints) for supported methods and access requirements.

## Verification checklist

1. Replace the Composer package name.
2. Use `TeemoCell\SteamWebApi` imports.
3. Confirm `STEAM_API_KEY` is configured.
4. Replace deprecated achievement calls.
5. Run offline tests.
6. Test privacy-sensitive and publisher endpoints with appropriate accounts and keys.
