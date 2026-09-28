<?php
defined('ABSPATH') or die;

/**
 * Filter handlers live in app/Hooks/Handlers.
 * $app is the core plugin's app, so addCustomFilter() prefixes hooks with core's hook prefix.
 */

/**
 * @var $app WPSocialReviews\Framework\Foundation\Application
 */

$app->addFilter('wpsocialreviews/get_paginated_feed_html', 'CustomFeedForTiktok\Application\Hooks\Handlers\TiktokTemplateHandler@getPaginatedFeedHtml', 10, 2);
$app->addFilter('wpsocialreviews/render_tiktok_template', 'CustomFeedForTiktok\Application\Hooks\Handlers\ShortcodeHandler@renderTiktokTemplate', 10, 2);
$app->addFilter('wpsocialreviews/format_tiktok_config', 'CustomFeedForTiktok\Application\Hooks\Handlers\TiktokTemplateHandler@formatTiktokConfig', 10, 2);
$app->addFilter('wpsocialreviews/get_connected_source_list', 'CustomFeedForTiktok\Application\Services\Platforms\Feeds\Tiktok\Helper@getConnectedSourceList');
$app->addFilter('wpsocialreviews/get_template_meta', 'CustomFeedForTiktok\Application\Hooks\Handlers\TiktokTemplateHandler@getTemplateMeta', 10, 2);