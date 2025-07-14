<?php

namespace CustomFeedForTiktok\Application\Services\Platforms\Feeds\Tiktok;

use CustomFeedForTiktok\Application\Services\Platforms\Feeds\Tiktok\Config as TiktokConfig;
use WPSocialReviews\App\Services\DataProtector;
use WPSocialReviews\App\Services\GlobalSettings;
use WPSocialReviews\App\Services\Platforms\Feeds\BaseFeed;
use WPSocialReviews\App\Services\Platforms\Feeds\CacheHandler;
use WPSocialReviews\App\Services\Platforms\PlatformErrorManager;
use WPSocialReviews\App\Services\Platforms\Feeds\Common\FeedFilters;
use WPSocialReviews\App\Services\Platforms\ImageOptimizationHandler;
use WPSocialReviews\App\Services\Platforms\PlatformData;
use WPSocialReviews\Framework\Support\Arr;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class TiktokFeed extends BaseFeed
{
    public $platform = 'tiktok';
    public $feedData = [];
    protected $protector;
    protected $platfromData;
    private $remoteFetchUrl = 'https://open.tiktokapis.com/v2/';
    protected $cacheHandler;
    private $client_key = 'aw4cddbhcvsbl34m';
    private $client_secret = 'IV2QhJ7nxhvEthCI2QqZTTPpoNZOPZB6';
    private $redirect_uri = 'https://wpsocialninja.com/api/tiktok_callback';
    private $postId = null;
    
    protected $errorManager;

    public function __construct()
    {
        parent::__construct($this->platform);
        $this->cacheHandler = new CacheHandler('tiktok');
        $this->protector = new DataProtector();
        $this->platfromData = new PlatformData($this->platform);
        (new ImageOptimizationHandler($this->platform))->registerHooks();
        $this->errorManager = new PlatformErrorManager($this->platform);
        add_action('wpsr_tiktok_send_email_report', array($this, 'maybeSendFeedIssueEmail'));
    }

    public function pushValidPlatform($platforms)
    {
        $isActive = get_option('wpsr_tiktok_connected_sources_config');
        if ($isActive) {
            $platforms['tiktok'] = __('TikTok', 'custom-feed-for-tiktok');
        }
        return $platforms;
    }

    public function handleCredential($args = [])
    {
        try {
            if (!empty($args['access_code'])) {
                $this->saveVerificationConfigs($args['access_code']);
            }

            wp_send_json_success([
                'message' => __('You are Successfully Verified.', 'custom-feed-for-tiktok'),
                'status' => true
            ], 200);

        } catch (\Exception $exception) {
            wp_send_json_error([
                'message' => $exception->getMessage()
            ], 423);
        }
    }

    protected function getAccessToken($access_code = '')
    {
//        $app_credentials = $this->getAppCredentials();

        $args = build_query(array(
//            'client_key' => Arr::get($app_credentials, 'client_id'),
//            'client_secret' => $this->protector->maybe_decrypt(Arr::get($app_credentials, 'client_secret')),
            'client_key' => $this->client_key,
            'client_secret' => $this->client_secret,
            'code' => $access_code,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $this->redirect_uri,
//            'redirect_uri' => Arr::get($app_credentials, 'redirect_uri')
        ));

        $fetchUrl = $this->remoteFetchUrl . 'oauth/token/';

        $response = wp_remote_post($fetchUrl, array(
            'body' => $args,
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
        ));

        if (is_wp_error($response)) {
            throw new \Exception($response->get_error_message()); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
        }

        if (200 !== wp_remote_retrieve_response_code($response)) {
            $errorMessage = $this->getErrorMessage($response);
            throw new \Exception($errorMessage); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
        }

        return $response;
    }

    public function saveVerificationConfigs($access_code = '')
    {
        $response = $this->getAccessToken($access_code);

        if (200 === wp_remote_retrieve_response_code($response)) {
            $responseArr = json_decode(wp_remote_retrieve_body($response), true);
            $access_token = Arr::get($responseArr, 'access_token');
            $refresh_token = Arr::get($responseArr, 'refresh_token');
            $refresh_expires_in = Arr::get($responseArr, 'refresh_expires_in');
            $expires_in = intval(Arr::get($responseArr, 'expires_in'));
            $open_id = Arr::get($responseArr, 'open_id');

            $fetchUrl = $this->remoteFetchUrl . 'user/info/?fields=avatar_url,display_name,profile_deep_link';
            $response = wp_remote_get($fetchUrl, [
                'headers' => [
                    'Authorization' => "Bearer " . $access_token,
                    'Content-Type' => 'application/json'
                ],
            ]);

            do_action( 'wpsocialreviews/tiktok_feed_api_connect_response', $response );

            if (is_wp_error($response)) {
                throw new \Exception($response->get_error_message()); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
            }

            if (200 !== wp_remote_retrieve_response_code($response)) {
                $errorMessage = $this->getErrorMessage($response);
                throw new \Exception($errorMessage); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
            }

            if(Arr::get($response, 'error.code') && (new PlatformData('tiktok'))->isAppPermissionError($response)){
                do_action( 'wpsocialreviews/tiktok_feed_app_permission_revoked' );
            }

            if (200 === wp_remote_retrieve_response_code($response)) {
                $responseArr = json_decode(wp_remote_retrieve_body($response), true);
                $name = Arr::get($responseArr, 'data.user.display_name');
                $profile_url = Arr::get($responseArr, 'data.user.profile_deep_link');
                $avatar = Arr::get($responseArr, 'data.user.avatar_url');

                $data = [
                    'display_name' => $name,
                    'avatar_url' => $avatar,
                    'profile_url' => $profile_url,
                    'access_token' => $this->protector->encrypt($access_token),
                    'refresh_token' => $refresh_token,
                    'expiration_time' => time() + $expires_in,
                    'refresh_expires_in' => $refresh_expires_in,
                    'open_id' => $open_id,
                    'error_message'  => '',
                    'error_code'     => '',
                    'has_app_permission_error'     => false,
                    'has_critical_error'     => false,
                ];

                $sourceList = $this->getConnectedSourceList();
                $sourceList[$open_id] = $data;
                $accountArgs = [
                    'user_id' => $open_id,
                    'username' => $open_id,
                ];
                $this->errorManager->removeErrors('connection', $accountArgs);
                update_option('wpsr_tiktok_connected_sources_config', array('sources' => $sourceList));

                // add global tiktok settings when user verified
                $this->setGlobalSettings();
            }
        }
    }

    public function maybeRefreshToken($account)
    {
        $accessToken    = Arr::get($account, 'access_token');
        $userId         = Arr::get($account, 'open_id');
        $configs        = get_option('wpsr_tiktok_connected_sources_config', []);
        $sourceList     = Arr::get($configs, 'sources') ? $configs['sources'] : [];

        if (array_key_exists($userId, $sourceList)) {
            $existingData = $sourceList[$userId];
            $expirationTime = Arr::get($existingData, 'expiration_time', 0);
            $current_time = current_time('timestamp', true);
            $refreshToken = Arr::get($existingData, 'refresh_token', '');

            // Check if token is actually expired (not just close to expiring)
            if ($expirationTime < $current_time) {
                // Token is expired, try to refresh
                $refreshResult = $this->refreshAccessToken($refreshToken, $userId);

                // Check if refresh was successful
                if ($refreshResult && !is_array($refreshResult)) {
                    // Refresh successful, use new token
                    $accessToken = $refreshResult;
                } else {
                    // Refresh failed, but try to use existing token anyway (might still work for a while)
                    if (is_array($refreshResult) && isset($refreshResult['error_message'])) {
                        error_log('TikTok refresh error: ' . $refreshResult['error_message']);
                    }
                    // Decrypt and try existing token
                    $accessToken = $this->protector->decrypt($accessToken);

                    // Only fail if we have no token at all
                    if (empty($accessToken)) {
                        $this->clearExpiredToken($userId);
                        return false;
                    }
                }
            } else {
                // Token not expired, decrypt and return existing token
                $accessToken = $this->protector->decrypt($accessToken);
            }
        } else {
            // Decrypt the access token if it exists
            if ($accessToken) {
                $accessToken = $this->protector->decrypt($accessToken);
            }
        }

        return $accessToken;
    }

    public function refreshAccessToken($refreshTokenReceived, $userId)
    {
        $api_url = $this->remoteFetchUrl . 'oauth/token/';

//        $settings = get_option('wpsr_tiktok_global_settings');
//        $clientId = Arr::get($settings, 'app_settings.client_id', '');
//        $clientSecret = Arr::get($settings, 'app_settings.client_secret', '');
//
//        $clientId = $this->protector->decrypt($clientId);
//        $clientSecret = $this->protector->decrypt($clientSecret);

        $args = array(
            'body' => array(
//                'client_key' => $clientId,
//                'client_secret' => $clientSecret,
                'client_key' => $this->client_key,
                'client_secret' => $this->client_secret,
                'refresh_token' => $refreshTokenReceived,
                'grant_type' => 'refresh_token',
            ),
            'headers' => array(
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Cache-Control' => 'no-cache',
            ),
        );

        $response = wp_remote_post($api_url, $args);

        // Check for HTTP errors
        if (is_wp_error($response)) {
            error_log('TikTok token refresh HTTP error: ' . $response->get_error_message());
            return false;
        }

        $responseCode = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if ($responseCode !== 200) {
            error_log('TikTok token refresh failed with status code: ' . $responseCode);
            return false;
        }

        if (isset($data['error_description'])) {
            $errorMessage = $data['error_description'];
            $errorCode = Arr::get($data, 'error');

            error_log('TikTok token refresh API error: ' . $errorMessage);

            $data = [
                'error_message'  => $errorMessage,
                'error_code'     => $errorCode,
            ];
            $this->updateSourceList($userId, $data);
            return $data;
        }

        if (isset($data['open_id']) && isset($data['access_token'])) {
            $access_token = $data['access_token'];
            $refresh_token = $data['refresh_token'];
            $expires_in = intval($data['expires_in']);
            $expiration_time = time() + $expires_in;
            $open_id = $data['open_id'];
            $accountDetails = $this->getAccountDetails($open_id);

            $data = [
                'access_token' => $this->protector->encrypt($access_token),
                'refresh_token' => $refresh_token,
                'expiration_time' => $expiration_time,
                'open_id' => $open_id,
            ];

            $this->updateSourceList($userId, $data);
            return $access_token;
        } else {
            return false;
        }
    }

    private function updateSourceList($userId, $data)
    {
        $sourceList = $this->getConnectedSourceList();
        $existingData = $sourceList[$userId] ?? [];
        $mergedData = array_merge($existingData, $data);
        $sourceList[$userId] = $mergedData;
        update_option('wpsr_tiktok_connected_sources_config', ['sources' => $sourceList]);
    }

    /**
     * Clear expired token data when refresh fails
     */
    private function clearExpiredToken($userId)
    {
        $sourceList = $this->getConnectedSourceList();
        if (isset($sourceList[$userId])) {
            // Mark token as expired and requiring re-authentication
            $sourceList[$userId]['access_token'] = '';
            $sourceList[$userId]['refresh_token'] = '';
            $sourceList[$userId]['expiration_time'] = 0;
            $sourceList[$userId]['token_expired'] = true;
            $sourceList[$userId]['last_error'] = 'Token refresh failed - re-authentication required';
            $sourceList[$userId]['last_error_time'] = time();

            update_option('wpsr_tiktok_connected_sources_config', ['sources' => $sourceList]);
        }
    }

    public function getVerificationConfigs()
    {
        $connected_source_list  = $this->getConnectedSourceList();
        wp_send_json_success([
            'connected_source_list'  => $connected_source_list,
            'status'                 => true,
        ], 200);
    }

    public function clearVerificationConfigs($userId)
    {
        $sources = $this->getConnectedSourceList();

        $sources[$userId]['open_id'] = $userId;
        $sources[$userId]['user_id'] = $userId;
        $sources[$userId]['username'] = $userId;

        $this->errorManager->removeErrors('connection',  $sources[$userId]);
        $this->errorManager->removeErrors('platform_data_deleted');

        $this->platfromData->deleteDataByUser($sources[$userId]);
        unset($sources[$userId]);
        update_option('wpsr_tiktok_connected_sources_config', array('sources' => $sources));

        if (!count($sources)) {
            delete_option('wpsr_tiktok_connected_sources_config');
            delete_option('wpsr_tiktok_local_avatars');
            delete_option('wpsr_tiktok_revoke_platform_data');
//            delete_option('wpsr_tiktok_global_settings');
        }

        $cache_names = [
            'user_account_header_' . $userId,
            'user_feed_id_' . $userId,
            'single_video_feed_id' . $userId,
        ];

        foreach ($cache_names as $cache_name) {
            $this->cacheHandler->clearCacheByName($cache_name);
        }

        wp_send_json_success([
            'message' => __('Successfully Disconnected!', 'custom-feed-for-tiktok'),
        ], 200);
    }

    public function getConnectedSourceList()
    {
        $configs = get_option('wpsr_tiktok_connected_sources_config', []);
        $sourceList = Arr::get($configs, 'sources') ? $configs['sources'] : [];
        return $sourceList;
    }

    public function getTemplateMeta($settings = array(), $postId = null)
    {
        $this->postId = $postId;
        $feed_settings = Arr::get($settings, 'feed_settings', array());
        $apiSettings   = Arr::get($feed_settings, 'source_settings', array());
        $data = [];
        $settings['dynamic'] = [];
        if(!empty(Arr::get($apiSettings, 'selected_accounts'))) {
            $response = $this->apiConnection($apiSettings);
            if(isset($response['error_message']) && empty(Arr::get($response, 'items'))) {
                $settings['dynamic']['error_message']['error_message'] = $response['error_message'];
            } else {
                $data['items'] = $response['items'];
            }
        } else {
            $settings['dynamic']['error_message']['error_message'] = __('Please select an Account to get feeds.', 'custom-feed-for-tiktok');
        }

        $account = Arr::get($feed_settings, 'header_settings.account_to_show');
        if(!empty($account)) {
            $accountDetails = $this->getAccountDetails($account);
            $connectedSources = $this->getConnectedSourceList();
            $connectedAccount = Arr::get($connectedSources, $account);
            $has_account_error_code = Arr::get($connectedAccount, 'error_code');

            if(isset($accountDetails['error_message'])) {
                $settings['dynamic'] = $accountDetails;
            } else {
                $data['header'] = $accountDetails;
            }

            if($has_account_error_code && $has_account_error_code !== 'ok'){
                $errorMessage = Arr::get($connectedAccount, 'error_message');
                $userName = Arr::get($connectedAccount, 'display_name');

                if (empty($errorMessage)) {
                    // translators: %s: Account username or display name
                    $errorMessage = sprintf(__('There has been a problem with your account (%s). Please reconnect your account.', 'custom-feed-for-tiktok'), $userName);
                }

                if($has_account_error_code === 401){
                    // translators: %s: Account username or display name
                    $errorMessage = sprintf(__('There has been a problem with your account (%s). The user has not authorized application or revoked permission. Please reconnect your account.', 'custom-feed-for-tiktok'), $userName);
                }

                if($has_account_error_code === 'invalid_grant'){
                    // translators: %s: Account username or display name
                    $errorMessage =  sprintf(__('There has been a problem with your account(%s). Your access token is invalid has expired. Please reconnect your account. Otherwise, the feed will no longer work.', 'custom-feed-for-tiktok'), $userName);
                }
                $errorData = [
                    'error_message' => $errorMessage,
                    'error_code' => $has_account_error_code,
                ];
                $settings['dynamic']['error_message'] = $errorData;

                if ($has_account_error_code === 'invalid_grant' || $has_account_error_code === 401) {
                    $errorArray = [
                        'message' => $errorMessage,
                        'code' => $has_account_error_code,
                    ];
                    $errorResponse = [
                        'error' => $errorArray,
                    ];
                    if(Arr::get($errorResponse, 'error.code') && (new PlatformData('tiktok'))->isAppPermissionError($errorResponse)){
                        do_action( 'wpsocialreviews/tiktok_feed_app_permission_revoked' );
                    }

//                    $userId = Arr::get($accountDetails, 'data.user.open_id', '');
//                    $accountDetails['username'] = $userId;
//                    $accountDetails['user_id'] = $userId;
//                    $this->errorManager->addError('api', $errorResponse, $accountDetails);
                }
            }
        }

        if (Arr::get($settings, 'dynamic.error_message.error_message')) {
            $filterResponse = (new FeedFilters())->filterFeedResponse($this->platform, $feed_settings, $data);

//            $filterResponse = $settings['dynamic'];
        } else {
            $filterResponse = (new FeedFilters())->filterFeedResponse($this->platform, $feed_settings, $data);
        }
        $settings['dynamic'] = array_merge($settings['dynamic'], $filterResponse);

        $global_settings = get_option('wpsr_tiktok_global_settings');
        $advanceSettings = (new GlobalSettings())->getGlobalSettings('advance_settings');

        $optimized_images = Arr::get($global_settings, 'global_settings.optimized_images', 'false');
        $has_gdpr = Arr::get($advanceSettings, 'has_gdpr', "false");
        $items = $settings['dynamic']['items'] ?? [];

        foreach ($items as $index => $item) {
            $userAvatar = $item['user']['profile_image_url'] ?? null;
            $accountId = $item['user']['name'] ?? null;
            $headerMeta = 'avatars';

            $imageOptimizationHandler = new ImageOptimizationHandler($this->platform);
            if (method_exists($imageOptimizationHandler, 'maybeLocalHeader')) {
                $local_avatar = $imageOptimizationHandler->maybeLocalHeader($accountId, $userAvatar, $global_settings, $headerMeta);
                $settings['dynamic']['items'][$index]['user_avatar'] = ($optimized_images == "true" && $local_avatar ) ? $local_avatar : $userAvatar;
            }
        }

        if($has_gdpr === "true" && $optimized_images == "false") {
            $settings['dynamic']['items'] = [];
            $settings['dynamic']['header'] = [];
            $settings['dynamic']['error_message']['error_message'] = __('TikTok feeds are not being displayed due to the "optimize images" option being disabled. If the GDPR settings are set to "Yes," it is necessary to enable the optimize images option.', 'custom-feed-for-tiktok');
        }

        return $settings;
    }

    public function getEditorSettings($args = [])
    {
        $postId = Arr::get($args, 'postId');
        $this->postId = $postId;
        $tiktokConfig = new TiktokConfig();
        $feed_meta       = get_post_meta($postId, '_wpsr_template_config', true);
        $feed_template_style_meta = get_post_meta($postId, '_wpsr_template_styles_config', true);
        $decodedMeta     = json_decode($feed_meta, true);
        $feed_settings   = Arr::get($decodedMeta, 'feed_settings', array());
        $feed_settings   = TiktokConfig::formatTiktokConfig($feed_settings, array());
        $settings        = $this->getTemplateMeta($feed_settings);
        $templateDetails = get_post($postId);
        $settings['feed_type'] = Arr::get($settings, 'feed_settings.source_settings.feed_type');
        $settings['styles_config'] = $tiktokConfig->formatStylesConfig(json_decode($feed_template_style_meta, true), $postId);

        $global_settings = get_option('wpsr_'.$this->platform.'_global_settings');
        $advanceSettings = (new GlobalSettings())->getGlobalSettings('advance_settings');

        $image_settings = [
            'optimized_images' => Arr::get($global_settings, 'global_settings.optimized_images', 'false'),
            'has_gdpr' => Arr::get($advanceSettings, 'has_gdpr', "false")
        ];

        $translations = GlobalSettings::getTranslations();
        wp_send_json_success([
            'message'          => __('Success', 'custom-feed-for-tiktok'),
            'settings'         => $settings,
            'image_settings'   => $image_settings,
            'sources'          => $this->getConnectedSourceList(),
            'template_details' => $templateDetails,
            'elements'         => $tiktokConfig->getStyleElement(),
            'translations'     => $translations
        ], 200);
    }

    public function updateEditorSettings($settings = array(), $postId = null)
    {
        if(defined('WPSOCIALREVIEWS_PRO') && class_exists('\WPSocialReviewsPro\App\Services\TemplateCssHandler')){
            (new \WPSocialReviewsPro\App\Services\TemplateCssHandler())->saveCss($settings, $postId);
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
            'message' => __('Template Saved Successfully!!', 'custom-feed-for-tiktok'),
        ], 200);
    }

    public function editEditorSettings($settings = array(), $postId = null)
    {
        $this->postId = $postId;
        $styles_config = Arr::get($settings, 'styles_config');

        $format_feed_settings = TiktokConfig::formatTiktokConfig($settings['feed_settings'], array());
        $settings             = $this->getTemplateMeta($format_feed_settings);
        $settings['feed_type'] = Arr::get($settings, 'feed_settings.source_settings.feed_type');

        $settings['styles_config'] = $styles_config;

        $global_settings = get_option('wpsr_'.$this->platform.'_global_settings');
        $advanceSettings = (new GlobalSettings())->getGlobalSettings('advance_settings');

        $image_settings = [
            'optimized_images' => Arr::get($global_settings, 'global_settings.optimized_images', 'false'),
            'has_gdpr' => Arr::get($advanceSettings, 'has_gdpr', "false")
        ];
        $settings['image_settings'] = $image_settings;

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
        $connectedAccounts = $this->getConnectedSourceList();
        $multiple_feeds = [];
        $cacheData = [];
        $multipleAccountsConnected = count($ids) > 1;
        foreach ($ids as $id) {
            if (isset($connectedAccounts[$id])) {
                $accountInfo = $connectedAccounts[$id];
                $feedCacheName = 'user_feed_id_' . $id;
                $feed = $this->getAccountFeed($accountInfo, $apiSettings);
                if(isset($feed['error'])) {
                    $cacheData = $this->cacheHandler->getFeedCache($feedCacheName);
                    $error_message = $feed['error']['error_message'];
                    continue;
                }
                if(!empty($feed['videos'])) {
                    $multiple_feeds[] = $feed['videos'];
                }
            }
            else{
                // translators: %s: Account ID linked to the configuration
                $base_error_message = __('The account ID (%s) linked to your configuration has been deleted. To continue displaying your feed from this account, please reauthorize and reconnect it in the configuration settings. Then, go to the template editor, navigate to Source → Select Account, and choose the account again.', 'custom-feed-for-tiktok');
                if ($multipleAccountsConnected) {
                    $error_message = __('There are multiple accounts being used on this template. ', 'custom-feed-for-tiktok') . sprintf($base_error_message, $id);
                } else {
                    $error_message = sprintf($base_error_message, $id);
                }
            }
        }
        $tiktok_feeds = [];
        foreach ($multiple_feeds as $index => $feeds) {
            if(!empty($feeds) && is_array($feeds)) {
                $tiktok_feeds = array_merge($tiktok_feeds, $feeds);
            }
        }
        if(!empty($cacheData)){
            $tiktok_feeds = array_merge($tiktok_feeds, $cacheData);
        }

        return [
            'items' => $tiktok_feeds,
            'error_message' => $error_message ?? ''
        ];
    }

    public function getAccountId($connectedSources, $accountId)
    {
        foreach ($connectedSources as $source){
            $source_account_id = Arr::get($source, 'open_id');
            if($accountId === $source_account_id){
                $account_id = Arr::get($source, 'open_id');
                return $account_id;
            }
        }
    }

    public function getAccountFeed($account, $apiSettings, $cache = false)
    {
        $accessToken    = $this->protector->decrypt($account['access_token']) ? $this->protector->decrypt($account['access_token']) : $account['access_token'];
        $accountId         = Arr::get($account, 'open_id');
        $account = [
            'access_token' => $accessToken,
            'open_id' => $accountId
        ];
        $access_token = $this->maybeRefreshToken($account);

        // Check if token refresh failed completely
        if ($access_token === false) {
            // Try to use the original token as fallback
            $access_token = $accessToken;
        }

        if(isset($access_token['error_message'])){
            return [
                'error' => $access_token
            ];
        }
        $accessToken = $access_token;
        $feedType       = Arr::get($apiSettings, 'feed_type', 'user_feed');

        $totalFeed      = Arr::get($apiSettings, 'feed_count');
        $totalFeed      = !defined('WPSOCIALREVIEWS_PRO') && $totalFeed > 10 ? 10 : $totalFeed;
        $totalFeed      = apply_filters('custom_feed_for_tiktok/tiktok_feeds_limit', $totalFeed);
        if(defined('WPSOCIALREVIEWS_PRO') && $totalFeed > 200){
            $totalFeed = 200;
        }

        if($totalFeed >= 20){
            $perPage = 20;
        } else {
            $perPage = $totalFeed;
        }

        $pages = (int)($totalFeed / $perPage);
        if(($totalFeed % $perPage) > 0){
            $pages++;
        }


        $accountCacheName = '';
        if ($feedType === 'user_feed') {
            $accountCacheName  = $feedType.'_id_'.$accountId.'_num_'.$totalFeed;
        } else if ($feedType === 'single_video_feed') {
            $apiSpecificVideos = Arr::get($apiSettings, 'single_video_feed_ids', '');
            $video_ids = array_map('trim', explode(',', $apiSpecificVideos));
            $accountCacheName = 'single_video_feed_id_' . $accountId .'_template_'.$this->postId. '_num_' . count($video_ids);
        }

        $feeds = [];
        if(!$cache && $accountCacheName) {
            $feeds = $this->cacheHandler->getFeedCache($accountCacheName);
        }
        $fetchUrl = '';

        if(!$feeds) {
            $request_data = '';
            $fields = '';

            $body_args = [];

            if($feedType === 'user_feed') {
                $fields = 'video/list/?fields=id,title,duration,cover_image_url,embed_link,create_time';
                $fields = apply_filters('custom_feed_for_tiktok/tiktok_video_api_details', $fields);
                $fetchUrl = $this->remoteFetchUrl . $fields ;
                $body_args = [
                    'max_count' => $perPage
                ];
            } elseif ($feedType === 'single_video_feed') {
                $fields = apply_filters('custom_feed_for_tiktok/tiktok_specific_video_api_details', '');
                $fetchUrl = $this->remoteFetchUrl . $fields;

                $video_ids = apply_filters('custom_feed_for_tiktok/tiktok_specific_video_ids', $apiSettings);
                if (empty($video_ids)) {
                    return [
                        'error_message' => __('Please enter at least one video id', 'custom-feed-for-tiktok')
                    ];
                }

                $body_args = [
                    "filters" => [
                        "video_ids" => $video_ids
                    ],
                ];
            }

            $account_data = $this->makeRequest($fetchUrl, $accessToken, $body_args);

            do_action( 'wpsocialreviews/tiktok_feed_api_connect_response', $account_data );

            if(is_wp_error($account_data)) {
                $errorMessage = ['error_message' => $account_data->get_error_message()];
                return $errorMessage;
            }

            if(Arr::get($account_data, 'response.code') !== 200) {
                $errorMessage = $this->getErrorMessage($account_data);
                $pages_response_data = json_decode(wp_remote_retrieve_body($account_data), true);
                $errorCode = Arr::get($account_data, 'response.code');
                $pages_response_data['error']['code'] = $errorCode;

                if(Arr::get($pages_response_data, 'error.code') && (new PlatformData('tiktok'))->isAppPermissionError($pages_response_data)){
                    do_action( 'wpsocialreviews/tiktok_feed_app_permission_revoked' );
                }

                $data = [
                    'error_message'  => $errorMessage,
                    'error_code'     => $errorCode,
                ];

                return ['error' => $data];
            }

            if (Arr::get($account_data, 'response.code') === 200) {

                $account_feeds = json_decode(wp_remote_retrieve_body($account_data), true);
                
                if (isset($account_feeds['data']) && !empty($account_feeds['data'])) {
                    $this->feedData = $account_feeds['data']['videos'];

                    if (isset($account_feeds['data']['has_more']) && !empty($account_feeds['data']['has_more']) && isset($account_feeds['data']['cursor']) && ($account_feeds['data']['cursor']) !== 0) {
                        $x = 0;
                        while ($x < $pages) {
                            $cursorIs = $account_feeds['data']['cursor'];
                            $fetchUrl = $this->remoteFetchUrl . $fields;
                            $body_args = [
                                'max_count' => 20,
                                'cursor' => $cursorIs,
                            ];
                            $account_data = $this->makeRequest($fetchUrl, $accessToken, $body_args);

                            $account_feeds = json_decode(wp_remote_retrieve_body($account_data), true);
                            $new_data = $account_feeds['data']['videos'];
                            $this->feedData = array_merge($this->feedData, $new_data);
                            $x++;

                            if (isset($account_feeds['data']['has_more']) && $account_feeds['data']['has_more'] === false) {
                                break;
                            }
                        }
                    }

                    $this->feedData = array_slice($this->feedData, 0, $totalFeed);

                    $account_feeds['data']['videos'] = $this->feedData;

                    if (isset($account_feeds['data']['videos'])) {
                        foreach ($account_feeds['data']['videos'] as &$feed) {
                            $accountDetails = $this->getAccountDetails($accountId);
                            $feed['from']['avatar_url'] = Arr::get($accountDetails, 'data.user.avatar_url', '');
                            $feed['from']['display_name'] = Arr::get($accountDetails, 'data.user.display_name', '');
                            $feed['from']['profile_url'] = Arr::get($accountDetails, 'data.user.profile_deep_link', '');
                            $feed['from']['id'] = Arr::get($accountDetails, 'data.user.open_id', '');
                        }
                    }

                    $dataFormatted = $this->formatData($account_feeds['data']);
                    $account_feeds['data'] = $dataFormatted;
                    $this->cacheHandler->createCache($accountCacheName, $dataFormatted);
                }
                $feeds = Arr::get($account_feeds, 'data', []);
            }
        }

        if(!$feeds || empty($feeds)) {
            return [];
        }

        return $feeds;
    }


    public function makeRequest($url, $accessToken, $bodyArgs)
    {
        $args = [
            'headers' => [
                'Authorization' => "Bearer " . $accessToken,
                'Content-Type' => 'application/json',
            ],
            'timeout' => 60,
        ];

        if ($bodyArgs) {
            $args['body'] = json_encode($bodyArgs);
        }

        return wp_remote_post($url, $args);
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

//    public function getAppCredentials()
//    {
//        $settings = get_option('wpsr_' . $this->platform . '_global_settings');
//        $enableApp = Arr::get($settings, 'app_settings.enable_app', 'false');
//        $client_id = Arr::get($settings, 'app_settings.client_id', '');
//        $client_secret = Arr::get($settings, 'app_settings.client_secret', '');
//        $redirect_uri = Arr::get($settings, 'app_settings.redirect_uri', '');
//
//        return [
//            'enable_app' => $enableApp,
//            'client_id' => $this->protector->maybe_decrypt($client_id),
//            'client_secret' => $client_secret,
//            'redirect_uri' => $redirect_uri,
//        ];
//    }

    public function formatData($data = [])
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
            $formattedVideos[$index]['text'] = Arr::get($video, 'video_description', '');
        }

        $allData['videos'] = $formattedVideos;

        return $allData;
    }

    public function getAccountDetails($account)
    {
        $connectedAccounts = $this->getConnectedSourceList();
        $accountDetails = [];
        if (isset($connectedAccounts[$account])) {
            $accountInfo = $connectedAccounts[$account];
            $accountDetails  = $this->getHeaderDetails($accountInfo, false);
        }
        return $accountDetails;
    }

    public function getHeaderDetails($account, $cacheFetch = false)
    {
        $accountId         = Arr::get($account, 'open_id');
        $accessToken    = $this->protector->decrypt($account['access_token']) ? $this->protector->decrypt($account['access_token']) : $account['access_token'];

        // Try to refresh token if needed
        $refreshedToken = $this->maybeRefreshToken([
            'access_token' => $accessToken,
            'open_id' => $accountId
        ]);

        // Use refreshed token if available, otherwise use original
        if ($refreshedToken !== false) {
            $accessToken = $refreshedToken;
        }
        // If refresh failed, we'll still try with the original token
        $accountCacheName = 'user_account_header_'.$accountId;

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

            do_action( 'wpsocialreviews/tiktok_feed_api_connect_response', $accountData);

            if(is_wp_error($accountData)) {
                return ['error_message' => $accountData->get_error_message()];
            }

            if(Arr::get($accountData, 'error.code') && (new PlatformData('tiktok'))->isAppPermissionError($accountData)){
                do_action( 'wpsocialreviews/tiktok_feed_app_permission_revoked' );
            }

            if(Arr::get($accountData, 'response.code') !== 200) {
//                $errorMessage = $this->getErrorMessage($accountData);
//                return ['error_message' => $errorMessage];
                $errorCode = Arr::get($accountData, 'response.code');
                $accountData = json_decode(wp_remote_retrieve_body($accountData), true);
                $accountData['error']['code'] = $errorCode;
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
            $error = __('Something went wrong', 'custom-feed-for-tiktok');
        }
        return $error;
    }

    public function setGlobalSettings()
    {
        $option_name    = 'wpsr_' . $this->platform . '_global_settings';
        $existsSettings = get_option($option_name);
        if (!$existsSettings) {
            $args = array(
                'global_settings' => array(
                    'expiration'    => 60*60*24,
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
            $feed_type = '';
            $sourceId = '';
            $total = 0;
            
            // Extract feed type
            if (strpos($optionName, 'user_feed_id_') !== false) {
                $feed_type = 'user_feed';
            } elseif (strpos($optionName, 'single_video_feed_id_') !== false) {
                $feed_type = 'single_video_feed';
            }

            $apiSettings = [];
            
            // Extract source ID based on feed type
            if ($feed_type === 'user_feed') {
                $accountIdStart = strpos($optionName, 'user_feed_id_') + strlen('user_feed_id_');
                if (strpos($optionName, '_num_') !== false) {
                    $numPosition = strpos($optionName, '_num_');
                    $sourceId = substr($optionName, $accountIdStart, $numPosition - $accountIdStart);
                    $total = substr($optionName, $numPosition + strlen('_num_'));
                }

                $apiSettings = [
                    'feed_type' => $feed_type,
                    'feed_count' => $total
                ];
            } elseif ($feed_type === 'single_video_feed') {
                $accountIdStart = strpos($optionName, 'single_video_feed_id_') + strlen('single_video_feed_id_');

                if (strpos($optionName, '_template_') !== false) {
                    $numPosition = strpos($optionName, '_num_');
                    $templatePosition = strpos($optionName, '_template_');

                    $sourceId = substr($optionName, $accountIdStart, $templatePosition - $accountIdStart);
                    $total = substr($optionName, $numPosition + strlen('_num_'));
                }

                // get post ID from the option name
                $postIdPosition = strpos($optionName, '_template_');
                if ($postIdPosition !== false) {
                    $templatePos = strpos($optionName, '_template_') + strlen('_template_');
                    $numPos = strpos($optionName, '_num_');
                    $postId = substr($optionName, $templatePos, $numPos - $templatePos); // $number will be '106'
                    $this->postId = $postId; // Set post ID for later use
                } else {
                    $this->postId = null;
                }

                $specificVideos = [];
                $videos = Arr::get($cache, 'option_value.videos', []);
                
                foreach ($videos as $key => $value) {
                    $specificVideos[] = Arr::get($value, 'id', '');
                }
                $specificVideos = implode(',', $specificVideos);

                $apiSettings = [
                    'feed_type' => $feed_type,
                    'feed_count' => $total,
                    'single_video_feed_ids' => $specificVideos
                ];
            }
            
            $connectedSources = $this->getConnectedSourceList();
            if (!empty($sourceId) && isset($connectedSources[$sourceId])) {
                $account = $connectedSources[$sourceId];
                $this->maybeRefreshToken($account);
                $this->getAccountFeed($account, $apiSettings, true);
                
                // Update header details
                $page_header_response = $this->getHeaderDetails($account, true);
                $hasApiError = Arr::get($page_header_response, 'error.message', '');
                if ($hasApiError) {
                    $account['username'] = Arr::get($page_header_response, 'data.user.display_name', '');
                    $connectedSources = $this->addPlatformApiErrors($page_header_response, $connectedSources, $account);
                    update_option('wpsr_tiktok_connected_sources_config', array('sources' => $connectedSources));
                }
            }
        }
    }

    public function clearCache()
    {
        $this->cacheHandler->clearPageCaches($this->platform);
        $this->cacheHandler->clearCache();
        wp_send_json_success([
            'message' => __('Cache cleared successfully!', 'custom-feed-for-tiktok'),
        ], 200);
    }

    public function addPlatformApiErrors($response, $connectedAccounts, $accountDetails)
    {
        $critical_codes = array(
            401, // app permissions or scopes
        );

        $responseErrorCode = Arr::get($response, 'error.code', '');
        $userId   =  Arr::get($accountDetails, 'open_id', '');

        if(!empty($responseErrorCode)){
            $connectedAccounts[$userId]['error_message'] = Arr::get($response, 'error.message', '');
            $connectedAccounts[$userId]['error_code'] = $responseErrorCode;
            $connectedAccounts[$userId]['has_critical_error'] = in_array( $responseErrorCode, $critical_codes, true );
            $connectedAccounts[$userId]['has_app_permission_error'] = $this->platfromData->isAppPermissionError($response);
        }
        $connectedAccounts[$userId]['status'] = 'error';
        ;
        $accountDetails['user_id'] = $userId;
        $accountDetails['username'] = $userId;
        $this->errorManager->addError('api', $response, $accountDetails);

        return $connectedAccounts;
    }

    public function maybeSendFeedIssueEmail()
    {
        if( !$this->errorManager->hasCriticalError($this->platform) ){
            return;
        }
        $this->platfromData->sendScheduleEmailReport();
    }
}