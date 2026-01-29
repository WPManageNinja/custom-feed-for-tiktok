<?php

namespace CustomFeedForTiktok\Application\Services\Platforms\Feeds\Tiktok;

use CustomFeedForTiktok\Application\Services\Platforms\Feeds\Tiktok\Helper as TiktokHelper;
use WPSocialReviews\Framework\Support\Arr;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class Config
{
    public function __construct()
    {

    }

    public static function formatTiktokConfig($settings, $response)
    {
        $accounts    = TiktokHelper::getConnectedSourceList();
        $selectedAccounts = Arr::get($settings, 'source_settings.selected_accounts', []);

        $firstKey = '';
        if(!empty($accounts) && empty($selectedAccounts)) {
            $accountsKeys = array_keys($accounts);
            $firstKey = ''.$accountsKeys[0];
            $selectedAccounts = Arr::get($settings, 'source_settings.account_ids', [0 => $firstKey]);
        }

        return array(
            'feed_settings' => array(
                'platform'                  => 'tiktok',
                'created_from_onboarding'   => Arr::get($settings,'created_from_onboarding', false),
                'template'                  => Arr::get($settings, 'template', 'template1'),
                'layout_type'               => Arr::get($settings, 'layout_type', 'grid'),
                'responsive_column_number'  => array(
                    'desktop'  => Arr::get($settings, 'responsive_column_number.desktop', '4'),
                    'tablet'   => Arr::get($settings, 'responsive_column_number.tablet','6'),
                    'mobile'   => Arr::get($settings, 'responsive_column_number.mobile', '12')
                ),
                'column_gaps'               => Arr::get($settings, 'column_gaps', 'default'),
                'enable_style'           => Arr::get($settings,'enable_style', 'false'),
                'source_settings'  => array(
                    'feed_type'         => Arr::get($settings, 'source_settings.feed_type', 'user_feed'),
                    'selected_accounts' => $selectedAccounts,
                    'single_video_feed_ids'    => sanitize_text_field(Arr::get($settings,'source_settings.single_video_feed_ids', '')),
                    'feed_count'        => (int) Arr::get($settings, 'source_settings.feed_count', 50),
                ),
                'filters'  => array(
                    'total_posts_number'  => array(
                        'desktop'  => (int) Arr::get($settings, 'filters.total_posts_number.desktop', 50),
                        'mobile'   => (int) Arr::get($settings, 'filters.total_posts_number.mobile', 50)
                    ),
                    'post_order'       => Arr::get($settings,'filters.post_order', 'ascending'),
                    'includes_inputs'  => sanitize_text_field(Arr::get($settings,'filters.includes_inputs', '')),
                    'excludes_inputs'  => sanitize_text_field(Arr::get($settings,'filters.excludes_inputs', '')),
                    'hide_posts_by_id' => sanitize_text_field(Arr::get($settings,'filters.hide_posts_by_id', '')),
                ),
                'post_settings' => array(
                    'resolution'              => Arr::get($settings,'post_settings.resolution', 'full'),
                    'display_mode'            => Arr::get($settings,'post_settings.display_mode', 'tiktok'),
                    'display_author_photo'    => Arr::get($settings,'post_settings.display_author_photo', 'true'),
                    'display_author_name'     => Arr::get($settings,'post_settings.display_author_photo', 'true'),
                    'display_date'            => Arr::get($settings,'post_settings.display_date', 'true'),
                    'display_description'     => Arr::get($settings,'post_settings.display_description', 'true'),
                    'display_views_count'     => Arr::get($settings,'post_settings.display_views_count', 'true'),
                    'display_likes_count'     => Arr::get($settings,'post_settings.display_likes_count', 'true'),
                    'display_comments_count'  => Arr::get($settings,'post_settings.display_comments_count', 'true'),
                    'display_play_icon'       => Arr::get($settings,'post_settings.display_play_icon', 'true'),
                    'display_platform_icon'   => Arr::get($settings,'post_settings.display_platform_icon', 'true'),
                    'content_length'          => (int) Arr::get($settings,'post_settings.content_length', null),
                ),
                'header_settings' => array(
                    'display_header'             => Arr::get($settings,'header_settings.display_header', 'true'),
                    'account_to_show'            => Arr::get($settings,'header_settings.account_to_show', $firstKey),
                    'display_profile_photo'      => Arr::get($settings,'header_settings.display_profile_photo', 'true'),
                    'display_page_name'          => Arr::get($settings,'header_settings.display_page_name', 'true'),
                    'display_description'        => Arr::get($settings,'header_settings.display_description', 'true'),
//                    'display_posts_counter'      => Arr::get($settings,'header_settings.display_posts_counter', 'true'),
                    'display_likes_counter'      => Arr::get($settings,'header_settings.display_likes_counter', 'true'),
                    'display_followers_counter'  => Arr::get($settings,'header_settings.display_followers_counter', 'true'),
                    'display_following_counter'  => Arr::get($settings,'header_settings.display_following_counter', 'true'),
                ),
                'carousel_settings' => array(
                    'autoplay'         => Arr::get($settings,'carousel_settings.autoplay', 'true'),
                    'autoplay_speed'   => (int) Arr::get($settings,'carousel_settings.autoplay_speed', 3000),
                    'responsive_slides_to_show'  => array(
                        'desktop'  => (int)Arr::get($settings, 'carousel_settings.responsive_slides_to_show.desktop', 3),
                        'tablet'   => (int)Arr::get($settings, 'carousel_settings.responsive_slides_to_show.tablet',2),
                        'mobile'   => (int)Arr::get($settings, 'carousel_settings.responsive_slides_to_show.mobile', 1)
                    ),
                    'responsive_slides_to_scroll' => array(
                        'desktop'  => (int)Arr::get($settings, 'carousel_settings.responsive_slides_to_scroll.desktop', 3),
                        'tablet'   => (int)Arr::get($settings, 'carousel_settings.responsive_slides_to_scroll.tablet',2),
                        'mobile'   => (int)Arr::get($settings, 'carousel_settings.responsive_slides_to_scroll.mobile', 1)
                    ),
                    'navigation'       => Arr::get($settings,'carousel_settings.navigation', 'dot')
                ),
                'popup_settings'     => array(
                    'display_sidebar'       => Arr::get($settings,'popup_settings.display_sidebar', 'true'),
                    'display_video'         => Arr::get($settings,'popup_settings.display_video', 'true'),
                    'display_profile_photo' => Arr::get($settings,'popup_settings.display_profile_photo', 'true'),
                    'display_username'      => Arr::get($settings,'popup_settings.display_username', 'true'),
                    'display_caption'       => Arr::get($settings,'popup_settings.display_caption', 'true'),
                    'display_date'          => Arr::get($settings,'popup_settings.display_date', 'true'),
                    'display_cta_btn'      => Arr::get($settings,'popup_settings.display_cta_btn', 'true'),
                    'autoplay'              => Arr::get($settings,'popup_settings.autoplay', 'true')
                ),
                'follow_button_settings' => array(
                    'display_follow_button'      => Arr::get($settings,'follow_button_settings.display_follow_button', 'true'),
                    'follow_button_text'         => sanitize_text_field(Arr::get($settings,'follow_button_settings.follow_button_text', __('Follow on TikTok', 'custom-feed-for-tiktok'))),
                    'follow_button_position'     => Arr::get($settings,'follow_button_settings.follow_button_position', 'header'),
                ),
                'pagination_settings' => array(
                    'pagination_type' => Arr::get($settings,'pagination_settings.pagination_type', 'none'),
                    'load_more_button_text' => sanitize_text_field(Arr::get($settings, 'pagination_settings.load_more_button_text', __('Load More', 'custom-feed-for-tiktok'))),
                    'paginate'        => (int) Arr::get($settings,'pagination_settings.paginate', 6),
                    'paginate_number'       => array(
                        'desktop' => (int) Arr::get($settings, 'pagination_settings.paginate_number.desktop', Arr::get($settings, 'pagination_settings.paginate', 6)),
                        'mobile'  => (int) Arr::get($settings, 'pagination_settings.paginate_number.mobile', 6)
                    ),
                ),
            ),
        );
    }

    public function getStyleElement()
    {
        $has_pro = defined('WPSOCIALREVIEWS_PRO') && WPSOCIALREVIEWS_PRO;

        return array(
            'header' => array(
                'title' => __('Header', 'custom-feed-for-tiktok'),
                'key'  => 'header',
                array(
                    'title'     => __('User Name', 'custom-feed-for-tiktok'),
                    'key'      => 'user_name',
                    'divider' => false,
                    'typography' => true,
                    'padding' => false,
                    'border' => false,
                    'slider' => array(
                        'title' => __('Bottom Spacing', 'custom-feed-for-tiktok'),
                    ),
                    'styles' => array(
                        array(
                            'title'      => __('Text Color:', 'custom-feed-for-tiktok'),
                            'fieldKey'  => 'text_color',
                            'type'      => 'color_picker',
                            'flex'      => true,
                            'disabled' => !$has_pro,
                        )
                    )
                ),
                array(
                    'title'     => __('Description', 'custom-feed-for-tiktok'),
                    'key'      => 'description',
                    'divider' => true,
                    'typography' => true,
                    'padding' => false,
                    'border' => false,
                    'slider' => array(
                        'title' => __('Bottom Spacing', 'custom-feed-for-tiktok'),
                    ),
                    'styles' => array(
                        array(
                            'title'      => __('Text Color:', 'custom-feed-for-tiktok'),
                            'fieldKey'  => 'text_color',
                            'type'      => 'color_picker',
                            'flex'      => true,
                            'disabled' => !$has_pro,
                        ),
                    )
                ),
                array(
                    'title'     => __('Statistics', 'custom-feed-for-tiktok'),
                    'key'      => 'statistics',
                    'divider' => true,
                    'typography' => true,
                    'padding' => false,
                    'border' => false,
                    'styles' => array(
                        array(
                            'title'      => __('Text Color:', 'custom-feed-for-tiktok'),
                            'fieldKey'  => 'text_color',
                            'type'      => 'color_picker',
                            'flex'      => true,
                            'disabled' => !$has_pro,
                        ),
                    )
                ),
                array(
                    'title'     => __('Header Box', 'custom-feed-for-tiktok'),
                    'key'      => 'header_box',
                    'divider' => false,
                    'typography' => false,
                    'padding' => true,
                    'border' => true,
                    'border_radius' => true,
                    'box_shadow' => true,
                    'styles' => array(
                        array(
                            'title'      => __('Background Color:', 'custom-feed-for-tiktok'),
                            'fieldKey'  => 'background_color',
                            'type'      => 'color_picker',
                            'flex'      => true,
                            'disabled' => !$has_pro,
                        )
                    )
                )
            ),
            'content' => array(
                'title' => __('Content', 'custom-feed-for-tiktok'),
                'key'  => 'content',
                array(
                    'title'     => __('Author', 'custom-feed-for-tiktok'),
                    'key'      => 'author',
                    'divider' => false,
                    'typography' => true,
                    'padding' => false,
                    'border' => false,
                    'styles' => array(
                        array(
                            'title'      => __('Text Color:', 'custom-feed-for-tiktok'),
                            'fieldKey'  => 'text_color',
                            'type'      => 'color_picker',
                            'flex'      => true,
                            'disabled' => !$has_pro,
                        )
                    )
                ),
                array(
                    'title'     => __('Post Date', 'custom-feed-for-tiktok'),
                    'key'      => 'post_date',
                    'divider' => false,
                    'typography' => true,
                    'padding' => false,
                    'border' => false,
                    'styles' => array(
                        array(
                            'title'      => __('Text Color:', 'custom-feed-for-tiktok'),
                            'fieldKey'  => 'text_color',
                            'type'      => 'color_picker',
                            'flex'      => true,
                            'disabled' => !$has_pro,
                        )
                    )
                ),
                array(
                    'title'     => __('Post Text', 'custom-feed-for-tiktok'),
                    'key'      => 'post_content',
                    'divider' => false,
                    'typography' => true,
                    'padding' => false,
                    'border' => false,
                    'styles' => array(
                        array(
                            'title'      => __('Text Color:', 'custom-feed-for-tiktok'),
                            'fieldKey'  => 'text_color',
                            'type'      => 'color_picker',
                            'flex'      => true,
                            'disabled' => !$has_pro,
                        )
                    )
                ),
                array(
                    'title'     => __('Statistics Count', 'custom-feed-for-tiktok'),
                    'key'      => 'statistics_count',
                    'divider' => false,
                    'typography' => true,
                    'padding' => false,
                    'border' => false,
                    'styles' => array(
                        array(
                            'title'      => __('Text Color:', 'custom-feed-for-tiktok'),
                            'fieldKey'  => 'text_color',
                            'type'      => 'color_picker',
                            'flex'      => true,
                            'disabled' => !$has_pro,
                        )
                    )
                ),
                array(
                    'key'      => 'read_more_link_color',
                    'divider' => false,
                    'typography' => false,
                    'padding' => false,
                    'border' => false,
                    'styles' => array(
                        array(
                            'title'      => __('Read More Link Color:', 'custom-feed-for-tiktok'),
                            'fieldKey'  => 'text_color',
                            'type'      => 'color_picker',
                            'flex'      => true,
                            'disabled' => !$has_pro,
                        )
                    )
                ),
            ),
            'follow_button' => array(
                'title' => __('Follow Button', 'custom-feed-for-tiktok'),
                'key'  => 'follow_button',
                array(
                    'key'      => 'follow_button',
                    'divider' => false,
                    'typography' => true,
                    'padding' => true,
                    'border' => true,
                    'border_radius' => true,
                    'slider' => array(
                        'title' => __('Top Spacing', 'custom-feed-for-tiktok'),
                    ),
                    'styles' => array(
                        array(
                            'title'      => __('Text Color:', 'custom-feed-for-tiktok'),
                            'fieldKey'  => 'text_color',
                            'type'      => 'color_picker',
                            'flex'      => true,
                            'disabled' => !$has_pro,
                        ),
                        array(
                            'title'      => __('Background Color:', 'custom-feed-for-tiktok'),
                            'fieldKey'  => 'background_color',
                            'type'      => 'color_picker',
                            'flex'      => true,
                            'disabled' => !$has_pro,
                        )
                    )
                ),
                array(
                    'title'     => __('Hover Style', 'custom-feed-for-tiktok'),
                    'key'      => 'follow_button_hover',
                    'divider' => false,
                    'typography' => false,
                    'padding' => false,
                    'border' => false,
                    'styles' => array(
                        array(
                            'title'      => __('Text Hover Color:', 'custom-feed-for-tiktok'),
                            'fieldKey'  => 'text_color',
                            'type'      => 'color_picker',
                            'flex'      => true,
                            'disabled' => !$has_pro,
                        ),
                        array(
                            'title'      => __('Background Hover Color:', 'custom-feed-for-tiktok'),
                            'fieldKey'  => 'background_color',
                            'type'      => 'color_picker',
                            'flex'      => true,
                            'disabled' => !$has_pro,
                        ),
                        array(
                            'title'      => __('Border Hover Color:', 'custom-feed-for-tiktok'),
                            'fieldKey'  => 'border_color',
                            'type'      => 'color_picker',
                            'flex'      => true,
                            'disabled' => !$has_pro,
                        )
                    )
                )
            ),
            'pagination' => array(
                'title' => __('Pagination', 'custom-feed-for-tiktok'),
                'key'  => 'pagination',
                array(
                    'key'      => 'tiktok_pagination',
                    'divider' => false,
                    'typography' => true,
                    'padding' => true,
                    'border' => true,
                    'border_radius' => true,
                    'slider' => array(
                        'title' => __('Top Spacing', 'custom-feed-for-tiktok'),
                    ),
                    'styles' => array(
                        array(
                            'title'      => __('Text Color:', 'custom-feed-for-tiktok'),
                            'fieldKey'  => 'text_color',
                            'type'      => 'color_picker',
                            'flex'      => true,
                            'disabled' => !$has_pro,
                        ),
                        array(
                            'title'      => __('Background Color:', 'custom-feed-for-tiktok'),
                            'fieldKey'  => 'background_color',
                            'type'      => 'color_picker',
                            'flex'      => true,
                            'disabled' => !$has_pro,
                        ),
                    )
                ),
                array(
                    'title'     => __('Hover Style', 'custom-feed-for-tiktok'),
                    'key'      => 'tiktok_pagination_hover',
                    'divider' => false,
                    'typography' => false,
                    'padding' => false,
                    'border' => false,
                    'styles' => array(
                        array(
                            'title'      => __('Text Hover Color:', 'custom-feed-for-tiktok'),
                            'fieldKey'  => 'text_color',
                            'type'      => 'color_picker',
                            'flex'      => true,
                            'disabled' => !$has_pro,
                        ),
                        array(
                            'title'      => __('Background Hover Color:', 'custom-feed-for-tiktok'),
                            'fieldKey'  => 'background_color',
                            'type'      => 'color_picker',
                            'flex'      => true,
                            'disabled' => !$has_pro,
                        ),
                        array(
                            'title'      => __('Border Hover Color:', 'custom-feed-for-tiktok'),
                            'fieldKey'  => 'border_color',
                            'type'      => 'color_picker',
                            'flex'      => true,
                            'disabled' => !$has_pro,
                        ),
                    )
                )
            ),
            'item' => array(
                'title' => __('Item Box Content', 'custom-feed-for-tiktok'),
                'key'  => 'item_box',
                array(
                    'key'      => 'item_box',
                    'divider' => false,
                    'typography' => false,
                    'padding' => true,
                    'styles' => array(
                        array(
                            'title'      => __('Background Color:', 'custom-feed-for-tiktok'),
                            'fieldKey'  => 'background_color',
                            'type'      => 'color_picker',
                            'flex'      => true,
                            'disabled' => !$has_pro,
                        )
                    )
                ),
                array(
                    'title'     => __('Item Box Content Hover', 'custom-feed-for-tiktok'),
                    'key'      => 'item_box_hover',
                    'divider' => false,
                    'typography' => false,
                    'padding' => false,
                    'border' => false,
                    'styles' => array(
                        array(
                            'title'      => __('Background Hover Color:', 'custom-feed-for-tiktok'),
                            'fieldKey'  => 'background_color',
                            'type'      => 'color_picker',
                            'flex'      => true,
                            'disabled' => !$has_pro,
                        ),
                    )
                )
            ),
            'item_wrapper' => array(
                'title' => __('Item Box', 'custom-feed-for-tiktok'),
                'key'  => 'item_box_wrapper',
                array(
                    'key'      => 'item_box_wrapper',
                    'divider' => false,
                    'typography' => false,
                    'border' => true,
                    'border_radius' => true,
                    'box_shadow' => true,
                    'styles' => array(
                        array(
                            'title'      => __('Background Color:', 'custom-feed-for-tiktok'),
                            'fieldKey'  => 'background_color',
                            'type'      => 'color_picker',
                            'flex'      => true,
                            'disabled' => !$has_pro,
                        )
                    )
                ),
                array(
                    'title'     => __('Item Box Hover Style', 'custom-feed-for-tiktok'),
                    'key'      => 'item_box_wrapper_hover',
                    'divider' => false,
                    'typography' => false,
                    'padding' => false,
                    'border' => false,
                    'styles' => array(
                        array(
                            'title'      => __('Border Hover Color:', 'custom-feed-for-tiktok'),
                            'fieldKey'  => 'border_color',
                            'type'      => 'color_picker',
                            'flex'      => true,
                            'disabled' => !$has_pro,
                        ),
                    )
                )
            ),
        );
    }

    public function formatStylesConfig($settings = [] , $postId = null)
    {
        $prefix = '.wpsr-tiktok-feed-template-'.$postId;
        return [
            'styles' => array(
                'user_name' => array(
                    'selector' => $prefix.' .wpsr-tiktok-feed-header .wpsr-tiktok-feed-user-info-wrapper .wpsr-tiktok-feed-user-info-head .wpsr-tiktok-feed-header-info .wpsr-tiktok-feed-user-info .wpsr-tiktok-feed-user-info-name-wrapper a',
                    'color'  => array(
                        'text_color' => Arr::get($settings,'styles.user_name.color.text_color', '')
                    ),
                    'typography' => array(
                        'font_size' => array(
                            'desktop' => Arr::get($settings,'styles.user_name.typography.font_size.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.user_name.typography.font_size.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.user_name.typography.font_size.mobile', ''),
                        ),
                        'letter_spacing'  => array(
                            'desktop' => Arr::get($settings,'styles.user_name.typography.letter_spacing.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.user_name.typography.letter_spacing.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.user_name.typography.letter_spacing.mobile', ''),
                        ),
                        'line_height'  => array(
                            'desktop' => Arr::get($settings,'styles.user_name.typography.line_height.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.user_name.typography.line_height.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.user_name.typography.line_height.mobile', ''),
                        ),
                        'font_weight'  => Arr::get($settings,'styles.user_name.typography.font_weight', ''),
                        'font_style'  => Arr::get($settings,'styles.user_name.typography.font_style', ''),
                        'text_transform'  => Arr::get($settings,'styles.user_name.typography.text_transform', ''),
                        'text_decoration'  => Arr::get($settings,'styles.user_name.typography.text_decoration', ''),
                    ),
                    'slider'  => array(
                        'bottom' => array(
                            'desktop' => Arr::get($settings,'styles.user_name.slider.bottom.desktop', 0),
                            'tablet' => Arr::get($settings,'styles.user_name.slider.bottom.tablet', 0),
                            'mobile' => Arr::get($settings,'styles.user_name.slider.bottom.mobile', 0),
                        ),
                    ),
                ),
                'description' => array(
                    'selector' => $prefix.' .wpsr-tiktok-feed-header .wpsr-tiktok-feed-user-info-wrapper .wpsr-tiktok-feed-user-info-head .wpsr-tiktok-feed-header-info .wpsr-tiktok-feed-user-info .wpsr-tiktok-feed-user-info-description p',
                    'color'  => array(
                        'text_color' => Arr::get($settings,'styles.description.color.text_color', ''),
                        'background_color' => Arr::get($settings,'styles.description.color.background_color', ''),
                    ),
                    'typography' => array(
                        'font_size' => array(
                            'desktop' => Arr::get($settings,'styles.description.typography.font_size.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.description.typography.font_size.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.description.typography.font_size.mobile', ''),
                        ),
                        'letter_spacing'  => array(
                            'desktop' => Arr::get($settings,'styles.description.typography.letter_spacing.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.description.typography.letter_spacing.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.description.typography.letter_spacing.mobile', ''),
                        ),
                        'line_height'  => array(
                            'desktop' => Arr::get($settings,'styles.description.typography.line_height.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.description.typography.line_height.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.description.typography.line_height.mobile', ''),
                        ),
                        'font_weight'  => Arr::get($settings,'styles.description.typography.font_weight', ''),
                        'font_style'  => Arr::get($settings,'styles.description.typography.font_style', ''),
                        'text_transform'  => Arr::get($settings,'styles.description.typography.text_transform', ''),
                        'text_decoration'  => Arr::get($settings,'styles.description.typography.text_decoration', ''),
                    ),
                    'slider'  => array(
                        'bottom' => array(
                            'desktop' => Arr::get($settings,'styles.description.slider.bottom.desktop', 0),
                            'tablet' => Arr::get($settings,'styles.description.slider.bottom.tablet', 0),
                            'mobile' => Arr::get($settings,'styles.description.slider.bottom.mobile', 0),
                        ),
                    ),
                ),
                'statistics' => array(
                    'selector' => $prefix.' .wpsr-tiktok-feed-header .wpsr-tiktok-feed-user-info-wrapper .wpsr-tiktok-feed-user-info-head .wpsr-tiktok-feed-header-info .wpsr-tiktok-feed-user-info .wpsr-tiktok-feed-user-statistics span',
                    'color'  => array(
                        'text_color' => Arr::get($settings,'styles.statistics.color.text_color', ''),
                    ),
                    'typography' => array(
                        'font_size' => array(
                            'desktop' => Arr::get($settings,'styles.statistics.typography.font_size.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.statistics.typography.font_size.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.statistics.typography.font_size.mobile', ''),
                        ),
                        'letter_spacing'  => array(
                            'desktop' => Arr::get($settings,'styles.statistics.typography.letter_spacing.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.statistics.typography.letter_spacing.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.statistics.typography.letter_spacing.mobile', ''),
                        ),
                        'line_height'  => array(
                            'desktop' => Arr::get($settings,'styles.statistics.typography.line_height.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.statistics.typography.line_height.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.statistics.typography.line_height.mobile', ''),
                        ),
                        'font_weight'  => Arr::get($settings,'styles.statistics.typography.font_weight', ''),
                        'font_style'  => Arr::get($settings,'styles.statistics.typography.font_style', ''),
                        'text_transform'  => Arr::get($settings,'styles.statistics.typography.text_transform', ''),
                        'text_decoration'  => Arr::get($settings,'styles.statistics.typography.text_decoration', ''),
                    ),
                ),
                'header_box' => array(
                    'selector' => $prefix.' .wpsr-tiktok-feed-header .wpsr-tiktok-feed-user-info-wrapper',
                    'color'  => array(
                        'background_color' => Arr::get($settings,'styles.header_box.color.background_color', ''),
                    ),
                    'padding' => array(
                        'top' => array(
                            'desktop' => Arr::get($settings,'styles.header_box.padding.top.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.header_box.padding.top.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.header_box.padding.top.mobile', ''),
                        ),
                        'right' => array(
                            'desktop' => Arr::get($settings,'styles.header_box.padding.right.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.header_box.padding.right.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.header_box.padding.right.mobile', ''),
                        ),
                        'bottom' => array(
                            'desktop' => Arr::get($settings,'styles.header_box.padding.bottom.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.header_box.padding.bottom.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.header_box.padding.bottom.mobile', ''),
                        ),
                        'left' => array(
                            'desktop' => Arr::get($settings,'styles.header_box.padding.left.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.header_box.padding.left.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.header_box.padding.left.mobile', ''),
                        ),
                        'linked' => Arr::get($settings,'styles.header_box.padding.linked', false),
                    ),
                    'border' => array(
                        'top' => array(
                            'desktop' => Arr::get($settings,'styles.header_box.border.top.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.header_box.border.top.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.header_box.border.top.mobile', ''),
                        ),
                        'right' => array(
                            'desktop' => Arr::get($settings,'styles.header_box.border.right.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.header_box.border.right.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.header_box.border.right.mobile', ''),
                        ),
                        'bottom' => array(
                            'desktop' => Arr::get($settings,'styles.header_box.border.bottom.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.header_box.border.bottom.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.header_box.border.bottom.mobile', ''),
                        ),
                        'left' => array(
                            'desktop' => Arr::get($settings,'styles.header_box.border.left.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.header_box.border.left.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.header_box.border.left.mobile', ''),
                        ),
                        'linked' => Arr::get($settings,'styles.header_box.border.linked', false),
                        'border_style' => Arr::get($settings,'styles.header_box.border.border_style', ''),
                        'border_color' => Arr::get($settings,'styles.header_box.border.border_color', ''),
                    ),
                    'border_radius' => array(
                        'top' => array(
                            'desktop' => Arr::get($settings,'styles.header_box.border_radius.top.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.header_box.border_radius.top.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.header_box.border_radius.top.mobile', ''),
                        ),
                        'right' => array(
                            'desktop' => Arr::get($settings,'styles.header_box.border_radius.right.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.header_box.border_radius.right.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.header_box.border_radius.right.mobile', ''),
                        ),
                        'bottom' => array(
                            'desktop' => Arr::get($settings,'styles.header_box.border_radius.bottom.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.header_box.border_radius.bottom.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.header_box.border_radius.bottom.mobile', ''),
                        ),
                        'left' => array(
                            'desktop' => Arr::get($settings,'styles.header_box.border_radius.left.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.header_box.border_radius.left.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.header_box.border_radius.left.mobile', ''),
                        ),
                        'linked' => Arr::get($settings,'styles.header_box.border_radius.linked', false),
                    ),
                    'box_shadow' => array(
                        'box_shadow_style' => Arr::get($settings,'styles.header_box.box_shadow.box_shadow_style', 'none'),
                        'horizontal' => Arr::get($settings,'styles.header_box.box_shadow.horizontal', ''),
                        'vertical' => Arr::get($settings,'styles.header_box.box_shadow.vertical', ''),
                        'blur' => Arr::get($settings,'styles.header_box.box_shadow.blur', ''),
                        'spread' => Arr::get($settings,'styles.header_box.box_shadow.spread', ''),
                        'color' => Arr::get($settings,'styles.header_box.box_shadow.color', ''),
                        'inset' => Arr::get($settings,'styles.header_box.box_shadow.inset', 'no'),
                    ),
                ),
                'author' => array(
                    'selector' => $prefix.'.wpsr-tiktok-feed-wrapper .wpsr-tiktok-feed-item .wpsr-tiktok-feed-author-name',
                    'color'  => array(
                        'text_color' => Arr::get($settings,'styles.author.color.text_color', '')
                    ),
                    'typography' => array(
                        'font_size' => array(
                            'desktop' => Arr::get($settings,'styles.author.typography.font_size.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.author.typography.font_size.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.author.typography.font_size.mobile', ''),
                        ),
                        'letter_spacing'  => array(
                            'desktop' => Arr::get($settings,'styles.author.typography.letter_spacing.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.author.typography.letter_spacing.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.author.typography.letter_spacing.mobile', ''),
                        ),
                        'line_height'  => array(
                            'desktop' => Arr::get($settings,'styles.author.typography.line_height.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.author.typography.line_height.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.author.typography.line_height.mobile', ''),
                        ),
                        'font_weight'  => Arr::get($settings,'styles.author.typography.font_weight', ''),
                        'font_style'  => Arr::get($settings,'styles.author.typography.font_style', ''),
                        'text_transform'  => Arr::get($settings,'styles.author.typography.text_transform', ''),
                        'text_decoration'  => Arr::get($settings,'styles.author.typography.text_decoration', ''),
                    ),
                ),
                'post_date' => array(
                    'selector' => $prefix.'.wpsr-tiktok-feed-wrapper .wpsr-tiktok-feed-item .wpsr-tiktok-feed-time',
                    'color'  => array(
                        'text_color' => Arr::get($settings,'styles.post_date.color.text_color', '')
                    ),
                    'typography' => array(
                        'font_size' => array(
                            'desktop' => Arr::get($settings,'styles.post_date.typography.font_size.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.post_date.typography.font_size.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.post_date.typography.font_size.mobile', ''),
                        ),
                        'letter_spacing'  => array(
                            'desktop' => Arr::get($settings,'styles.post_date.typography.letter_spacing.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.post_date.typography.letter_spacing.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.post_date.typography.letter_spacing.mobile', ''),
                        ),
                        'line_height'  => array(
                            'desktop' => Arr::get($settings,'styles.post_date.typography.line_height.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.post_date.typography.line_height.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.post_date.typography.line_height.mobile', ''),
                        ),
                        'font_weight'  => Arr::get($settings,'styles.post_date.typography.font_weight', ''),
                        'font_style'  => Arr::get($settings,'styles.post_date.typography.font_style', ''),
                        'text_transform'  => Arr::get($settings,'styles.post_date.typography.text_transform', ''),
                        'text_decoration'  => Arr::get($settings,'styles.post_date.typography.text_decoration', ''),
                    ),
                ),
                'post_content' => array(
                    'selector' => $prefix.'.wpsr-tiktok-feed-wrapper .wpsr-tiktok-feed-item .wpsr-feed-description-link .wpsr-feed-description-text',
                    'color'  => array(
                        'text_color' => Arr::get($settings,'styles.post_content.color.text_color', '')
                    ),
                    'typography' => array(
                        'font_size' => array(
                            'desktop' => Arr::get($settings,'styles.post_content.typography.font_size.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.post_content.typography.font_size.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.post_content.typography.font_size.mobile', ''),
                        ),
                        'letter_spacing'  => array(
                            'desktop' => Arr::get($settings,'styles.post_content.typography.letter_spacing.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.post_content.typography.letter_spacing.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.post_content.typography.letter_spacing.mobile', ''),
                        ),
                        'line_height'  => array(
                            'desktop' => Arr::get($settings,'styles.post_content.typography.line_height.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.post_content.typography.line_height.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.post_content.typography.line_height.mobile', ''),
                        ),
                        'font_weight'  => Arr::get($settings,'styles.post_content.typography.font_weight', ''),
                        'font_style'  => Arr::get($settings,'styles.post_content.typography.font_style', ''),
                        'text_transform'  => Arr::get($settings,'styles.post_content.typography.text_transform', ''),
                        'text_decoration'  => Arr::get($settings,'styles.post_content.typography.text_decoration', ''),
                    ),
                ),
                'statistics_count' => array(
                    'selector' => $prefix.'.wpsr-tiktok-feed-wrapper .wpsr-tiktok-feed-item .wpsr-tiktok-feed-statistics .wpsr-tiktok-feed-reaction-count',
                    'color'  => array(
                        'text_color' => Arr::get($settings,'styles.statistics_count.color.text_color', '')
                    ),
                    'typography' => array(
                        'font_size' => array(
                            'desktop' => Arr::get($settings,'styles.statistics_count.typography.font_size.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.statistics_count.typography.font_size.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.statistics_count.typography.font_size.mobile', ''),
                        ),
                        'letter_spacing'  => array(
                            'desktop' => Arr::get($settings,'styles.statistics_count.typography.letter_spacing.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.statistics_count.typography.letter_spacing.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.statistics_count.typography.letter_spacing.mobile', ''),
                        ),
                        'line_height'  => array(
                            'desktop' => Arr::get($settings,'styles.statistics_count.typography.line_height.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.statistics_count.typography.line_height.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.statistics_count.typography.line_height.mobile', ''),
                        ),
                        'font_weight'  => Arr::get($settings,'styles.statistics_count.typography.font_weight', ''),
                        'font_style'  => Arr::get($settings,'styles.statistics_count.typography.font_style', ''),
                        'text_transform'  => Arr::get($settings,'styles.statistics_count.typography.text_transform', ''),
                        'text_decoration'  => Arr::get($settings,'styles.statistics_count.typography.text_decoration', ''),
                    ),
                ),
                'read_more_link_color' => array(
                    'selector' => $prefix.' .wpsr_add_read_more .wpsr_read_more, .wpsr_add_read_more .wpsr_read_less',
                    'color'  => array(
                        'text_color' => Arr::get($settings,'styles.read_more_link_color.color.text_color', '')
                    ),
                ),
                'follow_button' => array(
                    'selector' => $prefix.' .wpsr-tiktok-feed-follow-button-group .wpsr-tiktok-feed-btn a',
                    'color'  => array(
                        'text_color' => Arr::get($settings,'styles.follow_button.color.text_color', ''),
                        'fill_color' => Arr::get($settings,'styles.follow_button.color.fill_color', ''),
                        'background_color' => Arr::get($settings,'styles.follow_button.color.background_color', ''),
                    ),
                    'typography' => array(
                        'font_size'  => array(
                            'desktop' => Arr::get($settings,'styles.follow_button.typography.font_size.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.follow_button.typography.font_size.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.follow_button.typography.font_size.mobile', ''),
                        ),
                        'letter_spacing'  => array(
                            'desktop' => Arr::get($settings,'styles.follow_button.typography.letter_spacing.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.follow_button.typography.letter_spacing.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.follow_button.typography.letter_spacing.mobile', ''),
                        ),
                        'line_height'  => array(
                            'desktop' => Arr::get($settings,'styles.follow_button.typography.line_height.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.follow_button.typography.line_height.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.follow_button.typography.line_height.mobile', ''),
                        ),
                        'font_weight'  => Arr::get($settings,'styles.follow_button.typography.font_weight', ''),
                        'font_style'  => Arr::get($settings,'styles.follow_button.typography.font_style', ''),
                        'text_transform'  => Arr::get($settings,'styles.follow_button.typography.text_transform', ''),
                        'text_decoration'  => Arr::get($settings,'styles.follow_button.typography.text_decoration', ''),
                    ),
                    'padding' => array(
                        'top' => array(
                            'desktop' => Arr::get($settings,'styles.follow_button.padding.top.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.follow_button.padding.top.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.follow_button.padding.top.mobile', ''),
                        ),
                        'right' => array(
                            'desktop' => Arr::get($settings,'styles.follow_button.padding.right.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.follow_button.padding.right.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.follow_button.padding.right.mobile', ''),
                        ),
                        'bottom' => array(
                            'desktop' => Arr::get($settings,'styles.follow_button.padding.bottom.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.follow_button.padding.bottom.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.follow_button.padding.bottom.mobile', ''),
                        ),
                        'left' => array(
                            'desktop' => Arr::get($settings,'styles.follow_button.padding.left.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.follow_button.padding.left.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.follow_button.padding.left.mobile', ''),
                        ),
                        'linked' => Arr::get($settings,'styles.follow_button.padding.linked', false),
                    ),
                    'slider'  => array(
                        'top' => array(
                            'desktop' => Arr::get($settings,'styles.follow_button.slider.top.desktop', 0),
                            'tablet' => Arr::get($settings,'styles.follow_button.slider.top.tablet', 0),
                            'mobile' => Arr::get($settings,'styles.follow_button.slider.top.mobile', 0),
                        ),
                    ),
                    'border_radius' => array(
                        'top' => array(
                            'desktop' => Arr::get($settings,'styles.follow_button.border_radius.top.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.follow_button.border_radius.top.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.follow_button.border_radius.top.mobile', ''),
                        ),
                        'right' => array(
                            'desktop' => Arr::get($settings,'styles.follow_button.border_radius.right.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.follow_button.border_radius.right.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.follow_button.border_radius.right.mobile', ''),
                        ),
                        'bottom' => array(
                            'desktop' => Arr::get($settings,'styles.follow_button.border_radius.bottom.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.follow_button.border_radius.bottom.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.follow_button.border_radius.bottom.mobile', ''),
                        ),
                        'left' => array(
                            'desktop' => Arr::get($settings,'styles.follow_button.border_radius.left.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.follow_button.border_radius.left.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.follow_button.border_radius.left.mobile', ''),
                        ),
                        'linked' => Arr::get($settings,'styles.follow_button.border_radius.linked', false),
                    ),
                ),
                'follow_button_hover' => array(
                    'selector' => $prefix.' .wpsr-tiktok-feed-follow-button-group .wpsr-tiktok-feed-btn a:hover',
                    'color'  => array(
                        'text_color' => Arr::get($settings,'styles.follow_button_hover.color.text_color', ''),
                        'background_color' => Arr::get($settings,'styles.follow_button_hover.color.background_color', ''),
                        'border_color' => Arr::get($settings,'styles.follow_button_hover.color.border_color', ''),
                    ),
                ),
                'tiktok_pagination' => array(
                    'selector' => $prefix.' .wpsr_more',
                    'color'  => array(
                        'text_color' => Arr::get($settings,'styles.tiktok_pagination.color.text_color', ''),
                        'background_color' => Arr::get($settings,'styles.tiktok_pagination.color.background_color', ''),
                    ),
                    'typography' => array(
                        'font_size'  => array(
                            'desktop' => Arr::get($settings,'styles.tiktok_pagination.typography.font_size.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.tiktok_pagination.typography.font_size.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.tiktok_pagination.typography.font_size.mobile', ''),
                        ),
                        'letter_spacing'  => array(
                            'desktop' => Arr::get($settings,'styles.tiktok_pagination.typography.letter_spacing.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.tiktok_pagination.typography.letter_spacing.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.tiktok_pagination.typography.letter_spacing.mobile', ''),
                        ),
                        'line_height'  => array(
                            'desktop' => Arr::get($settings,'styles.tiktok_pagination.typography.line_height.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.tiktok_pagination.typography.line_height.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.tiktok_pagination.typography.line_height.mobile', ''),
                        ),
                        'font_weight'  => Arr::get($settings,'styles.tiktok_pagination.typography.font_weight', ''),
                        'font_style'  => Arr::get($settings,'styles.tiktok_pagination.typography.font_style', ''),
                        'text_transform'  => Arr::get($settings,'styles.tiktok_pagination.typography.text_transform', ''),
                        'text_decoration'  => Arr::get($settings,'styles.tiktok_pagination.typography.text_decoration', ''),
                    ),
                    'padding' => array(
                        'top' => array(
                            'desktop' => Arr::get($settings,'styles.tiktok_pagination.padding.top.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.tiktok_pagination.padding.top.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.tiktok_pagination.padding.top.mobile', ''),
                        ),
                        'right' => array(
                            'desktop' => Arr::get($settings,'styles.tiktok_pagination.padding.right.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.tiktok_pagination.padding.right.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.tiktok_pagination.padding.right.mobile', ''),
                        ),
                        'bottom' => array(
                            'desktop' => Arr::get($settings,'styles.tiktok_pagination.padding.bottom.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.tiktok_pagination.padding.bottom.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.tiktok_pagination.padding.bottom.mobile', ''),
                        ),
                        'left' => array(
                            'desktop' => Arr::get($settings,'styles.tiktok_pagination.padding.left.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.tiktok_pagination.padding.left.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.tiktok_pagination.padding.left.mobile', ''),
                        ),
                        'linked' => Arr::get($settings,'styles.tiktok_pagination.padding.linked', false),
                    ),
                    'border' => array(
                        'top' => array(
                            'desktop' => Arr::get($settings,'styles.tiktok_pagination.border.top.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.tiktok_pagination.border.top.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.tiktok_pagination.border.top.mobile', ''),
                        ),
                        'right' => array(
                            'desktop' => Arr::get($settings,'styles.tiktok_pagination.border.right.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.tiktok_pagination.border.right.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.tiktok_pagination.border.right.mobile', ''),
                        ),
                        'bottom' => array(
                            'desktop' => Arr::get($settings,'styles.tiktok_pagination.border.bottom.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.tiktok_pagination.border.bottom.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.tiktok_pagination.border.bottom.mobile', ''),
                        ),
                        'left' => array(
                            'desktop' => Arr::get($settings,'styles.tiktok_pagination.border.left.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.tiktok_pagination.border.left.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.tiktok_pagination.border.left.mobile', ''),
                        ),
                        'linked' => Arr::get($settings,'styles.tiktok_pagination.border.linked', false),
                        'border_style' => Arr::get($settings,'styles.tiktok_pagination.border.border_style', ''),
                        'border_color' => Arr::get($settings,'styles.tiktok_pagination.border.border_color', ''),
                    ),
                    'border_radius' => array(
                        'top' => array(
                            'desktop' => Arr::get($settings,'styles.tiktok_pagination.border_radius.top.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.tiktok_pagination.border_radius.top.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.tiktok_pagination.border_radius.top.mobile', ''),
                        ),
                        'right' => array(
                            'desktop' => Arr::get($settings,'styles.tiktok_pagination.border_radius.right.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.tiktok_pagination.border_radius.right.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.tiktok_pagination.border_radius.right.mobile', ''),
                        ),
                        'bottom' => array(
                            'desktop' => Arr::get($settings,'styles.tiktok_pagination.border_radius.bottom.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.tiktok_pagination.border_radius.bottom.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.tiktok_pagination.border_radius.bottom.mobile', ''),
                        ),
                        'left' => array(
                            'desktop' => Arr::get($settings,'styles.tiktok_pagination.border_radius.left.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.tiktok_pagination.border_radius.left.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.tiktok_pagination.border_radius.left.mobile', ''),
                        ),
                        'linked' => Arr::get($settings,'styles.tiktok_pagination.border_radius.linked', false),
                    ),
                    'slider'  => array(
                        'top' => array(
                            'desktop' => Arr::get($settings,'styles.tiktok_pagination.slider.top.desktop', 0),
                            'tablet' => Arr::get($settings,'styles.tiktok_pagination.slider.top.tablet', 0),
                            'mobile' => Arr::get($settings,'styles.tiktok_pagination.slider.top.mobile', 0),
                        ),
                    ),
                ),
                'tiktok_pagination_hover' => array(
                    'selector' => $prefix.' .wpsr_more:hover',
                    'color'  => array(
                        'text_color' => Arr::get($settings,'styles.tiktok_pagination_hover.color.text_color', ''),
                        'background_color' => Arr::get($settings,'styles.tiktok_pagination_hover.color.background_color', ''),
                        'border_color' => Arr::get($settings,'styles.tiktok_pagination_hover.color.border_color', ''),
                    ),
                ),
                'item_box' => array(
                    'selector' => $prefix.'.wpsr-tiktok-feed-wrapper .wpsr-tiktok-feed-item .wpsr-tiktok-feed-inner',
                    'color'  => array(
                        'background_color' => Arr::get($settings,'styles.item_box.color.background_color', ''),
                    ),
                    'padding' => array(
                        'top' => array(
                            'desktop' => Arr::get($settings,'styles.item_box.padding.top.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.item_box.padding.top.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.item_box.padding.top.mobile', ''),
                        ),
                        'right' => array(
                            'desktop' => Arr::get($settings,'styles.item_box.padding.right.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.item_box.padding.right.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.item_box.padding.right.mobile', ''),
                        ),
                        'bottom' => array(
                            'desktop' => Arr::get($settings,'styles.item_box.padding.bottom.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.item_box.padding.bottom.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.item_box.padding.bottom.mobile', ''),
                        ),
                        'left' => array(
                            'desktop' => Arr::get($settings,'styles.item_box.padding.left.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.item_box.padding.left.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.item_box.padding.left.mobile', ''),
                        ),
                        'linked' => Arr::get($settings,'styles.item_box.padding.linked', false),
                    ),
                ),
                'item_box_hover' => array(
                    'selector' => $prefix.'.wpsr-tiktok-feed-wrapper .wpsr-tiktok-feed-item .wpsr-tiktok-feed-inner:hover',
                    'color'  => array(
                        'background_color' => Arr::get($settings,'styles.item_box_hover.color.background_color', ''),
                    ),
                ),
                'item_box_wrapper' => array(
                    'selector' => $prefix.'.wpsr-tiktok-feed-wrapper .wpsr-tiktok-feed-item',
                    'border' => array(
                        'top' => array(
                            'desktop' => Arr::get($settings,'styles.item_box.border.top.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.item_box.border.top.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.item_box.border.top.mobile', ''),
                        ),
                        'right' => array(
                            'desktop' => Arr::get($settings,'styles.item_box.border.right.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.item_box.border.right.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.item_box.border.right.mobile', ''),
                        ),
                        'bottom' => array(
                            'desktop' => Arr::get($settings,'styles.item_box.border.bottom.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.item_box.border.bottom.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.item_box.border.bottom.mobile', ''),
                        ),
                        'left' => array(
                            'desktop' => Arr::get($settings,'styles.item_box.border.left.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.item_box.border.left.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.item_box.border.left.mobile', ''),
                        ),
                        'linked' => Arr::get($settings,'styles.item_box.border.linked', false),
                        'border_style' => Arr::get($settings,'styles.item_box.border.border_style', ''),
                        'border_color' => Arr::get($settings,'styles.item_box.border.border_color', ''),
                    ),
                    'border_radius' => array(
                        'top' => array(
                            'desktop' => Arr::get($settings,'styles.item_box.border_radius.top.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.item_box.border_radius.top.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.item_box.border_radius.top.mobile', ''),
                        ),
                        'right' => array(
                            'desktop' => Arr::get($settings,'styles.item_box.border_radius.right.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.item_box.border_radius.right.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.item_box.border_radius.right.mobile', ''),
                        ),
                        'bottom' => array(
                            'desktop' => Arr::get($settings,'styles.item_box.border_radius.bottom.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.item_box.border_radius.bottom.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.item_box.border_radius.bottom.mobile', ''),
                        ),
                        'left' => array(
                            'desktop' => Arr::get($settings,'styles.item_box.border_radius.left.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.item_box.border_radius.left.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.item_box.border_radius.left.mobile', ''),
                        ),
                        'linked' => Arr::get($settings,'styles.item_box.border_radius.linked', false),
                    ),
                    'box_shadow' => array(
                        'box_shadow_style' => Arr::get($settings,'styles.item_box_wrapper.box_shadow.box_shadow_style', 'none'),
                        'horizontal' => Arr::get($settings,'styles.item_box_wrapper.box_shadow.horizontal', ''),
                        'vertical' => Arr::get($settings,'styles.item_box_wrapper.box_shadow.vertical', ''),
                        'blur' => Arr::get($settings,'styles.item_box_wrapper.box_shadow.blur', ''),
                        'spread' => Arr::get($settings,'styles.item_box_wrapper.box_shadow.spread', ''),
                        'color' => Arr::get($settings,'styles.item_box_wrapper.box_shadow.color', ''),
                        'inset' => Arr::get($settings,'styles.item_box_wrapper.box_shadow.inset', 'no'),
                    ),
                ),
                'item_box_wrapper_hover' => array(
                    'selector' => $prefix.'.wpsr-tiktok-feed-wrapper .wpsr-tiktok-feed-item:hover',
                    'color'  => array(
                        'border_color' => Arr::get($settings,'styles.item_box_wrapper_hover.color.border_color', ''),
                    ),
                ),
            ),
        ];
    }
}