# Error Handling

The package converts client, server, decoding and empty-response failures into package-specific exceptions where possible.

## Exceptions

| Exception | Meaning |
| --- | --- |
| `InvalidApiKeyException` | The client received an empty or placeholder API key. |
| `ApiArgumentRequired` | A service endpoint was called without required `input_json` arguments. |
| `ApiCallFailedException` | Steam returned an HTTP error, an invalid/empty response or the HTTP client failed. |
| `UnrecognizedId` | A Steam ID cannot be parsed or normalized. |
| `ClassNotFoundException` | A legacy dynamic client method could not resolve an endpoint class. |

## Recommended handling

```php
use TeemoCell\SteamWebApi\Exceptions\ApiCallFailedException;
use TeemoCell\SteamWebApi\Exceptions\InvalidApiKeyException;
use TeemoCell\SteamWebApi\Exceptions\UnrecognizedId;

try {
    $players = $steam->user($steamId)->GetPlayerSummaries();
} catch (InvalidApiKeyException $exception) {
    // Configuration error: alert the operator, but never log the API key.
} catch (UnrecognizedId $exception) {
    // Validation error: ask for a supported Steam ID format.
} catch (ApiCallFailedException $exception) {
    // Remote/API error: inspect the status code and retry only when appropriate.
}
```

`ApiCallFailedException::getPrevious()` contains the original throwable when one exists.

## HTTP status guidance

- `401` or `403`: check the API key, profile privacy and publisher/app permissions.
- `404`: verify the interface, method, app ID and whether a legacy endpoint still exists.
- `429`: respect Steam's rate limits and retry with backoff.
- `5xx`: Steam is unavailable or returned a server error; retry later with bounded backoff.

Do not automatically retry validation errors, `401`, `403` or every empty response. Add timeouts and application-level caching when requests are made during user-facing web requests.

## Logging

Log the endpoint name, HTTP status, app ID and a request correlation ID. Do not log:

- normal Web API keys;
- publisher keys;
- game-server login tokens;
- full URLs containing a `key` query parameter.

## Private data

A user may have a private profile even when the request itself succeeds. Treat missing games, friends or stats as unavailable data rather than automatically treating it as an application error.

