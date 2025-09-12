<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

!defined('WPINC') && die;

define('CUSTOM_FEED_FOR_TIKTOK_VERSION', '1.1.7');
define('CUSTOM_FEED_FOR_TIKTOK', true);
define('CUSTOM_FEED_FOR_TIKTOK_URL', plugin_dir_url(__FILE__));
define('CUSTOM_FEED_FOR_TIKTOK_DIR', plugin_dir_path(__FILE__));

spl_autoload_register(function ($class){
    $match = 'CustomFeedForTiktok';
    if ( ! preg_match("/\b{$match}\b/", $class)) {
        return;
    }
    $path = plugin_dir_path(__FILE__);

    $file = str_replace(
        ['CustomFeedForTiktok', '\\', '/Application/'],
        ['', DIRECTORY_SEPARATOR, 'app/'],
        $class
    );

    $filePath = (trailingslashit($path) . trim($file, '/') . '.php');
    if (file_exists($filePath)) {
        require $filePath;
    }
});

class CustomFeedForTiktokDependency
{
    public function init()
    {
        $this->injectDependency();
    }

    /**
     * Notify the user about the WP Social Ninja dependency and instructs to install it.
     */
    protected function injectDependency()
    {
        add_action('admin_notices', function () {
            $pluginInfo = $this->getBasePluginInstallationDetails();

            $class = 'notice notice-error';

            $install_url_text = __('Click Here to Install the Plugin', 'custom-feed-for-tiktok');

            if ($pluginInfo->action == 'activate') {
                $install_url_text = __('Click Here to Activate the Plugin', 'custom-feed-for-tiktok');
            }

            $message = 'Custom Feed for TikTok Requires WP Social Ninja Base Plugin, <b><a href="' . $pluginInfo->url
                . '">' . $install_url_text . '</a></b>';

            // Allowed HTML for wp_kses
            $allowed_html = array(
                'a' => array(
                    'href' => array(),
                    'title' => array()
                ),
                'b' => array(),
                'br' => array(),
                'em' => array(),
                'strong' => array(),
            );

            printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), wp_kses($message, $allowed_html));
        });
    }

    /**
     * Get the WP Social Ninja plugin installation information e.g. the URL to install.
     *
     * @return \stdClass $activation
     */
    protected function getBasePluginInstallationDetails()
    {
        $activation = (object)[
            'action' => 'install',
            'url'    => ''
        ];

        $allPlugins = get_plugins();

        $plugin_path = 'wp-social-reviews/wp-social-reviews.php';

        if (isset($allPlugins[$plugin_path])) {
            $url = wp_nonce_url(
                self_admin_url('plugins.php?action=activate&plugin=' . $plugin_path . ''),
                'activate-plugin_' . $plugin_path . ''
            );

            $activation->action = 'activate';
        } else {
            $api = (object)[
                'slug' => 'wp-social-reviews'
            ];

            $url = wp_nonce_url(
                self_admin_url('update.php?action=install-plugin&plugin=' . $api->slug),
                'install-plugin_' . $api->slug
            );
        }
        $activation->url = $url;

        return $activation;
    }
}

add_action('init', function ($app) {
    if( !defined('WPSOCIALREVIEWS_VERSION') ){
        (new CustomFeedForTiktokDependency())->init();
    }
});

