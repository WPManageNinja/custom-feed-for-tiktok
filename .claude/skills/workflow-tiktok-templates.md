# Workflow: TikTok Template Rendering

Read this when working on TikTok feed rendering — the PHP template pipeline, action hook element system, page builder integrations, and popup/display modes.

> **Prerequisite:** Read `wp-social-reviews/.claude/skills/workflow-templates.md` first. This skill documents TikTok-specific rendering behaviour only.
>
> **Adding a new template?** Use `wp-social-reviews/.claude/skills/add-tiktok-template.md` — it has the
> full three-repo checklist. This file describes the pipeline a new template plugs into.

## Key Files

| File | Purpose |
|------|---------|
| `app/Hooks/Handlers/TiktokTemplateHandler.php` | Action callbacks for each template element + AJAX pagination |
| `app/Hooks/Handlers/ShortcodeHandler.php` | Shortcode entry point → orchestrates full render |
| `app/Hooks/actions.php` / `filters.php` | Authoritative hook registrations — check here, not this doc, if in doubt |
| `app/Views/public/feeds-templates/tiktok/header.php` | Account profile header + **opens** the wrapper/row divs |
| `app/Views/public/feeds-templates/tiktok/template1.php` | Free feed grid — iterates video items |
| `app/Views/public/feeds-templates/tiktok/footer.php` | Follow button (footer position) + load-more + **closes** the wrapper |
| `app/Views/public/feeds-templates/tiktok/elements/` | Element partials (7 files) |
| `app/Services/Widgets/ElementorWidget.php` | Elementor bootstrap (registers the widget) |
| `app/Services/Widgets/TikTokWidget.php` | Elementor widget class (`extends Widget_Base`) |
| `app/Services/Widgets/Oxygen/OxygenWidget.php` | Oxygen Builder element |
| `app/Services/Widgets/Beaver/BeaverWidget.php` | Beaver Builder module |

**Pro templates (`template2`, `template3`, …) live in `wp-social-ninja-pro/app/Views/public/feeds-templates/tiktok/`,
not in this plugin.** The Vue editor previews and all SCSS live in `wp-social-reviews` (core).

## Render Pipeline

```
[wp_social_ninja id="X" platform="tiktok"]
  │
  ▼  core ShortcodeHandler
apply_filters('wpsocialreviews/render_tiktok_template', $templateId, $platform)   // 2 args
  │
  ▼  CustomFeedForTiktok\Application\Hooks\Handlers\ShortcodeHandler::renderTiktokTemplate($templateId, $platform)
  ├─ LiteSpeed: do_action('litespeed_tag_add', 'wpsn_purge_tiktok')   if defined('LSCWP_V')
  ├─ $shortcodeHandler = new \WPSocialReviews\App\Hooks\Handlers\ShortcodeHandler()   // core does the heavy lifting
  ├─ $template_meta = $shortcodeHandler->templateMeta($templateId, $platform)
  ├─ do_action('wpsocialreviews/before_display_tiktok_feed', $account_ids)   // core >= 3.14.0
  ├─ $feed     = (new TiktokFeed())->getTemplateMeta($template_meta, $templateId)
  ├─ $settings = $shortcodeHandler->formatFeedSettings($feed)
  ├─ do_action('wp_social_review_loading_layout_' . $layout, $templateId, $settings)
  ├─ $pagination_settings = $shortcodeHandler->formatPaginationSettings($feed)
  │
  ├─ [popup mode] if post_settings.display_mode === 'popup':
  │      $shortcodeHandler->makePopupModal($feeds, $header, $feed_settings, $templateId, $platform)
  │      $shortcodeHandler->enqueuePopupScripts()
  │
  ├─ $image_settings ← wpsr_tiktok_global_settings.optimized_images + advance_settings.has_gdpr
  ├─ wp_enqueue_script('wpsr-image-resizer')   if optimization on and resize data incomplete
  ├─ $shortcodeHandler->enqueueScripts()
  ├─ do_action('wpsocialreviews/load_template_assets', $templateId)   // Pro prints the custom style block here
  ├─ error banner ← apply_filters('wpsocialreviews/display_frontend_error_message', …)
  │
  ├─ $html .= loadView('public/feeds-templates/tiktok/header', …)
  ├─ if (defined('WPSOCIALREVIEWS_PRO') && $template !== 'template1')
  │      $html .= apply_filters('custom_feed_for_tiktok/add_tiktok_feed_template', $template_body_data)
  │  else $html .= loadView('public/feeds-templates/tiktok/template1', $template_body_data)
  ├─ $html .= loadView('public/feeds-templates/tiktok/footer', …)
  └─ return $html
```

**No output buffering.** The handler concatenates `loadView()` return values into `$html`. Don't add
`ob_start()`/`include` — `loadView()` (`app/Traits/LoadView.php`) already returns a string.

`header.php` opens the wrapper, `.wpsr-container`, `.wpsr-tiktok-feed-wrapper-inner` and the
`.wpsr-row`/`.swiper-wrapper` div; `footer.php` closes them. A template view emits **only** the items —
which is why one unbalanced `</div>` in a template breaks the whole page layout.

## Action Hook Architecture

Every template element renders via action hooks — **not inline PHP** — so Pro can extend any element
without forking template files. Arg counts are exactly as registered in `app/Hooks/actions.php`:

| Hook (`custom_feed_for_tiktok/…`) | Handler | Args | Output |
|---|---|---|---|
| `tiktok_feed_template_item_wrapper_before` | `TiktokTemplateHandler@renderTemplateItemWrapper` | **1** (`$template_meta`) | opening column `<div>` (you close it) |
| `tiktok_feed_media` | `@renderFeedMedia` | 2 (`$feed`, `$template_meta`) | `elements/media.php` |
| `tiktok_feed_author` | `@renderFeedAuthor` | 2 | `elements/author.php` (avatar + name + date) |
| `tiktok_feed_author_name` | `@renderFeedAuthorName` | 2 | `elements/author-name.php` |
| `tiktok_feed_description` | `@renderFeedDescription` | 2 | `elements/description.php` |
| `tiktok_feed_icon` | `@renderFeedIcon` | **1** (`$class`) | `elements/icon.php` |
| `load_more_tiktok_button` | `@renderLoadMoreButton` | **7** | `elements/load-more.php` |

Registered by **Pro** (`wp-social-ninja-pro/app/Hooks/actions.php`) — these silently no-op without Pro:

| Hook (`custom_feed_for_tiktok/…`) | Handler | Args |
|---|---|---|
| `tiktok_feed_statistics` | `TiktokTemplateHandlerPro@renderTiktokFeedStatistics` | 2 (`$template_meta`, `$feed`) |
| `tiktok_feed_date` | `@renderFeedDate` | 2 (`$template_meta`, `$feed`) |
| `tiktok_header_statistics` | `@renderTiktokHeaderStatistics` | 3 |
| `tiktok_feed_bio_description` | `@renderTiktokFeedBioDescription` | 2 |
| `tiktok_follow_button` | `@renderTiktokFollowButtonHtml` | 2 |

Note the two per-item Pro hooks take **`$template_meta` first**, the opposite of the free element hooks.

**To override an element** — the namespace segment is `Application`, not `App`:
```php
remove_action('custom_feed_for_tiktok/tiktok_feed_media',
    [\CustomFeedForTiktok\Application\Hooks\Handlers\TiktokTemplateHandler::class, 'renderFeedMedia'], 10);

add_action('custom_feed_for_tiktok/tiktok_feed_media', function ($feed, $meta) {
    // custom output
}, 10, 2);
```

## Variables Available in Template Files

Each view receives a **different** set — they are not interchangeable.

**Template views** (`template1.php`, Pro `templateN.php`), from `$template_body_data`:

| Variable | Content |
|---|---|
| `$templateId` | template post ID |
| `$feeds` | `$settings['feeds']` — all items, unpaginated |
| `$template_meta` | the `feed_settings` array (see below) |
| `$paginate` | items per page |
| `$sinceId` / `$maxId` | inclusive index range for the current page — **honour both** |
| `$pagination_settings` | full pagination config |
| `$translations` | `GlobalSettings::getTranslations()` |
| `$image_settings` | `['optimized_images' => 'true'\|'false', 'has_gdpr' => 'true'\|'false']` |

**`header.php`:** `$templateId`, `$template`, `$header`, `$feed_settings`, `$layout_type`, `$column_gaps`, `$translations`
**`footer.php`:** `$templateId`, `$feeds`, `$feed_settings`, `$layout_type`, `$column_gaps`, `$paginate`, `$pagination_type`, `$header`, `$total`

Inside `$template_meta`:
```php
$template_meta['post_settings']      // display_mode, resolution, display_* toggles, content_length
$template_meta['source_settings']    // feed_type, selected_accounts, feed_count
$template_meta['header_settings']    // display_header, display_profile_photo, counters
$template_meta['carousel_settings']  // autoplay, autoplay_speed, responsive_slides_*, navigation
$template_meta['popup_settings']     // display_sidebar, display_video, display_caption, …
$template_meta['layout_type']        // grid | masonry | carousel
$template_meta['responsive_column_number']  // ['desktop','tablet','mobile']
$template_meta['column_gaps']        // default | no_gap | narrow | small | wide | wider
```

Always guard the page range in the item loop:
```php
foreach ($feeds as $wpsr_tiktok_index => $wpsr_tiktok_feed) {
    if ($wpsr_tiktok_index >= $sinceId && $wpsr_tiktok_index <= $maxId) { … }
}
```

## Display Modes

Set by `post_settings.display_mode`:

| Mode | Value | Behaviour |
|---|---|---|
| Open in TikTok | `'tiktok'` | media wrapped in `<a href="https://www.tiktok.com/@{username}/video/{id}" target="_blank">` |
| Popup lightbox | `'popup'` | JS intercepts the click and opens the shared modal |
| No link | `'none'` | static image, not clickable |

**Popup is click-delegated, not attribute-addressed.** Core's `resources/public/social-ninja-modal.js` binds:
```js
$(document).on('click', '.wpsr-tiktok-feed-playmode, .wpsr-tiktok-feed-video-playmode, .wpsr-tiktok-icon-position',
    function (e) { TiktokPopup.init(e, this); });
```
`TiktokPopup.checkTiktokFeedType()` then reads, **off the clicked element itself**: `data-playmode`,
`data-index`, `data-template-id`, `data-feed_type`, `data-optimized_images`, `data-has_gdpr`,
`data-image_size` — and looks the item up in `window.WPSR_TiktokFrontEndJson[templateId][index]`.

Consequences:
- Whatever element carries a playmode class **must also carry those `data-*` attributes**, or the popup opens empty.
- `elements/media.php` renders `<a href="{video url}">` whenever `display_mode !== 'none'` — popup mode
  included — and puts no `data-*` on it. For a card whose whole surface is clickable, inline the media
  instead of using the hook: anchor only for `'tiktok'`, otherwise a `div` carrying the data attrs. See
  Pro's `tiktok/template2.php` / `template3.php`.
- The modal markup is emitted **once per widget** by core's `makePopupModal()`, not per item.

There is no `data-tiktok-popup-id` attribute anywhere in the codebase.

## Column Layout (Grid)

`responsive_column_number` values are **Bootstrap-style spans out of 12**, used verbatim in the class
name — they are not a column count:

```php
['desktop' => '4', 'tablet' => '6', 'mobile' => '12']   // defaults
// '3' → wpsr-col-3  → 4 across       '4'  → wpsr-col-4  → 3 across
// '6' → wpsr-col-6  → 2 across       '12' → wpsr-col-12 → 1 across
```
The editor's "Number of Columns" dropdown stores the span — its "4 Column" option has `value: '3'`.

`renderTemplateItemWrapper()` outputs (note `sm`/`xs`, and that desktop comes first):
```html
<div class="wpsr-mb-30 wpsr-col-{desktop} wpsr-col-sm-{tablet} wpsr-col-xs-{mobile}">
```

**Carousel mode** skips the wrapper hook entirely — items get `swiper-slide`, and `header.php` emits
`.swiper-wrapper` in place of `.wpsr-row`. Guard with `layout_type !== 'carousel'` around both the
wrapper hook call *and* its closing `</div>`.

## Video Media Rendering (`elements/media.php`)

```php
$wpsr_tiktok_media_url     = Arr::get($feed, 'media_url', '');                   // local/optimized copy
$wpsr_tiktok_default_media = Arr::get($feed, 'media.preview_image_url', '');     // TikTok CDN
$wpsr_tiktok_image_optimization = Arr::get($image_settings, 'optimized_images'); // 'true' | 'false'

// which URL is used
src = $wpsr_tiktok_image_optimization === 'true' ? $wpsr_tiktok_media_url : $wpsr_tiktok_default_media;

// placeholder detection drives the show/hide + skeleton classes
$wpsr_tiktok_img_class = !empty($media_url) && strpos($media_url, 'placeholder') === false
    ? 'wpsr-tt-post-img wpsr-show' : 'wpsr-tt-post-img wpsr-hide';
$wpsr_tiktok_animation_img_class = $media_url && strpos($media_url, 'placeholder') !== false
    ? 'wpsr-animated-background' : '';
```

- The keys are `$feed['media_url']` and `$feed['media']['preview_image_url']`. There is no
  `$feed['media']['local_url']` and no `$meta['image_optimization_enabled']`.
- Use `strpos(...) === false`, not `str_contains()` — this plugin targets PHP 7.4. (Pro's own views do use
  `str_contains`; that's a pre-existing inconsistency there, not a pattern to copy here.)
- `post_settings.resolution` (`full`/`medium`/`low`) selects which locally-resized file is requested and is
  passed through as `data-image_size` for the popup. No `srcset` is generated.

## Asset Enqueueing

**Assets are owned by the core plugin, not this one.** Core `ShortcodeHandler` registers:
```php
wp_register_style('wp_social_ninja_tt',
    WPSOCIALREVIEWS_URL . 'assets/css/wp_social_ninja_tt.css', [], WPSOCIALREVIEWS_VERSION);
```
and `enqueueStyles()` maps `tiktok => tt` onto that handle. The stylesheet is compiled from
`wp-social-reviews/resources/scss/public/tt.scss`.

JS is the single shared bundle — there is no TikTok-specific script:
```php
$shortcodeHandler->enqueueScripts();   // wp_enqueue_script('wp-social-review')
```
Frontend data arrives as `window.wpsr_ajax_params`, injected inline before the `wp-social-review` tag
(`addFrontendVarsBeforeScript()`); per-template feed JSON is `window.WPSR_TiktokFrontEndJson[templateId]`.

Handle `wp_social_ninja_tt` is referenced by the Oxygen and Beaver widgets — don't rename it.
There is no `tiktok-feed.css`, no `tiktok-feed.js`, and no `wpsrTiktokVars`.

## Pro Template Registration

Pro registers, gated on this plugin being active (`wp-social-ninja-pro/app/Hooks/filters.php`):
```php
if (defined('CUSTOM_FEED_FOR_TIKTOK_VERSION')) {
    $app->addFilter('custom_feed_for_tiktok/add_tiktok_feed_template',
        'WPSocialReviewsPro\App\Hooks\Handlers\TiktokTemplateHandlerPro@addTemplate');
}
```
The filter takes **one argument** (`$template_body_data`) and returns rendered HTML. `addTemplate()`
resolves the view from `template_meta.template` against an allow-list:
```php
public function addTemplate($data = [])
{
    $template = Arr::get($data, 'template_meta.template', 'template2');
    $templates = ['template2', 'template3'];
    if (!in_array($template, $templates, true)) { $template = 'template2'; }
    return $this->loadView('feeds-templates/tiktok/' . $template, $data);
}
```

**Two independent routing paths — keep them in sync:**
1. `ShortcodeHandler::renderTiktokTemplate()` — initial render
2. `TiktokTemplateHandler::getPaginatedFeedHtml()` — load-more / paged output, via the
   `wpsocialreviews/get_paginated_feed_html` filter

Both must use the same condition:
```php
if (defined('WPSOCIALREVIEWS_PRO') && $template !== 'template1') { … Pro filter … }
```
`getPaginatedFeedHtml()` previously hardcoded `$templateNumber === 'template2'`, which silently rendered
**template1 markup on page 2+** of every other Pro template. Always test page 2, not just page 1.

`header.php` already emits `'wpsr-tiktok-feed-' . $template`, so a new template's wrapper class appears
with no change there.

## Debugging Rendering Issues

1. **Feed renders blank without error:**
   - Check the GDPR + image optimization compound requirement (see `workflow-tiktok-feed.md`)
   - Check the error banner — inspect source for `wpsr-error`
   - Check `$feeds` is non-empty *and* that `$sinceId`/`$maxId` actually overlap it

2. **Grid columns wrong:**
   - Check `responsive_column_number` in the `_wpsr_template_config` post meta. The value is the **span**:
     `3` → `wpsr-col-3` → 4 across. A value of `4` gives 3 across, not 4.
   - Confirm `wp_social_ninja_tt` is enqueued (core owns it)

3. **Carousel not initializing:**
   - Check `layout_type === 'carousel'` — the wrapper is `.swiper-wrapper`/`swiper-slide`, not `.wpsr-row`
   - Check `carousel_settings.autoplay_speed` is an integer, not a string

4. **Popup opens empty, or navigates away instead of opening:**
   - The clicked element needs both a playmode class *and* the full `data-*` set (see Display Modes)
   - Check `window.WPSR_TiktokFrontEndJson[templateId][index]` exists for that index
   - Check no overlay above the media has `pointer-events: auto` on a link — it will win the click

5. **Author name not showing independently of photo:**
   - Known bug (PR #6): `Config.php` read `display_author_name` from the `display_author_photo` key.
     Verify `$settings['post_settings']['display_author_name']` reads its own key.

6. **Load-more shows previously seen items:**
   - `$sinceId = ($page - 1) * $paginate`, `$maxId = $sinceId + $paginate - 1`
   - Confirm the JS increments the page counter before sending the request

7. **Page 2 renders a different template** → see "Two independent routing paths" above.

## Page Builder Integrations

### Elementor

**Bootstrap:** `app/Services/Widgets/ElementorWidget.php`, instantiated from `actions.php` when
`defined('ELEMENTOR_VERSION')`. **Widget class:** `app/Services/Widgets/TikTokWidget.php`
(`extends Elementor\Widget_Base`).

- Widget category: `wp-social-ninja` (registered by the core plugin)
- Controls: dropdown of available TikTok templates
- Renders via `do_shortcode('[wp_social_ninja id="…" platform="tiktok"]')`
- Style dependency: `wp_social_ninja_tt`

**Debugging:** widget missing from the panel → check `defined('ELEMENTOR_VERSION')` in `actions.php`.
Blank in the editor → editor mode is excluded from template rendering (check
`\Elementor\Plugin::$instance->editor->is_edit_mode()`).

### Oxygen Builder

**File:** `app/Services/Widgets/Oxygen/OxygenWidget.php` (plus `Oxygen/TikTokWidget.php`).

- Registered only if `class_exists('OxyEl')`
- Falls back to `[wp_social_ninja id="…" platform="tiktok"]`
- **Fixed bug (PR #7):** the fallback had a space — `[wp_ social_ninja …]`. If you see a space, that fix is missing.

### Beaver Builder

**File:** `app/Services/Widgets/Beaver/BeaverWidget.php` and `Beaver/TikTok/`.

- `extends FLBuilderModule`, registered via `fl_builder_register_module` when `class_exists('FLBuilder')`
- Frontend template: `Beaver/TikTok/includes/frontend.php`; CSS: `includes/frontend.css.php`
