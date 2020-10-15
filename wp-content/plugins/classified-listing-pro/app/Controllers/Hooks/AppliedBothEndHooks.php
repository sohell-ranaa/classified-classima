<?php

namespace Rtcl\Controllers\Hooks;

use Rtcl\Helpers\Text;
use Rtcl\Models\Listing;
use Rtcl\Models\Payment;
use Rtcl\Models\PaymentGateway;
use Rtcl\Models\PaymentGateways;
use Rtcl\Models\Pricing;
use Rtcl\Helpers\Functions;
use Rtcl\Emails\NewListingEmailToOwner;
use Rtcl\Resources\Options;

class AppliedBothEndHooks
{

    static public function init() {

        add_action('rtcl_new_user_created', [__CLASS__, 'new_user_notification_email_admin'], 10);
        add_action('rtcl_new_user_created', [__CLASS__, 'new_user_notification_email_user'], 10, 2);
        add_action('rtcl_listing_form_after_save_or_update', [__CLASS__, 'new_post_notification_email_user_submitted'], 10, 4);
        add_action('rtcl_listing_form_after_save_or_update', [__CLASS__, 'new_post_notification_email_user_published'], 20, 4);
        add_action('rtcl_listing_form_after_save_or_update', [__CLASS__, 'new_post_notification_email_admin'], 30, 2);
        add_action('rtcl_listing_form_after_save_or_update', [__CLASS__, 'update_post_notification_email_admin'], 40, 2);

        add_filter('rtcl_my_account_endpoint', [__CLASS__, 'my_account_end_point_filter'], 10);
        add_filter('rtcl_account_menu_item_classes', [__CLASS__, 'my_account_menu_item_classes_filter_edit_account_for_wc'], 10, 3);

        add_filter('rtcl_account_menu_item_classes', [__CLASS__, 'my_account_menu_item_classes_filter_chat'], 10, 3);

        add_action('rtcl_listing_form_price_unit', [__CLASS__, 'rtcl_listing_form_price_unit_cb'], 10, 2);
        add_filter('rtcl_formatted_price_html', [__CLASS__, 'add_on_call_text_at_price'], 10, 2);
        add_action('rtcl_price_meta_html', [__CLASS__, 'add_price_unit_to_price'], 10);
        add_action('rtcl_price_meta_html', [__CLASS__, 'add_price_type_to_price'], 20);
        self::applyHook();


        add_filter('rtcl_checkout_validation_errors', [__CLASS__, 'add_rtcl_checkout_validation'], 10, 4);
        add_filter('rtcl_checkout_process_new_payment_args', [__CLASS__, 'add_listing_id_at_regular_payment'], 10, 4);

        add_action('rtcl_checkout_process_success', [__CLASS__, 'add_checkout_process_notice'], 10);
        add_action('clear_auth_cookie', [__CLASS__, 'offline_beacon']);
        add_filter('rtcl_listing_get_custom_field_group_ids', [__CLASS__, 'get_custom_field_group_ids'], 10, 2);
    }

    static function get_custom_field_group_ids($ids, $category_id) {
        $group_ids = is_array($ids) && !empty($ids) ? $ids : [];
        // Get category fields
        if ($category_id > 0) {

            // Get global fields
            $args = array(
                'post_type'        => rtcl()->post_type_cfg,
                'post_status'      => 'publish',
                'posts_per_page'   => -1,
                'fields'           => 'ids',
                'suppress_filters' => false,
                'meta_query'       => array(
                    array(
                        'key'   => 'associate',
                        'value' => 'all'
                    ),
                )
            );

            $group_ids = get_posts($args);

            $args = array(
                'post_type'        => rtcl()->post_type_cfg,
                'post_status'      => 'publish',
                'posts_per_page'   => -1,
                'fields'           => 'ids',
                'suppress_filters' => false,
                'tax_query'        => array(
                    array(
                        'taxonomy'         => rtcl()->category,
                        'field'            => 'term_id',
                        'terms'            => $category_id,
                        'include_children' => false,
                    ),
                ),
                'meta_query'       => array(
                    array(
                        'key'   => 'associate',
                        'value' => 'categories'
                    ),
                )
            );

            $category_groups = get_posts($args);

            $group_ids = array_merge($group_ids, $category_groups);
            $group_ids = array_unique($group_ids);

        }

        return $group_ids;
    }

    static function offline_beacon() {
        update_user_meta(get_current_user_id(), 'online_status', 0);
    }

    /**
     * @param Payment $payment
     */
    static function add_checkout_process_notice($payment) {
        if ($payment->gateway) {
            if ('paypal' === $payment->gateway->id) {
                Functions::add_notice(__("Redirecting to paypal.", "classified-listing"), 'success');
            } else if ('offline' === $payment->gateway->id) {
                Functions::add_notice(__("Payment made pending confirmation.", "classified-listing"), 'success');
            } else {
                Functions::add_notice(__("Payment successfully made.", "classified-listing"), 'success');
            }
        }
    }

    /**
     * @param \WP_Error      $errors
     * @param array          $checkout_data
     * @param Pricing        $pricing
     * @param PaymentGateway $gateway
     *
     * @return \WP_Error
     */
    static function add_rtcl_checkout_validation($errors, $checkout_data, $pricing, $gateway) {
        if (!$pricing || ($pricing && !is_a($pricing, Pricing::class)) || ($pricing && is_a($pricing, Pricing::class) && !$pricing->exists())) {
            $errors->add('rtcl_checkout_error_empty_pricing', __("No pricing selected to make payment.", "classified-listing"));
        }
        if (!$gateway || !is_object($gateway)) {
            $errors->add('rtcl_checkout_error_empty_payment_gateway', __("No payment Gateway selected.", "classified-listing"));
        }

        if (($pricing && 'regular' === $pricing->getType()) && (!isset($checkout_data['listing_id']) || !rtcl()->factory->get_listing($checkout_data['listing_id']))) {
            $errors->add('rtcl_checkout_error_empty_listing', __("No ad selected to make payment.", "classified-listing"));
        }

        return $errors;
    }

    /**
     * @param array          $new_payment_args
     * @param Pricing        $pricing
     * @param PaymentGateway $gateway
     * @param array          $checkout_data
     *
     * @return array
     */
    static function add_listing_id_at_regular_payment($new_payment_args, $pricing, $gateway, $checkout_data) {
        if ($pricing && 'regular' === $pricing->getType()) {
            $new_payment_args['meta_input']['listing_id'] = isset($checkout_data['listing_id']) ? absint($checkout_data['listing_id']) : 0;
        }

        return $new_payment_args;
    }

    /**
     * @param $formatted_price_html
     * @param $listing Listing
     *
     * @return mixed|void
     */
    public static function add_on_call_text_at_price($formatted_price_html, $listing) {
        if (is_a($listing, Listing::class)) {
            if ($listing->get_price_type() == "on_call") {
                $formatted_price_html = sprintf('<span class="rtcl-price-type-label rtcl-on_call">%s</span>', esc_html(Text::price_type_on_call())
                );
            }
        }

        return $formatted_price_html;
    }

    /**
     * @param $listing Listing
     *
     */
    public static function add_price_type_to_price($listing) {
        if (is_a($listing, Listing::class)) {
            $is_single = Functions::get_option_item('rtcl_moderation_settings', 'display_options_detail', 'price_type', 'multi_checkbox');
            $is_listing = Functions::get_option_item('rtcl_moderation_settings', 'display_options', 'price_type', 'multi_checkbox');
            if (($is_single && is_singular(rtcl()->post_type)) || ($is_listing && !is_singular(rtcl()->post_type))) {
                $price_type = $listing->get_price_type();
                $price_type_html = null;
                if ($price_type == "negotiable") {
                    $price_type_html = sprintf('<span class="rtcl-price-type-label rtcl-price-type-negotiable">(%s)</span>', esc_html(Text::price_type_negotiable()));
                } elseif ($price_type == "fixed") {
                    $price_type_html = sprintf('<span class="rtcl-price-type-label rtcl-price-type-fixed">(%s)</span>', esc_html(Text::price_type_fixed()));
                }
                echo apply_filters('rtcl_add_price_type_to_price', $price_type_html, $price_type, $listing);
            }
        }
    }

    /**
     * @param $listing Listing
     */
    public static function add_price_unit_to_price($listing) {
        if (is_a($listing, Listing::class) && $listing->get_price_type() !== 'on_call' && $price_unit = $listing->get_price_unit()) {
            $price_unit_html = null;
            $price_units = Options::get_price_unit_list();
            if (in_array($price_unit, array_keys($price_units))) {
                $price_unit_html = sprintf('<span class="rtcl-price-unit-label rtcl-price-unit-%s">%s</span>', $price_unit, $price_units[$price_unit]['short']);
            }
            echo apply_filters('rtcl_add_price_unit_to_price', $price_unit_html, $price_unit, $listing);
        }
    }


    static function my_account_menu_item_classes_filter_edit_account_for_wc($classes, $endpoint, $query_vars) {
        if ($endpoint === 'edit-account' && Functions::is_woo_activated() && isset($query_vars['rtcl_edit_account']) && $query_vars['rtcl_edit_account'] === $endpoint && !in_array('is-active', $classes)) {
            $classes[] = 'is-active';
        }

        return $classes;
    }

    static function my_account_menu_item_classes_filter_chat($classes, $endpoint) {
        if ($endpoint === 'chat') {
            $classes[] = 'rtcl-chat-unread-count';
        }

        return $classes;
    }

    /**
     * @param $endpoints
     *
     * @return mixed
     */
    public static function my_account_end_point_filter($endpoints) {

        // Remove payment endpoint
        if (Functions::is_payment_disabled()) {
            unset($endpoints['payments']);
        }

        if (!Functions::is_enable_chat()) {
            unset($endpoints['chat']);
        }

        if (!Functions::get_option_item('rtcl_account_settings', 'user_verification', '', 'checkbox')) {
            unset($endpoints['verify']);
        }

        // Remove favourites endpoint
        if (Functions::is_favourites_disabled()) {
            unset($endpoints['favourites']);
        }

        return $endpoints;
    }

    static public function new_user_notification_email_admin($user_id) {
        if (Functions::get_option_item('rtcl_email_settings', 'notify_admin', 'register_new_user', 'multi_checkbox')) {
            rtcl()->mailer()->emails['User_New_Registration_Email_To_Admin']->trigger($user_id);
        }
    }

    static public function new_user_notification_email_user($user_id, $new_user_data) {
        rtcl()->mailer()->emails['User_New_Registration_Email_To_User']->trigger($user_id, $new_user_data);
    }

    static public function update_post_notification_email_admin($post_id, $type) {
        if ($type == 'update' && Functions::get_option_item('rtcl_email_settings', 'notify_admin', 'listing_edited', 'multi_checkbox')) {
            rtcl()->mailer()->emails['Listing_Update_Email_To_Admin']->trigger($post_id);
        }
    }

    static public function new_post_notification_email_admin($post_id, $type) {
        if ($type == 'new' && Functions::get_option_item('rtcl_email_settings', 'notify_admin', 'listing_submitted', 'multi_checkbox')) {
            rtcl()->mailer()->emails['Listing_Submitted_Email_To_Admin']->trigger($post_id);
        }
    }

    static public function new_post_notification_email_user_submitted($post_id, $type, $cat_id, $new_listing_status) {
        if ($type == 'new' && Functions::get_option_item('rtcl_email_settings', 'notify_users', 'listing_submitted', 'multi_checkbox') && $new_listing_status !== 'publish') {
            rtcl()->mailer()->emails['Listing_Submitted_Email_To_Owner']->trigger($post_id);
        }
    }

    static public function new_post_notification_email_user_published($post_id, $type, $cat_id, $new_listing_status) {
        if ($type == 'new' && Functions::get_option_item('rtcl_email_settings', 'notify_users', 'listing_published', 'multi_checkbox') && $new_listing_status === 'publish') {
            rtcl()->mailer()->emails['Listing_Published_Email_To_Owner']->trigger($post_id);
        }
    }

    /**
     * @param     $listing Listing
     * @param int $category_id
     */
    static public function rtcl_listing_form_price_unit_cb($listing, $category_id = 0) {
        echo Functions::get_listing_form_price_unit_html($category_id, $listing);
    }

    private static function applyHook() {
        /**
         * Short Description (excerpt).
         */
        if (function_exists('do_blocks')) {
            add_filter('rtcl_short_description', 'do_blocks', 9);
        }
        add_filter('rtcl_short_description', 'wptexturize');
        add_filter('rtcl_short_description', 'convert_smilies');
        add_filter('rtcl_short_description', 'convert_chars');
        add_filter('rtcl_short_description', 'wpautop');
        add_filter('rtcl_short_description', 'shortcode_unautop');
        add_filter('rtcl_short_description', 'prepend_attachment');
        add_filter('rtcl_short_description', 'do_shortcode', 11); // After wpautop().
        add_filter('rtcl_short_description', array(
            Functions::class,
            'format_product_short_description'
        ), 9999999);
        add_filter('rtcl_short_description', array(Functions::class, 'do_oembeds'));
        add_filter('rtcl_short_description', array(
            $GLOBALS['wp_embed'],
            'run_shortcode'
        ), 8); // Before wpautop().
    }

}