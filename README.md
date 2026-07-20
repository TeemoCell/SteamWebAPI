# Steam Web API

[![Unit Tests](https://github.com/TeemoCell/SteamWebAPI/actions/workflows/php.yml/badge.svg)](https://github.com/TeemoCell/SteamWebAPI/actions/workflows/php.yml)
[![Latest Stable Version](https://poser.pugx.org/teemocell/steam-web-api/v/stable.svg)](https://packagist.org/packages/teemocell/steam-web-api)
[![Total Downloads](https://poser.pugx.org/teemocell/steam-web-api/downloads.svg)](https://packagist.org/packages/teemocell/steam-web-api)
[![License](https://poser.pugx.org/teemocell/steam-web-api/license.svg)](https://packagist.org/packages/teemocell/steam-web-api)

A modern PHP client for the Steam Web API with optional Laravel integration.

## Requirements

- PHP 8.1 or newer
- Laravel 10, 11, 12 or 13 when used inside Laravel
- A [Steam Web API key](https://steamcommunity.com/dev/apikey)

## Installation

```bash
composer require teemocell/steam-web-api
```

## Quick start

```php
use TeemoCell\SteamWebApi\Client;

$steam = new Client(apiKey: $_ENV['STEAM_API_KEY']);

$player = $steam
    ->user(76561197960287930)
    ->GetPlayerSummaries()[0];

echo $player->personaName;
```

The package uses the `TeemoCell\SteamWebApi` PHP namespace.

## Laravel

Add the API key to `.env`:

```dotenv
STEAM_API_KEY=your-key-here
```

The package registers `TeemoCell\SteamWebApi\Client` as a singleton through Laravel package discovery:

```php
use TeemoCell\SteamWebApi\Client;

final class SteamProfileController
{
    public function __invoke(Client $steam, string $steamId)
    {
        return $steam->user($steamId)->GetPlayerSummaries();
    }
}
```

Publish the configuration only when it needs to be customized:

```bash
php artisan vendor:publish --provider="TeemoCell\SteamWebApi\SteamApiServiceProvider"
```

## Supported services

- `ISteamNews`
- `IPlayerService`
- `ISteamUser`
- `ISteamUserStats`
- `IStoreService`
- `IPublishedFileService`
- `ISteamWebAPIUtil`
- read-only `IGameServersService`
- publisher-key methods for `ISteamUser` and `ISteamNews`

Legacy Store and Steam Community endpoints remain available for backwards compatibility.

## Documentation

Full installation, configuration, endpoint and migration documentation is available in the [GitHub Wiki](https://github.com/TeemoCell/SteamWebAPI/wiki):

- [Installation](https://github.com/TeemoCell/SteamWebAPI/wiki/Installation)
- [Configuration](https://github.com/TeemoCell/SteamWebAPI/wiki/Configuration)
- [Laravel integration](https://github.com/TeemoCell/SteamWebAPI/wiki/Laravel-Integration)
- [Client usage](https://github.com/TeemoCell/SteamWebAPI/wiki/Client-Usage)
- [Endpoint reference](https://github.com/TeemoCell/SteamWebAPI/wiki/Endpoints)
- [Error handling](https://github.com/TeemoCell/SteamWebAPI/wiki/Error-Handling)
- [Migration guide](https://github.com/TeemoCell/SteamWebAPI/wiki/Migration-Guide)

## Testing

Run the deterministic offline suite:

```bash
php vendor/bin/phpunit --filter "AppListTest|ClientConstructionTest|CurrentApiEndpointsTest|UserStatsResponseTest|LaravelClientBindingTest"
```

The complete suite contains live Steam API tests and requires `STEAM_API_KEY`.

## Security

Never expose normal API keys, publisher keys or game-server login tokens in client-side code, logs, issues or committed files. Publisher methods must only run on a trusted server.

## Contributing

Bug reports and pull requests are welcome through the [GitHub issue tracker](https://github.com/TeemoCell/SteamWebAPI/issues).

## Contributors

- [Stygiansabyss](https://github.com/stygiansabyss)
- [nicekiwi](https://github.com/nicekiwi)
- [rannmann](https://github.com/rannmann)
- [Amegatron](https://github.com/Amegatron)
- [mjmarianetti](https://github.com/mjmarianetti)
- [MaartenStaa](https://github.com/MaartenStaa)
- [JRizzle88](https://github.com/JRizzle88)
- [jastend](https://github.com/jastend)
- [Teakowa](https://github.com/Teakowa)
- [Ben Sherred](https://github.com/bensherred)

## License

This package is open-source software licensed under the [MIT License](LICENSE).
