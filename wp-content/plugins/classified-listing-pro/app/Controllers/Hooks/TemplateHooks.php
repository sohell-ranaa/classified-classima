<?php

namespace Rtcl\Controllers\Hooks;

use Rtcl\Helpers\Functions;
use Rtcl\Helpers\Link;
use Rtcl\Helpers\Text;
use Rtcl\Models\Listing;
use Rtcl\Resources\Options;
use Rtcl\Shortcodes\Checkout;
use Rtcl\Shortcodes\MyAccount;
use Rtcl\Widgets\Filter;
use WP_Query;

class TemplateHooks
{
    static function init() {
        add_filter('body_class', [__CLASS__, 'body_class']);
        add_filter('post_class', [__CLASS__, 'listing_post_class'], 20, 3);

        /**
         * Listing form hook
         */
        add_action("rtcl_listing_form", [__CLASS__, 'listing_category'], 5);
        add_action("rtcl_listing_form", [__CLASS__, 'listing_information'], 10);
        add_action("rtcl_listing_form", [__CLASS__, 'listing_gallery'], 20);
        add_action("rtcl_listing_form", [__CLASS__, 'listing_contact'], 30);
        add_action("rtcl_listing_form", [__CLASS__, 'listing_recaptcha'], 40);
        add_action("rtcl_listing_form", [__CLASS__, 'listing_terms_conditions'], 50);
        add_action("rtcl_listing_form_end", [__CLASS__, 'add_listing_form_hidden_field'], 10);
        add_action("rtcl_listing_form_end", [__CLASS__, 'add_wpml_support'], 20);
        add_action("rtcl_widget_filter_form", [__CLASS__, 'add_wpml_support']);
        add_action("rtcl_widget_search_inline_form", [__CLASS__, 'add_wpml_support']);
        add_action("rtcl_widget_search_vertical_form", [__CLASS__, 'add_wpml_support']);


        /**
         * Content Wrappers.
         *
         * @see output_content_wrapper()
         * @see breadcrumb()
         * @see output_content_wrapper_end()
         */
        add_action('rtcl_before_main_content', array(__CLASS__, 'output_content_wrapper'), 10);
        add_action('rtcl_before_main_content', [__CLASS__, 'breadcrumb'], 20);
        add_action('rtcl_after_main_content', array(__CLASS__, 'output_content_wrapper_end'), 10);
        /**
         *
         * Sidebar.
         *
         * @see get_sidebar()
         */
        add_action('rtcl_sidebar', array(__CLASS__, 'get_sidebar'), 10);

        add_action('rtcl_archive_description', [__CLASS__, 'taxonomy_archive_description'], 10);
        add_action('rtcl_archive_description', [__CLASS__, 'listing_archive_description'], 10);

        add_action('rtcl_top_listings', [__CLASS__, 'top_listing_items'], 20);

        /**
         * Reviews
         *
         */
        add_action('rtcl_review_before', [__CLASS__, 'rtcl_review_display_gravatar'], 10);
        add_action('rtcl_review_meta', [__CLASS__, 'rtcl_review_display_meta'], 10);
        add_action('rtcl_review_after_meta', [__CLASS__, 'rtcl_review_display_rating'], 10);
        add_action('rtcl_review_comment_text', [__CLASS__, 'rtcl_review_display_comment_title'], 10);
        add_action('rtcl_review_comment_text', [__CLASS__, 'rtcl_review_display_comment_text'], 20);

        add_action('rtcl_before_listing_loop', [__CLASS__, 'listing_actions'], 20);
        add_action('rtcl_listing_loop_action', [__CLASS__, 'result_count'], 10);
        add_action('rtcl_listing_loop_action', [__CLASS__, 'catalog_ordering'], 20);
        add_action('rtcl_listing_loop_action', [__CLASS__, 'view_switcher'], 30);
        add_action('rtcl_no_listings_found', [__CLASS__, 'no_listings_found']);

        add_action('rtcl_listing_loop_item_start', [__CLASS__, 'listing_thumbnail']);


        add_action('rtcl_listing_loop_item', [__CLASS__, 'loop_item_wrapper_start'], 10);
        add_action('rtcl_listing_loop_item', [__CLASS__, 'loop_item_listing_title'], 20);
        add_action('rtcl_listing_loop_item', [__CLASS__, 'loop_item_labels'], 30);
        add_action('rtcl_listing_loop_item', [__CLASS__, 'loop_item_listable_fields'], 40);
        add_action('rtcl_listing_loop_item', [__CLASS__, 'loop_item_meta'], 50);
        add_action('rtcl_listing_loop_item', [__CLASS__, 'loop_item_excerpt'], 60);
        add_action('rtcl_listing_loop_item', [__CLASS__, 'loop_item_wrapper_end'], 100);

        add_action('rtcl_listing_loop_item_end', [__CLASS__, 'listing_price']);

        add_action('rtcl_after_listing_loop', [__CLASS__, 'pagination'], 10);

        /**
         * Notice
         */
        add_action('rtcl_before_listing_loop', [__CLASS__, 'output_all_notices'], 10);

        add_action('rtcl_account_navigation', [__CLASS__, 'account_navigation']);
        add_action('rtcl_account_content', [__CLASS__, 'account_content']);
        add_action('rtcl_account_listings_endpoint', [__CLASS__, 'account_listings_endpoint']);
        add_action('rtcl_account_favourites_endpoint', [__CLASS__, 'account_favourites_endpoint']);
        add_action('rtcl_account_chat_endpoint', [__CLASS__, 'account_chat_endpoint']);

        add_action('rtcl_account_edit-account_endpoint', [__CLASS__, 'account_edit_account_endpoint']);
        add_action('rtcl_account_rtcl_edit_account_endpoint', [__CLASS__, 'account_edit_account_endpoint']);

        add_action('rtcl_account_payments_endpoint', [__CLASS__, 'account_payments_endpoint']);

        add_action('rtcl_checkout_content', [__CLASS__, 'checkout_content']);
        add_action('rtcl_checkout_submission_endpoint', [__CLASS__, 'checkout_submission_endpoint'], 10, 2);
        add_action('rtcl_checkout_payment-receipt_endpoint', [
            __CLASS__,
            'checkout_payment_receipt_endpoint'
        ], 10, 2);

        add_action('rtcl_account_dashboard', [__CLASS__, 'user_information']);

        add_action('rtcl_single_listing_content', [__CLASS__, 'add_single_listing_title'], 5);
        add_action('rtcl_single_listing_content', [__CLASS__, 'add_single_listing_meta'], 10);
        add_action('rtcl_single_listing_content', [__CLASS__, 'add_single_listing_gallery'], 20);
        add_action('rtcl_single_listing_sidebar', [__CLASS__, 'add_single_listing_sidebar'], 10);
        add_action('rtcl_single_listing_inner_sidebar', [__CLASS__, 'add_single_listing_inner_sidebar_custom_field'], 10);
        add_action('rtcl_single_listing_inner_sidebar', [__CLASS__, 'add_single_listing_inner_sidebar_action'], 20);
        if (!Functions::get_option_item('rtcl_account_settings', 'disable_name_phone_registration', false, 'checkbox')) {
            add_action('rtcl_register_form_start', [__CLASS__, 'add_name_fields_at_registration_form'], 10);
            add_action('rtcl_register_form_start', [__CLASS__, 'add_phone_at_registration_form'], 20);
        } else {
            add_filter('rtcl_registration_name_validation', __return_false());
            add_filter('rtcl_registration_phone_validation', __return_false());
        }


        /**
         * Check out form
         */
        add_action('rtcl_before_checkout_form', [__CLASS__, 'add_checkout_form_instruction'], 10);
        add_action('rtcl_checkout_form_start', [__CLASS__, 'add_checkout_form_promotion_options'], 10, 2);
        add_action('rtcl_checkout_form', [__CLASS__, 'add_checkout_payment_method'], 10);
        add_action('rtcl_checkout_form', [__CLASS__, 'checkout_terms_and_conditions'], 50);
        add_action('rtcl_checkout_form_submit_button', [__CLASS__, 'checkout_form_submit_button'], 10);
        add_action('rtcl_checkout_form_end', [__CLASS__, 'add_checkout_hidden_field'], 50);
        add_action('rtcl_checkout_form_end', [__CLASS__, 'add_submission_checkout_hidden_field'], 60, 2);

        add_action('rtcl_checkout_terms_and_conditions', [__CLASS__, 'checkout_privacy_policy_text'], 20);
        add_action('rtcl_checkout_terms_and_conditions', [__CLASS__, 'checkout_terms_and_conditions_page_content'], 30);

        /**
         * Misc Hooks
         */
        add_action('rtcl_widget_after_filter_form', [__CLASS__, 'add_hidden_field_filter_form'], 50);
        add_action('rtcl_login_form_end', [__CLASS__, 'social_login_shortcode'], 10);
        add_action('rtcl_register_form', [__CLASS__, 'registration_privacy_policy_text'], 20);
    }


    /**
     * @param int $post_id
     *
     * @throws \Exception
     */
    public static function listing_category($post_id) {
        if ($post_id) {
            $category_id = wp_get_object_terms($post_id, rtcl()->category, array('fields' => 'ids'));
            $category_id = (is_array($category_id) && !empty($category_id)) ? end($category_id) : 0;
            $selected_type = get_post_meta($post_id, 'ad_type', true);
        } else {
            $category_id = isset($_GET['category']) ? absint($_GET['category']) : 0;
            $selected_type = (isset($_GET['type']) && in_array($_GET['type'], array_keys(Functions::get_listing_types()))) ? $_GET['type'] : '';
        }
        Functions::get_template("listing-form/category-section", compact('post_id', 'category_id', 'selected_type'));
    }

    /**
     * @param int $post_id
     *
     * @throws \Exception
     */
    public static function listing_information($post_id) {
        if ($post_id) {
            $category_id = wp_get_object_terms($post_id, rtcl()->category, array('fields' => 'ids'));
            $category_id = (is_array($category_id) && !empty($category_id)) ? end($category_id) : 0;
            $selected_type = get_post_meta($post_id, 'ad_type', true);
        } else {
            $category_id = isset($_GET['category']) ? absint($_GET['category']) : 0;
            $selected_type = (isset($_GET['type']) && in_array($_GET['type'], array_keys(Functions::get_listing_types()))) ? $_GET['type'] : '';
        }
        $general_settings = Functions::get_option('rtcl_general_settings');
        $moderation_settings = Functions::get_option('rtcl_moderation_settings');
        $editor = !empty($general_settings['text_editor']) ? $general_settings['text_editor'] : 'wp_editor';
        $price = $post_content = $price_type = $title = '';
        $listing = null;
        if ($post_id > 0) {
            $listing = new Listing($post_id);
            $category_id = wp_get_object_terms($post_id, rtcl()->category, array('fields' => 'ids'));
            $category_id = (is_array($category_id) && !empty($category_id)) ? end($category_id) : 0;
            $price_type = get_post_meta($post_id, 'price_type', true);
            $price = get_post_meta($post_id, 'price', true);
            global $post;
            $post = get_post($post_id);
            setup_postdata($post);
            $title = get_the_title();
            $post_content = get_the_content();
            wp_reset_postdata();
        }
        Functions::get_template("listing-form/information", array(
            'listing'           => $listing,
            'post_id'           => $post_id,
            'title'             => $title,
            'post_content'      => $post_content,
            'price'             => $price,
            'price_type'        => $price_type,
            'editor'            => $editor,
            'category_id'       => $category_id,
            'selected_type'     => $selected_type,
            'title_limit'       => Functions::get_title_character_limit(),
            'description_limit' => Functions::get_description_character_limit(),
            'parent_cat_id'     => 0,
            'child_cat_id'      => 0,
            'hidden_fields'     => (!empty($moderation_settings['hide_form_fields'])) ? $moderation_settings['hide_form_fields'] : array()
        ));
    }

    /**
     * @param $post_id
     */
    public static function listing_gallery($post_id) {
        if (!Functions::is_gallery_disabled()) {
            Functions::get_template("listing-form/gallery", compact('post_id'));
        }
    }

    public static function listing_recaptcha($post_id) {
        $settings = Functions::get_option_item('rtcl_misc_settings', 'recaptcha_forms', array());
        if (!empty($settings) && is_array($settings) && in_array('listing', $settings)) {
            Functions::get_template("listing-form/recaptcha", compact('post_id'));
        }
    }

    public static function listing_terms_conditions($post_id) {
        $agreed = get_post_meta($post_id, 'rtcl_agree', true);
        Functions::get_template("listing-form/terms-conditions", compact('post_id', 'agreed'));
    }

    public static function listing_contact($post_id) {

        $location_id = $sub_location_id = $sub_sub_location_id = 0;
        $user_id = get_current_user_id();
        $user = get_userdata($user_id);
        $email = $user ? $user->user_email : '';
        $phone = get_user_meta($user_id, '_rtcl_phone', true);
        $whatsapp_number = get_user_meta($user_id, '_rtcl_whatsapp_number', true);
        $website = get_user_meta($user_id, '_rtcl_website', true);
        $selected_locations = (array)get_user_meta($user_id, '_rtcl_location', true);
        $zipcode = get_user_meta($user_id, '_rtcl_zipcode', true);
        $address = get_user_meta($user_id, '_rtcl_address', true);
        $latitude = get_user_meta($user_id, '_rtcl_latitude', true);
        $longitude = get_user_meta($user_id, '_rtcl_longitude', true);
        $has_map = Functions::get_option_item('rtcl_moderation_settings', 'has_map', false, 'checkbox');
        $hide_map = false;

        if ($post_id) {
            $selected_locations = wp_get_object_terms($post_id, rtcl()->location, array('fields' => 'ids'));
            $latitude = get_post_meta($post_id, 'latitude', true);
            $longitude = get_post_meta($post_id, 'longitude', true);
            $hide_map = get_post_meta($post_id, 'hide_map', true);
            $zipcode = get_post_meta($post_id, 'zipcode', true);
            $address = get_post_meta($post_id, 'address', true);
            $phone = get_post_meta($post_id, 'phone', true);
            $whatsapp_number = get_post_meta($post_id, '_rtcl_whatsapp_number', true);
            $email = get_post_meta($post_id, 'email', true);
            $website = get_post_meta($post_id, 'website', true);
        }
        $state_text = Text::location_level_first();
        $city_text = Text::location_level_second();
        $town_text = Text::location_level_third();
        $moderation_settings = Functions::get_option('rtcl_moderation_settings');

        Functions::get_template("listing-form/contact", array(
            'post_id'                    => $post_id,
            'state_text'                 => $state_text,
            'city_text'                  => $city_text,
            'town_text'                  => $town_text,
            'selected_locations'         => $selected_locations,
            'latitude'                   => $latitude,
            'longitude'                  => $longitude,
            'zipcode'                    => $zipcode,
            'address'                    => $address,
            'phone'                      => $phone,
            'whatsapp_number'            => $whatsapp_number,
            'email'                      => $email,
            'website'                    => $website,
            'location_id'                => $location_id,
            'sub_location_id'            => $sub_location_id,
            'sub_sub_location_id'        => $sub_sub_location_id,
            'has_map'                    => $has_map,
            'hide_map'                   => $hide_map,
            'hidden_fields'              => (!empty($moderation_settings['hide_form_fields'])) ? $moderation_settings['hide_form_fields'] : array(),
            'enable_post_for_unregister' => !is_user_logged_in() && Functions::get_option_item('rtcl_account_settings', 'enable_post_for_unregister', '', 'checkbox')
        ));
    }

    public static function add_wpml_support($post_id) {
        if (function_exists('icl_object_id') && isset($_REQUEST['lang'])) {
            echo sprintf('<input type="hidden" name="lang" value="%s" />', esc_attr($_REQUEST['lang']));
        }
    }

    public static function add_listing_form_hidden_field($post_id) {
        echo sprintf('<input type="hidden" name="_post_id" id="_post_id" value="%d"/>', esc_attr($post_id));
        wp_nonce_field(rtcl()->nonceText, rtcl()->nonceId);
        if (!$post_id) {
            $category_id = isset($_GET['category']) ? absint($_GET['category']) : 0;
            $selected_type = (isset($_GET['type']) && in_array($_GET['type'], array_keys(Functions::get_listing_types()))) ? $_GET['type'] : '';
            echo sprintf('<input type="hidden" name="_category_id" id="category-id" value="%d"/>', esc_attr($category_id));
            echo sprintf('<input type="hidden" name="_ad_type" id="ad-type" value="%s"/>', esc_attr($selected_type));
        }
    }

    static function add_name_fields_at_registration_form() {
        ?>
        <div class="form-group row">
            <div class="col-md-6">
                <label for="rtcl-reg-first-name" class="control-label">
                    <?php esc_html_e('First Name', 'classified-listing'); ?>
                    <strong class="rtcl-required">*</strong>
                </label>
                <input type="text" name="first_name" id="rtcl-reg-first-name"
                       value="<?php if (!empty($_POST['first_name'])) esc_attr_e($_POST['first_name']); ?>"
                       class="form-control" required/>
            </div>
            <div class="col-md-6">
                <label for="rtcl-reg-last-name" class="control-label">
                    <?php esc_html_e('Last Name', 'classified-listing'); ?>
                    <strong class="rtcl-required">*</strong>
                </label>
                <input type="text" name="last_name"
                       value="<?php if (!empty($_POST['last_name'])) esc_attr_e($_POST['last_name']); ?>"
                       id="rtcl-reg-last-name" class="form-control" required/>
            </div>
        </div>
        <?php
    }

    static function add_phone_at_registration_form() {
        ?>
        <div class="form-group">
            <label for="rtcl-reg-phone" class="control-label">
                <?php esc_html_e('Phone Number', 'classified-listing'); ?>
                <strong class="rtcl-required">*</strong>
            </label>
            <input type="text" name="phone"
                   value="<?php if (!empty($_POST['phone'])) esc_attr_e($_POST['phone']); ?>"
                   id="rtcl-reg-phone" class="form-control" required/>
        </div>
        <?php
    }

    static function add_single_listing_sidebar() {
        Functions::get_template("listing/listing-sidebar");
    }

    static function add_single_listing_inner_sidebar_custom_field() {
        global $listing;
        $listing->the_custom_fields();
    }

    static function add_single_listing_inner_sidebar_action() {
        global $listing;
        $listing->the_actions();
    }

    static function add_single_listing_gallery() {
        global $listing;
        $listing->the_gallery();
    }

    static function add_single_listing_meta() {
        global $listing;
        ?>
        <!-- Meta data -->
        <div class="rtcl-listing-meta mb-3">
            <?php $listing->the_labels(); ?>
            <?php $listing->the_meta(); ?>
        </div>
        <?php
    }

    static function add_single_listing_title() {
        global $listing;
        ?>
        <div class="rtcl-listing-title"><h2 class="entry-title"><?php $listing->the_title(); ?></h2></div>
        <?php
    }


    static function listing_actions() {
        Functions::get_template('listing/loop/actions');
    }

    static function pagination() {
        Functions::pagination();
    }


    /**
     * Output the Listing sorting options.
     */
    static function catalog_ordering() {
        if (!Functions::get_loop_prop('is_paginated')) {
            return;
        }
        $orderby = Functions::get_option_item('rtcl_general_settings', 'orderby');
        $order = Functions::get_option_item('rtcl_general_settings', 'order');
        $orderby_order = $orderby . "-" . $order;
        $is_orderby_selected = 'date-desc' === apply_filters('rtcl_default_catalog_orderby', $orderby_order);
        $catalog_orderby_options = Options::get_listing_orderby_options();

        $default_orderby = Functions::get_loop_prop('is_search') ? 'relevance' : $orderby_order;
        $orderby = isset($_GET['orderby']) ? Functions::clean(wp_unslash($_GET['orderby'])) : $default_orderby; // WPCS: sanitization ok, input var ok, CSRF ok.

        if (Functions::get_loop_prop('is_search')) {
            $catalog_orderby_options = array_merge(array('relevance' => __('Relevance', 'classified-listing')), $catalog_orderby_options);

            unset($catalog_orderby_options['menu_order']);
        }

        if (!$is_orderby_selected) {
            unset($catalog_orderby_options['date-desc']);
        }

        if (!array_key_exists($orderby, $catalog_orderby_options)) {
            $orderby = current(array_keys($catalog_orderby_options));
        }

        Functions::get_template(
            'listing/loop/orderby',
            array(
                'catalog_orderby_options' => $catalog_orderby_options,
                'orderby'                 => $orderby,
                'is_orderby_selected'     => $is_orderby_selected,
            )
        );
    }

    /**
     * Output the Listing view switcher
     */
    static function view_switcher() {
        $views = Options::get_listings_view_options();
        $default_view = Functions::get_option_item('rtcl_general_settings', 'default_view', 'list');
        $current_view = (!empty($_GET['view']) && array_key_exists($_GET['view'], $views)) ? $_GET['view'] : $default_view;
        Functions::get_template(
            'listing/loop/view-switcher',
            compact('views', 'current_view', 'default_view')
        );
    }


    /**
     * Output the result count text (Showing x - x of x results).
     */
    static function result_count() {
        if (!Functions::get_loop_prop('is_paginated')) {
            return;
        }
        $args = array(
            'total'    => Functions::get_loop_prop('total'),
            'per_page' => Functions::get_loop_prop('per_page'),
            'current'  => Functions::get_loop_prop('current_page'),
        );

        Functions::get_template('listing/loop/result-count', $args);
    }

    /**
     * Outputs all queued notices on.
     *
     * @since 1.5.5
     */
    static function output_all_notices() {
        echo '<div class="rtcl-notices-wrapper">';
        Functions::print_notices();
        echo '</div>';
    }

    static function loop_item_excerpt() {
        global $listing;
        if ($listing->can_show_excerpt()) {
            $listing->the_excerpt();
        }
    }

    static function loop_item_meta() {
        global $listing;
        $listing->the_meta();
    }

    static function loop_item_listable_fields() {
        global $listing;
        $listing->the_listable_fields();
    }

    static function loop_item_labels() {
        global $listing;
        $listing->the_labels();
    }

    static function loop_item_listing_title() {
        echo '<h3 class="' . esc_attr(apply_filters('rtcl_listing_loop_title_classes', 'listing-title rtcl-listing-title')) . '"><a href="' . get_the_permalink() . '">' . get_the_title() . '</a></h3>';
    }

    static function loop_item_wrapper_start() {
        global $listing;
        echo apply_filters('rtcl_loop_item_wrapper_start', sprintf('<div class="item-content%s">', esc_attr($listing->can_show_price() ? ' no-price' : '')));
    }

    static function loop_item_wrapper_end() {
        echo apply_filters('rtcl_loop_item_wrapper_end', '</div>');
    }

    static function listing_price() {
        Functions::get_template('listing/loop/price');
    }

    static function listing_thumbnail() {
        Functions::get_template('listing/loop/thumbnail');
    }

    public static function output_content_wrapper() {
        Functions::get_template('global/wrapper-start');
    }

    public static function breadcrumb() {
        Functions::breadcrumb();
    }

    public static function output_content_wrapper_end() {
        Functions::get_template('global/wrapper-end');
    }

    public static function get_sidebar() {
        Functions::get_template('global/sidebar');
    }

    /**
     * Show an archive description on taxonomy archives.
     */
    static function taxonomy_archive_description() {
        if (Functions::is_listing_taxonomy() && 0 === absint(get_query_var('paged'))) {
            $term = get_queried_object();

            if ($term && !empty($term->description)) {
                echo '<div class="rtcl-term-description">' . Functions::format_content($term->description) . '</div>'; // WPCS: XSS ok.
            }
        }
    }

    static function listing_archive_description() {
        // Don't display the description on search results page.
        if (is_search()) {
            return;
        }

        if (is_post_type_archive(rtcl()->post_type) && in_array(absint(get_query_var('paged')), array(
                0,
                1
            ), true)) {
            $listings_page = get_post(Functions::get_page_id('listings'));
            if ($listings_page) {
                $description = Functions::format_content($listings_page->post_content);
                if ($description) {
                    echo '<div class="rtcl-page-description">' . $description . '</div>'; // WPCS: XSS ok.
                }
            }
        }
    }


    /**
     * @param null| WP_Query $query
     */
    static function top_listing_items($query = null) {
        $query = !empty($query) && is_a($query, WP_Query::class) ? $query : Functions::get_top_listings_query();

        $paginated = !$query->get('no_found_rows');
        $listings = (object)array(
            'total'        => $paginated ? (int)$query->found_posts : count($query->posts),
            'total_pages'  => $paginated ? (int)$query->max_num_pages : 1,
            'per_page'     => (int)$query->get('posts_per_page'),
            'current_page' => $paginated ? (int)max(1, $query->get('paged', 1)) : 1,
        );
        Functions::setup_loop(
            array(
                'is_shortcode' => true,
                'is_search'    => false,
                'is_paginated' => false,
                'total'        => $listings->total,
                'total_pages'  => $listings->total_pages,
                'per_page'     => $listings->per_page,
                'current_page' => $listings->current_page,
            )
        );
        if (Functions::get_loop_prop('total')) {
            while ($query->have_posts()) : $query->the_post();
                Functions::get_template_part('content', 'listing');
            endwhile;
            wp_reset_postdata();
        }

        Functions::reset_loop();
    }

    static function no_listings_found() {
        Functions::get_template('listing/loop/no-listings-found');
    }


    /**
     * Add body classes for Rtcl pages.
     *
     * @param array $classes Body Classes.
     *
     * @return array
     */
    static function body_class($classes) {
        $classes = (array)$classes;
        if (Functions::is_rtcl()) {
            $classes[] = 'rtcl';
            $classes[] = 'rtcl-page';
        } elseif (Functions::is_checkout_page()) {
            $classes[] = 'rtcl-checkout';
            $classes[] = 'rtcl-page';
        } elseif (Functions::is_account_page()) {
            $classes[] = 'rtcl-account';
            $classes[] = 'rtcl-page';
        }

        $classes[] = 'rtcl-no-js';

        add_action('wp_footer', [__CLASS__, 'no_js']);

        return array_unique($classes);

    }


    /**
     * Adds extra post classes for listings via the WordPress post_class hook, if used.
     *
     * Note: For performance reasons we instead recommend using listing_class/get_listing_class instead.
     *
     * @param array        $classes Current classes.
     * @param string|array $class   Additional class.
     * @param int          $post_id Post ID.
     *
     * @return array
     * @since 1.5.4
     */
    static function listing_post_class($classes, $class = '', $post_id = 0) {
        if (!$post_id || rtcl()->post_type !== get_post_type($post_id)) {
            return $classes;
        }

        $listing = rtcl()->factory->get_listing($post_id);

        if (!$listing) {
            return $classes;
        }

        $classes[] = 'listing-item';
        $classes[] = 'rtcl-listing-item';

        return $classes;
    }


    /**
     * NO JS handling.
     *
     * @since 1.5.4
     */
    static function no_js() {
        ?>
        <script type="text/javascript">
            var c = document.body.className;
            c = c.replace(/rtcl-no-js/, 'rtcl-js');
            document.body.className = c;
        </script>
        <?php
    }


    /**
     * Display the review authors gravatar
     *
     * @param array $comment \WP_Comment.
     *
     * @return void
     */
    public static function rtcl_review_display_gravatar($comment) {
        echo get_avatar($comment, apply_filters('rtcl_review_gravatar_size', '60'), '');
    }

    /**
     * Display the reviewers star rating
     *
     * @return void
     */
    public static function rtcl_review_display_rating() {
        if (post_type_supports(rtcl()->post_type, 'comments')) {
            Functions::get_template('listing/review-rating');
        }
    }

    /**
     * Display the review content.
     */
    public static function rtcl_review_display_comment_title($comment) {
        echo '<span class="rtcl-review__title">';
        echo esc_html(get_comment_meta($comment->comment_ID, 'title', true));
        echo '</span>';
    }

    /**
     * Display the review content.
     */
    public static function rtcl_review_display_comment_text() {
        echo '<div class="description">';
        comment_text();
        echo '</div>';
    }

    /**
     * Display the review authors meta (name, verified owner, review date)
     *
     * @return void
     */
    public static function rtcl_review_display_meta() {
        Functions::get_template('listing/review-meta');
    }

    public static function user_information($current_user) {
        Functions::get_template('myaccount/user-info', compact('current_user'));
    }

    public static function account_navigation() {
        Functions::get_template('myaccount/navigation');
    }

    public static function account_content() {
        global $wp;

        if (!empty($wp->query_vars)) {

            foreach ($wp->query_vars as $key => $value) {
                // Ignore pagename param.
                if ('pagename' === $key) {
                    continue;
                }

                if (has_action('rtcl_account_' . $key . '_endpoint')) {
                    do_action('rtcl_account_' . $key . '_endpoint', $value);

                    return;
                }
            }
        }

        // No endpoint found? Default to dashboard.
        Functions::get_template('myaccount/dashboard', array(
            'current_user' => get_user_by('id', get_current_user_id()),
        ));
    }

    public static function checkout_content() {
        global $wp;

        if (!empty($wp->query_vars)) {
            foreach ($wp->query_vars as $key => $value) {
                // Ignore pagename param.
                if ('pagename' === $key) {
                    continue;
                }

                if (has_action('rtcl_checkout_' . $key . '_endpoint')) {
                    do_action('rtcl_checkout_' . $key . '_endpoint', $key, $value);

                    return;
                }
            }
        }

        // No endpoint found? Default to error.
        Functions::get_template('checkout/error');
    }

    public static function checkout_submission_endpoint($type, $listing_id) {
        Checkout::checkout_form($type, $listing_id);
    }

    public static function checkout_payment_receipt_endpoint($type, $payment_id) {
        Checkout::payment_receipt($payment_id);
    }

    public static function account_edit_account_endpoint() {
        MyAccount::edit_account();
    }

    public static function account_listings_endpoint() {
        MyAccount::my_listings();
    }

    public static function account_favourites_endpoint() {
        MyAccount::favourite_listings();
    }

    public static function account_chat_endpoint() {
        MyAccount::chat_conversations();
    }

    public static function account_payments_endpoint() {
        MyAccount::payments_history();
    }


    static function social_login_shortcode() {
        if (!apply_filters('rtcl_social_login_shortcode_disabled', false)) {
            $shortcode = apply_filters('rtcl_social_login_shortcode', Functions::get_option_item('rtcl_account_settings', 'social_login_shortcode', ''));
            if ($shortcode) {
                echo sprintf('<div class="rtcl-social-login-wrap">%s</div>', do_shortcode($shortcode));
            }
        }
    }


    /**
     * Render privacy policy text on the register forms.
     */
    public static function registration_privacy_policy_text() {
        Functions::privacy_policy_text('registration');
    }


    static function add_checkout_form_instruction() {
        ?>
        <p><?php esc_html_e('Please review your order, and click Purchase once you are ready to proceed.', 'classified-listing'); ?></p>
        <?php
    }


    static function add_checkout_form_promotion_options($type, $listing_id) {
        if ('submission' === $type) {
            if ($listing_id && rtcl()->post_type === get_post_type($listing_id)) {
                $pricing_options = Functions::get_regular_pricing_options();
                Functions::get_template("checkout/promotions", array(
                    'pricing_options' => $pricing_options,
                    'listing_id'      => $listing_id
                ));
            } else {
                Functions::add_notice(__("Given Listing Id is not a valid listing", "classified-listing"), "error");
                Functions::get_template("checkout/error");
            }
        }
    }

    static function add_checkout_payment_method() {
        Functions::get_template("checkout/payment-methods");
    }


    static function checkout_terms_and_conditions() {
        Functions::get_template("checkout/terms-conditions");
    }


    static function checkout_form_submit_button() {
        ?>
        <div class="rtcl-submit-btn-wrap d-md-flex justify-content-between">
            <a class="btn btn-primary"
               href="<?php echo esc_url(Link::get_my_account_page_link()) ?>"><?php esc_html_e("Go to My Account", 'classified-listing'); ?></a>
            <button type="submit" id="rtcl-checkout-submit-btn" name="rtcl-checkout" class="btn btn-primary"
                    value="1"><?php esc_html_e('Proceed to payment', 'classified-listing'); ?></button>
        </div>
        <?php
    }


    static function add_checkout_hidden_field($type) {
        wp_nonce_field('rtcl_checkout', 'rtcl_checkout_nonce');
        printf('<input type="hidden" name="type" value="%s"/>', esc_attr($type));
        ?><input type="hidden" name="action" value="rtcl_ajax_checkout_action"/><?php
    }


    static function add_submission_checkout_hidden_field($type, $listing_id) {
        if ('submission' === $type) {
            printf('<input type="hidden" name="listing_id" value="%d"/>', absint($listing_id));
        }
    }


    /**
     * Render privacy policy text on the checkout.
     */
    static function checkout_privacy_policy_text() {
        Functions::privacy_policy_text('checkout');
    }


    static function checkout_terms_and_conditions_page_content() {
        $terms_page_id = Functions::get_terms_and_conditions_page_id();

        if (!$terms_page_id) {
            return;
        }

        $page = get_post($terms_page_id);

        if ($page && 'publish' === $page->post_status && $page->post_content && !has_shortcode($page->post_content, 'rtcl_checkout')) {
            echo '<div class="rtcl-terms-and-conditions" style="display: none; max-height: 200px; overflow: auto;">' . wp_kses_post(Functions::format_content($page->post_content)) . '</div>';
        }
    }


    /**
     * @param Filter $object
     */
    static function add_hidden_field_filter_form($object) {
        $args = $object->get_instance();
        $current_category = !empty($args['current_taxonomy'][rtcl()->category]) ? $args['current_taxonomy'][rtcl()->category]->slug : '';
        $current_location = !empty($args['current_taxonomy'][rtcl()->location]) ? $args['current_taxonomy'][rtcl()->location]->slug : '';
        ?>
        <input type="hidden" name="rtcl_category" value="<?php echo esc_attr($current_category) ?>">
        <input type="hidden" name="rtcl_location" value="<?php echo esc_attr($current_location) ?>">
        <?php
    }
}
