# Claude Code Skills — Custom Feed for TikTok

This plugin is a **dependent extension** of WP Social Ninja. Always load the relevant core skill first — only load TikTok-specific skills for logic that doesn't exist in the base system.

## Skill Loading Order (mandatory)

```
1. Core plugin skills    → wp-social-reviews/.claude/skills/
2. TikTok-specific skills → (this directory)
```

## When to Use Core Skills (DO NOT duplicate)

| Task | Load instead |
|------|-------------|
| Understanding CacheHandler API | `wp-social-reviews/.claude/skills/workflow-caching.md` |
| Understanding BaseFeed pattern | `wp-social-reviews/.claude/skills/workflow-feeds.md` |
| PlatformErrorManager usage | `wp-social-reviews/.claude/skills/workflow-error-handling.md` |
| Shortcode / template rendering base | `wp-social-reviews/.claude/skills/workflow-templates.md` |
| Adding a new platform (checklist) | `wp-social-reviews/.claude/skills/workflow-new-platform.md` |
| Controller / policy patterns | `wp-social-reviews/.claude/skills/coding-patterns.md` |
| Bug fix process, security checklist | `wp-social-reviews/.claude/skills/workflow-bugfix.md` |
| Cron / scheduled sync patterns | `wp-social-ninja-pro/.claude/skills/workflow-scheduled-sync.md` |

## TikTok-Specific Skills (load these only for TikTok logic)

| File | When to read |
|------|-------------|
| `architecture.md` | Plugin structure, boot chain, constants, namespace, dependencies |
| `workflow-tiktok-api.md` | TikTok Open API v2, OAuth via middleman, token lifecycle, endpoint field selectors |
| `workflow-tiktok-feed.md` | Feed types (user/single video), multi-source aggregation, Config normalizer, formatData schema, pagination |
| `workflow-tiktok-templates.md` | Template rendering pipeline (header/grid/footer), action hook elements, page builder integrations (Elementor, Oxygen, Beaver) |
| `workflow-tiktok-bugfix.md` | TikTok-specific pitfalls, known bugs, security issues unique to this plugin |

## Quick Decision Guide

```
"Why is my TikTok feed empty?"
  → workflow-tiktok-api.md (token expired?) +
    workflow-caching.md (cache stale?) +
    workflow-tiktok-feed.md (formatData issue?)

"How do I add a new TikTok feed type?"
  → workflow-tiktok-feed.md +
    workflow-new-platform.md (base checklist)

"Why isn't the TikTok widget rendering in Elementor?"
  → workflow-tiktok-templates.md

"TikTok OAuth not completing"
  → workflow-tiktok-api.md (OAuth + middleman flow)

"Token keeps expiring too fast"
  → workflow-tiktok-api.md (maybeRefreshToken) +
    workflow-tiktok-bugfix.md (known token issues)
```
