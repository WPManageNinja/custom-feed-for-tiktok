<?php

namespace CustomFeedForTiktok\Application\Hooks\Handlers;
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

use CustomFeedForTiktok\Application\Services\Platforms\Feeds\Tiktok\TiktokFeed;
use CustomFeedForTiktok\Application\Services\Platforms\Feeds\Tiktok\Config;
use WPSocialReviews\Framework\Support\Arr;
use WPSocialReviews\App\Services\Helper as GlobalHelper;
use WPSocialReviews\App\Services\GlobalSettings;
use CustomFeedForTiktok\Application\Traits\LoadView;
use WPSocialReviews\App\Hooks\Handlers\ShortcodeHandler;


class TiktokTemplateHandler
{
    /**
     *
     * Render parent opening div for the template item
     *
     * @param $template_meta
     *
     * @since 3.13.0
     *
     **/
    use LoadView;
    public function renderTemplateItemWrapper($template_meta = [])
    {
        $desktop_column = Arr::get($template_meta, 'responsive_column_number.desktop');
        $tablet_column = Arr::get($template_meta, 'responsive_column_number.tablet');
        $mobile_column = Arr::get($template_meta, 'responsive_column_number.mobile');

        $classes = 'wpsr-mb-30 wpsr-col-' . esc_attr($desktop_column) . ' wpsr-col-sm-' . esc_attr($tablet_column) . ' wpsr-col-xs-' . esc_attr($mobile_column);
        $html = $this->loadView('public/feeds-templates/tiktok/elements/item-parent-wrapper', array(
            'classes' => $classes,
        ));
        echo wp_kses_post($html);
    }

    public function renderFeedAuthor($feed = [], $template_meta = [])
    {
        $html = $this->loadView('public/feeds-templates/tiktok/elements/author', array(
            'feed'          => $feed,
            'account'       => Arr::get($feed, 'user'),
            'template_meta' => $template_meta
        ));

        echo wp_kses_post($html);
    }

    public function renderFeedAuthorName($feed = [], $template_meta = [])
    {
        $html = $this->loadView('public/feeds-templates/tiktok/elements/author-name', array(
            'account'       => Arr::get($feed, 'user'),
            'template_meta' => $template_meta
        ));

        echo wp_kses_post($html);
    }

    public function renderFeedDescription($feed = [], $template_meta = [])
    {
        if (Arr::get($template_meta, 'post_settings.display_description') === 'false') {
            return;
        }
        $allowed_tags = GlobalHelper::allowedHtmlTags();
        $text = Arr::get($feed, 'text');
        if (strlen($text) === 0) {
            return;
        }

        $html =$this->loadView('public/feeds-templates/tiktok/elements/description', array(
            'feed'          => $feed,
            'allowed_tags'  => $allowed_tags,
            'message'       => $text,
            'content_length'  => Arr::get($template_meta, 'post_settings.content_length' , null),
        ));
        echo wp_kses_post($html);
    }

    public function renderFeedMedia($feed = [], $template_meta = [])
    {
        $gdpr_settings = [];
        $tiktokFeed = new TiktokFeed();
        if (method_exists($tiktokFeed, 'getGdprSettings')) {
            $gdpr_settings = (new TiktokFeed())->getGdprSettings('tiktok');
        }

        $html = $this->loadView('public/feeds-templates/tiktok/elements/media', array(
            'feed'          => $feed,
            'template_meta' => $template_meta,
            'image_settings' => $gdpr_settings
        ));
        echo wp_kses_post($html);
    }

    public function renderFeedIcon($class = '')
    {
        $html = $this->loadView('public/feeds-templates/tiktok/elements/icon', array(
            'class' => $class
        ));
        echo wp_kses_post($html);
    }

    public function getPaginatedFeedHtml($templateId = null, $page = null , $feed_id = null , $feed_type = '')
    {
        $shortcodeHandler = new ShortcodeHandler();
        $template_meta = $shortcodeHandler->templateMeta($templateId, 'tiktok');
        $templateNumber = Arr::get($template_meta, 'feed_settings.template');
        $feed = (new TiktokFeed())->getTemplateMeta($template_meta);
        $settings = $shortcodeHandler->formatFeedSettings($feed);
        $pagination_settings = $shortcodeHandler->formatPaginationSettings($feed);
        $sinceId = (($page - 1) * $pagination_settings['paginate']);
        $maxId = ($sinceId + $pagination_settings['paginate']) - 1;
        $gdpr_settings = (new TiktokFeed())->getGdprSettings('tiktok');

        $template_body_data = [
            'templateId'    => $templateId,
            'feeds'         => $settings['feeds'],
            'template_meta' => $settings['feed_settings'],
            'paginate'      => $pagination_settings['paginate'],
            'sinceId'       => $sinceId,
            'maxId'         => $maxId,
            'translations'  => GlobalSettings::getTranslations(),
            'image_settings' => $gdpr_settings
        ];

        if ($templateNumber === 'template2') {
            $html = apply_filters('custom_feed_for_tiktok/add_tiktok_feed_template', $template_body_data); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
            return $html;
        } else {
            return (string)$this->loadView('public/feeds-templates/tiktok/template1', $template_body_data);
        }
    }

    public function renderLoadMoreButton ($template_meta = null, $templateId = null, $paginate = null, $layout_type = "", $total = null, $feed_type = "", $feed = null)
    {
        $html = $this->loadView('public/feeds-templates/tiktok/elements/load-more', array(
            'template_meta' => $template_meta,
            'templateId' => $templateId,
            'paginate' => $paginate,
            'layout_type' => $layout_type,
            'feed_type' => $feed_type,
            'feed' => $feed,
            'total' => $total
        ));
        echo wp_kses_post($html);
    }

    public function formatTiktokConfig($configs = [] , $response = [])
    {
        return Config::formatTiktokConfig($configs, $response);
    }

    public function getTemplateMeta($template_meta = [])
    {
        return (new TiktokFeed())->getTemplateMeta($template_meta);
    }
}