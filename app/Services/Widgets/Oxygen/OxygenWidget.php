<?php

/**
 * Oxygen Builder TikTok Widget Integration
 *
 * This integrates TikTok feeds with Oxygen Builder page builder.
 * Uses Oxygen's specific hook naming conventions for proper integration.
 */

namespace CustomFeedForTiktok\Application\Services\Widgets\Oxygen;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

if (!class_exists('OxyEl') ) {
    return;
}

class OxygenWidget
{
    public function __construct()
    {
        add_action('init', array($this, 'initWidgets'));
        add_action('oxygen_add_plus_wpsocialninja_section_content', array($this, 'registerAddPlusSubsections'));
    }

    public function initWidgets()
    {
        if ( file_exists( CUSTOM_FEED_FOR_TIKTOK_DIR.'app/Services/Widgets/Oxygen/TikTokWidget.php' ) ) {
            require_once CUSTOM_FEED_FOR_TIKTOK_DIR.'app/Services/Widgets/Oxygen/TikTokWidget.php';
        }
    }

    public function registerAddPlusSubsections()
    {
        do_action("oxygen_add_plus_wpsocialninja_tiktok"); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
    }
}