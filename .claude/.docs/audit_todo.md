# Custom Feed for TikTok — Audit Report

- **Table of Contents**

## Executive Summary

Overall risk level: High

Severity snapshot:

| Severity | Count |
| --- | --- |
| CRITICAL | 0 |
| HIGH | 2 |
| MEDIUM | 3 |
| SUGGESTION | 1 |

Top 3 risks:

- The public render path uses `str_contains()` even though the plugin declares PHP 7.4 support, so supported installs can fatal when a feed renders.
- TikTok OAuth credentials are handled insecurely: the client secret is shipped in code and refresh tokens are stored unencrypted in `wp_options`.
- Feed pagination over-fetches at least one extra TikTok API page per account, increasing latency and external API usage on live page loads.

Audit scope notes:

- This was a repo-only audit. The WP Social Ninja base plugin, policy layer, and external route/controller code were not present, so cross-plugin entrypoints were treated conservatively.
- No local PHP runtime was available in this workspace, so verification was static rather than execution-based.
- No plugin-owned SQL queries, file upload handlers, or REST routes were found in this repository; the main surface area is shortcode rendering, widget integrations, and TikTok API exchange logic.

## Findings by Severity

### High

### HIGH-01: Exposed TikTok OAuth secret and unencrypted refresh tokens
## Will be fixed centrally with middleman site
- Area: Security
- Confidence: High
- File:line: `app/Services/Platforms/Feeds/Tiktok/TiktokFeed.php:29`, `app/Services/Platforms/Feeds/Tiktok/TiktokFeed.php:157`, `app/Services/Platforms/Feeds/Tiktok/TiktokFeed.php:238`
- Evidence: `TiktokFeed` hard-codes `client_key`, `client_secret`, and `redirect_uri` as class properties, then persists `refresh_token` directly into `wpsr_tiktok_connected_sources_config` while only `access_token` is wrapped in `DataProtector`.
- Impact: The plugin distributes a shared OAuth secret in source code and stores long-lived refresh tokens unencrypted at rest. Anyone with read access to plugin files or the site database can recover the material needed to refresh connected TikTok accounts, and incident response becomes global because every install shares the same embedded app secret.
- Recommended fix: Remove shared OAuth credentials from distributable plugin code, move token exchange to a server-side broker or per-site app configuration, and encrypt `refresh_token` with the same protection used for `access_token`.
- Task statement: Externalize TikTok OAuth credentials and encrypt refresh tokens before saving connected-source configuration.

### HIGH-02: PHP 7.4 support claim conflicts with `str_contains()` in the render path
## DONE (https://github.com/WPManageNinja/custom-feed-for-tiktok/pull/4)
- Area: Traceability
- Confidence: High
- File:line: `readme.txt:6`, `app/Views/public/feeds-templates/tiktok/elements/media.php:16`
- Evidence: The plugin declares `Requires PHP: 7.4`, but the media template calls `str_contains()` on the live shortcode render path: `renderTiktokTemplate()` -> `template1.php` -> `renderFeedMedia()` -> `elements/media.php`.
- Impact: PHP 7.4 sites are told the plugin is supported, yet any request that renders a TikTok feed will call an undefined PHP 8-only function and break output. On sites that place the feed on key pages, this is a production-facing fatal compatibility failure.
- Recommended fix: Replace `str_contains()` with a PHP 7.4-safe equivalent such as `strpos(...) !== false`, or raise the declared PHP minimum to 8.0+ and block unsupported installs.
- Task statement: Make the frontend render path PHP-7.4-compatible or update the plugin’s PHP requirement and distribution metadata to 8.0+.

### Medium

### MEDIUM-01: Feed pagination always requests one extra TikTok API page
## DONE (https://github.com/WPManageNinja/custom-feed-for-tiktok/pull/5)
- Area: Optimization
- Confidence: High
- File:line: `app/Services/Platforms/Feeds/Tiktok/TiktokFeed.php:664`
- Evidence: `getAccountFeed()` loads the first page before entering the pagination loop, but computes `$pages` from the total desired page count and then executes `while ($x < $pages)`. For example, `totalFeed = 20` yields one initial request plus one extra paginated request before the result is sliced back to 20 items.
- Impact: Every multi-page feed render incurs unnecessary remote TikTok API calls, increasing latency, quota consumption, and failure surface. The cost grows with multiple connected accounts because the over-fetch happens per account.
- Recommended fix: Track remaining pages instead of total pages, or stop paginating once the accumulated item count reaches `totalFeed`.
- Task statement: Correct the pagination math so only the required number of TikTok API pages are requested.

### MEDIUM-02: Author-name visibility is wired to the author-photo setting
## DONE (https://github.com/WPManageNinja/custom-feed-for-tiktok/pull/6)
- Area: Traceability
- Confidence: High
- File:line: `app/Services/Platforms/Feeds/Tiktok/Config.php:64`, `app/Views/public/feeds-templates/tiktok/elements/author-name.php:8`
- Evidence: `Config::formatTiktokConfig()` assigns `display_author_name` from `post_settings.display_author_photo`, while the author-name template renders only when `post_settings.display_author_name === 'true'`.
- Impact: The saved template configuration cannot represent independent author-name visibility. Disabling the author photo also disables the author name on the frontend, and enabling the name independently is impossible after settings are normalized.
- Recommended fix: Read `post_settings.display_author_name` into the `display_author_name` config key and add a regression test that toggles photo and name independently.
- Task statement: Rewire the author-name setting to its correct source key and test all post-visibility toggles independently.

### MEDIUM-03: Oxygen widget fallback uses a malformed shortcode tag

- Area: Traceability
- Confidence: High
- File:line: `app/Services/Widgets/Oxygen/TikTokWidget.php:451`
- Evidence: The fallback branch calls `do_shortcode('[wp_ social_ninja id="..." platform="tiktok"]')`, which inserts a space inside the shortcode tag name instead of using `wp_social_ninja`.
- Impact: When `do_oxygen_elements()` is unavailable, the intended fallback renderer cannot resolve the shortcode, so the TikTok widget fails to render content in that branch.
- Recommended fix: Change the fallback string to `[wp_social_ninja ...]` and add a regression check for both the primary Oxygen rendering branch and the fallback shortcode branch.
- Task statement: Fix the Oxygen fallback shortcode name and verify widget rendering in both renderer branches.

### Suggestion

### SUGGESTION-01: Dead route/bootstrap scaffolding obscures the real plugin surface

- Area: Traceability
- Confidence: High
- File:line: `app/Application.php:20`, `app/Http/Routes/api.php:1`, `app/Hooks/Handlers/ShortcodeHandler.php:46`, `app/Services/Platforms/Feeds/Tiktok/TiktokFeed.php:627`
- Evidence: `Application::boot()` always requires an empty `app/Http/Routes/api.php`; `ShortcodeHandler` keeps an unused `templateMapping`; `TiktokFeed::getAccountId()` has no call sites in the repository.
- Impact: Dead bootstrap files and unused helpers widen the apparent entry-point surface, make audits noisier, and increase the chance that future changes are wired into abandoned paths instead of the live rendering pipeline.
- Recommended fix: Remove empty route/bootstrap artifacts and unused helpers, or replace them with explicit documentation if they are placeholders for code generated elsewhere.
- Task statement: Delete unused route/helper scaffolding or document the intended external owner so the plugin surface stays explicit.

## Prioritized Backlog (Quick Wins First)

- [x]  Replace `str_contains()` with PHP-7.4-safe logic or raise the declared PHP minimum to 8.0+ before the next release.
- [ ]  Remove embedded TikTok OAuth credentials from plugin code and encrypt refresh tokens at rest. *(Will be fixed centrally with middleman site)*
- [x]  Fix `display_author_name` key mapping and correct the malformed Oxygen fallback shortcode.
- [x]  Rework `getAccountFeed()` pagination so it fetches only the remaining TikTok API pages needed.
- [ ]  Remove dead bootstrap artifacts (`api.php`, unused template map, unused helper methods) to keep the entry-point map accurate.
- [ ]  Manually verify the base-plugin callback and AJAX guard rails listed below, because those authorization layers are not present in this repository.

## Needs Manual Verification

- OAuth callback state/session binding
- Area: Security
- File:line: `app/Services/Platforms/Feeds/Tiktok/TiktokFeed.php:56`, `app/Services/Platforms/Feeds/Tiktok/TiktokFeed.php:75`, `app/Services/Platforms/Feeds/Tiktok/TiktokFeed.php:111`
- Why uncertain: `handleCredential()` accepts an `access_code` and there is no local `state`/session binding in this repository, but the route/controller that invokes it lives in the base plugin and is out of scope here.
- Manual test to confirm: Start an OAuth flow in one browser session, replay the returned code from a different session or forged request, and verify whether the base-plugin callback rejects codes that are not bound to the initiating session.
- Public load-more AJAX authorization and nonce enforcement
- Area: Security
- File:line: `app/Hooks/actions.php:35`, `app/Views/public/feeds-templates/tiktok/elements/load-more.php:20`
- Why uncertain: The public entrypoint `wp_ajax_nopriv_wpsr_get_more_feeds` is registered here, but the target `ShortcodeHandler@handleLoadMoreAjax` implementation is not present in this repository.
- Manual test to confirm: Send unauthenticated `admin-ajax.php?action=wpsr_get_more_feeds` requests with and without any expected nonce fields and confirm the base handler enforces the intended guard rails.
- Capability checks around account/config mutation methods
- Area: Security
- File:line: `app/Services/Platforms/Feeds/Tiktok/TiktokFeed.php:293`, `app/Services/Platforms/Feeds/Tiktok/TiktokFeed.php:302`, `app/Services/Platforms/Feeds/Tiktok/TiktokFeed.php:521`, `app/Services/Platforms/Feeds/Tiktok/TiktokFeed.php:1137`
- Why uncertain: Methods that read sources, disconnect accounts, save editor settings, and clear caches contain no local capability checks, but may be protected by the missing WP Social Ninja route/policy layer.
- Manual test to confirm: Trace each corresponding base-plugin AJAX/REST/controller route and verify policy coverage, nonce validation, and capability checks before these methods execute.