# Workflow: TikTok Feed Pipeline

Read this when working on TikTok feed types, multi-source aggregation, the Config normalizer, data formatting, caching, and pagination.

> **Prerequisite:** Read `wp-social-reviews/.claude/skills/workflow-feeds.md` and `workflow-caching.md` first. This skill documents TikTok-specific behaviour only.

## Key Files

| File | Purpose |
|------|---------|
| `app/Services/Platforms/Feeds/Tiktok/TiktokFeed.php` | Core service — all feed logic (1213 lines) |
| `app/Services/Platforms/Feeds/Tiktok/Config.php` | Settings normalizer — `formatTiktokConfig()` (1110 lines) |
| `app/Services/Platforms/Feeds/Tiktok/Helper.php` | `getConnectedSourceList()`, credential lookup statics |
| `app/Hooks/Handlers/TiktokTemplateHandler.php` | `getPaginatedFeedHtml()` for load-more AJAX |

## Feed Types

| `feed_type` | Description | API endpoint |
|-------------|-------------|-------------|
| `user_feed` | All public videos from one or more connected accounts | `POST /video/list/` |
| `single_video_feed` | Specific video IDs curated by admin | `POST /video/list/` with `filters.video_ids` |

Both types use the same API endpoint and `formatData()` normalizer. The difference is the request body.

## Full Feed Fetch Lifecycle

```
[Shortcode render] → ShortcodeHandler::renderTiktokTemplate()
  │
  ▼
apply_filters('wpsocialreviews/get_template_meta', $meta, $postId)
  → TiktokTemplateHandler::getTemplateMeta($meta)
    → TiktokFeed::getTemplateMeta($settings, $postId)
      │
      ├─ Load template config from post meta: get_post_meta($postId, '_wpsr_template_config', true)
      ├─ Parse via Config::formatTiktokConfig($raw, [])
      │
      ├─ Determine feed_type from config
      │
      ├─ [user_feed]   → apiConnection($apiSettings)
      │   └─ getMultipleFeeds($apiSettings)
      │       └─ For each selected account:
      │           └─ getAccountFeed($account, $apiSettings, $cache)
      │               ├─ maybeRefreshToken($account)
      │               ├─ CacheHandler::getCache('tiktok', $openId, $cacheKey)
      │               │   ├─ HIT + valid → return cached
      │               │   └─ MISS/expired → fetch from API
      │               ├─ callVideoListApi($accessToken, $fields, $body)  [paginated]
      │               ├─ getAccountDetails($account, $accessToken)
      │               ├─ formatData($rawVideos)
      │               └─ CacheHandler::storeCache('tiktok', $openId, $data, $ttlHours, $cacheKey)
      │
      ├─ [single_video_feed] → same flow, body includes filter.video_ids
      │
      ├─ FeedFilters::apply($items, $filterSettings)
      │   (keyword include/exclude, post order, hide_posts_by_id)
      │
      ├─ Apply image optimization (if enabled + not GDPR-blocked)
      │
      └─ Return $templateMeta: { feed_settings, feeds, account_details, error_html, ... }
```

## Multi-Source Aggregation (`getMultipleFeeds`)

When multiple TikTok accounts are selected (`selected_accounts` array in feed settings):

```php
// TiktokFeed::getMultipleFeeds($apiSettings)
$allItems = [];
$errorMessage = '';

foreach ($apiSettings['selected_accounts'] as $openId) {
    $account = $this->getAccountById($openId);
    if (!$account) continue;

    $result = $this->getAccountFeed($account, $apiSettings, $cacheKey);
    if (!empty($result['error_message'])) {
        $errorMessage = $result['error_message'];  // Last error wins
        continue;
    }
    $allItems = array_merge($allItems, $result['items']);
}

// After merge — apply sort order across all accounts
if ($apiSettings['post_order'] === 'descending') {
    usort($allItems, fn($a, $b) => $b['created_at'] <=> $a['created_at']);
} else {
    usort($allItems, fn($a, $b) => $a['created_at'] <=> $b['created_at']);
}

// Trim to total requested count
$allItems = array_slice($allItems, 0, $apiSettings['feed_count']);
```

**Important:** Each account fetches its own full `feed_count` from the API. After merging, items are sorted and trimmed. This means account A may contribute 8 items while account B contributes 2, depending on recency. The `selected_accounts` order does not affect final ordering — only `post_order` and `created_at` timestamps do.

**Free vs Pro feed count limit:**
```php
$maxCount = apply_filters('custom_feed_for_tiktok/tiktok_feeds_limit', 10);
// Free: 10 videos per account
// Pro: 200 videos per account (Pro plugin hooks this filter)
```

## Cache Key Strategy

TikTok uses **named cache keys** (not hashed settings) because the feed type and count are deterministic:

```php
// User feed cache key
$cacheKey = 'user_feed_id_' . $openId . '_num_' . $totalFeed;

// Single video feed cache key
$cacheKey = 'single_video_feed_id_' . $openId . '_template_' . $postId . '_num_' . $count;

// Account header cache key (user info — avatar, name, stats)
$cacheKey = 'user_account_header_' . $openId;
```

**Note:** Unlike the core feed platform pattern (which uses `md5(serialize($settings))`), TikTok uses **human-readable cache keys**. This makes `clearFeedCache($openId)` explicit:

```php
// TiktokFeed::clearFeedCache($openId)
CacheHandler::clearCache('tiktok', $openId . '_' . 'user_feed');
CacheHandler::clearCache('tiktok', $openId . '_' . 'single_video_feed');
CacheHandler::clearCache('tiktok', $openId . '_' . 'header');
```

**TTL source:**
```php
$globalSettings = get_option('wpsr_tiktok_global_settings', []);
$ttlSeconds = $globalSettings['global_settings']['expiration'] ?? 86400;  // default 24h
$ttlHours = ceil($ttlSeconds / 3600);
CacheHandler::storeCache('tiktok', $openId, $data, $ttlHours, $cacheKey);
```

## Background Cache Refresh

TikTok uses proactive background refresh (not lazy rebuild):

```php
// Cron hook: wpsr_tiktok_send_email_report
// Handler: TiktokFeed::updateCachedFeeds($caches)
foreach ($caches as $cacheKey => $cacheData) {
    // Re-fetch API data for this cache entry
    // Store fresh data — no gap for end-users
}
```

This cron does NOT use `wp_schedule_event()` directly — verify in `PlatformHandler::registerHooks()` how the cron is registered.

## formatData() — Normalized Video Schema

`TiktokFeed::formatData($rawVideos)` converts TikTok API response to internal format:

```php
// Input: $rawVideos = $response['data']['videos']  (array of TikTok video objects)
// Output per item:
[
    'id'          => 'tiktok_video_id',
    'created_at'  => 1705312800,          // Unix timestamp (NOT converted to MySQL datetime)
    'title'       => 'Video title/caption',
    'text'        => 'Video caption text',
    'user'        => [
        'id'                => $account['open_id'],
        'name'              => $account['display_name'],
        'profile_image_url' => $account['avatar_url'],
        'profile_url'       => $account['profile_url'],
    ],
    'statistics'  => [
        'like_count'    => 0,   // Available from API if in fields list
        'view_count'    => 0,
        'comment_count' => 0,
        'share_count'   => 0,
    ],
    'media'       => [
        'url'               => 'https://p16-sign.tiktokcdn.com/...',  // cover_image_url
        'preview_image_url' => 'https://p16-sign.tiktokcdn.com/...',
        'duration'          => 15,  // seconds
    ],
]
```

**Critical:** `created_at` is a **Unix timestamp**, not a MySQL datetime string. The template PHP files handle date formatting. Don't convert in `formatData()` — the sort in `getMultipleFeeds()` uses `<=>` on integers.

**Statistics availability:** Engagement stats (`like_count`, `view_count`, etc.) are only populated when included in the `fields` parameter of the API request. If stats show as 0, check the `custom_feed_for_tiktok/tiktok_video_api_details` filter.

## Config::formatTiktokConfig() — Settings Normalizer

`Config.php` (1110 lines) is the **single source of truth** for all template settings. It normalizes raw post meta into a structured PHP array with defaults for every setting.

**How to add a new setting:**

1. Add the key to the relevant section in `formatTiktokConfig()`:
```php
// In the 'post_settings' section:
'display_my_new_setting' => $settings['post_settings']['display_my_new_setting'] ?? 'false',
```

2. Add default rendering in the relevant template element file (`elements/*.php`)
3. Add the control to the admin editor UI (Vue component in core plugin or Pro)
4. Add to `getStyleElement()` if it affects styling

**Never access `_wpsr_template_config` post meta directly in template files** — always go through `Config::formatTiktokConfig()` so defaults apply.

## Load-More / AJAX Pagination

TikTok feed uses client-side slice pagination — all feed items are fetched and cached server-side; pagination reveals slices:

```
Initial render:   shows items 0 to (paginate_number - 1)
Load More click:  AJAX → wp_ajax_wpsr_get_more_feeds
                    → TiktokTemplateHandler::getPaginatedFeedHtml($templateId, $page)
                    → Recalculate slice: sinceId = page * paginate_number
                    → Return rendered HTML for next slice
```

```php
// TiktokTemplateHandler::getPaginatedFeedHtml($templateId, $page)
$perPage = $settings['pagination_settings']['paginate_number']['desktop'] ?? 9;
$sinceId = intval($page) * $perPage;
$maxId   = $sinceId + $perPage;

// Re-fetch the full cached feed (no new API call — cache hit)
$feedData = TiktokFeed::getTemplateMeta($settings, $templateId);

// Slice and render items $sinceId to $maxId
// Return ob_get_clean() HTML string
```

**Pro template support:** `getPaginatedFeedHtml()` checks for Pro template rendering:
```php
$proHtml = apply_filters('custom_feed_for_tiktok/add_tiktok_feed_template', '', $templateId, $page, $settings);
if ($proHtml) return $proHtml;
// else fall through to free template1.php rendering
```

## Feed Filters (Post-Fetch)

After fetching and merging, `FeedFilters` from the core plugin applies:

```php
// From Config feed_settings.filters:
'includes_inputs'  => 'comma,separated,keywords',   // keep only items containing any keyword
'excludes_inputs'  => 'comma,separated,keywords',   // drop items containing any keyword
'hide_posts_by_id' => 'id1,id2,id3',               // drop specific video IDs
'post_order'       => 'descending',                 // date sort direction
```

Filtering runs on `text`/`title` fields. It is applied **after** cache retrieval — the cache stores unfiltered data, filters apply at render time. This allows different templates with different filters to share one cache entry per account.

## GDPR + Image Optimization Interaction

There is a **compound requirement** for GDPR-safe image serving:

```php
// TiktokFeed::getTemplateMeta()
$hasGdpr       = $advanceSettings['has_gdpr'] ?? false;
$optimizeImages = $globalSettings['optimized_images'] ?? 'false';

if ($hasGdpr === true && $optimizeImages !== 'true') {
    // Feed is intentionally hidden — return empty feeds + GDPR notice
    return ['feeds' => [], 'gdpr_notice' => true, ...];
}
```

**Rule:** GDPR mode requires local image storage. If GDPR is ON but image optimization is OFF, the feed does not display (to prevent loading TikTok CDN images which would ping TikTok servers before user consent).

To serve TikTok feeds under GDPR:
1. Enable image optimization: `wpsr_tiktok_global_settings.global_settings.optimized_images = 'true'`
2. Optionally: implement a consent mechanism before loading the widget

## Debugging Feed Issues

1. **Feed shows no items (no error message):**
   - Check GDPR + image optimization compound check above
   - Check `selected_accounts` in template config — is `open_id` correct?
   - Check account has `has_critical_error = false` in `wpsr_tiktok_connected_sources_config`

2. **Multi-source feed shows items from only one account:**
   - Check `selected_accounts` array in template config has multiple `open_id` values
   - Check each account's cache independently: `CacheHandler::getCache('tiktok', $openId, $cacheKey)`
   - One account may have a token error — check `PlatformErrorManager::getErrors('tiktok')`

3. **Items sorted incorrectly:**
   - `created_at` is Unix timestamp integer — ensure `formatData()` isn't converting to string
   - Check `post_order` setting in Config — default is `'descending'`

4. **Load-more button loads duplicate items:**
   - Check `paginate_number` vs initial `feed_count` — if `paginate_number > feed_count`, there's nothing left to load
   - Check `sinceId` calculation in `getPaginatedFeedHtml()` — `$page` is 0-indexed

5. **Statistics all showing as 0:**
   - Engagement stats not in API fields. Check `custom_feed_for_tiktok/tiktok_video_api_details` filter output
   - Add `like_count,comment_count,view_count,share_count` to the fields string
