<?php
/**
 * Beaver Builder TikTok Module
 *
 * This is a Beaver Builder integration module for the Custom Feed for TikTok plugin.
 * The class name follows Beaver Builder's naming conventions (CFFT_Fl_TikTok_Module)
 * rather than WordPress plugin conventions for proper Beaver Builder integration.
 *
 * @copyright 2024 Fastline Media LLC
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 * @link      https://www.wpbeaverbuilder.com/terms-and-conditions/
 */

use WPSocialReviews\App\Services\Widgets\Helper;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}


/**
 * Beaver Builder TikTok Feed Module
 *
 * Integrates TikTok feeds with Beaver Builder page builder.
 * Class name follows Beaver Builder conventions for proper integration.
 *
 * @class CFFT_Fl_TikTok_Module
 */
class CFFT_Fl_TikTok_Module extends FLBuilderModule { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound

    /**
     * Constructor function for the module. You must pass the
     * name, description, dir and url in an array to the parent class.
     *
     * @method __construct
     */
    public function __construct()
    {
        parent::__construct(array(
            'name'          => __('TikTok Feeds', 'custom-feed-for-tiktok'),
            'description'   => '',
            'category'		=> __('WP Social Ninja', 'custom-feed-for-tiktok'),
            'dir'           => CUSTOM_FEED_FOR_TIKTOK_DIR . 'app/Services/Widgets/Beaver/TikTok/',
            'url'           => CUSTOM_FEED_FOR_TIKTOK_URL . 'app/Services/Widgets/Beaver/TikTok/',
            'editor_export' => true, // Defaults to true and can be omitted.
            'enabled'       => true, // Defaults to true and can be omitted.
            'partial_refresh' => true, // Set this to true to enable partial refresh.
        ));

        $this->add_css(
            'wp_social_ninja_tt',
            WPSOCIALREVIEWS_URL . 'assets/css/wp_social_ninja_tt.css',
            array(),
            WPSOCIALREVIEWS_VERSION
        );
        if(defined('WPSOCIALREVIEWS_PRO')){
            $this->add_css(
                'swiper',
                WPSOCIALREVIEWS_PRO_URL . 'assets/libs/swiper/swiper-bundle.min.css',
                array(),
                WPSOCIALREVIEWS_VERSION
            );
        }
    }
}

/**
 * Register the module and its form settings.
 */
FLBuilder::register_module('CFFT_Fl_TikTok_Module', array(
    'general'       => array( // Tab
        'title'         => __('General', 'custom-feed-for-tiktok'), // Tab title
        'sections'      => array( // Tab Sections
            'general'       => array( // Section
                'title'     => '', // Section Title
                'fields' => array( // Section Fields
                    'template_id'  => array(
                        'type'         => 'select',
                        'label'        => __( 'Select a Template', 'custom-feed-for-tiktok' ),
                        'options'      => Helper::getTemplates(['tiktok'])
                    ),
                )
            )
        )
    ),
    'style'   => array(
        'title'    => __( 'Style', 'custom-feed-for-tiktok' ),
        'sections' => array(
            'header_style' => array(
                'title'  => __( 'Header', 'custom-feed-for-tiktok' ),
                'fields' => array(
                    'tt_header_bg_color' => array(
                        'type'        => 'color',
                        'label'       => __( 'Header Background Color', 'custom-feed-for-tiktok' ),
                        'show_reset' => true,
                        'preview'     => array(
                            'type'      => 'css',
                            'selector'  => '.wpsr-tiktok-feed-header .wpsr-tiktok-feed-user-info-wrapper',
                            'property'  => 'background',
                        ),
                    ),
                    'tt_header_account_name_color' => array(
                        'type'        => 'color',
                        'label'       => __( 'Account Name Color', 'custom-feed-for-tiktok' ),
                        'show_reset' => true,
                        'preview'     => array(
                            'type'      => 'css',
                            'selector'  => '.wpsr-tiktok-feed-header .wpsr-tiktok-feed-user-info-wrapper .wpsr-tiktok-feed-user-info-head .wpsr-tiktok-feed-header-info .wpsr-tiktok-feed-user-info .wpsr-tiktok-feed-user-info-name-wrapper a',
                            'property'  => 'color',
                        ),
                    ),
                    'tt_header_description_color' => array(
                        'type'        => 'color',
                        'label'       => __( 'Description Color', 'custom-feed-for-tiktok' ),
                        'show_reset' => true,
                        'preview'     => array(
                            'type'      => 'css',
                            'selector'  => '.wpsr-tiktok-feed-header .wpsr-tiktok-feed-user-info-wrapper .wpsr-tiktok-feed-user-info-head .wpsr-tiktok-feed-header-info .wpsr-tiktok-feed-user-info .wpsr-tiktok-feed-user-info-description p',
                            'property'  => 'color',
                        ),
                    ),
                    'tt_header_statistics_count_color' => array(
                        'type'        => 'color',
                        'label'       => __( 'Statistics Color', 'custom-feed-for-tiktok' ),
                        'show_reset' => true,
                        'preview'     => array(
                            'type'      => 'css',
                            'selector'  => '.wpsr-tiktok-feed-header .wpsr-tiktok-feed-user-info-wrapper .wpsr-tiktok-feed-user-info-head .wpsr-tiktok-feed-header-info .wpsr-tiktok-feed-user-info .wpsr-tiktok-feed-user-statistics span',
                            'property'  => 'color',
                        ),
                    ),
                    'tt_header_account_name_typography'		=> array(
                        'type'					=> 'typography',
                        'label'					=> __('Account Name Typography', 'custom-feed-for-tiktok'),
                        'responsive'  			=> true,
                        'preview'				=> array(
                            'type'					=> 'css',
                            'selector'				=> '.wpsr-tiktok-feed-header .wpsr-tiktok-feed-user-info-wrapper .wpsr-tiktok-feed-user-info-head .wpsr-tiktok-feed-header-info .wpsr-tiktok-feed-user-info .wpsr-tiktok-feed-user-info-name-wrapper a'
                        )
                    ),
                    'tt_header_account_description_typography'		=> array(
                        'type'					=> 'typography',
                        'label'					=> __('Description Typography', 'custom-feed-for-tiktok'),
                        'responsive'  			=> true,
                        'preview'				=> array(
                            'type'					=> 'css',
                            'selector'				=> '.wpsr-tiktok-feed-header .wpsr-tiktok-feed-user-info-wrapper .wpsr-tiktok-feed-user-info-head .wpsr-tiktok-feed-header-info .wpsr-tiktok-feed-user-info .wpsr-tiktok-feed-user-info-description p'
                        )
                    ),
                    'tt_header_account_statistics_counter_typography'		=> array(
                        'type'					=> 'typography',
                        'label'					=> __('Likes Counter Typography', 'custom-feed-for-tiktok'),
                        'responsive'  			=> true,
                        'preview'				=> array(
                            'type'					=> 'css',
                            'selector'				=> '.wpsr-tiktok-feed-header .wpsr-tiktok-feed-user-info-wrapper .wpsr-tiktok-feed-user-info-head .wpsr-tiktok-feed-header-info .wpsr-tiktok-feed-user-info .wpsr-tiktok-feed-user-statistics span'
                        )
                    ),
                ),
            ),
            'content_author_style' => array(
                'title'  => __( 'Post Author', 'custom-feed-for-tiktok' ),
                'fields' => array(
                    'tt_content_author_color' => array(
                        'type'        => 'color',
                        'label'       => __( 'Text Color', 'custom-feed-for-tiktok' ),
                        'show_reset' => true,
                        'preview'     => array(
                            'type'      => 'css',
                            'selector'  => '.wpsr-tiktok-feed-wrapper .wpsr-tiktok-feed-item .wpsr-tiktok-feed-author-name',
                            'property'  => 'color',
                        ),
                    ),
                    'tt_content_author_typography'		=> array(
                        'type'					=> 'typography',
                        'label'					=> __('Typography', 'custom-feed-for-tiktok'),
                        'responsive'  			=> true,
                        'preview'				=> array(
                            'type'					=> 'css',
                            'selector'				=> '.wpsr-tiktok-feed-wrapper .wpsr-tiktok-feed-item .wpsr-tiktok-feed-author-name'
                        )
                    ),
                ),
            ),
            'content_date_style' => array(
                'title'  => __( 'Post Date', 'custom-feed-for-tiktok' ),
                'fields' => array(
                    'tt_content_date_color' => array(
                        'type'        => 'color',
                        'label'       => __( 'Text Color', 'custom-feed-for-tiktok' ),
                        'show_reset' => true,
                        'preview'     => array(
                            'type'      => 'css',
                            'selector'  => '.wpsr-tiktok-feed-wrapper .wpsr-tiktok-feed-item .wpsr-tiktok-feed-time',
                            'property'  => 'color',
                        ),
                    ),
                    'tt_content_date_typography'		=> array(
                        'type'					=> 'typography',
                        'label'					=> __('Typography', 'custom-feed-for-tiktok'),
                        'responsive'  			=> true,
                        'preview'				=> array(
                            'type'					=> 'css',
                            'selector'				=> '.wpsr-tiktok-feed-wrapper .wpsr-tiktok-feed-item .wpsr-tiktok-feed-time'
                        )
                    ),
                ),
            ),
            'post_content_style' => array(
                'title'  => __( 'Post Text', 'custom-feed-for-tiktok' ),
                'fields' => array(
                    'tt_post_content_color' => array(
                        'type'        => 'color',
                        'label'       => __( 'Color', 'custom-feed-for-tiktok' ),
                        'show_reset' => true,
                        'preview'     => array(
                            'type'      => 'css',
                            'selector'  => '.wpsr-tiktok-feed-wrapper .wpsr-tiktok-feed-item .wpsr-feed-description-link .wpsr-feed-description-text',
                            'property'  => 'color',
                        ),
                    ),
                    'tt_post_content_rm_link_color' => array(
                        'type'        => 'color',
                        'label'       => __( 'Read More Link Color', 'custom-feed-for-tiktok' ),
                        'show_reset' => true,
                        'preview'     => array(
                            'type'      => 'css',
                            'selector'  => '.wpsr_add_read_more .wpsr_read_more, .wpsr_add_read_more .wpsr_read_less',
                            'property'  => 'color',
                        ),
                    ),
                    'tt_post_content_typography'		=> array(
                        'type'					=> 'typography',
                        'label'					=> __('Typography', 'custom-feed-for-tiktok'),
                        'responsive'  			=> true,
                        'preview'				=> array(
                            'type'					=> 'css',
                            'selector'				=> '.wpsr-tiktok-feed-wrapper .wpsr-tiktok-feed-item .wpsr-feed-description-link .wpsr-feed-description-text'
                        )
                    ),
                ),
            ),
            'post_statistics_count' => array(
                'title'  => __( 'Statistics Count', 'custom-feed-for-tiktok' ),
                'fields' => array(
                    'tt_post_statistics_count_color' => array(
                        'type'        => 'color',
                        'label'       => __( 'Color', 'custom-feed-for-tiktok' ),
                        'show_reset' => true,
                        'preview'     => array(
                            'type'      => 'css',
                            'selector'  => '.wpsr-tiktok-feed-wrapper .wpsr-tiktok-feed-item .wpsr-tiktok-feed-statistics .wpsr-tiktok-feed-reaction-count',
                            'property'  => 'color',
                        ),
                    ),
                    'tt_post_statistics_count_typography'		=> array(
                        'type'					=> 'typography',
                        'label'					=> __('Typography', 'custom-feed-for-tiktok'),
                        'responsive'  			=> true,
                        'preview'				=> array(
                            'type'					=> 'css',
                            'selector'				=> '.wpsr-tiktok-feed-wrapper .wpsr-tiktok-feed-item .wpsr-tiktok-feed-statistics .wpsr-tiktok-feed-reaction-count'
                        )
                    ),
                ),
            ),
            'follow_btn_style' => array(
                'title'  => __( 'Follow Button', 'custom-feed-for-tiktok' ),
                'fields' => array(
                    'tt_feed_follow_button_text_color' => array(
                        'type'        => 'color',
                        'label'       => __( 'Text Color', 'custom-feed-for-tiktok' ),
                        'show_reset' => true,
                        'preview'     => array(
                            'type'      => 'css',
                            'selector'  => '.wpsr-tiktok-feed-follow-button-group .wpsr-tiktok-feed-btn a',
                            'property'  => 'color',
                        ),
                    ),
                    'tt_feed_follow_button_background_color' => array(
                        'type'        => 'color',
                        'label'       => __( 'Background Color', 'custom-feed-for-tiktok' ),
                        'show_reset' => true,
                        'preview'     => array(
                            'type'      => 'css',
                            'selector'  => '.wpsr-tiktok-feed-follow-button-group .wpsr-tiktok-feed-btn a',
                            'property'  => 'background',
                        ),
                    ),
                    'tt_feed_follow_button_typography'		=> array(
                        'type'					=> 'typography',
                        'label'					=> __('Typography', 'custom-feed-for-tiktok'),
                        'responsive'  			=> true,
                        'preview'				=> array(
                            'type'					=> 'css',
                            'selector'				=> '.wpsr-tiktok-feed-follow-button-group .wpsr-tiktok-feed-btn a'
                        )
                    ),
                ),
            ),
            'load_more_style' => array(
                'title'  => __( 'Load More Button', 'custom-feed-for-tiktok' ),
                'fields' => array(
                    'tt_load_more_color' => array(
                        'type'        => 'color',
                        'label'       => __( 'Color', 'custom-feed-for-tiktok' ),
                        'show_reset' => true,
                        'preview'     => array(
                            'type'      => 'css',
                            'selector'  => '.wpsr_more',
                            'property'  => 'color',
                        ),
                    ),
                    'tt_load_more_hover_color' => array(
                        'type'        => 'color',
                        'label'       => __( 'Hover Color', 'custom-feed-for-tiktok' ),
                        'show_reset' => true,
                        'preview'     => array(
                            'type'      => 'css',
                            'selector'  => '.wpsr_more',
                            'property'  => 'color',
                        ),
                    ),
                    'tt_load_more_bg_color' => array(
                        'type'        => 'color',
                        'label'       => __( 'Background Color', 'custom-feed-for-tiktok' ),
                        'show_reset' => true,
                        'preview'     => array(
                            'type'      => 'css',
                            'selector'  => '.wpsr_more',
                            'property'  => 'background',
                        ),
                    ),
                    'tt_load_more_bg_hover_color' => array(
                        'type'        => 'color',
                        'label'       => __( 'Background Hover Color', 'custom-feed-for-tiktok' ),
                        'show_reset' => true,
                        'preview'     => array(
                            'type'      => 'css',
                            'selector'  => '.wpsr_more',
                            'property'  => 'background',
                        ),
                    ),
                    'tt_load_more_typography'		=> array(
                        'type'					=> 'typography',
                        'label'					=> __('Typography', 'custom-feed-for-tiktok'),
                        'responsive'  			=> true,
                        'preview'				=> array(
                            'type'					=> 'css',
                            'selector'				=> '.wpsr_more'
                        )
                    ),
                ),
            ),
            'box_style' => array(
                'title'  => __( 'Box', 'custom-feed-for-tiktok' ),
                'fields' => array(
                    'tt_box_bg_color' => array(
                        'type'        => 'color',
                        'label'       => __( 'Box Background Color', 'custom-feed-for-tiktok' ),
                        'show_reset' => true,
                        'preview'     => array(
                            'type'      => 'css',
                            'selector'  => '.wpsr-tiktok-feed-wrapper .wpsr-tiktok-feed-item .wpsr-tiktok-feed-inner',
                            'property'  => 'background',
                        ),
                    ),
                ),
            ),
        ),
    ),
));