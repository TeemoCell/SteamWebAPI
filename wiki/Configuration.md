# Configuration

## API key resolution

The client accepts the API key in its constructor:

```php
use TeemoCell\SteamWebApi\Client;

$steam = new Client(apiKey: $apiKey);
```

In Laravel, the package reads `STEAM_API_KEY` from the `steam-api` configuration.

```dotenv
STEAM_API_KEY=your-key-here
```

Never expose a normal Web API key—and especially not a publisher key—in browser JavaScript, a mobile application or a game client.

## Publish the Laravel configuration

```bash
php artisan vendor:publish --provider="TeemoCell\SteamWebApi\SteamApiServiceProvider"
```

This creates `config/steam-api.php`:

```php
return [
    'steamApiKey' => env('STEAM_API_KEY'),
];
```

After changing `.env` in a cached production deployment, refresh the Laravel configuration cache:

```bash
php artisan config:cache
```

## Custom HTTP client

Any PSR-18-compatible Guzzle `ClientInterface` implementation can be injected. This is useful for timeouts, proxies, logging and tests.

```php
use GuzzleHttp\Client as HttpClient;
use TeemoCell\SteamWebApi\Client;

$http = new HttpClient([
    'timeout' => 10,
    'connect_timeout' => 5,
]);

$steam = new Client(apiKey: $apiKey, client: $http);
```

The same HTTP client is reused by endpoint clients created through `$steam->user()`, `$steam->player()` and the other factory methods.

## Publisher keys

Publisher-only calls use a separate client so that normal and privileged credentials are not mixed:

```php
$publisher = (new Client(apiKey: $publisherKey))->publisher();
```

Store publisher keys only in trusted server-side secret storage.

