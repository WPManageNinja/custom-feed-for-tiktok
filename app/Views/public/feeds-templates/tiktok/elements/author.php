<?php
use WPSocialReviews\Framework\Support\Arr;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

$wpsr_tiktok_profile_image = Arr::get($account, 'profile_image_url', ''); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$wpsr_tiktok_user_name = Arr::get($account, 'name', ''); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$wpsr_tiktok_media_url = Arr::get($feed, 'media.url', ''); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$wpsr_tiktok_profile_url = Arr::get($account, 'profile_url', ''); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$wpsr_tiktok_local_user_avatar = Arr::get($feed, 'user_avatar'); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$feed['user_avatar'] = !empty($wpsr_tiktok_local_user_avatar) ? $wpsr_tiktok_local_user_avatar : $wpsr_tiktok_profile_image; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$wpsr_tiktok_display_author_photo = Arr::get($template_meta, 'post_settings.display_author_photo'); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

if( is_array($account)){ ?>
    <div class="wpsr-tiktok-feed-author-avatar-wrapper">
        <?php if($wpsr_tiktok_profile_image && $wpsr_tiktok_display_author_photo === 'true'){ ?>
            <img src="<?php echo esc_url($feed['user_avatar']); ?>" alt="<?php echo esc_attr($wpsr_tiktok_user_name); ?>" class="wpsr-tiktok-feed-author-avatar" />
        <?php } ?>

        <div class="wpsr-feed-avatar-right">
            <?php
            /**
             * tiktok_feed_author_name hook.
             *
             * @hooked render_author_name 10
             * */
            do_action('custom_feed_for_tiktok/tiktok_feed_author_name', $feed, $template_meta);

            /**
             * tiktok_feed_date hook.
             *
             * @hooked render_feed_date 10
             * */
            do_action('custom_feed_for_tiktok/tiktok_feed_date', $template_meta, $feed);
            ?>
        </div>
    </div>
<?php } ?>