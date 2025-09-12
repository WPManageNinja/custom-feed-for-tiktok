<?php
/*
Plugin Name:  Custom Feed for TikTok
Plugin URI:   https://github.com/WPManageNinja/custom-feed-for-tiktok
Description:  Create eye-catchy and responsive TikTok feed on your WordPress website.
Version:      1.1.7
Author:       Social Feed - WP Social Ninja Team
Author URI:   https://wpsocialninja.com/platforms/tiktok-feed/
License:      GPLv2 or later
Text Domain:  custom-feed-for-tiktok
Domain Path:  /language
*/

defined('ABSPATH') or die;

if (defined('CUSTOM_FEED_FOR_TIKTOK_MAIN_FILE')) {
    return;
}

define('CUSTOM_FEED_FOR_TIKTOK_MAIN_FILE', __FILE__);

require_once(plugin_dir_path(__FILE__) . 'custom-feed-for-tiktok-boot.php');

add_action('wp_social_reviews_loaded_v2', function ($app) {
    (new \CustomFeedForTiktok\Application\Application($app));
    do_action('custom_feed_for_tiktok_loaded', $app);
});

add_action('init', function () {
    load_plugin_textdomain('custom-feed-for-tiktok', false, basename(dirname(__FILE__)) . '/language');
});


