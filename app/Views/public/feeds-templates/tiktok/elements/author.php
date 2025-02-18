<?php
use WPSocialReviews\Framework\Support\Arr;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

$profileImage = Arr::get($account, 'profile_image_url', '');
$userName = Arr::get($account, 'name', '');
$mediaUrl = Arr::get($feed, 'media.url', '');
$profileUrl = Arr::get($account, 'profile_url', '');
$local_user_avatar = Arr::get($feed, 'user_avatar');
$feed['user_avatar'] = !empty($local_user_avatar) ? $local_user_avatar : $profileImage;
$display_author_photo = Arr::get($template_meta, 'post_settings.display_author_photo');

if( is_array($account)){ ?>
    <div class="wpsr-tiktok-feed-author-avatar-wrapper">
        <?php if($profileImage && $display_author_photo === 'true'){ ?>
            <img src="<?php echo esc_url($feed['user_avatar']); ?>" alt="<?php echo esc_attr($userName); ?>" class="wpsr-tiktok-feed-author-avatar" />
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