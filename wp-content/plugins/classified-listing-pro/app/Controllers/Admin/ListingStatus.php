<?php

namespace Rtcl\Controllers\Admin;
use Rtcl\Helpers\Functions;

/**
 * Class ListingStatus
 *
 * @package Rtcl\Controllers\Admin
 */
class ListingStatus
{
    static function init() {
        add_action('transition_post_status', [__CLASS__, 'transition_post_status'], 10, 3);
    }
    public static function transition_post_status($new_status, $old_status, $post) {

        if (rtcl()->post_type !== $post->post_type) {
            return;
        }

        // Check if we are transitioning from pending to publish
        if ('pending' == $old_status && 'publish' == $new_status) {

            try {
                Functions::apply_payment_pricing($post->ID);
            } catch (\Exception $e) {
                $log = rtcl()->logger();
                $log->info('Pricing apply error', ['post_id', $post->ID, 'error' => $e->getMessage()]);
            }
            if (Functions::get_option_item('rtcl_email_settings', 'notify_users', 'listing_published', 'multi_checkbox')) {
                rtcl()->mailer()->emails['Listing_Published_Email_To_Owner']->trigger($post->ID);
            }
            $publish_count = absint(get_post_meta($post->ID, '_rtcl_publish_count', true));
            update_post_meta($post->ID, '_rtcl_publish_count', $publish_count + 1);
        }

        // Check if we are transitioning from private to publish
        if ('private' == $old_status && 'publish' == $new_status) {

            // TODO : If need some data

        }

        // Check if we are transitioning from private to publish
        if ('draft' == $old_status && 'publish' == $new_status) {

            // TODO : If need some data

        }

    }
}