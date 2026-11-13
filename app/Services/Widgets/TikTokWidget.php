<?php
/**
 * Class CustomFeedForTiktok\Application\Services\Widgets\TikTokWidget
 *
 * @copyright Elementor
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 * @link      https://elementor.com/terms/
 */

namespace CustomFeedForTiktok\Application\Services\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use WPSocialReviews\Framework\Support\Arr;
use WPSocialReviews\App\Services\Widgets\Helper;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class TikTokWidget extends Widget_Base
{
    public function get_name() {
        return 'wp-social-ninja-tiktok-widget';
    }

    public function get_title() {
        return __( 'Social Ninja TikTok Feed', 'custom-feed-for-tiktok' );
    }

    public function get_icon() {
        return 'eicon-video-playlist';
    }

    public function get_keywords() {
        return [
            'wpsocialninja',
            'wp social ninja',
            'social ninja',
            'tiktok',
            'feed',
            'tiktok feed'
        ];
    }

    public function get_categories() {
        return array('wp-social-reviews');
    }

    public function get_style_depends() {
        return ['wp_social_ninja_tt'];
    }

    public function get_script_depends() {
        return [];
    }

    protected function register_controls()
    {
        $this->register_general_controls();
        $this->register_header_style_controls();
        $this->register_content_style_controls();
        $this->register_button_style_controls();
        $this->register_pagination_style_controls();
        $this->register_box_style_controls();
    }

    protected function register_general_controls(){
        $platforms = ['tiktok'];

        $this->start_controls_section(
            'section_social_ninja_tiktok_templates',
            [
                'label' => __('Social Ninja TikTok Feed', 'custom-feed-for-tiktok'),
            ]
        );

        $this->add_control(
            'tiktok_feed_template_list',
            [
                'label' => __('Select a Template', 'custom-feed-for-tiktok'),
                'type' => Controls_Manager::SELECT,
                'label_block' => true,
                'options' => Helper::getTemplates($platforms),
                'default' => '0',
            ]
        );

        $this->end_controls_section();
    }

    protected function register_pagination_style_controls(){

        $this->start_controls_section(
            'section_tt_feed_pagination_style',
            [
                'label' => __('Pagination', 'custom-feed-for-tiktok'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'tt_feed_pagination_text_color',
            [
                'label' => __('Text Color', 'custom-feed-for-tiktok'),
                'type' => Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .wpsr_more' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'tt_pagination_background',
                'label' => __( 'Background', 'custom-feed-for-tiktok' ),
                'types' => [ 'classic', 'gradient' ],
                'selector' => '{{WRAPPER}} .wpsr_more',
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'tt_pagination_typography',
                'label' => __('Typography', 'custom-feed-for-tiktok'),
                'selector' => '{{WRAPPER}} .wpsr_more',
            ]
        );

        $this->add_responsive_control(
            'tt_pagination_margin',
            [
                'label' => esc_html__('Margin', 'custom-feed-for-tiktok'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .wpsr_more' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'tt_pagination_padding',
            [
                'label' => esc_html__('Padding', 'custom-feed-for-tiktok'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .wpsr_more' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );


        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'tt_pagination_border',
                'selector' => '{{WRAPPER}} .wpsr_more',
            ]
        );

        $this->add_control(
            'tt_pagination_border_radius',
            [
                'label' => esc_html__('Border Radius', 'custom-feed-for-tiktok'),
                'type' => Controls_Manager::DIMENSIONS,
                'separator' => 'before',
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .wpsr_more' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'tt_pagination_box_shadow',
                'selector' => '{{WRAPPER}} .wpsr_more',
            ]
        );

        $this->end_controls_section();
    }

    protected function register_button_style_controls()
    {
        $this->start_controls_section(
            'section_tt_button_style',
            [
                'label' => __('Follow Button', 'custom-feed-for-tiktok'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'tt_button_text_color',
            [
                'label' => __('Text Color', 'custom-feed-for-tiktok'),
                'type' => Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .wpsr-tiktok-feed-follow-button-group .wpsr-tiktok-feed-btn a ' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'tt_button_background_color',
            [
                'label' => __('Background Color', 'custom-feed-for-tiktok'),
                'type' => Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .wpsr-tiktok-feed-follow-button-group .wpsr-tiktok-feed-btn a ' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'tt_button_typography',
                'label' => __('Typography', 'custom-feed-for-tiktok'),
                'selector' => '{{WRAPPER}} .wpsr-tiktok-feed-follow-button-group .wpsr-tiktok-feed-btn a',
            ]
        );

        $this->add_responsive_control(
            'tt_button_padding',
            [
                'label' => esc_html__('Padding', 'custom-feed-for-tiktok'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .wpsr-tiktok-feed-follow-button-group .wpsr-tiktok-feed-btn a' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function register_content_style_controls()
    {
        $this->start_controls_section(
            'section_tt_content_style',
            [
                'label' => __('Content', 'custom-feed-for-tiktok'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'tt_header_author',
            [
                'label' => __('Author', 'custom-feed-for-tiktok'),
                'type' => Controls_Manager::HEADING,
            ]
        );

        $this->add_control(
            'tt_author_text_color',
            [
                'label' => __('Name Color', 'custom-feed-for-tiktok'),
                'type' => Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .wpsr-tiktok-feed-wrapper .wpsr-tiktok-feed-item .wpsr-tiktok-feed-author-name' => 'color: {{VALUE}}'
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'tt_author_typography',
                'label' => __('Typography', 'custom-feed-for-tiktok'),
                'selector' => '{{WRAPPER}} .wpsr-tiktok-feed-wrapper .wpsr-tiktok-feed-item .wpsr-tiktok-feed-author-name'
            ]
        );

        $this->add_control(
            'tt_header_post_date',
            [
                'label' => __('Post Date', 'custom-feed-for-tiktok'),
                'type' => Controls_Manager::HEADING,
            ]
        );

        $this->add_control(
            'tt_post_date_color',
            [
                'label' => __('Date Color', 'custom-feed-for-tiktok'),
                'type' => Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .wpsr-tiktok-feed-wrapper .wpsr-tiktok-feed-item .wpsr-tiktok-feed-time' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'tt_post_date_typography',
                'label' => __('Typography', 'custom-feed-for-tiktok'),
                'selector' => '{{WRAPPER}} .wpsr-tiktok-feed-wrapper .wpsr-tiktok-feed-item .wpsr-tiktok-feed-time',
            ]
        );

        $this->add_control(
            'tt_header_post_text',
            [
                'label' => __('Post Text', 'custom-feed-for-tiktok'),
                'type' => Controls_Manager::HEADING,
            ]
        );

        $this->add_control(
            'tt_post_text_color',
            [
                'label' => __('Text Color', 'custom-feed-for-tiktok'),
                'type' => Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .wpsr-tiktok-feed-wrapper .wpsr-tiktok-feed-item .wpsr-feed-description-link .wpsr-feed-description-text' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'tt_post_title_typography',
                'label' => __('Typography', 'custom-feed-for-tiktok'),
                'selector' => '{{WRAPPER}} .wpsr-tiktok-feed-wrapper .wpsr-tiktok-feed-item .wpsr-feed-description-link .wpsr-feed-description-text',
            ]
        );

        $this->end_controls_section();
    }

    protected function register_header_style_controls() {
        $this->start_controls_section(
            'section_tt_header_style',
            [
                'label' => __('Header', 'custom-feed-for-tiktok'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'tt_header_username_title',
            [
                'label' => __('User Name', 'custom-feed-for-tiktok'),
                'type' => Controls_Manager::HEADING,
            ]
        );

        $this->add_control(
            'tt_header_username_color',
            [
                'label' => __('Text Color', 'custom-feed-for-tiktok'),
                'type' => Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .wpsr-tiktok-feed-header .wpsr-tiktok-feed-user-info-wrapper .wpsr-tiktok-feed-user-info-head .wpsr-tiktok-feed-header-info .wpsr-tiktok-feed-user-info .wpsr-tiktok-feed-user-info-name-wrapper a' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'tt_header_username_typography',
                'label' => __('Typography', 'custom-feed-for-tiktok'),
                'selector' => '{{WRAPPER}} .wpsr-tiktok-feed-header .wpsr-tiktok-feed-user-info-wrapper .wpsr-tiktok-feed-user-info-head .wpsr-tiktok-feed-header-info .wpsr-tiktok-feed-user-info .wpsr-tiktok-feed-user-info-name-wrapper a',
            ]
        );

        $this->add_responsive_control(
            'tt_header_username_spacing',
            [
                'label' => esc_html__('Bottom Spacing', 'custom-feed-for-tiktok'),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                        'step' => 1,
                    ],
                ],
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .wpsr-tiktok-feed-header .wpsr-tiktok-feed-user-info-wrapper .wpsr-tiktok-feed-user-info-head .wpsr-tiktok-feed-header-info .wpsr-tiktok-feed-user-info .wpsr-tiktok-feed-user-info-name-wrapper a' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'tt_header_description',
            [
                'label' => __('Description', 'custom-feed-for-tiktok'),
                'type' => Controls_Manager::HEADING,
            ]
        );

        $this->add_control(
            'tt_header_description_color',
            [
                'label' => __('Text Color', 'custom-feed-for-tiktok'),
                'type' => Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .wpsr-tiktok-feed-header .wpsr-tiktok-feed-user-info-wrapper .wpsr-tiktok-feed-user-info-head .wpsr-tiktok-feed-header-info .wpsr-tiktok-feed-user-info .wpsr-tiktok-feed-user-info-description p' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'tt_header_description_typography',
                'label' => __('Typography', 'custom-feed-for-tiktok'),
                'selector' => '{{WRAPPER}} .wpsr-tiktok-feed-header .wpsr-tiktok-feed-user-info-wrapper .wpsr-tiktok-feed-user-info-head .wpsr-tiktok-feed-header-info .wpsr-tiktok-feed-user-info .wpsr-tiktok-feed-user-info-description p',
            ]
        );

        $this->add_responsive_control(
            'tt_header_description_spacing',
            [
                'label' => esc_html__('Bottom Spacing', 'custom-feed-for-tiktok'),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                        'step' => 1,
                    ],
                ],
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .wpsr-tiktok-feed-header .wpsr-tiktok-feed-user-info-wrapper .wpsr-tiktok-feed-user-info-head .wpsr-tiktok-feed-header-info .wpsr-tiktok-feed-user-info .wpsr-tiktok-feed-user-info-description p' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'tt_header_statistics',
            [
                'label' => __('Statistics', 'custom-feed-for-tiktok'),
                'type' => Controls_Manager::HEADING,
            ]
        );

        $this->add_control(
            'tt_header_statistics_color',
            [
                'label' => __('Color', 'custom-feed-for-tiktok'),
                'type' => Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .wpsr-tiktok-feed-header .wpsr-tiktok-feed-user-info-wrapper .wpsr-tiktok-feed-user-info-head .wpsr-tiktok-feed-header-info .wpsr-tiktok-feed-user-info .wpsr-tiktok-feed-user-statistics span' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'tt_header_statistics_typography',
                'label' => __('Typography', 'custom-feed-for-tiktok'),
                'selector' => '{{WRAPPER}} .wpsr-tiktok-feed-header .wpsr-tiktok-feed-user-info-wrapper .wpsr-tiktok-feed-user-info-head .wpsr-tiktok-feed-header-info .wpsr-tiktok-feed-user-info .wpsr-tiktok-feed-user-statistics span',
            ]
        );

        $this->add_control(
            'tt_header_box_title',
            [
                'label' => __('Box', 'custom-feed-for-tiktok'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'tt_header_bg_color',
            [
                'label' => __('Background Color', 'custom-feed-for-tiktok'),
                'type' => Controls_Manager::COLOR,
                'default' => '',
                'separator' => 'before',
                'selectors' => [
                    '{{WRAPPER}} .wpsr-tiktok-feed-header .wpsr-tiktok-feed-user-info-wrapper' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_responsive_control(
            'tt_header_padding',
            [
                'label' => esc_html__('Padding', 'custom-feed-for-tiktok'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .wpsr-tiktok-feed-header .wpsr-tiktok-feed-user-info-wrapper' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'tt_header_box_border',
                'selector' => '{{WRAPPER}} .wpsr-tiktok-feed-header .wpsr-tiktok-feed-user-info-wrapper',
            ]
        );

        $this->add_control(
            'tt_header_border_radius',
            [
                'label' => esc_html__('Border Radius', 'custom-feed-for-tiktok'),
                'type' => Controls_Manager::DIMENSIONS,
                'separator' => 'before',
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .wpsr-tiktok-feed-header .wpsr-tiktok-feed-user-info-wrapper' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'tt_header_box_shadow',
                'selector' => '{{WRAPPER}} .wpsr-tiktok-feed-header .wpsr-tiktok-feed-user-info-wrapper',
            ]
        );

        $this->end_controls_section();
    }

    protected function register_box_style_controls(){

        $this->start_controls_section(
            'section_tt_box_style',
            [
                'label' => __('Item Box', 'custom-feed-for-tiktok'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'tt_box_background',
                'label' => __( 'Background Color', 'custom-feed-for-tiktok' ),
                'types' => [ 'classic', 'gradient' ],
                'selector' => '{{WRAPPER}} .wpsr-tiktok-feed-item .wpsr-tiktok-feed-inner',
            ]
        );

        $this->add_responsive_control(
            'tt_box_margin',
            [
                'label' => esc_html__('Margin', 'custom-feed-for-tiktok'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .wpsr-tiktok-feed-item' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'tt_box_padding',
            [
                'label' => esc_html__('Padding', 'custom-feed-for-tiktok'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .wpsr-tiktok-feed-item .wpsr-tiktok-feed-inner' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );


        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'tt_box_border',
                'selector' => '{{WRAPPER}} .wpsr-tiktok-feed-item .wpsr-tiktok-feed-inner',
            ]
        );

        $this->add_control(
            'tt_box_border_radius',
            [
                'label' => esc_html__('Border Radius', 'custom-feed-for-tiktok'),
                'type' => Controls_Manager::DIMENSIONS,
                'separator' => 'before',
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .wpsr-tiktok-feed-item .wpsr-tiktok-feed-inner' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'tt_box_box_shadow',
                'selector' => '{{WRAPPER}} .wpsr-tiktok-feed-item .wpsr-tiktok-feed-inner',
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Render the widget output on the frontend.
     *
     * Written in PHP and used to generate the final HTML.
     *
     * @since 3.13.0
     *
     * @access protected
     */
    protected function render() {
        $settings = $this->get_settings_for_display();
        $wpsr_template_id = absint(Arr::get($settings, 'tiktok_feed_template_list'));
        if(!empty($wpsr_template_id)){
            $postId  = get_the_ID();
            if ($postId) {
                Helper::saveTemplateMeta($postId, 'tt');
            }
            echo wp_kses_post(do_shortcode('[wp_social_ninja id="' . $wpsr_template_id . '" platform="tiktok"]'));
        }
    }

}