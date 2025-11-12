<?php
use WPSocialReviews\Framework\Support\Arr;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

echo '</div>'; // row end

if( $layout_type === 'carousel' && defined('WPSOCIALREVIEWS_PRO')) {
    echo '</div>'; // swiper container end
    echo '<div class="wpsr-swiper-carousel-wrapper">';
    if( $feed_settings['carousel_settings']['navigation'] === 'arrow' || $feed_settings['carousel_settings']['navigation'] === 'both') {
        echo '<div class="wpsr-swiper-prev-next wpsr-swiper-next swiper-button-next"></div>
              <div class="wpsr-swiper-prev-next wpsr-swiper-prev swiper-button-prev"></div>';
    }
    if( $feed_settings['carousel_settings']['navigation'] === 'dot' || $feed_settings['carousel_settings']['navigation'] === 'both') {
        echo '<div class="wpsr-swiper-pagination swiper-pagination" aria-label="Pagination"></div>';
    }
    echo '</div>';
}

$wpsr_tiktok_mt_30 = $column_gaps === 'no_gap' ? 'wpsr-mt-20' : ''; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
echo '<div class="wpsr-tiktok-feed-footer wpsr-tiktok-feed-follow-button-group wpsr-row ' . esc_attr($wpsr_tiktok_mt_30) . '">';
//pagination
$wpsr_tiktok_feed_type = Arr::get($feed_settings, 'source_settings.feed_type', ''); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
if (count($feeds) > $paginate && $layout_type !== 'carousel' && $pagination_type === 'load_more' ) {
    do_action('custom_feed_for_tiktok/load_more_tiktok_button', $feed_settings, $templateId, $paginate, $layout_type, $total, $wpsr_tiktok_feed_type);
}

if (Arr::get($feed_settings, 'follow_button_settings.follow_button_position') !== 'header' && Arr::get($feed_settings, 'follow_button_settings.display_follow_button') ) {

    /**
     * tiktok_like_button hook.
     *
     * @hooked render_tiktok_like_button_html 10
     * */
    do_action('custom_feed_for_tiktok/tiktok_follow_button', $feed_settings, $header);

}
echo '</div>';

echo '</div>'; // wpsr-tiktok-feed-wrapper-inner end

echo '</div>'; // wpsr-container end
echo '</div>'; // wpsr-tiktok-feed-wrapper end