# Testing

## Install development dependencies

```bash
composer install
```

## Offline tests

The construction, Laravel binding and current-endpoint tests use mocked HTTP responses and do not require a real Steam API key:

```bash
php vendor/bin/phpunit --filter "AppListTest|ClientConstructionTest|CurrentApiEndpointsTest|UserStatsResponseTest|LaravelClientBindingTest"
```

## Full test suite

Live API tests need a valid key and depend on Steam, profile visibility and network availability.

PowerShell:

```powershell
$env:STEAM_API_KEY = 'your-key-here'
php vendor\bin\phpunit
```

Bash:

```bash
STEAM_API_KEY=your-key-here php vendor/bin/phpunit
```

Never paste a real API key into issue descriptions, test output or committed configuration.

## Composer checks

```bash
composer validate --strict --no-check-publish
composer audit --locked
```

## Rector

Check proposed automated refactors without changing files:

```bash
php vendor/bin/rector process --dry-run
```

## Docker

```bash
docker-compose build
docker-compose run --rm php composer install
docker-compose run --rm php composer test
```

Pass `STEAM_API_KEY` into the container only for the live suite.

## Adding endpoint tests

Prefer Guzzle mock responses and history middleware for endpoint URL, API version and query-parameter assertions. This keeps CI deterministic and prevents accidental API-key exposure.

