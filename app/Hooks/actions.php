<?php
defined('ABSPATH') or die;

/**
 * All registered action's handlers should be in app\Hooks\Handlers,
 * addAction is similar to add_action and addCustomAction is just a
 * wrapper over add_action which will add a prefix to the hook name
 * using the plugin slug to make it unique in all wordpress plugins,
 * ex: $app->addCustomAction('foo', ['FooHandler', 'handleFoo']) is
 * equivalent to add_action('slug-foo', ['FooHandler', 'handleFoo']).
 */

/**
 * @var $app CustomFeedForTiktok\Application\Application
 */

/*******
 *
 * Tiktok feed templates action hooks
 *
 *******/

(new \CustomFeedForTiktok\Application\Hooks\Handlers\PlatformHandler())->register();

$app->addAction('custom_feed_for_tiktok/tiktok_feed_template_item_wrapper_before', 'CustomFeedForTiktok\Application\Hooks\Handlers\TiktokTemplateHandler@renderTemplateItemWrapper');
$app->addAction('custom_feed_for_tiktok/tiktok_feed_author', 'CustomFeedForTiktok\Application\Hooks\Handlers\TiktokTemplateHandler@renderFeedAuthor', 10, 2);
$app->addAction('custom_feed_for_tiktok/tiktok_feed_author_name', 'CustomFeedForTiktok\Application\Hooks\Handlers\TiktokTemplateHandler@renderFeedAuthorName', 10, 2);
$app->addAction('custom_feed_for_tiktok/tiktok_feed_description', 'CustomFeedForTiktok\Application\Hooks\Handlers\TiktokTemplateHandler@renderFeedDescription', 10, 2);
$app->addAction('custom_feed_for_tiktok/tiktok_feed_media', 'CustomFeedForTiktok\Application\Hooks\Handlers\TiktokTemplateHandler@renderFeedMedia', 10, 2);
$app->addAction('custom_feed_for_tiktok/tiktok_feed_icon', 'CustomFeedForTiktok\Application\Hooks\Handlers\TiktokTemplateHandler@renderFeedIcon', 10, 1);

$app->addAction('custom_feed_for_tiktok/load_more_tiktok_button', 'CustomFeedForTiktok\Application\Hooks\Handlers\TiktokTemplateHandler@renderLoadMoreButton', 10, 7);


$app->addAction('wp_ajax_wpsr_get_more_feeds', 'ShortcodeHandler@handleLoadMoreAjax');
$app->addAction('wp_ajax_nopriv_wpsr_get_more_feeds', 'ShortcodeHandler@handleLoadMoreAjax');


/*
 * Oxygen Widget Init
 */
if (class_exists('OxyEl') ) {
    if ( file_exists( CUSTOM_FEED_FOR_TIKTOK_DIR.'app/Services/Widgets/Oxygen/OxygenWidget.php' ) ) {
        new CustomFeedForTiktok\Application\Services\Widgets\Oxygen\OxygenWidget();
    }
}

/*
 * Elementor Widget Init
 */
if (defined('ELEMENTOR_VERSION')) {
    new CustomFeedForTiktok\Application\Services\Widgets\ElementorWidget();
}

/*
 * Beaver Builder Widget Init
 */
if ( class_exists( 'FLBuilder' ) ) {
    new CustomFeedForTiktok\Application\Services\Widgets\Beaver\BeaverWidget();
}