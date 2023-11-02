<?php

namespace WPNinjaTiktokFeed\App\Services\Platforms\Feeds\Tiktok;

use WPNinjaTiktokFeed\App\Services\Platforms\Feeds\Tiktok\Config as TiktokConfig;
use WPSocialReviews\App\Services\DataProtector;
use WPSocialReviews\App\Services\GlobalSettings;
use WPSocialReviews\App\Services\Platforms\Feeds\BaseFeed;
use WPSocialReviews\App\Services\Platforms\Feeds\CacheHandler;
use WPSocialReviews\App\Services\Platforms\Feeds\Common\FeedFilters;
use WPSocialReviews\App\Services\Platforms\Feeds\Config;
use WPSocialReviews\App\Services\Platforms\PlatformData;
use WPSocialReviews\Framework\Foundation\App;
use WPSocialReviews\Framework\Support\Arr;

if (!defined('ABSPATH')) {
    exit;
}

class TiktokFeed extends BaseFeed
{
    public $platform = 'tiktok';

    public $cursorValue = '';

    public $feedData = [];
    protected $protector;
    protected $platfromData;
    private $remoteFetchUrl = 'https://open.tiktokapis.com/v2/';
    protected $cacheHandler;



    public function __construct()
    {
        parent::__construct($this->platform);
        $this->cacheHandler = new CacheHandler('tiktok');
        $this->protector = new DataProtector();
        $this->platfromData = new PlatformData($this->platform);
    }

    public function pushValidPlatform($platforms)
    {
        $isActive = get_option('wpsr_tiktok_connected_sources_config');
        if ($isActive) {
            $platforms['tiktok'] = __('Tiktok Feed', 'wp-social-reviews');
        }
        return $platforms;
    }

    public function handleCredential($args = [])
    {
        try {
            if(!empty($args['access_token'])){
                $this->saveVerificationConfigs($args['access_token']);
            }

            wp_send_json_success([
                'message' => __('You are Successfully Verified.', 'wp-social-reviews'),
                'status' => true
            ], 200);

        } catch (\Exception $exception) {
            wp_send_json_error([
                'message' => $exception->getMessage()
            ], 423);
        }
    }

    public function saveVerificationConfigs($accessToken = '')
    {
        $fetchUrl = $this->remoteFetchUrl.'user/info/?fields=open_id,union_id,avatar_url,display_name,profile_deep_link';
        $response = wp_remote_get($fetchUrl, [
            'headers' => [
                'Authorization' => "Bearer ". $accessToken,
                'Content-Type' => 'application/json'
            ],
        ]);
        if(is_wp_error($response)) {
            throw new \Exception($response->get_error_message());
        }

        if(200 !== wp_remote_retrieve_response_code($response)) {
            $errorMessage = $this->getErrorMessage($response);
            throw new \Exception($errorMessage);
        }

        if (200 === wp_remote_retrieve_response_code($response)) {
            $responseArr = json_decode(wp_remote_retrieve_body($response), true);
            $name = Arr::get($responseArr, 'data.user.display_name');
            $profile_url = Arr::get($responseArr, 'data.user.profile_deep_link');
            $avatar = Arr::get($responseArr, 'data.user.avatar_url');
            $open_id = Arr::get($responseArr, 'data.user.open_id');

            $configs = get_option('wpsr_tiktok_connected_sources_config', []);
            $sourceList = Arr::get($configs, 'sources') ? $configs['sources'] : [];

            if (array_key_exists($open_id, $sourceList)) {
                $existingData = $sourceList[$open_id];
                $data = [
                    'display_name' => $name,
                    'avatar_url' => $avatar,
                    'profile_url' => $profile_url,
                ];

                $mergedData = array_merge($existingData, $data);
                $sourceList[$open_id] = $mergedData;

                update_option('wpsr_tiktok_connected_sources_config', array('sources' => $sourceList));
                $this->setGlobalSettings();
            }
        }

    }

    public function maybeRefreshToken($page)
    {
        $accessToken = $page['access_token'];
        $userId = $page['open_id'];
        $configs = get_option('wpsr_tiktok_connected_sources_config', []);
        $sourceList = Arr::get($configs, 'sources') ? $configs['sources'] : [];

        if (array_key_exists($userId, $sourceList)) {
            $existingData = $sourceList[$userId];
            $expirationTime = Arr::get($existingData, 'expiration_time', 0);
            $current_time = current_time('timestamp', true);
            $refreshToken = Arr::get($existingData, 'refresh_token', '');
            if ($expirationTime < $current_time) {
                $accessToken = $this->refreshAccessToken($refreshToken, $userId);
            }
        }
        return $accessToken;
    }

    public function refreshAccessToken($refreshTokenReceived , $userId)
    {
        $api_url = 'https://open.tiktokapis.com/v2/oauth/token/';
        $protector = new DataProtector();
        $app = App::getInstance();

        $settings = get_option('wpsr_tiktok_global_settings');
        $clientKey = sanitize_text_field(Arr::get($settings, 'app_settings.client_key', ''));
        $clientSecret = sanitize_text_field(Arr::get($settings, 'app_settings.client_secret', ''));

        $curlPost = http_build_query(array(
            'client_Key' => $protector->decrypt($clientKey),
            'client_secret' => $protector->decrypt($clientSecret),
            'refresh_token' => $refreshTokenReceived,
            'grant_type' => 'refresh_token',
        ));

        $ch = curl_init($api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);

        $headers = [
            'Content-Type: application/x-www-form-urlencoded',
            'Cache-Control: no-cache',
        ];

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);

        if (isset($data['open_id']) && isset($data['access_token'])) {
            $access_token = sanitize_textarea_field($data['access_token']);
            $refresh_token = sanitize_textarea_field($data['refresh_token']);
            $expires_in = intval($data['expires_in']);
            $expiration_time = time() + $expires_in;
            $open_id = sanitize_textarea_field($data['open_id']);

            $data = [
                'access_token' => $access_token,
                'refresh_token' => $refresh_token,
                'expiration_time' => $expiration_time,
                'open_id' => $open_id,
            ];

            $configs = get_option('wpsr_tiktok_connected_sources_config', []);
            $sourceList = Arr::get($configs, 'sources') ? $configs['sources'] : [];

            $existingData = $sourceList[$userId];
            $mergedData = array_merge($existingData, $data);

            $sourceList[$userId] = $mergedData;
            update_option('wpsr_tiktok_connected_sources_config', array('sources' => $sourceList));
            return $access_token;
        } else {
            $error_message = 'An error occurred while refreshing the access token. Please try again later.';
            header("Content-Type: text/html");
            $app->view->render('admin.html_code', [
                'title' => 'Error',
                'body' => '<p>' . $error_message . '</p>',
                'error_occurred' => true,
            ]);
            die();
        }
    }

    public function getVerificationConfigs()
    {
        $connected_source_list  = $this->getConncetedSourceList();
        wp_send_json_success([
            'connected_source_list'  => $connected_source_list,
            'status'                 => true,
        ], 200);
    }

    public function clearVerificationConfigs($userId)
    {
        $sources = $this->getConncetedSourceList();
        unset($sources[$userId]);
        update_option('wpsr_tiktok_connected_sources_config', array('sources' => $sources));

        if (!count($sources)) {
            delete_option('wpsr_tiktok_connected_sources_config');
            delete_option('wpsr_tiktok_global_settings');
        }

        $cache_names = [
            'user_account_header_' . $userId,
            'user_feed_id_' . $userId,
            'hashtag_feed_id_' . $userId,
        ];

        foreach ($cache_names as $cache_name) {
            $this->cacheHandler->clearCacheByName($cache_name);
        }

        wp_send_json_success([
            'message' => __('Successfully Disconnected!', 'wp-social-reviews'),
        ], 200);
    }
    public function getConncetedSourceList()
    {
        $configs = get_option('wpsr_tiktok_connected_sources_config', []);
        $sourceList = Arr::get($configs, 'sources') ? $configs['sources'] : [];
        $filteredSourceList = [];
        foreach ($sourceList as $key => $data) {
            if (isset($data['refresh_token']) && isset($data['expiration_time']) && isset($data['avatar_url'])) {
                $filteredSourceList[$key] = $data;
            }
        }

        return $filteredSourceList;
    }


    public function getTemplateMeta($settings = array(), $postId = null)
    {
        $feed_settings = Arr::get($settings, 'feed_settings', array());
        $apiSettings   = Arr::get($feed_settings, 'source_settings', array());
        $data = [];
        if(!empty(Arr::get($apiSettings, 'selected_accounts'))) {
            $response = $this->apiConnection($apiSettings);
            if(isset($response['error_message'])) {
                $settings['dynamic'] = $response;
            } else {
                $data['items'] = $response;
            }
        } else {
            $settings['dynamic']['error_message'] = __('Please select an Account to get feeds', 'wp-social-reviews');
        }

        $account = Arr::get($feed_settings, 'header_settings.account_to_show');
        if(!empty($account)) {
            $accountDetails = $this->getAccountDetails($account);
            if(isset($accountDetails['error_message'])) {
                $settings['dynamic'] = $accountDetails;
            } else {
                $data['header'] = $accountDetails;
            }
        }

        $filterSettings = Arr::get($feed_settings, 'filters', []);
        if (Arr::get($settings, 'dynamic.error_message')) {
            $filterResponse = $settings['dynamic'];
        } else {
            $filterResponse = (new FeedFilters())->filterFeedResponse($this->platform, $filterSettings, $data);
        }
        $settings['dynamic'] = $filterResponse;
        return $settings;
    }

    public function getEditorSettings($args = [])
    {
        $postId = Arr::get($args, 'postId');
        $tiktokConfig = new TiktokConfig();
        $feed_meta       = get_post_meta($postId, '_wpsr_template_config', true);
        $feed_template_style_meta = get_post_meta($postId, '_wpsr_template_styles_config', true);
        $decodedMeta     = json_decode($feed_meta, true);
        $feed_settings   = Arr::get($decodedMeta, 'feed_settings', array());
        $feed_settings   = Config::formatTiktokConfig($feed_settings, array());
        $settings        = $this->getTemplateMeta($feed_settings, $postId);
        $templateDetails = get_post($postId);
        $settings['feed_type'] = Arr::get($settings, 'feed_settings.source_settings.feed_type');
        $settings['styles_config'] = $tiktokConfig->formatStylesConfig(json_decode($feed_template_style_meta, true), $postId);

        $translations = GlobalSettings::getTranslations();
        wp_send_json_success([
            'message'          => __('Success', 'wp-social-reviews'),
            'settings'         => $settings,
            'sources'          => $this->getConncetedSourceList(),
            'template_details' => $templateDetails,
            'elements'         => $tiktokConfig->getStyleElement(),
            'translations'     => $translations
        ], 200);
    }

    public function updateEditorSettings($settings = array(), $postId = null)
    {
        if(defined('WPSOCIALREVIEWS_PRO_VERSION')){
            (new \WPSocialReviewsPro\Classes\TemplateCssHandler())->saveCss($settings, $postId);
        }

        // unset them for wpsr_template_config meta
        $unsetKeys = ['dynamic', 'feed_type', 'styles_config', 'styles', 'responsive_styles'];
        foreach ($unsetKeys as $key){
            if(Arr::get($settings, $key, false)){
                unset($settings[$key]);
            }
        }

        $encodedMeta = json_encode($settings, JSON_UNESCAPED_UNICODE);
        update_post_meta($postId, '_wpsr_template_config', $encodedMeta);

        $this->cacheHandler->clearPageCaches($this->platform);
        wp_send_json_success([
            'message' => __('Template Saved Successfully!!', 'wp-social-reviews'),
        ], 200);
    }

    public function editEditorSettings($settings = array(), $postId = null)
    {
        $styles_config = Arr::get($settings, 'styles_config');

        $format_feed_settings = Config::formatTiktokConfig($settings['feed_settings'], array());
        $settings             = $this->getTemplateMeta($format_feed_settings);
        $settings['feed_type'] = Arr::get($settings, 'feed_settings.source_settings.feed_type');

        $settings['styles_config'] = $styles_config;
        wp_send_json_success([
            'settings' => $settings,
        ]);
    }

    public function apiConnection($apiSettings)
    {
        return $this->getMultipleFeeds($apiSettings);
    }

    public function getMultipleFeeds($apiSettings)
    {
        $ids = Arr::get($apiSettings, 'selected_accounts');
        $connectedAccounts = $this->getConncetedSourceList();
        $multiple_feeds = [];
        foreach ($ids as $id) {
            if (isset($connectedAccounts[$id])) {
                $pageInfo = $connectedAccounts[$id];

                $feed = $this->getPageFeed($pageInfo, $apiSettings);
                if(isset($feed['error_message'])) {
                    return $feed;
                }
                $multiple_feeds[] = $feed['videos'];
            }
        }

        $tiktok_feeds = [];
        foreach ($multiple_feeds as $index => $feeds) {
            $tiktok_feeds = array_merge($tiktok_feeds, $feeds);
        }

        return $tiktok_feeds;
    }

    public function getPageFeed($page, $apiSettings, $cache = false)
    {
        $accessToken    = $this->maybeRefreshToken($page);
        $pageId         =  $page['open_id'];
        $feedType       = Arr::get($apiSettings, 'feed_type', 'user_feed');

        $totalFeed      = Arr::get($apiSettings, 'feed_count');
        $totalFeed      = !defined('WPSOCIALREVIEWS_PRO') && $totalFeed > 20 ? 20 : $totalFeed;
        $totalFeed      =  apply_filters('ninja_tiktok_feed/tiktok_feeds_limit', $totalFeed);
        if(defined('WPSOCIALREVIEWS_PRO') && $totalFeed > 200){
            $totalFeed = 200;
        }

        if($totalFeed >= 5){
            $perPage = 5;
        } else {
            $perPage = $totalFeed;
        }

        $pages = (int)($totalFeed / $perPage);
        if(($totalFeed % $perPage) > 0){
            $pages++;
        }

        if ($feedType === 'user_feed') {
            $pageCacheName  = $feedType.'_id_'.$pageId.'_num_'.$totalFeed;
        } elseif ($feedType === 'specific_videos') {
            $apiSpecificVideos = Arr::get($apiSettings, 'specific_videos', []);
            $video_ids = array_map('trim', explode(',', $apiSpecificVideos));

            $cached_video_ids = get_option('wpsr_tiktok_specific_video_ids', []);

            $difference1 = array_diff($video_ids, $cached_video_ids);
            $difference2 = array_diff($cached_video_ids, $video_ids);

            $pageCacheName = $feedType . '_id_' . $pageId . '_video_ids_' . count($video_ids);

            if (!empty($difference1) && !empty($difference2)) {
                if(!empty($cached_video_ids)){
                    $this->cacheHandler->clearCacheByName($pageCacheName);
                }
                $cache = false;
            }

            if($cached_video_ids !== $video_ids) {
                update_option('wpsr_tiktok_specific_video_ids', $video_ids);
            }

        }

        $feeds = [];
        if(!$cache) {
            $feeds = $this->cacheHandler->getFeedCache($pageCacheName);
        }
        $fetchUrl = '';

        if(!$feeds) {
            if($feedType === 'user_feed') {
                $fields = 'video/list/?fields=id,title,video_description,duration,create_time,cover_image_url,like_count,comment_count,share_count,view_count,embed_link';
//                $fields = apply_filters('ninja_tiktok_feed/tiktok_feed_api_fields', $fields);
                $fetchUrl = $this->remoteFetchUrl . $fields ;
                $request_data = json_encode(array(
                    'max_count' => 5
                ));
            } elseif ($feedType === 'specific_videos') {
                $fields = 'video/query/?fields=id,title,video_description,duration,create_time,cover_image_url,like_count,comment_count,share_count,view_count,embed_link';
                $fetchUrl = $this->remoteFetchUrl . $fields;
                $video_ids = Arr::get($apiSettings, 'specific_videos', []);

                if (empty($video_ids)) {
                    return [
                        'error_message' => __('Please enter at least one video id', 'wp-social-reviews')
                    ];
                }

                $video_ids = explode(',', $video_ids);
                $video_ids = array_map('trim', $video_ids);

                update_option('wpsr_tiktok_specific_video_ids', $video_ids);

                $request_data = json_encode(array(
                    "filters" => [
                        "video_ids" => $video_ids
                    ],
                    'max_count' => 5,
                ));
            }

            $args     = array(
                'headers' => [
                    'Authorization' => "Bearer ". $accessToken,
                    'Content-Type' => 'application/json'
                ],
                'body' => $request_data,
                'timeout'   => 60
            );
            $pages_data = wp_remote_post($fetchUrl, $args);

            if(is_wp_error($pages_data)) {
                $errorMessage = ['error_message' => $pages_data->get_error_message()];
                return $errorMessage;
            }

            if(Arr::get($pages_data, 'response.code') !== 200) {
                $errorMessage = $this->getErrorMessage($pages_data);
                return ['error_message' => $errorMessage];
            }

            if (Arr::get($pages_data, 'response.code') === 200) {
                $page_feeds = json_decode(wp_remote_retrieve_body($pages_data), true);

                if (isset($page_feeds['data']) && !empty($page_feeds['data'])) {

                    if (isset($page_feeds['data']['has_more']) && isset($page_feeds['data']['cursor'])) {
                        $this->feedData = $page_feeds['data']['videos'];
                        $x=0;
                        while ($x < $pages  ) {
                            $cursorIs = $page_feeds['data']['cursor'];
                            $fetchUrl = $this->remoteFetchUrl . $fields;
                            $pages_data = $this->fetchPageFeeds($fetchUrl, $cursorIs, $accessToken);
                            $page_feeds = json_decode(wp_remote_retrieve_body($pages_data), true);
                            $new_data = $page_feeds['data']['videos'];
                            $this->feedData = array_merge($this->feedData, $new_data);
                            $x++;

                            if (isset($page_feeds['data']['has_more']) && $page_feeds['data']['has_more'] === false) {
                                break;
                            }
                        }

                        $this->feedData = array_slice($this->feedData, 0, $totalFeed);

                        $page_feeds['data']['videos'] = $this->feedData;

                        $configs = get_option('wpsr_tiktok_connected_sources_config', []);
                        $sourceList = Arr::get($configs, 'sources', []);
                        $sourceFrom = Arr::get($sourceList, $pageId, '');

                        if (isset($page_feeds['data']['videos'])) {
                            foreach ($page_feeds['data']['videos'] as &$feed) {
                                $feed['from'] = $sourceFrom;
                            }
                        }

                        $dataFormatted = $this->formatData($page_feeds['data']);
                        $page_feeds['data'] = $dataFormatted;
                        $this->cacheHandler->createCache($pageCacheName, $dataFormatted);
                    }
                }

                $feeds = Arr::get($page_feeds, 'data', []);
            }
        }

        if(!$feeds || empty($feeds)) {
            return [];
        }

        return $feeds;
    }

    public function fetchPageFeeds($fetchUrl, $cursor, $accessToken)
    {
        $request_data = json_encode([
                'max_count' => 5,
                'cursor' => $cursor,
        ]);

        $args = [
            'headers' => [
                'Authorization' => "Bearer " . $accessToken,
                'Content-Type' => 'application/json',
            ],
            'body' => $request_data,
            'timeout' => 60,
        ];
        return wp_remote_post($fetchUrl, $args);
    }

    public function getFormattedUser($user)
    {
        $curUser = [];
        $curUser["name"] = Arr::get($user, 'display_name', '');
		$curUser["profile_image_url"] = Arr::get($user, 'avatar_url', '');
        $curUser["profile_url"] = Arr::get($user, 'profile_url', '');
        $curUser["id"] = Arr::get($user, 'open_id', '');
        return $curUser;
    }

    public function getFormattedStatistics($video)
    {
        $curStatistics = [];
        $curStatistics['like_count'] = Arr::get($video, 'like_count', 0);
        $curStatistics['view_count'] = Arr::get($video, 'view_count', 0);
        $curStatistics['comment_count'] = Arr::get($video, 'comment_count', 0);
        $curStatistics['share_count'] = Arr::get($video, 'share_count', 0);

        return $curStatistics;
    }

    public function getFormattedMedia($video)
    {
        $curMedia = [];
        $curMedia['url'] = Arr::get($video, 'embed_link', '');
        $curMedia['preview_image_url'] = Arr::get($video, 'cover_image_url', '');
        $curMedia['duration'] = Arr::get($video, 'duration', 0);

        return $curMedia;
    }

    public function getUserCredentials()
    {
        $protector = new DataProtector();

        $settings = get_option('wpsr_' . $this->platform . '_global_settings');
        $enableApp = Arr::get($settings, 'app_settings.enable_app', 'false');
        $clientKey = sanitize_text_field(Arr::get($settings, 'app_settings.client_key', ''));
        $clientSecret = sanitize_text_field(Arr::get($settings, 'app_settings.client_secret', ''));
        $redirectUri = sanitize_text_field(Arr::get($settings, 'app_settings.redirect_uri', ''));
//        $redirectUri = 'https://gutendev.com/wp-json/wpsocialreviews/tiktok_callback';

        $configs = [
            'enableApp' => $enableApp,
            'clientKey' => $protector->decrypt($clientKey),
            'clientSecret' => $protector->decrypt($clientSecret),
            'redirectUri' => $redirectUri,
        ];

        wp_send_json_success([
            'message'  => __('success', 'wp-social-reviews'),
            'configs' => $configs
        ], 200);
    }

    public function formatData ($data = [])
    {
        $allData = $data;
        $videos = Arr::get($data, 'videos', []);

        $formattedVideos = [];
        foreach ($videos as $index => $video) {
            $user = Arr::get($video, 'from', []);
            $formattedUser = $this->getFormattedUser($user);
            $formattedVideos[$index]['id'] = Arr::get($video, 'id', '');
            $formattedVideos[$index]['user'] = $formattedUser;

            $formattedStatistics = $this->getFormattedStatistics($video);
            $formattedVideos[$index]['statistics'] = $formattedStatistics;

            $formattedMedia = $this->getFormattedMedia($video);
            $formattedVideos[$index]['media'] = $formattedMedia;

            $formattedVideos[$index]['created_at'] = Arr::get($video, 'create_time', '');
            $formattedVideos[$index]['title'] = Arr::get($video, 'title', '');
            $formattedVideos[$index]['description'] = Arr::get($video, 'video_description', '');
        }

        $allData['videos'] = $formattedVideos;

        return $allData;
    }

    public function getNextPageUrlResponse($nextUrl, $pageData)
    {
        $response = wp_remote_get($nextUrl);

        if(is_wp_error($response)) {
            $errorMessage = ['error_message' => $response->get_error_message()];
            return $errorMessage;
        }

        if(Arr::get($response, 'response.code') !== 200) {
            $errorMessage = $this->getErrorMessage($response);
            return ['error_message' => $errorMessage];
        }

        if(Arr::get($response, 'response.code') === 200) {
            $result = json_decode(wp_remote_retrieve_body($response), true);

            $newData = Arr::get($result, 'data', []);
            $oldData = Arr::get($pageData, 'data', []);

            $pageData = [];
            $pageData['data'] = array_merge($newData, $oldData);
        }

        return $pageData;
    }

    public function getAccountDetails($account)
    {
        $connectedAccounts = $this->getConncetedSourceList();
        $pageDetails = [];
        if (isset($connectedAccounts[$account])) {
            $pageInfo = $connectedAccounts[$account];
            $pageDetails  = $this->getPageDetails($pageInfo, false);
        }
        return $pageDetails;
    }

    public function getPageDetails($page, $cacheFetch = false)
    {
        $pageId = $page['open_id'];
        $accessToken = $this->maybeRefreshToken($page);

        $accountCacheName = 'user_account_header_'.$pageId;

        $accountData = [];

        if(!$cacheFetch) {
            $accountData = $this->cacheHandler->getFeedCache($accountCacheName);
        }

        if(empty($accountData) || $cacheFetch) {
            $fetchUrl = $this->remoteFetchUrl . 'user/info/?fields=open_id,union_id,avatar_url,profile_deep_link,display_name,bio_description,is_verified,follower_count,following_count,likes_count,video_count';
            $args     = array(
                'headers' => [
                    'Authorization' => "Bearer ". $accessToken,
                    'Content-Type' => 'application/json'
                ],
            );
            $accountData = wp_remote_get($fetchUrl , $args);


            if(is_wp_error($accountData)) {
                return ['error_message' => $accountData->get_error_message()];
            }

            if(Arr::get($accountData, 'response.code') !== 200) {
                $errorMessage = $this->getErrorMessage($accountData);
                return ['error_message' => $errorMessage];
            }

            if(Arr::get($accountData, 'response.code') === 200) {
                $accountData = json_decode(wp_remote_retrieve_body($accountData), true);

                $this->cacheHandler->createCache($accountCacheName, $accountData);
            }
        }

        return $accountData;
    }

    public function getErrorMessage($response = [])
    {
        $userProfileErrors = json_decode(wp_remote_retrieve_body($response), true);

        $message = Arr::get($response, 'response.message');
        if (Arr::get($userProfileErrors, 'error')) {
            if(Arr::get($userProfileErrors, 'error.message')) {
                $error = Arr::get($userProfileErrors, 'error.message');
            }else {
                $error = Arr::get( $userProfileErrors, 'error.error_user_msg', '' );
            }
        } else if (Arr::get($response, 'response.error')) {
            $error = Arr::get($response, 'response.error.message');
        } else if ($message) {
            $error = $message;
        } else {
            $error = __('Something went wrong', 'wp-social-reviews');
        }
        return $error;
    }

    public function setGlobalSettings()
    {
        $option_name    = 'wpsr_' . $this->platform . '_global_settings';
        $existsSettings = get_option($option_name);
        if (!$existsSettings) {
            // add global instagram settings when user verified
            $args = array(
                'global_settings' => array(
                    'expiration'    => 60*60*6,
                    'caching_type'  => 'background'
                )
            );
            update_option($option_name, $args);
        }
    }

    public function updateCachedFeeds($caches)
    {
        $this->cacheHandler->clearPageCaches($this->platform);
        foreach ($caches as $index => $cache) {
            $optionName = $cache['option_name'];
            $num_position = strpos($optionName, '_num_');
            $total    = substr($optionName, $num_position + strlen('_num_'), strlen($optionName));

            $feed_type  = '';
            $separator        = '_feed';
            $feed_position    = strpos($optionName, $separator) + strlen($separator);
            $initial_position = 0;
            if ($feed_position) {
                $feed_type = substr($optionName, $initial_position, $feed_position - $initial_position);
            }

            $id_position = strpos($optionName, '_id_');
            $sourceId    = substr($optionName, $id_position + strlen('_id_'),
                $num_position - ($id_position + strlen('_id_')));

            $feedTypes = ['user_feed', 'hashtag_feed'];
            $connectedSources = $this->getConncetedSourceList();
            if(in_array($feed_type, $feedTypes)) {
                if(isset($connectedSources[$sourceId])) {
                    $page = $connectedSources[$sourceId];
                    $apiSettings['feed_type'] = $feed_type;
                    $apiSettings['feed_count'] = $total;
                    $this->getPageFeed($page, $apiSettings, true);
                }
            }

            $accountIdPosition = strpos($optionName, '_account_header_');
            $accountId = substr($optionName, $accountIdPosition + strlen('_account_header_'), strlen($optionName));
            if(!empty($accountId)) {
                if(isset($connectedSources[$accountId])) {
                    $page = $connectedSources[$accountId];
                    $this->getPageDetails($page, true);
                }
            }
        }
    }

    public function clearCache()
    {
        $this->cacheHandler->clearPageCaches($this->platform);
        $this->cacheHandler->clearCache();
        wp_send_json_success([
            'message' => __('Cache cleared successfully!', 'wp-social-reviews'),
        ], 200);
    }
}