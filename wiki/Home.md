# Steam Web API for PHP and Laravel

`teemocell/steam-web-api` is a PHP 8.1+ client for the Steam Web API with optional Laravel 10–13 integration.

## Quick start

```bash
composer require teemocell/steam-web-api
```

```php
use TeemoCell\SteamWebApi\Client;

$steam = new Client(apiKey: $_ENV['STEAM_API_KEY']);
$players = $steam->user(76561197960287930)->GetPlayerSummaries();
```

The package uses the `TeemoCell\SteamWebApi` PHP namespace.

## Documentation

- [Installation](Installation)
- [Configuration](Configuration)
- [Laravel integration](Laravel-Integration)
- [Client usage](Client-Usage)
- [Endpoint reference](Endpoints)
- [Error handling](Error-Handling)
- [Legacy and deprecated methods](Legacy-and-Deprecated-Methods)
- [Testing](Testing)
- [Migration guide](Migration-Guide)

## Requirements

- PHP 8.1 or newer
- Guzzle 7.8 or newer
- PHP extensions: BCMath, cURL, JSON, LibXML and SimpleXML
- A [Steam Web API key](https://steamcommunity.com/dev/apikey) for authenticated calls

Some data is only returned for public Steam profiles. Publisher and game-server methods may require a Steamworks publisher key and must only be called from a trusted server.

## Links

- [GitHub repository](https://github.com/TeemoCell/SteamWebAPI)
- [Issue tracker](https://github.com/TeemoCell/SteamWebAPI/issues)
- [Steamworks Web API documentation](https://partner.steamgames.com/doc/webapi)
