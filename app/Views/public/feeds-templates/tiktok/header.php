<?php

use WPSocialReviews\Framework\Support\Arr;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$wpsr_tiktok_data_attrs  = array();
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$wpsr_tiktok_slider_data = array();
if ($layout_type === 'carousel') {
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
    $wpsr_tiktok_slider_data = array(
        'autoplay'               => $feed_settings['carousel_settings']['autoplay'],
        'autoplay_speed'         => $feed_settings['carousel_settings']['autoplay_speed'],
        'responsive_slides_to_show'  => Arr::get($feed_settings, 'carousel_settings.responsive_slides_to_show'),
        'responsive_slides_to_scroll'  => Arr::get($feed_settings, 'carousel_settings.responsive_slides_to_scroll'),
        'navigation'             => $feed_settings['carousel_settings']['navigation'],
    );
}

$wpsr_tiktok_data_attrs[] = $layout_type === 'carousel' && defined('WPSOCIALREVIEWS_PRO') ? 'data-slider_settings=' . json_encode($wpsr_tiktok_slider_data) . '' : ''; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$wpsr_tiktok_feed_type = Arr::get($feed_settings, 'source_settings.feed_type'); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

// wrapper classes
$wpsr_tiktok_classes   = array('wpsr-tiktok-feed-wrapper', 'wpsr-feed-wrap', 'wpsr_content'); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$wpsr_tiktok_classes[] = $template === 'template2' ? 'wpsr-tiktok-feed-template2' : ''; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$wpsr_tiktok_classes[] = 'wpsr-tiktok-feed-' . esc_attr($template) . ''; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$wpsr_tiktok_classes[] = 'wpsr-tiktok-' . esc_attr($wpsr_tiktok_feed_type) . ''; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$wpsr_tiktok_classes[] = $layout_type === 'carousel' && defined('WPSOCIALREVIEWS_PRO') ? 'wpsr-tiktok-feed-slider-activate' : ''; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$wpsr_tiktok_classes[] = $layout_type === 'masonry' ? 'wpsr-tiktok-feed-masonry-activate' : ''; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$wpsr_tiktok_classes[] = 'wpsr-tiktok-feed-template-' . esc_attr($templateId) . ''; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

$wpsr_tiktok_classes[] = Arr::get($feed_settings, 'post_settings.equal_height') === 'true' ? 'wpsr-has-equal-height' : ''; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$wpsr_tiktok_classes[] = Arr::get($feed_settings, 'layout_type') === 'timeline' ? 'wpsr-tiktok-feed-layout-standard' : ''; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$wpsr_tiktok_desktop_column_number   = Arr::get($feed_settings, 'responsive_column_number.desktop'); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

$wpsr_tiktok_header_settings = Arr::get($feed_settings, 'header_settings'); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$wpsr_tiktok_display_profile_photo = Arr::get($wpsr_tiktok_header_settings, 'display_profile_photo'); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$wpsr_tiktok_profile_photo_hide_class = $wpsr_tiktok_display_profile_photo === 'false' ? 'wpsr-tiktok-feed-profile-pic-hide' : ''; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$wpsr_tiktok_display_header = Arr::get($wpsr_tiktok_header_settings, 'display_header'); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

echo '<div  id="wpsr-tiktok-feed-' . esc_attr($templateId) . '" class="' . esc_attr(implode(' ', $wpsr_tiktok_classes)) . '" ' . esc_attr(implode(' ',
        $wpsr_tiktok_data_attrs)) . '  data-column="' . esc_attr($wpsr_tiktok_desktop_column_number) . '">';
echo '<div class="wpsr-loader">
        <div class="wpsr-spinner-animation"></div>
    </div>';
echo '<div class="wpsr-container">';

if ($wpsr_tiktok_display_header === 'true' && !empty($header)) {
    $wpsr_tiktok_avatar_url = Arr::get($header, 'avatar_url', ''); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
    $wpsr_tiktok_display_name = Arr::get($header, 'display_name', ''); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
    $wpsr_tiktok_profile_deep_link = Arr::get($header, 'profile_deep_link', ''); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

    echo '<div class="wpsr-row">
        <div class="wpsr-tiktok-feed-header wpsr-col-12 ' . ($wpsr_tiktok_header_settings['display_profile_photo'] === 'false' ? 'wpsr-tiktok-feed-profile-pic-hide' : '') . '">
            <div class="wpsr-tiktok-feed-user-info-wrapper">
                <div class="wpsr-tiktok-feed-user-info-head">
                    <div class="wpsr-tiktok-feed-header-info">';
                        if ($wpsr_tiktok_avatar_url && $wpsr_tiktok_header_settings['display_profile_photo'] === 'true') {
                            echo '<a rel="nofollow" href="' . esc_url($wpsr_tiktok_profile_deep_link) . '" target="_blank" class="wpsr-tiktok-feed-user-profile-pic">
                                    <img src="' . esc_url($wpsr_tiktok_avatar_url) . '" alt="' . esc_attr($wpsr_tiktok_display_name) . '">
                                  </a>';
                        }

                        echo '<div class="wpsr-tiktok-feed-user-info">
                                <div class="wpsr-tiktok-feed-user-info-name-wrapper">';
                        if ($wpsr_tiktok_display_name && $wpsr_tiktok_header_settings['display_page_name'] === 'true') {
                            echo '<a class="wpsr-tiktok-feed-user-info-name" rel="nofollow" href="' . esc_url($wpsr_tiktok_profile_deep_link) . '" title="' . esc_attr($wpsr_tiktok_display_name) . '" target="_blank">
                                      ' . esc_html($wpsr_tiktok_display_name) . '
                                  </a>';
                        }
                        echo '</div>';

                        /**
                         * tiktok_feed_bio_description hook.
                         *
                         * @hooked render_tiktok_feed_bio_description 10
                         * */
                        do_action('custom_feed_for_tiktok/tiktok_feed_bio_description', $wpsr_tiktok_header_settings, $header);

                        /**
                         * tiktok_feed_statistics hook.
                         *
                         * @hooked render_tiktok_feed_statistics 10
                         * */
                        do_action('custom_feed_for_tiktok/tiktok_header_statistics', $wpsr_tiktok_header_settings, $header, $translations);

                echo' </div>
            </div>';

            if ($feed_settings['follow_button_settings']['display_follow_button'] === 'true' && $feed_settings['follow_button_settings']['follow_button_position'] !== 'footer') {
                do_action('custom_feed_for_tiktok/tiktok_follow_button', $feed_settings, $header);
            }
    echo '</div>
        </div>
      </div>
    </div>';
}

echo '<div class="wpsr-tiktok-feed-wrapper-inner">';
if ($layout_type === 'carousel' && defined('WPSOCIALREVIEWS_PRO')) {
    echo '<div class="swiper-container" tabindex="0">';
}
$wpsr_tiktok_row_classes = $layout_type === 'carousel' && defined('WPSOCIALREVIEWS_PRO') ? 'swiper-wrapper' : 'wpsr-row'; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

echo '<div class="' . esc_attr($wpsr_tiktok_row_classes) . ' wpsr-tt-all-feed wpsr_feeds wpsr-column-gap-' . esc_attr($column_gaps) . '">';
?>
