<?php

/**
 * TikTok Feed Load More Button Template
 *
 * This template renders the load more button for TikTok feeds.
 * Variables are template-scoped for rendering the load more functionality.
 */

use WPSocialReviews\Framework\Support\Arr;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

$wpsr_tiktok_feed_type = $feed_type ? $feed_type : ''; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$wpsr_tiktok_feed_id =  Arr::get($feed, 'id', ''); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$wpsr_tiktok_load_more_button_text = Arr::get($template_meta, 'pagination_settings.load_more_button_text'); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

echo '<button aria-label="'.esc_attr($wpsr_tiktok_load_more_button_text).'" class="wpsr-tiktok-load-more wpsr_more wpsr-load-more-default"
        id="wpsr-tiktok-load-more-btn-' . esc_attr($templateId) . '"
        data-paginate="' . intval($paginate) . '"
        data-template_id="' . intval($templateId) . '"
        data-template_type="' . esc_attr($layout_type) . '"
        data-platform="tiktok"
        data-page="1"
        data-feed_type="' . esc_attr($wpsr_tiktok_feed_type) . '"
        data-feed_id="' . esc_attr($wpsr_tiktok_feed_id) . '"
        data-total="' . intval($total) . '">
                '.esc_html($wpsr_tiktok_load_more_button_text).'
        <div class="wpsr-load-icon-wrapper">
            <span></span>
        </div>
    </button>';
?>