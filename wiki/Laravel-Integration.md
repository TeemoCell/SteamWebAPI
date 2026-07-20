# Laravel Integration

Laravel package discovery registers `TeemoCell\SteamWebApi\SteamApiServiceProvider`. The provider binds `TeemoCell\SteamWebApi\Client` as a singleton and also registers the `steam-api` container alias.

## Dependency injection

Constructor injection is the recommended Laravel integration:

```php
<?php

namespace App\Http\Controllers;

use TeemoCell\SteamWebApi\Client;

final class SteamProfileController
{
    public function __invoke(Client $steam, string $steamId)
    {
        return $steam->user($steamId)->GetPlayerSummaries();
    }
}
```

The singleton receives the key configured in `steam-api.steamApiKey`.

## Facade

The facade remains available for applications that prefer it:

```php
use TeemoCell\SteamWebApi\Facades\SteamApi;

$games = SteamApi::player($steamId)->GetOwnedGames();
```

Dependency injection is easier to replace in unit tests and makes dependencies explicit.

## Configuration

Publish the configuration when it needs to be customized:

```bash
php artisan vendor:publish --provider="TeemoCell\SteamWebApi\SteamApiServiceProvider"
```

Set the API key in `.env`:

```dotenv
STEAM_API_KEY=your-key-here
```

See [Configuration](Configuration) for custom Guzzle clients and publisher-key handling.

