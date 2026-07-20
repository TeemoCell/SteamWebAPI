# Legacy and Deprecated Methods

Some methods remain available so existing applications do not break, but new integrations should prefer documented Steamworks Web API interfaces.

## Deprecated aliases

### `GetPlayerAchievementsAPI()`

Use `GetPlayerAchievements()` instead:

```php
$achievements = $steam
    ->userStats($steamId)
    ->GetPlayerAchievements($appId);
```

### `GetPlayerAchievementsFromCommunity()`

This reads the legacy Steam Community XML endpoint. It depends on public community data and may be less reliable than the official Web API. Prefer `GetPlayerAchievements()`.

### `IsPlayingSharedGame()`

Steam no longer documents this player-service endpoint. Existing calls remain available for backwards compatibility. Publisher integrations should use:

```php
$ownership = $steam
    ->publisher()
    ->CheckAppOwnership($steamId, $appId);
```

`CheckAppOwnership()` requires a publisher key.

## Legacy Store and Community endpoints

| Method | Legacy source | Recommendation |
| --- | --- | --- |
| `app()->appDetails()` | Steam Store app-details endpoint | Continue only when Store metadata is required; use `GetAppList()` for the official app list. |
| `package()->packageDetails()` | Steam Store package-details endpoint | Cache responses and handle missing packages. |
| `item()->GetPlayerItems()` | Legacy inventory endpoint | Verify game support before relying on it. |
| `group()->GetGroupSummary()` | Steam Community XML | Handle unavailable/private groups and XML failures. |
| `GetPlayerAchievementsFromCommunity()` | Steam Community XML | Use the official Web API method instead. |

Legacy does not necessarily mean immediately broken. It means the endpoint is outside the current documented Steamworks interface and can change with less notice.

## Compatibility policy

Deprecated methods should remain usable during a compatible release line. New code should avoid them so they can be removed in a future major version after a documented migration period.

