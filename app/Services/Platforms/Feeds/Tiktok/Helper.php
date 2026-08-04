<?php

namespace CustomFeedForTiktok\Application\Services\Platforms\Feeds\Tiktok;

use WPSocialReviews\Framework\Support\Arr;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class Helper
{
    public static function getConnectedSourceList()
    {
        $configs = get_option('wpsr_tiktok_connected_sources_config', []);
        $sourceList = Arr::get($configs, 'sources') ? $configs['sources'] : [];
        return $sourceList;
    }

    /**
     *
     * Resolve the header layout CSS class from the feed settings.
     * 'classic' keeps the original header, 'minimal' renders the flat header.
     *
     * @param $feed_settings
     *
     * @return string
     *
     **/
    public static function getHeaderLayoutClass($feed_settings = [])
    {
        $layout = Arr::get($feed_settings, 'header_settings.header_layout', 'classic');
        if (!in_array($layout, array('classic', 'minimal'), true)) {
            $layout = 'classic';
        }

        return 'wpsr-tiktok-feed-header-layout-' . $layout;
    }
}