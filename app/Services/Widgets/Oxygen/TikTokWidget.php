<?php
namespace CustomFeedForTiktok\Application\Services\Widgets\Oxygen;
use WPSocialReviews\App\Hooks\Handlers\ShortcodeHandler;
use WPSocialReviews\App\Services\Widgets\Helper;
use WPSocialReviews\Framework\Support\Arr;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class TikTokWidget extends OxygenEl
{
    public $css_added = false;

    function name() {
        return __( "TikTok", 'custom-feed-for-tiktok' );
    }

    function slug() {
        return "tiktok_widget";
    }

    function accordion_button_place() {
        return "tiktok";
    }

    function icon() {
        return '';
    }

    function controls() {
        /*****************************
         * template list
         *****************************/
        $platforms = ['tiktok'];
        $templates = Helper::getTemplates($platforms);
        $templates_control = $this->addOptionControl(
            array(
                'type' 		=> 'dropdown',
                'name' 		=> __('Select Template' , "custom-feed-for-tiktok"),
                'slug' 		=> 'wpsr_tiktok',
                'value' 	=> $templates,
                'default' 	=> "no",
                "css" 		=> false
            )
        );
        $templates_control->rebuildElementOnChange();

        /*****************************
         * Header
         *****************************/
        $tiktok_header_section = $this->addControlSection( "wpsr_tiktok_header_section", __("Header", "custom-feed-for-tiktok"), "assets/icon.png", $this );

        /*****************************
         * Header username
         *****************************/
        $tiktok_header_un = $tiktok_header_section->addControlSection( "wpsr_tiktok_header_username_section", __("Username", "custom-feed-for-tiktok"), "assets/icon.png", $this );
        $tiktok_header_un->addStyleControls(
            array(
                array(
                    "name" 				=> __('Color', "custom-feed-for-tiktok"),
                    "selector" 			=> '.wpsr-tiktok-feed-header .wpsr-tiktok-feed-user-info-wrapper .wpsr-tiktok-feed-user-info-head .wpsr-tiktok-feed-header-info .wpsr-tiktok-feed-user-info .wpsr-tiktok-feed-user-info-name-wrapper a',
                    "property" 			=> 'color',
                ),
                array(
                    "name" 				=> __('Font Size', "custom-feed-for-tiktok"),
                    "selector" 			=> '.wpsr-tiktok-feed-header .wpsr-tiktok-feed-user-info-wrapper .wpsr-tiktok-feed-user-info-head .wpsr-tiktok-feed-header-info .wpsr-tiktok-feed-user-info .wpsr-tiktok-feed-user-info-name-wrapper a',
                    "property" 			=> 'font-size',
                    'control_type' 		=> 'slider-measurebox'
                ),
                array(
                    "name" 				=> __('Font Weight', "custom-feed-for-tiktok"),
                    "selector" 			=> '.wpsr-tiktok-feed-header .wpsr-tiktok-feed-user-info-wrapper .wpsr-tiktok-feed-user-info-head .wpsr-tiktok-feed-header-info .wpsr-tiktok-feed-user-info .wpsr-tiktok-feed-user-info-name-wrapper a',
                    "property" 			=> 'font-weight',
                ),
                array(
                    "name" 				=> __('Line Height', "custom-feed-for-tiktok"),
                    "selector" 			=> '.wpsr-tiktok-feed-header .wpsr-tiktok-feed-user-info-wrapper .wpsr-tiktok-feed-user-info-head .wpsr-tiktok-feed-header-info .wpsr-tiktok-feed-user-info .wpsr-tiktok-feed-user-info-name-wrapper a',
                    "property" 			=> 'line-height',
                ),
                array(
                    "name" 				=> __('Bottom Spacing', "custom-feed-for-tiktok"),
                    "selector" 			=> '.wpsr-tiktok-feed-header .wpsr-tiktok-feed-user-info-wrapper .wpsr-tiktok-feed-user-info-head .wpsr-tiktok-feed-header-info .wpsr-tiktok-feed-user-info .wpsr-tiktok-feed-user-info-name-wrapper a',
                    "property" 			=> 'margin-bottom',
                    "control_type" 		=> 'slider-measurebox',
                    'unit' 				=> 'px'
                )
            )
        );

        /*****************************
         * Header description
         *****************************/
        $tiktok_header_des = $tiktok_header_section->addControlSection( "wpsr_tiktok_header_des_section", __("Description", "custom-feed-for-tiktok"), "assets/icon.png", $this );
        $tiktok_header_des->addStyleControls(
            array(
                array(
                    "name" 				=> __('Color', "custom-feed-for-tiktok"),
                    "selector" 			=> '.wpsr-tiktok-feed-header .wpsr-tiktok-feed-user-info-wrapper .wpsr-tiktok-feed-user-info-head .wpsr-tiktok-feed-header-info .wpsr-tiktok-feed-user-info .wpsr-tiktok-feed-user-info-description p',
                    "property" 			=> 'color',
                ),
                array(
                    "name" 				=> __('Font Size', "custom-feed-for-tiktok"),
                    "selector" 			=> '.wpsr-tiktok-feed-header .wpsr-tiktok-feed-user-info-wrapper .wpsr-tiktok-feed-user-info-head .wpsr-tiktok-feed-header-info .wpsr-tiktok-feed-user-info .wpsr-tiktok-feed-user-info-description p',
                    "property" 			=> 'font-size',
                    'control_type' 		=> 'slider-measurebox'
                ),
                array(
                    "name" 				=> __('Font Weight', "custom-feed-for-tiktok"),
                    "selector" 			=> '.wpsr-tiktok-feed-header .wpsr-tiktok-feed-user-info-wrapper .wpsr-tiktok-feed-user-info-head .wpsr-tiktok-feed-header-info .wpsr-tiktok-feed-user-info .wpsr-tiktok-feed-user-info-description p',
                    "property" 			=> 'font-weight',
                ),
                array(
                    "name" 				=> __('Line Height', "custom-feed-for-tiktok"),
                    "selector" 			=> '.wpsr-tiktok-feed-header .wpsr-tiktok-feed-user-info-wrapper .wpsr-tiktok-feed-user-info-head .wpsr-tiktok-feed-header-info .wpsr-tiktok-feed-user-info .wpsr-tiktok-feed-user-info-description p',
                    "property" 			=> 'line-height',
                )
            )
        );

        /*****************************
         * Header statistics
         *****************************/
        $tiktok_header_stat = $tiktok_header_section->addControlSection( "wpsr_tiktok_header_stat_section", __("Statistics", "custom-feed-for-tiktok"), "assets/icon.png", $this );
        $tiktok_header_stat->addStyleControls(
            array(
                array(
                    "name" 				=> __('Text Color', "custom-feed-for-tiktok"),
                    "selector" 			=> '.wpsr-tiktok-feed-header .wpsr-tiktok-feed-user-info-wrapper .wpsr-tiktok-feed-user-info-head .wpsr-tiktok-feed-header-info .wpsr-tiktok-feed-user-info .wpsr-tiktok-feed-user-statistics span',
                    "property" 			=> 'color',
                ),
                array(
                    "name" 				=> __('Font Size', "custom-feed-for-tiktok"),
                    "selector" 			=> '.wpsr-tiktok-feed-header .wpsr-tiktok-feed-user-info-wrapper .wpsr-tiktok-feed-user-info-head .wpsr-tiktok-feed-header-info .wpsr-tiktok-feed-user-info .wpsr-tiktok-feed-user-statistics span',
                    "property" 			=> 'font-size',
                    'control_type' 		=> 'slider-measurebox'
                ),
                array(
                    "name" 				=> __('Font Weight', "custom-feed-for-tiktok"),
                    "selector" 			=> '.wpsr-tiktok-feed-header .wpsr-tiktok-feed-user-info-wrapper .wpsr-tiktok-feed-user-info-head .wpsr-tiktok-feed-header-info .wpsr-tiktok-feed-user-info .wpsr-tiktok-feed-user-statistics span',
                    "property" 			=> 'font-weight',
                ),
                array(
                    "name" 				=> __('Line Height', "custom-feed-for-tiktok"),
                    "selector" 			=> '.wpsr-tiktok-feed-header .wpsr-tiktok-feed-user-info-wrapper .wpsr-tiktok-feed-user-info-head .wpsr-tiktok-feed-header-info .wpsr-tiktok-feed-user-info .wpsr-tiktok-feed-user-statistics span',
                    "property" 			=> 'line-height',
                )
            )
        );

        /*****************************
         * Header Box
         *****************************/
        $tiktok_header_box = $tiktok_header_section->addControlSection( "wpsr_tiktok_header_box_section", __("Box", "custom-feed-for-tiktok"), "assets/icon.png", $this );
        $tiktok_header_box->addStyleControls(
            array(
                array(
                    "name" 				=> __('Background Color','custom-feed-for-tiktok'),
                    "selector" 			=> '.wpsr-tiktok-feed-header .wpsr-tiktok-feed-user-info-wrapper',
                    "property" 			=> 'background-color'
                )
            )
        );

        $tiktok_header_box->addPreset(
            "padding",
            "wpsr_tiktok_header_box_padding",
            __("Padding", 'custom-feed-for-tiktok'),
            '.wpsr-tiktok-feed-header .wpsr-tiktok-feed-user-info-wrapper'
        )->whiteList();

        $tiktok_header_box->addPreset(
            "margin",
            "wpsr_tiktok_header_box_margin",
            __("Margin", 'custom-feed-for-tiktok'),
            '.wpsr-tiktok-feed-header .wpsr-tiktok-feed-user-info-wrapper'
        )->whiteList();

        $tiktok_header_box->addPreset(
            "border",
            "wpsr_tiktok_header_box_border",
            __("Border", 'custom-feed-for-tiktok'),
            '.wpsr-tiktok-feed-header .wpsr-tiktok-feed-user-info-wrapper'
        )->whiteList();

        $tiktok_header_box->addPreset(
            "border-radius",
            "wpsr_tiktok_header_box_border_radius",
            __("Border Radius", 'custom-feed-for-tiktok'),
            '.wpsr-tiktok-feed-header .wpsr-tiktok-feed-user-info-wrapper'
        )->whiteList();


        /*****************************
         * Content
         *****************************/
        $tiktok_content_section = $this->addControlSection( "wpsr_tiktok_content_section", __("Content", "custom-feed-for-tiktok"), "assets/icon.png", $this );
        $tiktok_content_section->typographySection( __('Post Text', 'custom-feed-for-tiktok'), '.wpsr-tiktok-feed-wrapper .wpsr-tiktok-feed-item .wpsr-feed-description-link .wpsr-feed-description-text', $this );
        $tiktok_content_section->addPreset(
            "padding",
            "wpsr_tiktok_content_padding",
            __("Padding", "custom-feed-for-tiktok"),
            '.wpsr-tiktok-feed-wrapper .wpsr-tiktok-feed-item .wpsr-feed-description-link .wpsr-feed-description-text'
        )->whiteList();

        $tiktok_author_section = $tiktok_content_section->addControlSection( "wpsr_tiktok_author_section", __("Author", "custom-feed-for-tiktok"), "assets/icon.png", $this );
        $tiktok_author_section->addStyleControls(
            array(
                array(
                    "name" 				=> __('Color', "custom-feed-for-tiktok"),
                    "selector" 			=> '.wpsr-tiktok-feed-wrapper .wpsr-tiktok-feed-item .wpsr-tiktok-feed-author-name',
                    "property" 			=> 'color',
                ),
                array(
                    "name" 				=> __('Font Size', "custom-feed-for-tiktok"),
                    "selector" 			=> '.wpsr-tiktok-feed-wrapper .wpsr-tiktok-feed-item .wpsr-tiktok-feed-author-name',
                    "property" 			=> 'font-size',
                    'control_type' 		=> 'slider-measurebox'
                ),
                array(
                    "name" 				=> __('Font Weight', "custom-feed-for-tiktok"),
                    "selector" 			=> '.wpsr-tiktok-feed-wrapper .wpsr-tiktok-feed-item .wpsr-tiktok-feed-author-name',
                    "property" 			=> 'font-weight',
                ),
                array(
                    "name" 				=> __('Line Height', "custom-feed-for-tiktok"),
                    "selector" 			=> '.wpsr-tiktok-feed-wrapper .wpsr-tiktok-feed-item .wpsr-tiktok-feed-author-name',
                    "property" 			=> 'line-height',
                ),
                array(
                    "name" 				=> __('Bottom Spacing', "custom-feed-for-tiktok"),
                    "selector" 			=> '.wpsr-tiktok-feed-wrapper .wpsr-tiktok-feed-item .wpsr-tiktok-feed-author-name',
                    "property" 			=> 'margin-bottom',
                    "control_type" 		=> 'slider-measurebox',
                    'unit' 				=> 'px'
                )
            )
        );

        $tiktok_date_section = $tiktok_content_section->addControlSection( "wpsr_tiktok_date_section", __("Date", "custom-feed-for-tiktok"), "assets/icon.png", $this );
        $tiktok_date_section->addStyleControls(
            array(
                array(
                    "name" 				=> __('Color', "custom-feed-for-tiktok"),
                    "selector" 			=> '.wpsr-tiktok-feed-wrapper .wpsr-tiktok-feed-item .wpsr-tiktok-feed-time',
                    "property" 			=> 'color',
                ),
                array(
                    "name" 				=> __('Font Size', "custom-feed-for-tiktok"),
                    "selector" 			=> '.wpsr-tiktok-feed-wrapper .wpsr-tiktok-feed-item .wpsr-tiktok-feed-time',
                    "property" 			=> 'font-size',
                    'control_type' 		=> 'slider-measurebox'
                ),
                array(
                    "name" 				=> __('Font Weight', "custom-feed-for-tiktok"),
                    "selector" 			=> '.wpsr-tiktok-feed-wrapper .wpsr-tiktok-feed-item .wpsr-tiktok-feed-time',
                    "property" 			=> 'font-weight',
                ),
                array(
                    "name" 				=> __('Line Height', "custom-feed-for-tiktok"),
                    "selector" 			=> '.wpsr-tiktok-feed-wrapper .wpsr-tiktok-feed-item .wpsr-tiktok-feed-time',
                    "property" 			=> 'line-height',
                ),
                array(
                    "name" 				=> __('Bottom Spacing', "custom-feed-for-tiktok"),
                    "selector" 			=> '.wpsr-tiktok-feed-wrapper .wpsr-tiktok-feed-item .wpsr-tiktok-feed-time',
                    "property" 			=> 'margin-bottom',
                    "control_type" 		=> 'slider-measurebox',
                    'unit' 				=> 'px'
                )
            )
        );

        $tiktok_statistics_count_section = $tiktok_content_section->addControlSection( "wpsr_tiktok_statistics_count_section", __("Statistics Count", "custom-feed-for-tiktok"), "assets/icon.png", $this );
        $tiktok_statistics_count_section->addStyleControls(
            array(
                array(
                    "name" 				=> __('Color', "custom-feed-for-tiktok"),
                    "selector" 			=> '.wpsr-tiktok-feed-wrapper .wpsr-tiktok-feed-item .wpsr-tiktok-feed-statistics .wpsr-tiktok-feed-reaction-count',
                    "property" 			=> 'color',
                ),
                array(
                    "name" 				=> __('Font Size', "custom-feed-for-tiktok"),
                    "selector" 			=> '.wpsr-tiktok-feed-wrapper .wpsr-tiktok-feed-item .wpsr-tiktok-feed-statistics .wpsr-tiktok-feed-reaction-count',
                    "property" 			=> 'font-size',
                    'control_type' 		=> 'slider-measurebox'
                ),
                array(
                    "name" 				=> __('Font Weight', "custom-feed-for-tiktok"),
                    "selector" 			=> '.wpsr-tiktok-feed-wrapper .wpsr-tiktok-feed-item .wpsr-tiktok-feed-statistics .wpsr-tiktok-feed-reaction-count',
                    "property" 			=> 'font-weight',
                ),
                array(
                    "name" 				=> __('Line Height', "custom-feed-for-tiktok"),
                    "selector" 			=> '.wpsr-tiktok-feed-wrapper .wpsr-tiktok-feed-item .wpsr-tiktok-feed-statistics .wpsr-tiktok-feed-reaction-count',
                    "property" 			=> 'line-height',
                ),
                array(
                    "name" 				=> __('Bottom Spacing', "custom-feed-for-tiktok"),
                    "selector" 			=> '.wpsr-tiktok-feed-wrapper .wpsr-tiktok-feed-item .wpsr-tiktok-feed-statistics .wpsr-tiktok-feed-reaction-count',
                    "property" 			=> 'margin-bottom',
                    "control_type" 		=> 'slider-measurebox',
                    'unit' 				=> 'px'
                )
            )
        );


        /*****************************
         *follow btn
         *****************************/
        $tiktok_follow_section = $this->addControlSection( "wpsr_tiktok_follow_section", __("Follow Button", "custom-feed-for-tiktok"), "assets/icon.png", $this );
        $tiktok_follow_section_fz = $tiktok_follow_section->addStyleControl(
            array(
                "name" 				=> __('Font Size', "custom-feed-for-tiktok"),
                "selector" 			=> '.wpsr-tiktok-feed-follow-button-group .wpsr-tiktok-feed-btn a',
                "property" 			=> 'font-size',
                'control_type' 		=> 'slider-measurebox'
            )
        );
        $tiktok_follow_section_fz->setRange('5', '100', '1');
        $tiktok_follow_section_fz->setUnits('px', 'px,%,em');

        $tiktok_follow_section->addStyleControls(
            array(
                array(
                    "name" 				=> __('Color','custom-feed-for-tiktok'),
                    "selector" 			=> '.wpsr-tiktok-feed-follow-button-group .wpsr-tiktok-feed-btn a',
                    "property" 			=> 'color'
                )
            )
        );
        $tiktok_follow_section->addStyleControls(
            array(
                array(
                    "name" 				=> __('Background Color','custom-feed-for-tiktok'),
                    "selector" 			=> '.wpsr-tiktok-feed-follow-button-group .wpsr-tiktok-feed-btn a',
                    "property" 			=> 'background-color'
                )
            )
        );
        $tiktok_follow_section->addPreset(
            "padding",
            "wpsr_tiktok_header_padding",
            __("Padding", 'custom-feed-for-tiktok'),
            '.wpsr-tiktok-feed-follow-button-group .wpsr-tiktok-feed-btn a'
        )->whiteList();

        /*****************************
         * Pagination
         *****************************/
        $pagination_section = $this->addControlSection( "wpsr_tiktok_pagination_section", __("Pagination", "custom-feed-for-tiktok"), "assets/icon.png", $this );
        $pagination_section->typographySection( __('Typography', 'custom-feed-for-tiktok'), '.wpsr_more', $this );
        $pagination_section->addStyleControls(
            array(
                array(
                    "name" 				=> __('Background Color','custom-feed-for-tiktok'),
                    "selector" 			=>  '.wpsr_more',
                    "property" 			=> 'background-color'
                )
            )
        );

        $pagination_section->addPreset(
            "padding",
            "wpsr_tiktok_pagination_padding",
            __("Padding", 'custom-feed-for-tiktok'),
            '.wpsr_more'
        )->whiteList();

        $pagination_section->addPreset(
            "margin",
            "wpsr_tiktok_pagination_margin",
            __("Margin", 'custom-feed-for-tiktok'),
            '.wpsr_more'
        )->whiteList();

        $pagination_section_border = $pagination_section->addControlSection( "wpsr_tiktok_pagination_border_section", __("Border", "custom-feed-for-tiktok"), "assets/icon.png", $this );
        $pagination_section_border->addPreset(
            "border",
            "wpsr_tiktok_pagination_border",
            __("Border", 'custom-feed-for-tiktok'),
            '.wpsr_more'
        )->whiteList();

        $pagination_section_border->addPreset(
            "border-radius",
            "wpsr_tiktok_pagination_radius",
            __("Border Radius", 'custom-feed-for-tiktok'),
            '.wpsr_more'
        )->whiteList();

        /*****************************
         * Box
         *****************************/
        $tiktok_box_section = $this->addControlSection( "wpsr_tiktok_box_section", __("Item Box", "custom-feed-for-tiktok"), "assets/icon.png", $this );
        $selector = '.wpsr-tiktok-feed-item .wpsr-tiktok-feed-inner';
        $tiktok_box_section->addStyleControls(
            array(
                array(
                    "name" 				=> __('Background Color','custom-feed-for-tiktok'),
                    "selector" 			=> $selector,
                    "property" 			=> 'background-color'
                )
            )
        );
        $tiktok_box_sp = $tiktok_box_section->addControlSection( "wpsr_tiktok_box_sp_section", __("Spacing", "custom-feed-for-tiktok"), "assets/icon.png", $this );
        $tiktok_box_sp->addPreset(
            "padding",
            "tiktok_box_padding",
            __("Padding", 'custom-feed-for-tiktok'),
            $selector
        )->whiteList();

        $tiktok_box_sp->addPreset(
            "margin",
            "tiktok_box_margin",
            __("Margin", 'custom-feed-for-tiktok'),
            $selector
        )->whiteList();

        $tiktok_box_border = $tiktok_box_section->addControlSection( "wpsr_tiktok_box_border_section", __("Border", "custom-feed-for-tiktok"), "assets/icon.png", $this );
        $tiktok_box_border->addPreset(
            "border",
            "tiktok_box_border",
            __("Border", 'custom-feed-for-tiktok'),
            $selector
        )->whiteList();

        $tiktok_box_border->addPreset(
            "border-radius",
            "tiktok_box_radius",
            __("Border Radius", 'custom-feed-for-tiktok'),
            $selector
        )->whiteList();
    }

    function render( $options, $defaults, $content ) {
        if( $options['wpsr_tiktok'] == "no" ) {
            echo '<h5 class="wpsr-template-missing">' . esc_html__('Select a template', 'custom-feed-for-tiktok') . '</h5>';
            return;
        }

        if(isset($options['selector'])){
            $this->save_meta($options['selector']);
        }

        if ( function_exists('do_oxygen_elements') ) {
            echo wp_kses_post(do_oxygen_elements('[wp_social_ninja id="' . esc_html($options['wpsr_tiktok']) . '" platform="tiktok"]'));
        } else {
            echo wp_kses_post(do_shortcode('[wp_ social_ninja id="' . esc_html($options['wpsr_tiktok']) . '" platform="tiktok"]'));
        }
    }

    function init() {
        $this->El->useAJAXControls();
        $post_id 	= isset($_REQUEST['post_id']) ? intval( $_REQUEST['post_id'] ) : null;

        if (!isset($_REQUEST['nonce']) || !wp_verify_nonce($_REQUEST['nonce'], 'oxygen-nonce-' . $post_id) ) {
            return;
        }

        $ct_builder = isset($_GET['ct_builder']) && sanitize_text_field(wp_unslash($_GET['ct_builder']));

        if ($ct_builder) {
            wp_enqueue_style(
                'wp_social_ninja_tt',
                WPSOCIALREVIEWS_URL . 'assets/css/wp_social_ninja_tt.css',
                array(),
                WPSOCIALREVIEWS_VERSION
            );
            wp_enqueue_script('wp-social-review');
            add_action('wp_footer', array(new ShortcodeHandler(), 'loadLocalizeScripts'), 99);
            if (defined('WPSOCIALREVIEWS_PRO')) {
                wp_enqueue_style(
                    'swiper',
                    WPSOCIALREVIEWS_PRO_URL
                    . 'assets/libs/swiper/swiper-bundle.min.css',
                    array(),
                    WPSOCIALREVIEWS_VERSION
                );
            }
        }
    }

    function enablePresets() {
        return true;
    }

    function enableFullPresets() {
        return true;
    }

    function customCSS( $options, $selector ) {

    }
}
new TikTokWidget();