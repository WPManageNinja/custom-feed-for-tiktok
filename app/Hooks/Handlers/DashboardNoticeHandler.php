<?php

namespace CustomFeedForTiktok\Application\Hooks\Handlers;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

use CustomFeedForTiktok\Application\Services\Platforms\Feeds\Tiktok\Helper;
use CustomFeedForTiktok\Application\Services\Platforms\Feeds\Tiktok\TiktokFeed;
use WPSocialReviews\Framework\Support\Arr;

class DashboardNoticeHandler
{
    /**
     * Date (Y-m-d) the old TikTok app gets deleted. Leave empty to omit it from the notice.
     */
    const OLD_APP_DELETION_DATE = '2026-12-15';

    /**
     * Ask users to reconnect TikTok accounts authorized against an older TikTok app.
     * The notice disappears once every connected account has been reconnected.
     *
     * @param array $notices
     * @return array
     */
    public function addReconnectNotice($notices = [])
    {
        if (!is_array($notices)) {
            $notices = [];
        }

        $outdatedAccounts = 0;
        foreach (Helper::getConnectedSourceList() as $source) {
            if ((int) Arr::get($source, 'auth_app_version', 0) < TiktokFeed::AUTH_APP_VERSION) {
                $outdatedAccounts++;
            }
        }

        if (!$outdatedAccounts) {
            return $notices;
        }

        $message = sprintf(
            /* translators: %d: number of TikTok accounts that need to be reconnected */
            _n(
                'We have moved Custom Feed for TikTok to a new TikTok app. %d connected TikTok account must be reconnected, otherwise its feed will stop updating.',
                'We have moved Custom Feed for TikTok to a new TikTok app. %d connected TikTok accounts must be reconnected, otherwise their feeds will stop updating.',
                $outdatedAccounts,
                'custom-feed-for-tiktok'
            ),
            $outdatedAccounts
        );

        $deletionMessage = $this->getDeletionMessage();
        if ($deletionMessage) {
            $message .= ' ' . $deletionMessage;
        }

        // Core before 4.5.0 ignores `path` and has no per-account Reconnect link, so spell out the old steps in the message.
        if (defined('WPSOCIALREVIEWS_VERSION') && version_compare(WPSOCIALREVIEWS_VERSION, '4.5.0', '<')) {
            $message .= ' ' . __('Go to Platforms → TikTok and connect the same TikTok account again.', 'custom-feed-for-tiktok');
        }

        // The new app gives the account a new ID, so templates keep the old account until the user changes them.
        $message .= ' ' . __('After you reconnect, the account shows as a new account. Select it in each TikTok template that uses the old account, then remove the old account.', 'custom-feed-for-tiktok');

        $notices[] = [
            'type'        => 'warning',
            'title'       => __('Action required: reconnect your TikTok account', 'custom-feed-for-tiktok'),
            'message'     => $message,
            // Core 4.5.0+ shows `group: reconnect` notices as rows of one reconnect card; older core ignores these keys.
            'group'       => 'reconnect',
            'label'       => __('TikTok', 'custom-feed-for-tiktok'),
            'path'        => __('Go to Platforms → TikTok → Reconnect, and sign in with the same TikTok account.', 'custom-feed-for-tiktok'),
            // Core hides a dismissed add-on notice only until reload, so this notice offers no close button.
            'dismissible' => false,
        ];

        return $notices;
    }

    /**
     * Tell users when the old TikTok app goes away.
     *
     * @return string
     */
    protected function getDeletionMessage()
    {
        $deletionTime = strtotime(self::OLD_APP_DELETION_DATE);
        if (!self::OLD_APP_DELETION_DATE || !$deletionTime) {
            return '';
        }

        /* translators: %s: date the old TikTok app will be deleted */
        return sprintf(__('The old TikTok app will be deleted on %s. Please reconnect before then.', 'custom-feed-for-tiktok'), date_i18n(get_option('date_format'), $deletionTime));
    }
}
