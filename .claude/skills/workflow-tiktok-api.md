# Workflow: TikTok API & OAuth

Read this when working on TikTok API integration — OAuth flow, token management, endpoint calls, field selectors, rate limits, and error handling.

## Key Files

| File | Purpose |
|------|---------|
| `app/Services/Platforms/Feeds/Tiktok/TiktokFeed.php` | All API logic — OAuth, token refresh, video fetch, user info |
| `app/Services/Platforms/Feeds/Tiktok/Helper.php` | Static helpers: `getConnectedSourceList()`, credential lookup |
| `app/Hooks/Handlers/PlatformHandler.php` | Registers credential hooks with core plugin |

## TikTok Open API v2 Endpoints

| Endpoint | Method | Purpose |
|----------|--------|---------|
| `https://open.tiktokapis.com/v2/oauth/token/` | POST | Exchange auth code OR refresh token |
| `https://open.tiktokapis.com/v2/user/info/` | GET | Fetch account info (name, avatar, stats) |
| `https://open.tiktokapis.com/v2/video/list/` | POST | Fetch paginated user video feed |

All requests use `Authorization: Bearer {access_token}` header. Base URL stored in `TiktokFeed::$remoteFetchUrl`.

## OAuth Architecture — Middleman Pattern

TikTok plugin does **NOT** handle OAuth directly with TikTok. It uses a middleman site:

```
Admin clicks "Connect TikTok Account"
  │
  ▼
Browser redirects → https://wpsocialninja.com/api/tiktok_callback
  (WPManageNinja's central server — holds the real client_key + client_secret)
  │
  ├─ Middleman exchanges auth code with TikTok API
  ├─ Middleman receives access_token + refresh_token
  └─ Middleman redirects back to site with encrypted payload

Back on WordPress site:
  │
  ▼
TiktokFeed::handleCredential($args)
  ├─ Receives encrypted callback data
  ├─ Decrypts payload (DataProtector)
  └─ Calls saveVerificationConfigs($accessCode)
```

**Why middleman?** TikTok's OAuth requires a registered `redirect_uri`. To avoid exposing `client_secret` in the distributable plugin file, the secret lives only on wpsocialninja.com. The plugin's hardcoded `client_key` is a public identifier (not sensitive), but `client_secret` must never be in the plugin file.

**Current security issue:** `TiktokFeed.php:28-30` still contains `$client_secret` hardcoded. This is a known issue flagged for remediation — the middleman approach should make the local secret unnecessary. **Do not add any new code that reads or uses `$client_secret` from the class property.** Route all OAuth through the middleman.

## Token Storage

Credentials stored in `wpsr_tiktok_connected_sources_config` option:

```php
[
    'sources' => [
        'open_id_abc123' => [
            'display_name'             => 'TikTok Username',
            'avatar_url'               => 'https://p16-sign.tiktokcdn.com/...',
            'profile_url'              => 'https://www.tiktok.com/@username',
            'open_id'                  => 'open_id_abc123',
            'access_token'             => 'ENCRYPTED_via_DataProtector',  // encrypted
            'refresh_token'            => 'PLAINTEXT_TOKEN',               // NOT encrypted — known issue
            'expiration_time'          => 1735689600,   // Unix timestamp
            'refresh_expires_in'       => 31536000,     // seconds (1 year for refresh token)
            'error_message'            => '',
            'error_code'               => null,
            'has_app_permission_error' => false,
            'has_critical_error'       => false,
            'encryption_error'         => false,
        ],
    ],
]
```

**Reading credentials:**
```php
// Always decrypt access_token before API use
$allSources = get_option('wpsr_tiktok_connected_sources_config', []);
$account = $allSources['sources'][$openId] ?? [];
$accessToken = DataProtector::decrypt($account['access_token']);
```

**Writing credentials:** Always use `DataProtector::encrypt()` for `access_token`:
```php
$account['access_token'] = DataProtector::encrypt($newToken);
update_option('wpsr_tiktok_connected_sources_config', $allSources, 'no');
```

**Known issue — refresh_token unencrypted:** `refresh_token` is stored plaintext. The fix is to wrap it with `DataProtector::encrypt()` on save and `DataProtector::decrypt()` on read. Until fixed, never log or expose `refresh_token` values.

## Token Lifecycle

```
OAuth callback received → access_token (expires in ~24h) + refresh_token (expires in ~1 year)
  │
  ▼
TiktokFeed::saveVerificationConfigs()
  ├─ Stores encrypted access_token
  ├─ Stores expiration_time = time() + expires_in
  └─ Stores refresh_expires_in

Each API call → TiktokFeed::maybeRefreshToken($account)
  ├─ if time() >= $account['expiration_time'] - 300:   (300s buffer)
  │   └─ refreshAccessToken($refreshToken, $openId)
  │       ├─ POST /oauth/token/ with grant_type=refresh_token
  │       ├─ Parse new access_token + expiration
  │       ├─ Update option: encrypted access_token + new expiration_time
  │       └─ Return updated account array
  └─ else: return account as-is (token still valid)
```

**Refresh token expiry:** Refresh tokens last ~1 year. When a refresh token expires, `refreshAccessToken()` returns an error and the account must be reconnected manually. `TiktokFeed` sets `has_critical_error = true` on the account and calls `PlatformErrorManager::saveError()`.

## API Call Pattern

All TikTok API calls follow this pattern:

```php
// Step 1: Get and maybe-refresh token
$account = $this->getAccountById($openId);        // from option
$account = $this->maybeRefreshToken($account);    // auto-refresh if expiring
$accessToken = DataProtector::decrypt($account['access_token']);

// Step 2: Make request
$response = wp_remote_post('https://open.tiktokapis.com/v2/video/list/?fields=' . $fields, [
    'headers' => [
        'Authorization' => 'Bearer ' . $accessToken,
        'Content-Type'  => 'application/json; charset=UTF-8',
    ],
    'body'    => json_encode(['max_count' => 20, 'cursor' => $cursor]),
    'timeout' => 20,
    'sslverify' => true,
]);

// Step 3: Check for WP_Error (network failure)
if (is_wp_error($response)) {
    PlatformErrorManager::saveError('tiktok', $response->get_error_message(), 'connection');
    return ['items' => [], 'error_message' => $response->get_error_message()];
}

// Step 4: Parse and check TikTok error structure
$body = json_decode(wp_remote_retrieve_body($response), true);
if (!empty($body['error']['code']) && $body['error']['code'] !== 'ok') {
    $msg = $this->getErrorMessage($body);
    PlatformErrorManager::saveError('tiktok', $msg, 'api');
    // Check for permission revocation:
    if ($body['error']['code'] === 'access_token_invalid') {
        $this->handleAppPermissionError($openId, $body['error']['code']);
    }
    return ['items' => [], 'error_message' => $msg];
}

// Step 5: Success — clear previous error
PlatformErrorManager::clearError('tiktok', 'connection');
do_action('wpsocialreviews/tiktok_feed_api_connect_response', $body);
```

## TikTok Error Response Format

TikTok uses a non-standard error format (different from typical HTTP status codes):

```json
{
    "error": {
        "code": "access_token_invalid",
        "message": "The access token is invalid.",
        "log_id": "...",
        "error_user_msg": "User-facing error message"
    },
    "data": {}
}
```

`TiktokFeed::getErrorMessage($response)` extracts the message in priority order:
1. `$response['error']['error_user_msg']` — user-facing message from TikTok
2. `$response['error']['message']` — technical message
3. `$response['message']` — top-level fallback
4. Generic "Unknown error" string

**Common TikTok error codes:**

| Code | Meaning | Action |
|------|---------|--------|
| `ok` | Success (not an error) | Continue |
| `access_token_invalid` | Token expired or revoked | Set `has_critical_error`, trigger reconnect |
| `spam_risk_too_many_requests` | Rate limited | Backoff, save rate limit timestamp |
| `app_permission_revoked` | User removed app permission | Set `has_app_permission_error`, trigger 7-day delete window |
| `scope_not_authorized` | Token missing required scope | User must reconnect with correct permissions |

## App Permission Revocation

When TikTok revokes app permission (user removes app in TikTok settings):

```php
// TiktokFeed::handleAppPermissionError($openId, $errorCode)
$allSources = get_option('wpsr_tiktok_connected_sources_config', []);
$allSources['sources'][$openId]['has_app_permission_error'] = true;
$allSources['sources'][$openId]['has_critical_error'] = true;
update_option('wpsr_tiktok_connected_sources_config', $allSources, 'no');

PlatformErrorManager::saveError('tiktok', 'App permission revoked...', 'revoked');
do_action('wpsocialreviews/tiktok_feed_app_permission_revoked', $openId);
// PlatformData picks up this action to start the 7-day GDPR deletion window
```

## Video List API — Field Selectors

The video list endpoint requires explicit field selection via query parameter. The fields are filterable:

```php
// Default fields (free plugin)
$defaultFields = 'id,title,duration,cover_image_url,embed_link,create_time';

// Pro fields (extended via filter)
$fields = apply_filters(
    'custom_feed_for_tiktok/tiktok_video_api_details',
    $defaultFields
);
// For single video feed:
$fields = apply_filters(
    'custom_feed_for_tiktok/tiktok_specific_video_api_details',
    $defaultFields
);
```

**Available TikTok v2 video fields:** `id`, `title`, `video_description`, `duration`, `cover_image_url`, `embed_link`, `embed_html`, `share_url`, `create_time`, `like_count`, `comment_count`, `share_count`, `view_count`

**Adding fields in Pro:** Hook `custom_feed_for_tiktok/tiktok_video_api_details` and append needed fields. Match the field to `formatData()` normalization.

## User Info API — Field Selectors

```php
$userFields = 'avatar_url,display_name,profile_deep_link,bio_description,open_id,union_id,'
            . 'is_verified,follower_count,following_count,likes_count,video_count';

// GET https://open.tiktokapis.com/v2/user/info/?fields={$userFields}
```

## Video List Pagination

TikTok's video list API uses cursor-based pagination:

```php
$cursor = 0;
$hasMore = true;
$allVideos = [];

while ($hasMore && count($allVideos) < $totalFeed) {
    $body = ['max_count' => min(20, $totalFeed - count($allVideos)), 'cursor' => $cursor];
    $response = $this->callVideoListApi($accessToken, $fields, $body);

    $allVideos = array_merge($allVideos, $response['data']['videos'] ?? []);
    $hasMore   = $response['data']['has_more'] ?? false;
    $cursor    = $response['data']['cursor'] ?? 0;
}

// Trim to exact requested count
$allVideos = array_slice($allVideos, 0, $totalFeed);
```

**Rate limit context:** TikTok enforces per-user, per-app rate limits. `max_count` per request is capped at 20 by the API. Requesting 100 videos = minimum 5 API calls. Use cache aggressively.

## Debugging API Issues

1. **Feed returns empty, no error shown:**
   - Check `wpsr_tiktok_connected_sources_config` → is `access_token` set for the account?
   - Run `DataProtector::decrypt($account['access_token'])` — does it return a valid string or empty?
   - Check `expiration_time` — is it in the past? Token may have expired AND refresh failed.
   - Check `PlatformErrorManager::getErrors('tiktok')` for stored error

2. **`encryption_error: true` on account:**
   - `DataProtector::decrypt()` returned false on the stored token
   - Usually means the WordPress `AUTH_KEY`/`SECURE_AUTH_KEY` salts changed (migration)
   - Fix: admin must reconnect the account; tokens encrypted with old keys can't be recovered

3. **Token refresh failing repeatedly:**
   - Check `refresh_token` in option — is it non-empty?
   - Verify middleman site is reachable: `wp_remote_post('https://open.tiktokapis.com/v2/oauth/token/')`
   - Check `refresh_expires_in` — if 1-year refresh token expired, reconnect required

4. **OAuth callback not completing:**
   - Check if `CUSTOM_FEED_FOR_TIKTOK_MAIN_FILE` is defined (plugin loaded)
   - Verify `wp_social_reviews_loaded_v2` has fired before the callback reaches `handleCredential()`
   - Check middleman site redirect URL matches the WordPress site URL exactly
