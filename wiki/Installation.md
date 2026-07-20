# Installation

## Requirements

The package supports PHP 8.1 and newer and Laravel 10 through 13. The required PHP extensions are listed in `composer.json`.

## Install with Composer

```bash
composer require teemocell/steam-web-api
```

Composer loads the package through PSR-4 under the `TeemoCell\SteamWebApi` namespace.

## Create a Steam Web API key

Create a key at [steamcommunity.com/dev/apikey](https://steamcommunity.com/dev/apikey). Keep it on the server and do not commit it to Git.

For framework-independent PHP, pass the key directly:

```php
use TeemoCell\SteamWebApi\Client;

$steam = new Client(apiKey: $_ENV['STEAM_API_KEY']);
```

For Laravel, add it to `.env`:

```dotenv
STEAM_API_KEY=your-key-here
```

Laravel package discovery registers the service provider automatically. See [Laravel integration](Laravel-Integration) for dependency injection, the facade and configuration publishing.

## Development installation

```bash
git clone https://github.com/TeemoCell/SteamWebAPI.git
cd SteamWebAPI
composer install
```

Copy `.env.example` to `.env` only when live API tests are needed. Offline tests do not require a real key.
