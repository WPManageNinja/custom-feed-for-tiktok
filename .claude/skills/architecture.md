# Architecture — Custom Feed for TikTok

Read this to understand the plugin's structure, how it boots, what it depends on, and how it connects to WP Social Ninja.

## Plugin Identity

| Property | Value |
|----------|-------|
| Namespace | `CustomFeedForTiktok\App\` (PSR-4) |
| Text domain | `custom-feed-for-tiktok` |
| Main constant | `CUSTOM_FEED_FOR_TIKTOK_MAIN_FILE` |
| Version constant | `CUSTOM_FEED_FOR_TIKTOK_VERSION` |
| URL constant | `CUSTOM_FEED_FOR_TIKTOK_URL` |
| Required dependency | `wp-social-reviews` v3.x+ (`WPSOCIALREVIEWS_VERSION`) |
| TikTok API base | `https://open.tiktokapis.com/v2/` |
| OAuth callback | `https://wpsocialninja.com/api/tiktok_callback` (middleman site) |

## Boot Chain

```
1. custom-feed-for-tiktok.php
   ├─ Guard: if defined('CUSTOM_FEED_FOR_TIKTOK_MAIN_FILE') → return
   ├─ define('CUSTOM_FEED_FOR_TIKTOK_MAIN_FILE', __FILE__)
   └─ require custom-feed-for-tiktok-boot.php

2. custom-feed-for-tiktok-boot.php
   ├─ SPL autoloader → maps CustomFeedForTiktok\App\ → app/
   ├─ Defines version + URL constants
   ├─ Checks WPSOCIALREVIEWS_VERSION defined → else admin notice + return
   └─ add_action('wp_social_reviews_loaded_v2', fn() → new App\Application())

3. App\Application::__construct()
   ├─ require app/Hooks/actions.php
   ├─ require app/Hooks/filters.php
   └─ require app/Http/Routes/api.php  (currently empty placeholder)

4. app/Hooks/actions.php
   ├─ Register template element action hooks (11 actions)
   ├─ Register AJAX handlers (wpsr_get_more_feeds)
   └─ Conditionally register page builder widgets:
       ├─ Elementor:     if defined('ELEMENTOR_VERSION')
       ├─ Oxygen:        if class_exists('OxyEl')
       └─ Beaver Builder: if class_exists('FLBuilder')

5. app/Hooks/filters.php
   ├─ wpsocialreviews/render_tiktok_template        → ShortcodeHandler@renderTiktokTemplate
   ├─ wpsocialreviews/format_tiktok_config          → TiktokTemplateHandler@formatTiktokConfig
   ├─ wpsocialreviews/get_template_meta             → TiktokTemplateHandler@getTemplateMeta
   ├─ wpsocialreviews/get_paginated_feed_html       → TiktokTemplateHandler@getPaginatedFeedHtml
   └─ wpsocialreviews/get_connected_source_list     → Helper@getConnectedSourceList
```

**Critical:** The plugin never runs before `wp_social_reviews_loaded_v2`. All base plugin classes (`BaseFeed`, `CacheHandler`, `PlatformErrorManager`, etc.) must be available before any TikTok class instantiates.

## Directory Structure

```
custom-feed-for-tiktok/
├── custom-feed-for-tiktok.php          # Entry point
├── custom-feed-for-tiktok-boot.php     # Autoloader + dependency check
├── app/
│   ├── Application.php                 # Boots hooks/filters/routes
│   ├── Hooks/
│   │   ├── actions.php                 # All add_action() registrations
│   │   ├── filters.php                 # All add_filter() registrations
│   │   └── Handlers/
│   │       ├── PlatformHandler.php     # Registers TiktokFeed hooks with core
│   │       ├── ShortcodeHandler.php    # Shortcode render entry → delegates to TiktokFeed
│   │       └── TiktokTemplateHandler.php # Template element rendering (action callbacks)
│   ├── Http/Routes/api.php             # Empty placeholder (no REST routes in this plugin)
│   ├── Services/
│   │   ├── Platforms/Feeds/Tiktok/
│   │   │   ├── TiktokFeed.php          # Core service (1213 lines) — API, OAuth, cache, data
│   │   │   ├── Config.php              # Settings normalizer (1110 lines)
│   │   │   └── Helper.php              # Static utility methods
│   │   └── Widgets/
│   │       ├── TikTokWidget.php        # Elementor widget
│   │       ├── ElementorWidget.php     # Elementor integration bootstrap
│   │       ├── Oxygen/                 # Oxygen Builder widget
│   │       └── Beaver/                 # Beaver Builder module
│   ├── Traits/
│   │   └── LoadView.php                # ob_start/include/get_clean helper
│   └── Views/public/feeds-templates/tiktok/
│       ├── header.php                  # Account profile header section
│       ├── template1.php               # Main feed grid (video items loop)
│       ├── footer.php                  # Follow button + load-more button
│       └── elements/
│           ├── author.php              # Author avatar + username block
│           ├── author-name.php         # Author name only (hover overlay)
│           ├── description.php         # Video caption text
│           ├── media.php               # Video preview image + link
│           ├── icon.php                # TikTok platform icon
│           ├── item-parent-wrapper.php # Grid column wrapper div
│           └── load-more.php           # AJAX load-more button
```

## Classes from Core Plugin Used

| Core Class | Purpose in TikTok plugin |
|-----------|--------------------------|
| `BaseFeed` | `TiktokFeed` extends this — provides hook registration contract |
| `CacheHandler` | Feed and account header caching (read/write/clear) |
| `PlatformErrorManager` | Store/retrieve API errors shown in admin dashboard |
| `PlatformData` | Track last-used timestamps, GDPR revocation handling |
| `DataProtector` | Encrypt/decrypt `access_token` at rest |
| `FeedFilters` | Post-fetch keyword include/exclude and ordering |
| `ImageOptimizationHandler` | Download TikTok CDN images to local server |
| `GlobalSettings` | Read global TTL and image optimization settings |

**Import pattern:**
```php
use WPSocialReviews\App\Services\Platforms\Feeds\CacheHandler;
use WPSocialReviews\App\Services\Platforms\PlatformErrorManager;
use WPSocialReviews\App\Services\Platforms\DataProtector;
```

## WordPress Options (Key)

| Option | Content |
|--------|---------|
| `wpsr_tiktok_connected_sources_config` | All connected TikTok accounts: keyed by `open_id`, contains encrypted `access_token`, unencrypted `refresh_token`, expiry, user metadata, error flags |
| `wpsr_tiktok_global_settings` | Plugin-wide settings: `expiration` (TTL seconds), `caching_type`, `optimized_images` |
| `wpsr_tiktok_local_avatars` | Map of `open_id` → local avatar URL (when image optimization enabled) |
| `wpsr_tiktok_revoke_platform_data` | GDPR revocation flag — when set, feed data should be withheld |

## Post Meta (Per Template)

| Meta key | Content |
|----------|---------|
| `_wpsr_template_config` | JSON-encoded `Config::formatTiktokConfig()` output — all feed display settings |
| `_wpsr_template_styles_config` | JSON-encoded style overrides (managed by Pro CSS handler) |
| `_wpsn_elementor_ids` | Page builder widget type IDs using this template |

## Hook Namespace

This plugin uses **two hook namespaces**:

| Namespace | Purpose |
|-----------|---------|
| `wpsocialreviews/` | Integration with core plugin — filters/actions core expects |
| `custom_feed_for_tiktok/` | Plugin's own extensibility hooks for Pro features |

Never register `custom_feed_for_tiktok/` hooks in the core or Pro plugins — direction is always: core fires `wpsocialreviews/` → TikTok plugin listens.

## No REST Routes

`app/Http/Routes/api.php` is **intentionally empty**. This plugin has no REST API endpoints of its own. All admin operations (credential save, cache clear, settings update) are handled by:
- Core plugin's `ConfigsController` triggering `wpsocialreviews/` hooks
- AJAX handlers for load-more pagination (`wp_ajax_wpsr_get_more_feeds`)
- Direct `update_post_meta()` calls in `TiktokFeed::updateEditorSettings()`
