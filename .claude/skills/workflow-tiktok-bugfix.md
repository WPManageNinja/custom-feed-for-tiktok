# Workflow: TikTok Bug Fixing & Known Issues

Read this before fixing any TikTok-specific bug. Covers known issues, fixed bugs, security problems unique to this plugin, and TikTok-specific pitfalls.

> **Prerequisite:** Read `wp-social-reviews/.claude/skills/workflow-bugfix.md` first. This skill adds TikTok-specific items on top of the core bug-fix process.

## Known Security Issues (Unresolved)

### CRITICAL-01 — Hardcoded OAuth Client Secret

**Location:** `app/Services/Platforms/Feeds/Tiktok/TiktokFeed.php:28-30`

```php
private $client_key    = 'aw4cddbhcvsbl34m';
private $client_secret = 'IV2QhJ7nxhvEthCI2QqZTTPpoNZOPZB6';
private $redirect_uri  = 'https://wpsocialninja.com/api/tiktok_callback';
```

**Risk:** `client_secret` is in the distributable plugin file. Anyone with file system access can extract it and impersonate the OAuth application.

**Intended fix:** The middleman site (`wpsocialninja.com`) should handle all secret usage server-side. The local `$client_secret` property should be unused. **Do not write new code that reads `$this->client_secret`.** If you find a code path calling the local secret, route it through the middleman instead.

**Do not commit a "fix" that moves the secret to a WordPress option** — that is equally insecure. The secret belongs only on the middleman server.

### MEDIUM-01 — Refresh Token Stored Unencrypted

**Location:** `TiktokFeed.php:157, 270` — `saveVerificationConfigs()` and `refreshAccessToken()`

```php
// Current (insecure):
$account['refresh_token'] = $response['refresh_token'];

// Correct fix:
$account['refresh_token'] = DataProtector::encrypt($response['refresh_token']);

// And on read:
$refreshToken = DataProtector::decrypt($account['refresh_token']);
```

`access_token` is encrypted; `refresh_token` is not. Both are long-lived credentials. Until fixed, do not log or expose the `refresh_token` value in any output, admin UI, or error message.

**When implementing the fix:** Search for all reads of `$account['refresh_token']` and wrap with `DataProtector::decrypt()`. Search for all writes and wrap with `DataProtector::encrypt()`.

---

## Recently Fixed Bugs (DO NOT reintroduce)

### FIX-01 — PHP 7.4 Fatal Error (`str_contains` → `strpos`)

**PR:** #4 | **Location:** `app/Views/public/feeds-templates/tiktok/elements/media.php:16`

**Was:**
```php
if (str_contains($media_url, 'placeholder')) {  // PHP 8.0+ only
```
**Fixed to:**
```php
if (strpos($media_url, 'placeholder') !== false) {  // PHP 7.4 compatible
```

**Rule:** Never use PHP 8.0+ functions (`str_contains`, `str_starts_with`, `str_ends_with`, `array_is_list`, `match` with no-match exception, `named arguments`) in this plugin. The declared minimum is PHP 7.4. Use `phpcs` or manual review.

### FIX-02 — Feed Pagination Over-fetch

**PR:** #5 | **Location:** `TiktokFeed.php` in `getAccountFeed()`

**Was:** Calculated `$pages = ceil($totalFeed / $perPage)` and then fetched all pages, always requesting one extra API call beyond what was needed.

**Fixed to:** Track `$remainingPages = max(0, $pages - 1)` — i.e., after the initial fetch, only fetch additional pages if needed.

**Rule:** When modifying pagination logic, always verify the page count formula doesn't over-request. TikTok API quota is shared across all connected accounts and all sites using the same OAuth app.

### FIX-03 — Author Name Config Mismapping

**PR:** #6 | **Location:** `Config.php:63` and `elements/author-name.php:8`

**Was:**
```php
// Config.php — author_name reading from wrong key:
'display_author_name' => $settings['post_settings']['display_author_photo'] ?? 'true',
```
**Fixed to:**
```php
'display_author_name' => $settings['post_settings']['display_author_name'] ?? 'true',
```

**Symptom:** Toggling "Display Author Photo" also hid/showed the author name independently. They couldn't be controlled separately.

**Rule:** In `Config.php`, every setting key must read from its own matching key name. When adding new settings, always verify the source key matches the destination key — they are identical in name but it's easy to copy-paste the wrong one.

### FIX-04 — Oxygen Widget Fallback Broken

**PR:** #7 | **Location:** `app/Services/Widgets/Oxygen/OxygenWidget.php:451`

**Was:**
```php
return '[wp_ social_ninja id="' . $id . '" platform="tiktok"]';
//           ^ space breaks shortcode tag resolution
```
**Fixed to:**
```php
return '[wp_social_ninja id="' . $id . '" platform="tiktok"]';
```

**Rule:** Shortcode tag names must match exactly. `[wp_social_ninja]` is registered by the core plugin — no spaces, no variations.

---

## TikTok-Specific Pitfalls

### PITFALL-01 — Token Decryption Fails After Migration

**Symptom:** All accounts show `encryption_error: true`, feeds return empty.

**Cause:** WordPress `AUTH_KEY` / `SECURE_AUTH_KEY` salts changed (site migration, security reset). `DataProtector::decrypt()` returns `false` for tokens encrypted with the old salts.

**Fix:** Tokens cannot be recovered. Admin must disconnect and reconnect all TikTok accounts. Document this in migration runbooks.

**Prevention:** Before a site migration, export tokens from the old environment → re-encrypt with new salts → import. Or: reconnect all accounts post-migration.

### PITFALL-02 — GDPR + Image Optimization Compound Requirement

**Symptom:** Feed doesn't display on a site with GDPR mode enabled, no error shown.

**Cause:** `TiktokFeed::getTemplateMeta()` returns empty feeds when `has_gdpr = true` AND `optimized_images !== 'true'`. This is intentional — serving TikTok CDN URLs would ping TikTok servers without consent.

**Fix options:**
1. Enable image optimization (`wpsr_tiktok_global_settings.global_settings.optimized_images = 'true'`)
2. Implement a user consent gate before rendering the widget
3. Disable GDPR mode if the site has another consent solution

**Do not remove this check** — it exists for GDPR compliance. If you see a bug report "feed not showing", check this compound condition first.

### PITFALL-03 — Background Cron Hook vs. Missing Schedule

**Symptom:** TikTok feeds become stale (old data) even after TTL expires.

**Cause:** `wpsr_tiktok_send_email_report` cron fires `updateCachedFeeds()`. If this cron isn't scheduled (plugin deactivation + reactivation cycle, or WP-Cron disabled on host), background refresh never runs. With only proactive refresh (no lazy rebuild), feeds stay stale until the cron fires or a manual clear is triggered.

**Diagnosis:**
```bash
wp cron event list | grep tiktok
```

**Fix:**
- Verify cron registration in `PlatformHandler::registerHooks()` or plugin activation
- If WP-Cron is disabled on the host, ensure a real server cron calls `wp-cron.php`
- Admin "Clear Cache" button → `TiktokFeed::clearCache()` forces a fresh API fetch on next render

### PITFALL-04 — Cache Key Collision Between Templates

**Symptom:** Two TikTok templates for the same account but different counts (e.g., 6 and 12 videos) show the same videos.

**Cause:** Cache key includes `_num_{totalFeed}` so they SHOULD be distinct. If collision occurs, check if `$totalFeed` is being read from the same settings path in both templates. A bug where `$totalFeed` is hardcoded or defaulted to the same value would cause collision.

**Verify:**
```php
// Expected cache keys:
'user_feed_id_openId123_num_6'   // template A
'user_feed_id_openId123_num_12'  // template B
```
If both generate `_num_10`, the `feed_count` setting is not being read from per-template config.

### PITFALL-05 — `single_video_feed` Ignores `feed_count` Limit

**Symptom:** Curated single-video feed shows more or fewer videos than configured.

**Cause:** `single_video_feed_ids` is a comma-separated string of video IDs. The count is determined by how many IDs the admin entered, not by `feed_count`. The `feed_count` setting may apply a secondary trim but the primary control is the ID list length.

**Fix pattern:**
```php
$videoIds = array_filter(array_map('trim', explode(',', $settings['single_video_feed_ids'])));
// Count is count($videoIds), not $settings['feed_count']
// Apply feed_count as a max cap: $videoIds = array_slice($videoIds, 0, $settings['feed_count']);
```

### PITFALL-06 — `created_at` is Unix Timestamp, Not MySQL Datetime

**Symptom:** Date display shows `1970-01-01` or wrong year. Sort order wrong after format conversion.

**Cause:** `formatData()` stores `created_at` as a Unix timestamp integer (e.g., `1705312800`), not a MySQL datetime string. Template files and any sort logic must handle this correctly.

**In template files:**
```php
// Wrong:
echo date('Y-m-d', strtotime($feed['created_at']));  // strtotime on integer = NaN

// Correct:
echo date('Y-m-d', intval($feed['created_at']));      // integer directly to date()
// or:
echo wp_date('Y-m-d', intval($feed['created_at']));   // WordPress-aware timezone
```

**In sort logic:**
```php
// Correct (integer comparison):
usort($items, fn($a, $b) => $b['created_at'] <=> $a['created_at']);  // descending

// Wrong (would fail if converted to string elsewhere):
usort($items, fn($a, $b) => strcmp($b['created_at'], $a['created_at']));
```

### PITFALL-07 — Empty `api.php` Routes File

**Symptom:** Developer adds REST routes to `app/Http/Routes/api.php` expecting them to be registered — they aren't.

**Cause:** `api.php` is an **empty placeholder**. The TikTok plugin has no REST routes. The file is required by `Application.php` but contains no route definitions. Adding routes here requires also wiring the router instance — follow the core plugin pattern where `$router` is passed from the base application.

**Rule:** Do not add REST routes to this plugin without first verifying the router instance is available and understanding the core plugin's route registration pattern (`app/Http/Routes/api.php` in core plugin for reference).

---

## TikTok-Specific Security Checklist

Apply in addition to the core plugin security checklist (`workflow-bugfix.md`):

- [ ] No new code reads `$this->client_secret` — all OAuth goes through middleman
- [ ] `refresh_token` wrapped in `DataProtector::encrypt()` on write, `decrypt()` on read
- [ ] `access_token` always decrypted via `DataProtector::decrypt()` before API use — never stored or logged in plaintext
- [ ] `open_id` values from user input: `sanitize_text_field()` — never used in SQL without sanitization
- [ ] Video IDs from `single_video_feed_ids`: split by comma, `sanitize_text_field()` each, then passed as array to API — never string-interpolated
- [ ] Media URLs from TikTok API (CDN): escape with `esc_url()` before `<img src>` or `<a href>` in templates
- [ ] `created_at` timestamps: `intval()` before `date()` — never trust string format from API
- [ ] GDPR compound check preserved — do not remove the `has_gdpr + optimized_images` guard in `getTemplateMeta()`
- [ ] PHP 7.4 compatibility maintained — no `str_contains`, `str_starts_with`, named arguments, `match` exceptions
- [ ] AJAX `wpsr_get_more_feeds` handler verifies nonce: `check_ajax_referer('wpsr_tiktok_nonce', 'nonce')`
