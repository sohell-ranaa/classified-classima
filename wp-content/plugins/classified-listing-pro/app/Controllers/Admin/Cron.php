<?php

namespace Rtcl\Controllers\Admin;

use DateInterval;
use Rtcl\Helpers\Functions;
use Rtcl\Log\Logger;

class Cron
{

    function __construct() {

        add_action('wp', array($this, 'schedule_events'));
        add_action('rtcl_hourly_scheduled_events', array($this, 'hourly_scheduled_events'));
        add_action('rtcl_twicedaily_scheduled_events', array($this, 'twicedaily_scheduled_events'));

    }

    function schedule_events() {

        if (!wp_next_scheduled('rtcl_hourly_scheduled_events')) {
            wp_schedule_event(current_time('timestamp'), 'hourly', 'rtcl_hourly_scheduled_events');
        }
        if (!wp_next_scheduled('rtcl_twicedaily_scheduled_events')) {
            wp_schedule_event(current_time('timestamp'), 'twicedaily', 'rtcl_twicedaily_scheduled_events');
        }

    }

    function twicedaily_scheduled_events() {
        $this->remove_inactive_conversation();
        do_action('rtcl_cron_twicedaily_scheduled_events');
    }

    function hourly_scheduled_events() {
        // TODO : Active all this function to active
        $this->sent_renewal_email_to_published_listings();
        $this->move_listings_publish_to_expired();
        $this->send_renewal_reminders();
        $this->delete_expired_listings();
        $this->remove_expired_bump_up();
        $this->remove_expired_featured();
        $this->remove_expired_top_listing();

        $this->do_hourly_bump_up();
        do_action('rtcl_cron_hourly_scheduled_events');
    }

    function sent_renewal_email_to_published_listings() {
        $email_settings = Functions::get_option('rtcl_email_settings');
        $email_threshold = (int)$email_settings['renewal_email_threshold'];

        if ($email_threshold > 0) {

            $email_threshold_date = date('Y-m-d H:i:s', strtotime("+" . $email_threshold . " days"));

            // Define the query
            $args = array(
                'post_type'      => rtcl()->post_type,
                'posts_per_page' => -1,
                'post_status'    => 'publish',
                'fields'         => 'ids',
                'meta_query'     => array(
                    'relation' => 'AND',
                    array(
                        'key'     => 'expiry_date',
                        'value'   => $email_threshold_date,
                        'compare' => '<',
                        'type'    => 'DATETIME'
                    ),
                    array(
                        'key'     => 'renewal_reminder_sent',
                        'compare' => 'NOT EXISTS'
                    ),
                    array(
                        'key'     => 'never_expires',
                        'compare' => 'NOT EXISTS',
                    )
                )
            );

            $rtcl_query = new \WP_Query(apply_filters('rtcl_cron_sent_renewal_email_to_published_listings_query_args', $args));

            if (!empty($rtcl_query->posts)) {

                foreach ($rtcl_query->posts as $post_id) {
                    // Send emails to user
                    if (Functions::get_option_item('rtcl_email_settings', 'notify_users', 'listing_renewal', 'multi_checkbox')) {
                        if (rtcl()->mailer()->emails['Listing_Renewal_Email_To_Owner']->trigger($post_id)) {
                            update_post_meta($post_id, 'renewal_reminder_sent', 1);
                        }
                    }
                    do_action("rtcl_cron_sent_renewal_email_to_published_listing", $post_id);
                }
            }

        }
    }

    function move_listings_publish_to_expired() {

        $moderation_settings = Functions::get_option('rtcl_moderation_settings');
        $email_settings = Functions::get_option('rtcl_email_template_renewal_reminder');
        $renewal_reminder_threshold = isset($email_settings['renewal_reminder_threshold']) ? absint($email_settings['renewal_reminder_threshold']) : 0;
        $delete_expired_listings = isset($moderation_settings['delete_expired_listings']) ? absint($moderation_settings['delete_expired_listings']) : 0;
        $delete_threshold = $renewal_reminder_threshold + $delete_expired_listings;

        // Define the query
        $args = array(
            'post_type'      => rtcl()->post_type,
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'fields'         => 'ids',
            'meta_query'     => array(
                'relation' => 'AND',
                array(
                    'key'     => 'expiry_date',
                    'value'   => current_time('mysql'),
                    'compare' => '<',
                    'type'    => 'DATETIME'
                ),
                array(
                    'key'     => 'never_expires',
                    'compare' => 'NOT EXISTS',
                )
            )
        );

        $rtcl_query = new \WP_Query(apply_filters('rtcl_cron_move_listings_publish_to_expired_query_args', $args));

        if (!empty($rtcl_query->posts)) {

            foreach ($rtcl_query->posts as $post_id) {
                // Update the post into the database
                $newData = array(
                    'ID'          => $post_id,
                    'post_status' => 'rtcl-expired'
                );

                wp_update_post($newData);      // Update post status to
                delete_post_meta($post_id, 'expiry_date');
                delete_post_meta($post_id, 'never_expired');
                update_post_meta($post_id, 'featured', 0);
                delete_post_meta($post_id, 'feature_expiry_date');
                update_post_meta($post_id, '_top', 0);
                delete_post_meta($post_id, '_top_expiry_date');
                delete_post_meta($post_id, '_bump_up');
                delete_post_meta($post_id, '_bump_up_expiry_date');
                delete_post_meta($post_id, 'renewal_reminder_sent');

                if ($delete_threshold > 0) {
                    $deletion_date_time = date('Y-m-d H:i:s', strtotime("+" . $delete_threshold . " days"));
                    update_post_meta($post_id, 'deletion_date', $deletion_date_time); // TODO : Need to check from where it to make action
                }

                if (Functions::get_option_item('rtcl_email_settings', 'notify_users', 'listing_expired', 'multi_checkbox')) {
                    rtcl()->mailer()->emails['Listing_Expired_Email_To_Owner']->trigger($post_id);
                }

                if (Functions::get_option_item('rtcl_email_settings', 'notify_admin', 'listing_expired', 'multi_checkbox')) {
                    rtcl()->mailer()->emails['Listing_Expired_Email_To_Admin']->trigger($post_id);
                }

                // Hook for developers
                do_action('rtcl_cron_move_listing_publish_to_expired', $post_id);
            }
        }

    }

    function delete_expired_listings() {

        $moderation_settings = Functions::get_option('rtcl_moderation_settings');
        $email_settings = Functions::get_option('rtcl_email_template_renewal_reminder');

        $renewal_reminder_threshold = isset($email_settings['renewal_reminder_threshold']) ? (int)$email_settings['renewal_reminder_threshold'] : 0;
        $delete_expired_listings = isset($moderation_settings['delete_expired_listings']) ? (int)$moderation_settings['delete_expired_listings'] : 0;
        $can_renew = Functions::get_option_item('rtcl_moderation_settings', 'has_listing_renewal', false, 'checkbox');

        if ($can_renew) {
            $delete_threshold = $renewal_reminder_threshold + $delete_expired_listings;
        } else {
            $delete_threshold = $delete_expired_listings;
        }

        if ($delete_threshold > 0) {

            // Define the query
            $args = array(
                'post_type'      => rtcl()->post_type,
                'posts_per_page' => -1,
                'post_status'    => 'rtcl-expired',
                'fields'         => 'ids',
                'meta_query'     => array(
                    'relation' => 'AND',
                    array(
                        'key'     => 'deletion_date',
                        'value'   => current_time('mysql'),
                        'compare' => '<',
                        'type'    => 'DATETIME'
                    ),
                    array(
                        'key'     => 'never_expires',
                        'compare' => 'NOT EXISTS',
                    )
                )
            );

            $rtcl_query = new \WP_Query(apply_filters('rtcl_cron_delete_expired_listings_query_args', $args));

            if (!empty($rtcl_query->posts)) {

                foreach ($rtcl_query->posts as $post_id) {
                    do_action("rtcl_cron_delete_expired_listing", $post_id);
                    Functions::delete_post($post_id);
                }
            }
        }
    }

    /**
     * Renewal Reminders
     *
     * @return void
     */
    function send_renewal_reminders() {
        $email_settings = Functions::get_option('rtcl_email_settings');
        $reminder_threshold = isset($email_settings['renewal_reminder_threshold']) ? (int)$email_settings['renewal_reminder_threshold'] : 0;

        if ($reminder_threshold > 0) {
            // Define the query
            $args = array(
                'post_type'      => rtcl()->post_type,
                'posts_per_page' => -1,
                'post_status'    => 'rtcl-expired',
                'fields'         => 'ids',
                'meta_query'     => array(
                    'relation' => 'AND',
                    array(
                        'key'     => 'renewal_reminder_sent',
                        'value'   => 0,
                        'compare' => '='
                    ),
                    array(
                        'key'     => 'never_expires',
                        'compare' => 'NOT EXISTS',
                    )
                )
            );

            $rtcl_query = new \WP_Query(apply_filters('rtcl_cron_send_renewal_reminders_query_args', $args));

            if (!empty($rtcl_query->posts)) {

                foreach ($rtcl_query->posts as $post_id) {

                    $expiration_date = get_post_meta($post_id, 'expiry_date', true);
                    $expiration_date_time = strtotime($expiration_date);
                    $reminder_date_time = strtotime("+" . $reminder_threshold . " days", strtotime($expiration_date_time));

                    if (current_time('timestamp') > $reminder_date_time) {

                        // Send renewal reminder emails to listing owner
                        update_post_meta($post_id, 'renewal_reminder_sent', 1);
                        if (Functions::get_option_item('rtcl_email_settings', 'notify_users', 'remind_renewal', 'multi_checkbox')) {
                            rtcl()->mailer()->emails['Listing_Renewal_Reminder_Email_To_Owner']->trigger($post_id);
                        }

                        do_action('rtcl_cron_send_renewal_reminders_listing', $post_id);
                    }
                }
            }

        }
    }

    private function remove_expired_bump_up() {

        // Define the query
        $args = array(
            'post_type'      => rtcl()->post_type,
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'fields'         => 'ids',
            'meta_query'     => array(
                'relation' => 'AND',
                array(
                    'key'     => '_bump_up_expiry_date',
                    'value'   => current_time('mysql'),
                    'compare' => '<',
                    'type'    => 'DATETIME'
                ),
                array(
                    'key'     => '_bump_up',
                    'compare' => '=',
                    'value'   => 1,
                )
            )
        );


        $rtcl_query = new \WP_Query(apply_filters('rtcl_cron_remove_expired_bump_up_query_args', $args));

        if (!empty($rtcl_query->posts)) {

            foreach ($rtcl_query->posts as $post_id) {
                delete_post_meta($post_id, '_bump_up');
                delete_post_meta($post_id, '_bump_up_expiry_date');
                do_action("rtcl_cron_remove_expired_bump_up_listing", $post_id); // TODO : make task
            }
        }


    }

    private function do_hourly_bump_up() {
        // Define the query
        $args = apply_filters('rtcl_cron_do_hourly_bump_up_query_args', [
            'post_type'      => rtcl()->post_type,
            'posts_per_page' => 10,
            'post_status'    => 'publish',
            'fields'         => 'ids',
            'date_query'     => [
                'before' => current_time('Y-m-d')
            ],
            'meta_query'     => [
                'relation' => 'AND',
                [
                    'key'     => '_bump_up_expiry_date',
                    'value'   => current_time('mysql'),
                    'compare' => '>',
                    'type'    => 'DATETIME'
                ],
                [
                    'key'     => '_bump_up',
                    'compare' => '=',
                    'value'   => 1,
                ]
            ]
        ]);

        $rtcl_query = new \WP_Query($args);
        if (!empty($rtcl_query->posts)) {
            foreach ($rtcl_query->posts as $post_id) {
                wp_update_post(
                    array(
                        'ID'            => $post_id,
                        'post_date'     => current_time('mysql'),
                        'post_date_gmt' => get_gmt_from_date(current_time('mysql'))
                    )
                );
                do_action("rtcl_cron_do_hourly_bump_up_listing", $post_id);
            }
        }
    }

    private function remove_expired_featured() {
        // Define the query
        $args = array(
            'post_type'      => rtcl()->post_type,
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'fields'         => 'ids',
            'meta_query'     => array(
                'relation' => 'AND',
                array(
                    'key'     => 'feature_expiry_date',
                    'value'   => current_time('mysql'),
                    'compare' => '<',
                    'type'    => 'DATETIME'
                ),
                array(
                    'key'     => 'featured',
                    'compare' => '=',
                    'value'   => 1,
                )
            )
        );


        $rtcl_query = new \WP_Query(apply_filters('rtcl_cron_remove_expired_featured_query_args', $args));

        if (!empty($rtcl_query->posts)) {

            foreach ($rtcl_query->posts as $post_id) {
                delete_post_meta($post_id, 'featured');
                delete_post_meta($post_id, 'feature_expiry_date');
                do_action("rtcl_cron_remove_expired_featured_listing", $post_id);
            }
        }
    }

    private function remove_expired_top_listing() {
        // Define the query
        $args = array(
            'post_type'      => rtcl()->post_type,
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'fields'         => 'ids',
            'meta_query'     => array(
                'relation' => 'AND',
                array(
                    'key'     => '_top_expiry_date',
                    'value'   => current_time('mysql'),
                    'compare' => '<',
                    'type'    => 'DATETIME'
                ),
                array(
                    'key'     => '_top',
                    'compare' => '=',
                    'value'   => 1,
                )
            )
        );


        $rtcl_query = new \WP_Query(apply_filters('rtcl_cron_remove_expired_top_listing_query_args', $args));

        if (!empty($rtcl_query->posts)) {

            foreach ($rtcl_query->posts as $post_id) {
                delete_post_meta($post_id, '_top');
                delete_post_meta($post_id, '_top_expiry_date');
                do_action("rtcl_cron_remove_expired_top_listing", $post_id);
            }
        }
    }

    private function remove_inactive_conversation() {
        if ($days = Functions::get_option_item('rtcl_chat_settings', 'remove_inactive_conversation_duration', 0, 'number')) {
            global $wpdb;
            $inactive_date = current_datetime()->sub(new DateInterval(sprintf('P%sD', $days)))->format('Y-m-d H:i:s');
            $ids = $wpdb->get_col($wpdb->prepare(
                "SELECT rc.con_id FROM {$wpdb->prefix}rtcl_conversations as rc, {$wpdb->prefix}rtcl_conversation_messages as rcm  WHERE rc.con_id = rcm.con_id AND rc.last_message_id = rcm.message_id AND rcm.created_at < %s LIMIT 500",
                $inactive_date
            ));

            if (!empty($ids)) {
                $wpdb->query(sprintf('DELETE FROM %s WHERE con_id IN (%s)', $wpdb->prefix . 'rtcl_conversations', implode(',', $ids)));
            }
        }
    }

}