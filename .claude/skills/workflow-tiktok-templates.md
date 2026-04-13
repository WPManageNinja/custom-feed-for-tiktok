# Workflow: TikTok Template Rendering

Read this when working on TikTok feed rendering — the PHP template pipeline, action hook element system, page builder integrations, and popup/display modes.

> **Prerequisite:** Read `wp-social-reviews/.claude/skills/workflow-templates.md` first. This skill documents TikTok-specific rendering behaviour only.

## Key Files

| File | Purpose |
|------|---------|
| `app/Hooks/Handlers/TiktokTemplateHandler.php` | Action callbacks for each template element |
| `app/Hooks/Handlers/ShortcodeHandler.php` | Shortcode entry point → orchestrates full render |
| `app/Views/public/feeds-templates/tiktok/header.php` | Account profile header (avatar, name, stats, follow button) |
| `app/Views/public/feeds-templates/tiktok/template1.php` | Main feed grid — iterates video items |
| `app/Views/public/feeds-templates/tiktok/footer.php` | Follow button (footer position) + load-more button |
| `app/Views/public/feeds-templates/tiktok/elements/` | Individual element partials (7 files) |
| `app/Services/Widgets/TikTokWidget.php` | Elementor widget definition |
| `app/Services/Widgets/Oxygen/OxygenWidget.php` | Oxygen Builder element |
| `app/Services/Widgets/Beaver/BeaverWidget.php` | Beaver Builder module |

## Render Pipeline

```
[wp_social_ninja id="X" platform="tiktok"] shortcode
  │
  ▼
apply_filters('wpsocialreviews/render_tiktok_template', '', $postId, $atts)
  │
  ▼
ShortcodeHandler::renderTiktokTemplate($html, $postId, $atts)
  ├─ LiteSpeed cache tag clear (if LSCache active)
  ├─ TiktokFeed::getTemplateMeta($settings, $postId) → full feed data
  ├─ Config::formatTiktokConfig() → normalized settings
  │
  ├─ [popup mode check]
  │   if display_mode === 'popup':
  │     makePopupModal($feeds, $settings)
  │     enqueuePopupScripts()
  │
  ├─ enqueue assets (if not already enqueued)
  ├─ Build $errorHtml if PlatformErrorManager has errors
  │
  ├─ ob_start()
  │   include header.php     ─── account profile section
  │   include template1.php  ─── video items grid/carousel
  │   include footer.php     ─── follow button + load-more
  └─ return ob_get_clean()
```

## Action Hook Architecture

Every template element is rendered via WordPress action hooks — **not inline PHP**. This allows Pro to override or extend any element without forking template files.

```
template1.php iterates $feeds:
  │
  ├─ do_action('custom_feed_for_tiktok/tiktok_feed_template_item_wrapper_before', $feed, $meta)
  │   → TiktokTemplateHandler::renderTemplateItemWrapper($meta)
  │   → Outputs: <div class="wpsr-col-X wpsr-tiktok-feed-item ...">
  │
  ├─ do_action('custom_feed_for_tiktok/tiktok_feed_media', $feed, $meta)
  │   → TiktokTemplateHandler::renderFeedMedia($feed, $meta)
  │   → Outputs: elements/media.php (video preview image)
  │
  ├─ do_action('custom_feed_for_tiktok/tiktok_feed_author', $feed, $meta)
  │   → TiktokTemplateHandler::renderFeedAuthor($feed, $meta)
  │   → Outputs: elements/author.php (avatar + username)
  │
  ├─ do_action('custom_feed_for_tiktok/tiktok_feed_author_name', $feed, $meta)
  │   → TiktokTemplateHandler::renderFeedAuthorName($feed, $meta)
  │   → Outputs: elements/author-name.php (name only, for hover overlay)
  │
  ├─ do_action('custom_feed_for_tiktok/tiktok_feed_description', $feed, $meta)
  │   → TiktokTemplateHandler::renderFeedDescription($feed, $meta)
  │   → Outputs: elements/description.php (caption text, truncated)
  │
  ├─ do_action('custom_feed_for_tiktok/tiktok_feed_icon', $class, $meta)
  │   → TiktokTemplateHandler::renderFeedIcon($class, $meta)
  │   → Outputs: elements/icon.php (TikTok logo icon)
  │
  └─ do_action('custom_feed_for_tiktok/load_more_tiktok_button', $meta)
      → TiktokTemplateHandler::renderLoadMoreButton($meta)
      → Outputs: elements/load-more.php (AJAX button)
```

**To override an element (Pro or custom):**
```php
// Remove default handler
remove_action('custom_feed_for_tiktok/tiktok_feed_media',
    [\CustomFeedForTiktok\App\Hooks\Handlers\TiktokTemplateHandler::class, 'renderFeedMedia'], 10);

// Add custom handler
add_action('custom_feed_for_tiktok/tiktok_feed_media', function($feed, $meta) {
    // Custom output
}, 10, 2);
```

## Template Variables Available in PHP Files

All template files receive these local variables:

| Variable | Type | Content |
|----------|------|---------|
| `$template_meta` | array | Full `Config::formatTiktokConfig()` output |
| `$feeds` | array | Array of `formatData()` video items |
| `$account_details` | array | User info: name, avatar, stats, profile_url |
| `$error_html` | string | Error banner HTML or empty string |
| `$sinceId` | int | Start index for current page (pagination) |
| `$maxId` | int | End index for current page |
| `$postId` | int | Template post ID |

Access nested settings via:
```php
$feedSettings  = $template_meta['feed_settings'];
$postSettings  = $feedSettings['post_settings'];
$headerSettings = $feedSettings['header_settings'];
$popupSettings = $feedSettings['popup_settings'];
$carouselSettings = $feedSettings['carousel_settings'];
```

## Display Modes

Each video item renders differently based on `post_settings.display_mode`:

| Mode | `display_mode` value | Behaviour |
|------|---------------------|-----------|
| Open in TikTok | `'tiktok'` | Clicking image opens `https://www.tiktok.com/@{username}/video/{id}` |
| Popup lightbox | `'popup'` | Clicking image opens inline popup with video player |
| No link | `'none'` | Static image, not clickable |

**Popup mode** requires extra setup:
```php
// In ShortcodeHandler::renderTiktokTemplate():
if ($displayMode === 'popup') {
    $this->makePopupModal($feeds, $settings);  // Renders hidden popup HTML
    $this->enqueuePopupScripts();              // Enqueues popup JS
}
```

The popup modal HTML is output **once** per widget (not per item). Each item's `media.php` wraps the image in a `data-tiktok-popup-id="{id}"` link that JS uses to open the correct popup.

## Column Layout (Grid)

Grid columns are controlled by `responsive_column_number`:
```php
$columns = $feedSettings['responsive_column_number'];
// ['desktop' => 4, 'tablet' => 6, 'mobile' => 12]
// Bootstrap-style: desktop=4 means col-3 (12/4), tablet=6 means col-6, mobile=12 means col-12
```

`renderTemplateItemWrapper()` outputs:
```html
<div class="wpsr-col-{mobile} wpsr-col-md-{tablet} wpsr-col-lg-{desktop} wpsr-tiktok-feed-item">
```

**Carousel mode** bypasses the grid wrapper — `item-parent-wrapper.php` outputs a different structure that the JS carousel library uses. Check `layout_type === 'carousel'` in template1.php before assuming grid markup.

## Video Media Rendering (elements/media.php)

```php
// Determines image URL (optimized local vs TikTok CDN)
$mediaUrl = $meta['image_optimization_enabled']
    ? ($feed['media']['local_url'] ?? $feed['media']['url'])
    : $feed['media']['url'];

// Determines placeholder vs live image (GDPR loading)
$isPlaceholder = strpos($mediaUrl, 'placeholder') !== false;
// (str_contains() was fixed to strpos() for PHP 7.4 compat — PR #4)

// Link wrapper based on display_mode
if ($display_mode === 'popup') {
    // <a href="#" data-tiktok-popup-id="{id}">
} elseif ($display_mode === 'tiktok') {
    // <a href="https://www.tiktok.com/@{username}/video/{id}" target="_blank">
} else {
    // <div class="wpsr-tiktok-media-wrap">  (no link)
}
```

**Resolution setting:**
```php
'resolution' => 'full'|'medium'|'low'
// Controls: img width attribute and srcset generation
// Only applies when image optimization is enabled (local images)
// CDN images always serve TikTok's default resolution
```

## Page Builder Integrations

### Elementor

**File:** `app/Services/Widgets/TikTokWidget.php`

- Extends `Elementor\Widget_Base`
- Registered via `elementor/widgets/register` action
- Widget category: `wp-social-ninja` (registered by core plugin)
- Controls: dropdown of available TikTok templates (fetched via `Helper::getConnectedSourceList()`)
- Renders: `echo do_shortcode('[wp_social_ninja id="' . $templateId . '" platform="tiktok"]')`
- Depends on stylesheet handle: `wp_social_ninja_tt`

**Debugging Elementor:**
- Widget not appearing in panel → check `defined('ELEMENTOR_VERSION')` in `actions.php`
- Widget renders blank in editor → Elementor editor mode is excluded from template rendering in `ShortcodeHandler` (same pattern as core plugin — check for `\Elementor\Plugin::$instance->editor->is_edit_mode()`)

### Oxygen Builder

**File:** `app/Services/Widgets/Oxygen/OxygenWidget.php`

- Checks `class_exists('OxyEl')` before registering
- Falls back to shortcode if element fails: `[wp_social_ninja id="..." platform="tiktok"]`
- **Fixed bug (PR #7):** Fallback used `[wp_ social_ninja ...]` (space in tag). Now correct.

**Debugging Oxygen:**
- Element not rendering → check `class_exists('OxyEl')` at activation time
- Fallback shortcode must use `[wp_social_ninja]` (no space) — if you see a space, this is the unfixed version

### Beaver Builder

**File:** `app/Services/Widgets/Beaver/BeaverWidget.php` and `Beaver/TikTok/`

- Extends `FLBuilderModule`
- Registered via `fl_builder_register_module`
- Frontend template: `Beaver/TikTok/includes/frontend.php`
- Stylesheet: `Beaver/TikTok/includes/frontend.css.php`
- Module settings: dropdown of TikTok templates

## Asset Enqueueing

Assets are enqueued in `ShortcodeHandler` when a TikTok widget is present on the page:

```php
wp_enqueue_style('wp_social_ninja_tt',
    CUSTOM_FEED_FOR_TIKTOK_URL . 'assets/css/tiktok-feed.css',
    [], CUSTOM_FEED_FOR_TIKTOK_VERSION);

wp_enqueue_script('wp_social_ninja_tt',
    CUSTOM_FEED_FOR_TIKTOK_URL . 'assets/js/tiktok-feed.js',
    ['jquery'], CUSTOM_FEED_FOR_TIKTOK_VERSION, true);

wp_localize_script('wp_social_ninja_tt', 'wpsrTiktokVars', [
    'ajaxUrl'    => admin_url('admin-ajax.php'),
    'nonce'      => wp_create_nonce('wpsr_tiktok_nonce'),
    'postId'     => $postId,
    'feedType'   => $feedType,
    'pagination' => $paginationSettings,
]);
```

**Handle name `wp_social_ninja_tt`** — this is the handle used in Elementor widget dependency. Don't rename it without updating `TikTokWidget.php`.

## Adding a New Template Style (Pro)

The free plugin has `template1`. Pro templates hook in via:

```php
// In Pro plugin:
add_filter('custom_feed_for_tiktok/add_tiktok_feed_template', function($html, $templateId, $page, $settings) {
    if ($settings['feed_settings']['template'] !== 'template2') return $html;

    ob_start();
    include PRO_PLUGIN_DIR . 'views/tiktok/template2.php';
    return ob_get_clean();
}, 10, 4);
```

The filter fires in `getPaginatedFeedHtml()` and (check) in initial render. If filter returns non-empty string, the free template1 is skipped.

**Template selection** in `Config.php`:
```php
'template' => $settings['feed_settings']['template'] ?? 'template1',
// Values: 'template1' (free), 'template2', 'template3' (Pro)
```

## Debugging Rendering Issues

1. **Feed renders blank without error:**
   - Check GDPR + image optimization compound requirement (see `workflow-tiktok-feed.md`)
   - Check `$error_html` — is there a hidden error? Inspect HTML source for `wpsr-error` class
   - Check `$feeds` is non-empty before reaching template files

2. **Grid columns wrong:**
   - Check `responsive_column_number` in `_wpsr_template_config` post meta
   - Bootstrap-style: value of 4 = Bootstrap col-3 (12/4 = 3 columns). Verify CSS is loaded.

3. **Carousel not initializing:**
   - Check `wp_social_ninja_tt` JS is enqueued (`wp_scripts()->queue`)
   - Check `layout_type === 'carousel'` in config — grid wrapper is different
   - Check `carousel_settings.autoplay_speed` is integer (not string)

4. **Author name not showing independently of photo:**
   - **This was a known bug (PR #6).** `Config.php:63` read `display_author_name` from `display_author_photo` key. Fixed — verify the fix is deployed. Check: `$settings['post_settings']['display_author_name']` reads from `display_author_name` (not `display_author_photo`).

5. **Elementor widget not in widget panel:**
   - Verify Elementor loaded before TikTok plugin: `did_action('elementor/loaded')` should be true
   - Check `ElementorWidget.php` bootstrap fires on `elementor/widgets/register`

6. **Load-more shows previously seen items:**
   - `$sinceId = $page * $perPage` — if `$page` starts at 0, first load-more should be `$page = 1`
   - Check AJAX handler increments page counter correctly in JS before sending request
