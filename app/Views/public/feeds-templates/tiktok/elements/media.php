<?php
use WPSocialReviews\Framework\Support\Arr;
use WPSocialReviews\App\Services\Helper;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

$wpsr_tiktok_user_name = Arr::get($feed, 'user.name', ''); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$wpsr_tiktok_feed_id = Arr::get($feed, 'id', ''); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$wpsr_tiktok_preview_image = Arr::get($feed, 'media.preview_image_url', ''); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$wpsr_tiktok_description = Arr::get($feed, 'text', ''); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$wpsr_tiktok_display_mode = Arr::get($template_meta, 'post_settings.display_mode'); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$wpsr_tiktok_media_url = Arr::get($feed, 'media_url', ''); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$wpsr_tiktok_default_media = Arr::get($feed, 'media.preview_image_url', ''); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$wpsr_tiktok_img_class = !empty($wpsr_tiktok_media_url) && !str_contains($wpsr_tiktok_media_url, 'placeholder') ? 'wpsr-tt-post-img wpsr-show' : 'wpsr-tt-post-img wpsr-hide'; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$wpsr_tiktok_video_url = 'https://www.tiktok.com/@'.$wpsr_tiktok_user_name.'/video/'.$wpsr_tiktok_feed_id; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$wpsr_tiktok_image_optimization = Arr::get($image_settings, 'optimized_images'); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$wpsr_tiktok_animation_img_class = str_contains($wpsr_tiktok_media_url, 'placeholder') && $wpsr_tiktok_media_url ? 'wpsr-animated-background' : ''; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$wpsr_tiktok_attrs = [
    'class'  => 'class="wpsr-tiktok-feed-video-preview wpsr-tiktok-feed-video-playmode wpsr-feed-link"',
    'target' => $wpsr_tiktok_display_mode !== 'none' ? 'target="_blank"' : '',
    'rel'    => 'rel="nofollow"',
    'href'   =>  $wpsr_tiktok_display_mode !== 'none' ? 'href="'.esc_url($wpsr_tiktok_video_url).'"' : '',
];

?>
    <div class="wpsr-tt-post-media <?php echo esc_attr($wpsr_tiktok_animation_img_class); ?>">
    <?php if ($wpsr_tiktok_display_mode !== 'none'): ?>
        <a <?php Helper::printInternalString(implode(' ', $wpsr_tiktok_attrs)); ?>>
    <?php else: ?>
        <div class="wpsr-tiktok-feed-video-preview wpsr-tiktok-feed-video-playmode wpsr-feed-link ">
    <?php endif; ?>
            <img class="<?php echo esc_attr($wpsr_tiktok_img_class); ?>" src="<?php echo esc_url($wpsr_tiktok_image_optimization === 'true' ? $wpsr_tiktok_media_url : $wpsr_tiktok_default_media); ?>" alt="<?php echo esc_attr($wpsr_tiktok_description); ?>"/>
    <?php if ($wpsr_tiktok_display_mode !== 'none'): ?>
        </a>
    <?php else: ?>
        </div>
    <?php endif; ?>
    </div>

