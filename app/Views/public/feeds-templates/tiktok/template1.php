<?php
use WPSocialReviews\Framework\Support\Arr;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

if (!empty($feeds) && is_array($feeds)) {
    $wpsr_tiktok_feed_type = Arr::get($template_meta, 'source_settings.feed_type'); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
    $wpsr_tiktok_column = isset($template_meta['column_number']) ? $template_meta['column_number'] : 4; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
    $wpsr_tiktok_column_class = 'wpsr-col-' . $wpsr_tiktok_column; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
    $wpsr_tiktok_layout_type = isset($template_meta['layout_type']) && defined('WPSOCIALREVIEWS_PRO') ? $template_meta['layout_type'] : 'grid'; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
    $wpsr_tiktok_animation_img_class = $wpsr_tiktok_layout_type === 'carousel' ? 'wpsr-animated-background' : ''; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

    // Check if the feed type is user_feed and the pro version is not defined
    if ($wpsr_tiktok_feed_type !== 'user_feed' && !defined('WPSOCIALREVIEWS_PRO')) {
        echo '<p>' . esc_html__('You need to upgrade to pro to use this feature.', 'custom-feed-for-tiktok') . '</p>';
        return;
    }

    // Check if post_settings exist in template_meta, if not, return
    if (!Arr::get($template_meta, 'post_settings')) {
        return;
    }

    $wpsr_tiktok_display_platform_icon = Arr::get($template_meta, 'post_settings.display_platform_icon'); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

    foreach ($feeds as $wpsr_tiktok_index => $wpsr_tiktok_feed) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
        if ($wpsr_tiktok_index >= $sinceId && $wpsr_tiktok_index <= $maxId) {
            if ($wpsr_tiktok_layout_type !== 'carousel') {
                do_action('custom_feed_for_tiktok/tiktok_feed_template_item_wrapper_before', $template_meta);
            }
            $wpsr_tiktok_user_name = Arr::get($wpsr_tiktok_feed, 'user.name'); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
            $wpsr_tiktok_feed_id = Arr::get($wpsr_tiktok_feed, 'id'); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
            $wpsr_tiktok_image_optimization = Arr::get($image_settings, 'optimized_images'); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
            $wpsr_tiktok_gdpr_enabled = Arr::get($image_settings, 'has_gdpr'); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
            $wpsr_tiktok_image_resolution = Arr::get($template_meta, 'post_settings.resolution'); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
            $wpsr_tiktok_data_play_mode = Arr::get($template_meta, 'post_settings.display_mode'); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
            ?>
            <div tabindex="0" role="group" class="wpsr-tiktok-feed-item wpsr-tt-post <?php echo ($wpsr_tiktok_layout_type === 'carousel' && defined('WPSOCIALREVIEWS_PRO')) ? 'swiper-slide' : ''; ?>"
                 data-post_id="<?php echo esc_attr($wpsr_tiktok_feed_id); ?>"
                 data-user_name="<?php echo esc_attr($wpsr_tiktok_user_name); ?>"
                 data-image_size="<?php echo esc_attr($wpsr_tiktok_image_resolution); ?>"
            >
                <div class="wpsr-tiktok-feed-playmode wpsr-tiktok-feed-inner"
                     data-feed_type="<?php echo esc_attr($wpsr_tiktok_feed_type); ?>"
                     data-index="<?php echo esc_attr($wpsr_tiktok_index); ?>"
                     data-playmode="<?php echo esc_attr($wpsr_tiktok_data_play_mode); ?>"
                     data-template-id="<?php echo esc_attr($templateId); ?>"
                     data-optimized_images="<?php echo esc_attr($wpsr_tiktok_image_optimization); ?>"
                     data-has_gdpr="<?php echo esc_attr($wpsr_tiktok_gdpr_enabled); ?>"
                     data-image_size="<?php echo esc_attr($wpsr_tiktok_image_resolution); ?>"
                >
                    <div class="wpsr-tiktok-feed-image">
                    <?php
                    /**
                     * tiktok_feed_media hook.
                     *
                     * @hooked TiktokTemplateHandler::renderFeedMedia 10
                     * */
                    do_action('custom_feed_for_tiktok/tiktok_feed_media', $wpsr_tiktok_feed, $template_meta);

                   if ($wpsr_tiktok_feed_type === 'user_feed') { ?>
                        <div class="wpsr-tiktok-feed-content-box">
                            <?php if ($template_meta['post_settings']['display_play_icon'] === 'true'): ?>
                                <div class="wpsr-tiktok-feed-video-play">
                                    <div class="wpsr-tiktok-feed-video-play-icon"></div>
                                </div>
                            <?php endif; ?>

                            <?php
                            if ($wpsr_tiktok_display_platform_icon === 'true') {
                                /**
                                 * tiktok_feed_icon hook.
                                 *
                                 * @hooked TiktokTemplateHandler::renderFeedIcon 10
                                 * */
                                 do_action('custom_feed_for_tiktok/tiktok_feed_icon', $wpsr_tiktok_class = 'wpsr-tiktok-icon-outer'); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
                            }

                            /**
                             * tiktok_feed_statistics hook.
                             *
                             * @hooked render_tiktok_feed_statistics 10
                             * */
                            do_action('custom_feed_for_tiktok/tiktok_feed_statistics', $template_meta, $wpsr_tiktok_feed);

                            /**
                             * tiktok_feed_author hook.
                             *
                             * @hooked TiktokTemplateHandler::renderFeedAuthor 10
                             * */
                            do_action('custom_feed_for_tiktok/tiktok_feed_author', $wpsr_tiktok_feed, $template_meta);
                            ?>
                        </div>
                    <?php } ?>
                    <?php if($wpsr_tiktok_layout_type === 'carousel'){ ?>
                        <div class="<?php echo esc_attr($wpsr_tiktok_animation_img_class); ?>"></div>
                    <?php } ?>
                    </div>
                    <div class="wpsr-tiktok-feed-image-hover-over-content">
                        <?php
                        if ($wpsr_tiktok_display_platform_icon === 'true') {
                            /**
                             * tiktok_feed_icon hook.
                             *
                             * @hooked TiktokTemplateHandler::renderFeedIcon 10
                             * */
                             do_action('custom_feed_for_tiktok/tiktok_feed_icon', $wpsr_tiktok_class = 'wpsr-tiktok-icon'); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
                        }
                        /**
                         * tiktok_feed_description hook.
                         *
                         * @hooked TiktokTemplateHandler::renderFeedDescription 10
                         * */
                        do_action('custom_feed_for_tiktok/tiktok_feed_description', $wpsr_tiktok_feed, $template_meta);

                        /**
                         * tiktok_feed_author_name hook.
                         *
                         * @hooked render_author_name 10
                         * */
                        do_action('custom_feed_for_tiktok/tiktok_feed_author_name', $wpsr_tiktok_feed, $template_meta);
                        ?>
                    </div>
                </div>
            </div>
            <?php if ($wpsr_tiktok_layout_type !== 'carousel') { ?>
                </div>
            <?php }
        }
    }
}
?>
