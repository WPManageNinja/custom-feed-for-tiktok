<?php
use WPSocialReviews\Framework\Support\Arr;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

if(Arr::get($template_meta, 'post_settings.display_author_name') === 'true'){
    $wpsr_tiktok_user_name = Arr::get($account, 'name', ''); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
    $wpsr_tiktok_profile_url = Arr::get($account, 'profile_url', ''); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
    ?>
    <a class="wpsr-tiktok-feed-author-name" href="<?php echo esc_url($wpsr_tiktok_profile_url); ?>" target="_blank" rel="nofollow">
        <?php echo esc_html($wpsr_tiktok_user_name); ?>
    </a>
<?php }