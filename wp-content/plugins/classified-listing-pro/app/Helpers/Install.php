<?php

namespace Rtcl\Helpers;


use Rtcl\Models\Roles;

class Install
{

    private static $db_updates = [
        '3.4.4' => array(
            'update_344_recreate_roles',
            'update_344_db_version',
        ),
    ];

    /**
     * Get list of DB update callbacks.
     *
     * @return array
     * @since  1.5.58
     */
    public static function get_db_update_callbacks() {
        return self::$db_updates;

    }

    /**
     * Push all needed DB updates to the queue for processing.
     */
    private static function update() {
        $current_db_version = get_option('woocommerce_db_version');
        $loop = 0;

        foreach (self::get_db_update_callbacks() as $version => $update_callbacks) {
            if (version_compare($current_db_version, $version, '<')) {
                foreach ($update_callbacks as $update_callback) {
                    WC()->queue()->schedule_single(
                        time() + $loop,
                        'woocommerce_run_update_callback',
                        array(
                            'update_callback' => $update_callback,
                        ),
                        'woocommerce-db-updates'
                    );
                    $loop++;
                }
            }
        }
    }


    public static function activate() {

        if (!is_blog_installed()) {
            return;
        }

        // Check if we are not already running this routine.
        if ('yes' === get_transient('rtcl_installing')) {
            return;
        }

        // If we made it till here nothing is running yet, lets set the transient now.
        set_transient('rtcl_installing', 'yes', MINUTE_IN_SECONDS * 10);

        if (!get_option('rtcl_version') || !get_option('rtcl_pro_version')) {
            self::create_options();
        }
        self::create_tables();
        self::create_roles();
        self::upgrade();
        self::update_rtcl_version();

        delete_transient('rtcl_installing');

        do_action('rtcl_flush_rewrite_rules');
        do_action('rtcl_installed');

    }

    private static function update_rtcl_version() {
        delete_option('rtcl_version');
        delete_option('rtcl_pro_version');
        add_option('rtcl_pro_version', RTCL_VERSION);
    }

    private static function create_options() {
        // Insert plugin settings and default values for the first time
        $options = array(
            'rtcl_general_settings'    => array(
                'load_bootstrap'               => array('css', 'js'),
                'include_results_from'         => array('child_categories', 'child_locations'),
                'listings_per_page'            => 20,
                'related_posts_per_page'       => 4,
                'default_view'                 => 'list',
                'orderby'                      => 'date',
                'order'                        => 'desc',
                'taxonomy_orderby'             => 'title',
                'taxonomy_order'               => 'asc',
                'text_editor'                  => 'wp_editor',
                'location_level_first'         => __("State", 'classified-listing'),
                'location_level_second'        => __("City", 'classified-listing'),
                'location_level_third'         => __("Town", 'classified-listing'),
                'currency'                     => 'USD',
                'currency_position'            => 'right',
                'currency_thousands_separator' => ',',
                'currency_decimal_separator'   => '.',
            ),
            'rtcl_moderation_settings' => array(
                'listing_duration'             => 15,
                'new_listing_threshold'        => 3,
                'new_listing_label'            => __("New", 'classified-listing'),
                'popular_listing_threshold'    => 1000,
                'popular_listing_label'        => __("Popular", 'classified-listing'),
                'listing_featured_label'       => __("Featured", 'classified-listing'),
                'listing_top_label'            => __("Top", 'classified-listing'),
                'listing_bump_up_label'        => __("Bump Up", 'classified-listing'),
                'listing_enable_top_listing'   => "yes",
                'listing_top_per_page'         => 2,
                'display_options'              => array(
                    'category',
                    'location',
                    'date',
                    'user',
                    'price',
                    'views',
                    'top',
                    'featured',
                    'new',
                    'popular'
                ),
                'display_options_detail'       => array(
                    'category',
                    'location',
                    'date',
                    'user',
                    'price',
                    'views',
                    'top',
                    'featured',
                    'new',
                    'popular'
                ),
                'detail_page_sidebar_position' => 'right',
                'has_favourites'               => 'yes',
                'has_report_abuse'             => 'yes',
                'has_map'                      => 'yes',
                'has_contact_form'             => 'yes',
                'maximum_images_per_listing'   => 5,
                'delete_expired_listings'      => 15,
                'new_listing_status'           => 'pending',
                'edited_listing_status'        => 'pending'
            ),
            'rtcl_payment_settings'    => array(
                'payment'                      => 'yes',
                'use_https'                    => 'no',
                'currency'                     => 'USD',
                'currency_position'            => 'right',
                'currency_thousands_separator' => ',',
                'currency_decimal_separator'   => '.',
            ),
            'rtcl_payment_offline'     => array(
                'enabled'      => 'yes',
                'title'        => __('Direct Bank Transfer', 'classified-listing'),
                'description'  => __("Make your payment directly in our bank account. Please use your Order ID as payment reference. Your order won't get approved until the funds have cleared in our account.",
                    'classified-listing'),
                'instructions' => __('Make your payment directly in our bank account. Please use your Order ID as payment reference. Your order won\'t get approved until the funds have cleared in our account.
Account details :
		
Account Name : YOUR ACCOUNT NAME
Account Number : YOUR ACCOUNT NUMBER
Bank Name : YOUR BANK NAME
		
If we don\'t receive your payment within 48 hrs, we will cancel the order.', 'classified-listing'),
            ),
            'rtcl_email_settings'      => array(
                'from_name'                  => get_option('blogname'),
                'from_email'                 => get_option('admin_email'),
                'admin_notice_emails'        => get_option('admin_email'),
                'email_type'                 => 'html',
                'notify_admin'               => array(
                    'register_new_user',
                    'listing_submitted',
                    'order_created',
                    'payment_received'
                ),
                'notify_users'               => array(
                    'listing_submitted',
                    'listing_published',
                    'listing_renewal',
                    'listing_expired',
                    'remind_renewal',
                    'order_created',
                    'order_completed'
                ),
                'listing_submitted_subject'  => __('[{site_title}] Listing "{listing_title}" is received', 'classified-listing'),
                'listing_submitted_heading'  => __('Your listing is received', 'classified-listing'),
                'listing_published_subject'  => __('[{site_title}] Listing "{listing_title}" is published', 'classified-listing'),
                'listing_published_heading'  => __('Your listing is published', 'classified-listing'),
                'renewal_email_threshold'    => 3,
                'renewal_subject'            => __('[{site_name}] {listing_title} - Expiration notice', 'classified-listing'),
                'renewal_heading'            => __('Expiration notice', 'classified-listing'),
                'expired_subject'            => __('[{site_title}] {listing_title} - Expiration notice', 'classified-listing'),
                'expired_heading'            => __('Expiration notice', 'classified-listing'),
                'renewal_reminder_threshold' => 3,
                'renewal_reminder_subject'   => __('[{site_title}] {listing_title} - Renewal reminder', 'classified-listing'),
                'renewal_reminder_heading'   => __('Renewal reminder', 'classified-listing'),
                'order_created_subject'      => __('[{site_title}] #{order_number} Thank you for your order', 'classified-listing'),
                'order_created_heading'      => __('New Order: #{order_number}', 'classified-listing'),
                'order_completed_subject'    => __('[{site_title}] : #{order_number} Order is completed.', 'classified-listing'),
                'order_completed_heading'    => __('Payment is completed: #{order_number}', 'classified-listing'),
                'contact_subject'            => __('[{site_title}] Contact via "{listing_title}"', 'classified-listing'),
                'contact_heading'            => __('Thank you for mail', 'classified-listing')
            ),
            'rtcl_account_settings'    => array(
                'enable_myaccount_registration' => "yes"
            ),
            'rtcl_style_settings'      => [
                'primary'      => "#0066bf",
                'link'         => "#111111",
                'link_hover'   => "#0066bf",
                'button'       => "#0066bf",
                'button_hover' => "#3065c1",
                'button_text'  => "#ffffff"
            ],
            'rtcl_misc_settings'       => array(
                'image_size_gallery'           => array('width' => 924, 'height' => 462, 'crop' => 'yes'),
                'image_size_gallery_thumbnail' => array('width' => 150, 'height' => 105, 'crop' => 'yes'),
                'image_size_thumbnail'         => array('width' => 320, 'height' => 240, 'crop' => 'yes'),
                'image_allowed_type'           => array('png', 'jpg', 'jpeg'),
                'image_allowed_memory'         => 2,
                'image_edit_cap'               => 'yes',
                'social_services'              => array('facebook', 'twitter', 'gplus'),
                'social_pages'                 => array('listing')
            ),
            'rtcl_chat_settings'       => [
                'enable'                                => 'yes',
                'unread_message_email'                  => 'yes',
                'remove_inactive_conversation_duration' => 30
            ],
            'rtcl_advanced_settings'   => [
                'permalink'                         => 'rtcl_listing',
                'category_base'                     => _x('listing-category', 'slug', 'classified-listing'),
                'location_base'                     => _x('listing-location', 'slug', 'classified-listing'),
                'myaccount_listings_endpoint'       => 'listings',
                'myaccount_favourites_endpoint'     => 'favourites',
                'myaccount_chat_endpoint'           => 'chat',
                'myaccount_edit_account_endpoint'   => 'edit-account',
                'myaccount_payments_endpoint'       => 'payments',
                'myaccount_lost_password_endpoint'  => 'lost-password',
                'myaccount_verify'                  => 'verify',
                'myaccount_logout_endpoint'         => 'logout',
                'checkout_submission_endpoint'      => 'submission',
                'checkout_promote_endpoint'         => 'promote',
                'checkout_payment_receipt_endpoint' => 'payment-receipt',
                'checkout_payment_failure_endpoint' => 'payment-failure'
            ]
        );

        foreach ($options as $option_name => $defaults) {
            if (false == get_option($option_name)) {
                add_option($option_name, apply_filters($option_name . '_defaults', $defaults));
            }
        }

        $pages = Functions::insert_custom_pages();
        if (!empty($pages)) {
            $pSettings = get_option('rtcl_advanced_settings', array());
            foreach ($pages as $pSlug => $pId) {
                $pSettings[$pSlug] = $pId;
            }
            update_option('rtcl_advanced_settings', $pSettings);
        }
    }

    private static function create_tables() {
        global $wpdb;

        $wpdb->hide_errors();

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta(self::get_schema());
    }

    private static function get_schema() {
        global $wpdb;

        $collate = '';

        if ($wpdb->has_cap('collation')) {
            $collate = $wpdb->get_charset_collate();
        }
        $table_schema = [];
        $table_schema[] = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}rtcl_sessions (
						  session_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
						  session_key char(32) NOT NULL,
						  session_value longtext NOT NULL,
						  session_expiry BIGINT UNSIGNED NOT NULL,
						  PRIMARY KEY  (session_key),
						  UNIQUE KEY session_id (session_id)
						) $collate;";
        $chat_schema = self::get_chat_table_schema();
        if (!empty($chat_schema)) {
            $table_schema = array_merge($table_schema, $chat_schema);
        }


        return $table_schema;
    }

    /**
     * @return array
     */
    static function get_chat_table_schema() {
        global $wpdb;

        $collate = '';

        if ($wpdb->has_cap('collation')) {
            $collate = $wpdb->get_charset_collate();
        }
        $conversation_table_name = $wpdb->prefix . "rtcl_conversations";
        $conversation_message_table_name = $wpdb->prefix . "rtcl_conversation_messages";
        $table_schema = [];
        /* We can not use (IF NOT EXISTS) when we use Foreign key REFERENCE declaration inside the table schema
        At this situation will create an sql ERROR : foreign key can't be added with the table because of tablet was not create yet
        SOLUTION 1: First need to check is table is already created or not
        SOLUTION 2: We can first create table except declaring the foreign ke reference and after then we can make the ALTER table using FOREIGN KEY REFERENCE
        */

        if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $conversation_table_name)) !== $conversation_table_name) {
            $table_schema[] = "CREATE TABLE `{$conversation_table_name}` (
                          `con_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                          `listing_id` bigint(20) unsigned NOT NULL,
                          `sender_id` int(10) unsigned NOT NULL,
                          `recipient_id` int(10) unsigned NOT NULL,
                          `sender_delete` tinyint(1) NOT NULL DEFAULT '0',
                          `recipient_delete` tinyint(1) NOT NULL DEFAULT '0',
                          `last_message_id` int(10) unsigned DEFAULT NULL,
                          `sender_review` tinyint(3) unsigned NOT NULL DEFAULT '0',
                          `recipient_review` tinyint(3) unsigned NOT NULL DEFAULT '0',
                          `invert_review` tinyint(3) unsigned NOT NULL DEFAULT '0',
                          `created_at` timestamp NOT NULL,
                          PRIMARY KEY (`con_id`),
                          KEY `rtcl_conversations_listing_id_index` (`listing_id`),
                          CONSTRAINT `rtcl_conversations_listing_id_foreign` FOREIGN KEY (`listing_id`) REFERENCES `{$wpdb->prefix}posts` (`ID`) ON DELETE CASCADE
                        ) $collate;";
        }
        if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $conversation_message_table_name)) !== $conversation_message_table_name) {
            $table_schema[] = "CREATE TABLE `{$conversation_message_table_name}` (
                      `message_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                      `con_id` bigint(20) unsigned NOT NULL,
                      `source_id` int(10) unsigned NOT NULL,
                      `message` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
                      `is_read` tinyint(1) NOT NULL DEFAULT '0',
                      `created_at` timestamp NOT NULL,
                      PRIMARY KEY (`message_id`),
                      KEY `rtcl_conversation_messages_con_id_index` (`con_id`),
                      CONSTRAINT `rtcl_conversation_messages_con_id_foreign` FOREIGN KEY (`con_id`) REFERENCES `{$wpdb->prefix}rtcl_conversations` (`con_id`) ON DELETE CASCADE
                    ) $collate;";
        }

        return $table_schema;
    }

    private static function upgrade() {

    }


    public static function deactivate() {
        Roles::remove_default_caps(); //TODO Need to remove in 4 - 5 version later
        // Un-schedules all previously-scheduled cron jobs
        wp_clear_scheduled_hook('rtcl_hourly_scheduled_events');
        wp_clear_scheduled_hook('rtcl_twicedaily_scheduled_events');
    }

    public static function create_roles() {
        $old_version = get_option('rtcl_pro_version');
        if ($old_version && version_compare($old_version, '1.5.59') < 0) { //TODO Need to remove in 4 - 5 version later
            Roles::remove_default_caps();
        }
        Roles::create_roles();
    }
}