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
    const OLD_APP_DELETION_DATE = '2026-11-30';

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
                'We have moved Custom Feed for TikTok to a new TikTok app. %d connected TikTok account must be reconnected, otherwise its feed will stop updating. Open the TikTok configuration, remove the account and connect it again.',
                'We have moved Custom Feed for TikTok to a new TikTok app. %d connected TikTok accounts must be reconnected, otherwise their feeds will stop updating. Open the TikTok configuration, remove the accounts and connect them again.',
                $outdatedAccounts,
                'custom-feed-for-tiktok'
            ),
            $outdatedAccounts
        );

        $deletionMessage = $this->getDeletionMessage();
        if ($deletionMessage) {
            $message .= ' ' . $deletionMessage;
        }

        $notices[] = [
            'type'        => 'warning',
            'title'       => __('Action required: reconnect your TikTok account', 'custom-feed-for-tiktok'),
            'message'     => $message,
            'dismissible' => true,
            'actions'     => [
                [
                    'label'  => __('Reconnect TikTok Account', 'custom-feed-for-tiktok'),
                    // ?install=tiktok_feed opens the TikTok configuration modal on the Platforms page.
                    // The extra query arg forces a full reload, since the notice already sits on that page
                    // and the modal only opens on mount.
                    'url'    => admin_url('admin.php?page=wpsocialninja.php&wpsr_reconnect=tiktok#/?install=tiktok_feed'),
                    'target' => '_self',
                ],
            ],
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
