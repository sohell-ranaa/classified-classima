<?php

namespace Rtcl\Helpers;

use Automattic\Jetpack\Constants;
use DateTime;
use DateTimeZone;
use Rtcl\Controllers\Admin\AddConfig;
use Rtcl\Helpers\SortImages as SortImages;
use Rtcl\Models\Listing;
use Rtcl\Models\Payment;
use Rtcl\Models\PaymentGateway;
use Rtcl\Models\RtclCFGField;
use Rtcl\Models\RtclDateTime;
use Rtcl\Resources\Options;
use Rtcl\Traits\ListingTrait;
use Rtcl\Traits\SettingsTrait;
use Rtcl\Traits\TemplateTrait;
use Rtcl\Traits\UtilityTrait;
use WP_Query;

/**
 * Class Functions
 *
 * @package Rtcl\Helpers
 */
class Functions
{

    use ListingTrait;
    use SettingsTrait;
    use UtilityTrait;
    use TemplateTrait;

    public static function verify_nonce() {
        $nonce = isset($_REQUEST[rtcl()->nonceId]) ? $_REQUEST[rtcl()->nonceId] : null;
        $nonceText = rtcl()->nonceText;
        if (wp_verify_nonce($nonce, $nonceText)) {
            return true;
        }

        return false;
    }

    /**
     * Get data if set, otherwise return a default value or null. Prevents notices when data is not set.
     *
     * @param      $var
     * @param null $default
     *
     * @return null
     */
    public static function get_var(&$var, $default = null) {
        return isset($var) ? $var : $default;
    }

    public static function get_raw_referer() {
        if (function_exists('wp_get_raw_referer')) {
            return wp_get_raw_referer();
        }

        if (!empty($_REQUEST['_wp_http_referer'])) { // WPCS: input var ok, CSRF ok.
            return wp_unslash($_REQUEST['_wp_http_referer']); // WPCS: input var ok, CSRF ok, sanitization ok.
        } elseif (!empty($_SERVER['HTTP_REFERER'])) { // WPCS: input var ok, CSRF ok.
            return wp_unslash($_SERVER['HTTP_REFERER']); // WPCS: input var ok, CSRF ok, sanitization ok.
        }

        return false;
    }

    public static function get_status_i18n($status) {
        $status_list = Options::get_status_list() + Options::get_payment_status_list();

        return !empty($status_list[$status]) ? $status_list[$status] : false;
    }

    public static function get_single_term_title() {
        $location = get_query_var('rtcl_location');
        $category = get_query_var('rtcl_category');
        $term = null;
        if ($location) {
            $term = get_term_by('slug', $location, rtcl()->location);
        }
        if ($category) {
            $term = get_term_by('slug', $category, rtcl()->category);
        }
        if ($term) {
            return $term->name;
        }

        return false;
    }

    /**
     * @param null   $post_id
     * @param        $meta_key
     * @param string $type
     *
     * @return bool
     */
    public static function meta_exist($post_id, $meta_key, $type = "post") {
        if (!$post_id) {
            return false;
        }
        return metadata_exists($type, $post_id, $meta_key);
    }

    public static function get_favourites_link($post_id) {
        if (is_user_logged_in()) {

            if ($post_id == 0) {
                global $post;
                $post_id = $post->ID;
            }

            $favourites = (array)get_user_meta(get_current_user_id(), 'rtcl_favourites', true);

            if (in_array($post_id, $favourites)) {
                return '<a href="javascript:void(0)" class="rtcl-favourites rtcl-active" data-id="' . $post_id . '"><span class="rtcl-icon rtcl-icon-heart"></span><span class="favourite-label">' . Text::remove_from_favourite() . '</span></a>';
            } else {
                return '<a href="javascript:void(0)" class="rtcl-favourites" data-id="' . $post_id . '"><span class="rtcl-icon rtcl-icon-heart-empty"></span><span class="favourite-label">' . Text::add_to_favourite() . '</span></a>';
            }

        } else {

            return '<a href="javascript:void(0)" class="rtcl-require-login"><span class="rtcl-icon rtcl-icon-heart-empty"></span><span class="favourite-label">' . Text::add_to_favourite() . '</span></a>';
        }
    }

    /**
     * @param string $tag
     *
     * @return bool
     */
    public static function post_content_has_shortcode($tag = '') {
        global $post;

        return is_singular() && is_a($post, '\WP_Post') && has_shortcode($post->post_content, $tag);
    }

    /**
     * @param $endpoint
     *
     * @return bool
     */
    public static function is_account_page($endpoint = null) {
        $is_account_page = is_page(self::get_page_id('myaccount')) || self::post_content_has_shortcode('rtcl_my_account') || apply_filters('rtcl_is_account_page', false);
        if ($is_account_page && $endpoint) {
            global $wp;

            return isset($wp->query_vars[$endpoint]);
        }

        return $is_account_page;

    }

    static function is_rtcl() {
        return apply_filters('rtcl_is_rtcl', self::is_listings() || self::is_listing_taxonomy() || self::is_listing());
    }

    /**
     * @return bool
     * @since 1.5.4
     * @deprecated
     */
    static function is_listings_page() {
        return self::is_listings();
    }

    /**
     * @return bool
     */
    static function is_listings() {
        return apply_filters('rtcl_is_listings_page', is_post_type_archive(rtcl()->post_type) || is_page(self::get_page_id('listings')));
    }

    static function is_enable_top_listings() {
        return Functions::get_option_item('rtcl_moderation_settings', 'listing_enable_top_listing', false, 'checkbox');
    }

    /**
     * Check is Listing Category archive page
     *
     * @param string $term
     *
     * @return bool
     */
    static function is_listing_category($term = '') {
        return is_tax(rtcl()->category, $term);
    }

    /**
     * Check is Listing Location archive page
     *
     * @param string $term
     *
     * @return bool
     */
    static function is_listing_location($term = '') {
        return is_tax(rtcl()->location, $term);
    }

    /**
     * Is_ajax - Returns true when the page is loaded via ajax.
     *
     * @return bool
     */
    static function is_ajax() {
        return function_exists('wp_doing_ajax') ? wp_doing_ajax() : Constants::is_defined('DOING_AJAX');
    }


    /**
     * Check is Listing taxonomy archive page
     *
     * @return bool
     */
    static function is_listing_taxonomy() {
        return is_tax(get_object_taxonomies(rtcl()->post_type));
    }

    /**
     * Single Listing page
     *
     * @return bool
     */
    static function is_listing() {
        return is_singular(array(rtcl()->post_type));
    }

    /**
     * Check is Listing submission form page
     *
     * @return bool
     */
    static function is_listing_form_page() {
        return is_page(self::get_page_id('listing_form')) || self::post_content_has_shortcode('rtcl_listing_form') || apply_filters('rtcl_is_listing_form_page', false);
    }

    /**
     * @param null $endpoint
     *
     * @return bool
     */
    static function is_checkout_page($endpoint = null) {
        $is_checkout_page = is_page(self::get_page_id('checkout')) || self::post_content_has_shortcode('rtcl_checkout') || apply_filters('rtcl_is_checkout_page', false);

        if ($is_checkout_page && $endpoint) {
            global $wp;

            return isset($wp->query_vars[$endpoint]);
        }

        return $is_checkout_page;
    }

    public static function get_my_account_page_endpoints() {

        $endpoints = array(
            // My account actions.
            'listings'      => Functions::get_option_item('rtcl_advanced_settings', 'myaccount_listings_endpoint', 'listings'),
            'favourites'    => Functions::get_option_item('rtcl_advanced_settings', 'myaccount_favourites_endpoint', 'favourites'),
            'chat'          => Functions::get_option_item('rtcl_advanced_settings', 'myaccount_chat_endpoint', 'chat'),
            'payments'      => Functions::get_option_item('rtcl_advanced_settings', 'myaccount_payments_endpoint', 'payments'),
            'edit-account'  => Functions::get_option_item('rtcl_advanced_settings', 'myaccount_edit_account_endpoint', 'edit-account'),
            'lost-password' => Functions::get_option_item('rtcl_advanced_settings', 'myaccount_lost_password_endpoint', 'lost-password'),
            'verify'        => Functions::get_option_item('rtcl_advanced_settings', 'myaccount_verify', 'verify'),
            'logout'        => Functions::get_option_item('rtcl_advanced_settings', 'myaccount_logout_endpoint', 'logout')
        );

        return apply_filters('rtcl_my_account_endpoint', $endpoints);
    }

    public static function get_checkout_page_endpoints() {
        $endpoints = array(
            'submission'      => self::get_option_item('rtcl_advanced_settings', 'checkout_submission_endpoint', 'submission'),
            'promote'         => self::get_option_item('rtcl_advanced_settings', 'checkout_promote_endpoint', 'promote'),
            'payment-receipt' => self::get_option_item('rtcl_advanced_settings', 'checkout_payment_receipt_endpoint', 'payment-receipt'),
            'payment-failure' => self::get_option_item('rtcl_advanced_settings', 'checkout_payment_failure_endpoint', 'payment-failure')
        );

        return apply_filters('rtcl_checkout_endpoints', $endpoints);
    }

    public static function is_human($form) {

        $misc_settings = Functions::get_option('rtcl_misc_settings');

        $has_captcha = false;
        if (isset($misc_settings['recaptcha_forms']) && '' !== $misc_settings['recaptcha_site_key'] && '' !== $misc_settings['recaptcha_secret_key']) {
            if (in_array($form, $misc_settings['recaptcha_forms'])) {
                $has_captcha = true;
            }
        }

        if ($has_captcha) {

            $response = isset($_POST['g-recaptcha-response']) ? esc_attr($_POST['g-recaptcha-response']) : '';

            if ('' !== $response) {

                // make a GET request to the Google reCAPTCHA Server
                $request = wp_remote_get('https://www.google.com/recaptcha/api/siteverify?secret=' . $misc_settings['recaptcha_secret_key'] . '&response=' . $response . '&remoteip=' . $_SERVER["REMOTE_ADDR"]);

                // get the request response body
                $response_body = wp_remote_retrieve_body($request);

                $result = json_decode($response_body, true);

                // return true or false, based on users input
                return true === $result['success'];

            } else {
                return false;
            }

        }

        return true;

    }

    /**
     * Check Moderation (rtcl_moderation_settings) hide_form_fields $field is hide
     *
     * @param $field
     *
     * @return bool
     */
    static function is_field_disabled($field) {
        return Functions::get_option_item('rtcl_moderation_settings', 'hide_form_fields', $field, 'multi_checkbox');
    }

    /**
     * @return bool
     */
    static function is_payment_disabled() {

        if (!Functions::get_option_item('rtcl_payment_settings', 'payment', false, 'checkbox')) {
            return true;
        }

        return false;
    }

    static function get_regular_pricing_options() {
        $regular_pricing = get_posts(apply_filters('rtcl_get_regular_pricing_query_args', [
            'post_type'        => rtcl()->post_type_pricing,
            'posts_per_page'   => -1,
            'post_status'      => 'publish',
            'orderby'          => 'menu_order',
            'order'            => 'ASC',
            'meta_query'       => [
                [
                    [
                        'key'   => 'pricing_type',
                        'value' => 'regular'
                    ],
                    [
                        'key'     => 'pricing_type',
                        'compare' => 'NOT EXISTS',
                    ],
                    'relation' => 'OR'
                ]
            ],
            'suppress_filters' => false
        ]));

        return apply_filters('rtcl_get_regular_pricing_options', $regular_pricing);

    }

    /**
     * @return bool
     */
    static function is_price_disabled() {
        if (Functions::get_option_item('rtcl_moderation_settings', 'hide_form_fields', 'price', 'multi_checkbox')) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    static function is_favourites_disabled() {

        if (!Functions::get_option_item('rtcl_moderation_settings', 'has_favourites', false, 'checkbox')) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    static function is_gallery_disabled() {
        if (Functions::get_option_item('rtcl_moderation_settings', 'hide_form_fields', 'gallery', 'multi_checkbox')) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    static function is_description_disabled() {
        if (Functions::get_option_item('rtcl_moderation_settings', 'hide_form_fields', 'description', 'multi_checkbox')) {
            return true;
        }

        return false;
    }


    /**
     * @return bool
     */
    static function is_ad_type_disabled() {
        if (Functions::get_option_item('rtcl_moderation_settings', 'hide_form_fields', 'ad_type', 'multi_checkbox')) {
            return true;
        }

        return false;
    }


    /**
     * @param string $type
     *
     * @return bool
     */
    static function is_enable_terms_conditions($type = 'listing') {
        if (in_array($type, array('checkout', 'listing'))) {
            return Functions::get_option_item('rtcl_account_settings', 'enable_' . $type . '_terms_conditions', null, 'checkbox') ? true : false;
        }

        return false;
    }

    /**
     * @param      $price
     * @param bool $payment
     *
     * @return mixed|void
     * @throws \Exception
     */
    static function get_formatted_price($price, $payment = false) {
        $price = self::get_formatted_amount($price, $payment);
        $currency = self::get_currency($payment);
        $currency_symbol = self::get_currency_symbol($currency, $payment);
        $price_format = self::get_price_format($payment);
        if ($payment) {
            $formatted_payment_price = apply_filters('rtcl_formatted_payment_price_html',
                sprintf($price_format, '<span class="rtcl-price-currencySymbol">' . $currency_symbol . '</span>', $price),
                $price_format, $currency_symbol, $price);
            ob_start();
            do_action('rtcl_payment_price_meta_html', $price_format, $currency_symbol, $price);
            $payment_price_meta_html = ob_get_clean();
            $payment_price_meta_html = $payment_price_meta_html ? apply_filters('rtcl_payment_price_meta_wrap_html', sprintf('<span class="rtcl-payment-price-meta">%s</span>', $payment_price_meta_html), $payment_price_meta_html) : null;
            $payment_price_html_format = apply_filters('rtcl_payment_price_amount_html_format', '<span class="rtcl-price-amount amount">%1$s</span>%2$s');
            $payment_price_html = sprintf($payment_price_html_format, $formatted_payment_price, $payment_price_meta_html);

            return apply_filters('rtcl_get_payment_formatted_price', $payment_price_html, $price, $payment, $currency, $currency_symbol, $price_format);
        }

        global $post;
        $listing = is_object($post) && rtcl()->post_type == $post->post_type ? new Listing($post->ID) : '';
        $formatted_price = apply_filters('rtcl_formatted_price_html',
            sprintf($price_format, '<span class="rtcl-price-currencySymbol">' . $currency_symbol . '</span>', $price),
            $listing, $price_format, $currency_symbol, $price);
        ob_start();
        do_action('rtcl_price_meta_html', $listing, $price_format, $currency_symbol, $price);
        $price_meta_html = ob_get_clean();
        $price_meta_html = $price_meta_html ? apply_filters('rtcl_price_meta_wrap_html', sprintf('<span class="rtcl-price-meta">%s</span>', $price_meta_html), $price_meta_html) : null;
        $price_html_format = apply_filters('rtcl_price_amount_html_format', '<span class="rtcl-price-amount amount">%1$s</span>%2$s');
        $price_html = sprintf($price_html_format, $formatted_price, $price_meta_html);

        return apply_filters('rtcl_get_formatted_price', $price_html, $price, $payment, $currency, $currency_symbol, $price_format);
    }


    /**
     * Format a price with WC Currency Locale settings.
     *
     * @param string $value Price to localize.
     *
     * @return string
     */
    static function format_localized_price($value) {
        $decimal_separator = self::get_decimal_separator_both();
        return apply_filters('rtcl_format_localized_price', str_replace('.', $decimal_separator[0], strval($value)), $value);
    }

    static function get_price_format($payment = false) {
        $currency_settings = Functions::get_option_item('rtcl_general_settings', 'currency_position');
        if ($payment) {
            $currency_settings = Functions::get_option_item('rtcl_payment_settings', 'currency_position');
        }
        $currency_pos = !empty($currency_settings) ? $currency_settings : 'left';
        $format = '%1$s%2$s';

        switch ($currency_pos) {
            case 'left' :
                $format = '%1$s%2$s';
                break;
            case 'right' :
                $format = '%2$s%1$s';
                break;
            case 'left_space' :
                $format = '%1$s&nbsp;%2$s';
                break;
            case 'right_space' :
                $format = '%2$s&nbsp;%1$s';
                break;
        }

        return apply_filters('rtcl_get_price_format', $format, $currency_pos, $payment);
    }

    static function get_formatted_amount($amount, $payment = false) {

        $thousands_sep = Functions::get_thousands_separator($payment);
        $decimal_sep = Functions::get_decimal_separator($payment);
        $decimals = self::currency_decimal_count();

        $un_formatted_price = $amount;
        $negative = $amount < 0;
        $amount = apply_filters('rtcl_raw_amount', floatval($negative ? $amount * -1 : $amount));
        $amount = apply_filters('rtcl_formatted_amount', number_format($amount, $decimals, $decimal_sep, $thousands_sep));

        if (apply_filters('rtcl_price_trim_zeros', true, $payment, $decimals) && $decimals > 0) {
            $amount = self::trim_zeros($amount, $decimal_sep);
        }

        return apply_filters('rtcl_get_formatted_amount', $amount, $un_formatted_price, $decimals, $decimal_sep, $thousands_sep);

    }

    static function trim_zeros($amount, $decimal_sep) {
        return preg_replace('/' . preg_quote($decimal_sep, '/') . '0++$/', '', $amount);
    }

    static function currency_decimal_count() {
        return apply_filters('rtcl_currency_decimal_count', 2);
    }

    static function request($key, $default = null) {
        if (isset($_POST[$key])) {
            return stripslashes_deep($_POST[$key]);
        } elseif (isset($_GET[$key])) {
            return stripslashes_deep($_GET[$key]);
        } else {
            return $default;
        }
    }

    static function get_temp_listing_status() {
        return apply_filters("rtcl_get_temp_listing_status", "rtcl-temp");
    }

    static function delete_post($post_id, $skip_trash = true) {

        $skip_trash = apply_filters("rtcl_skip_trash_to_delete", $skip_trash, $post_id);

        if ($skip_trash) {
            $result = wp_delete_post($post_id);
        } else {
            $result = wp_trash_post($post_id);
        }

        return $result;
    }

    static function user_can_edit_image() {
        $cap = rtcl()->gallery['image_edit_cap'];

        if ((!empty($cap) && $cap === true) || is_admin()) {
            return true;
        }

        return false;
    }


    /**
     * Formats information about specific attachment
     *
     * @param int     $attach_id WP_Post ID
     * @param boolean $is_new
     *
     * @return array
     */
    static function upload_item_data($attach_id, $is_new = false) {
        try {
            // Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
            require_once(ABSPATH . 'wp-admin/includes/image.php');

            // Generate the metadata for the attachment, and update the database record.
            $sizes = array();
            $image_keys = array("url", "width", "height", "is_intermidiate");


            $image_defaults = array(
                "full" => array(
                    "enabled" => 1,
                    "width"   => null,
                    "height"  => null,
                    "crop"    => false
                )
            );

            $image_sizes = array_merge($image_defaults, rtcl()->gallery['image_sizes']);

            foreach ($image_sizes as $image_key => $image_size) {
                if ($image_key !== "full" && !has_image_size($image_key)) {
                    continue;
                }

                $src = wp_get_attachment_image_src($attach_id, $image_key);

                if ($image_key !== "full" && isset($src[3]) && $src[3] === false) {
                    $src[1] = $sizes["full"]["width"];
                    $src[2] = $sizes["full"]["height"];
                }

                if ($src === false) {
                    $src = array(
                        "url"    => null,
                        "width"  => $image_size["width"],
                        "height" => $image_size["height"],
                        "crop"   => $image_size["crop"]
                    );
                } else {
                    $src = array_combine($image_keys, $src);
                }

                $sizes[str_replace("-", "_", $image_key)] = $src;
            }

            $featured = 0;
            $caption = "";
            $content = "";

            if (!$is_new) {
                $post = get_post($attach_id);
                $parent_id = wp_get_post_parent_id($post->ID);
                $caption = $post->post_excerpt;
                $content = $post->post_content;

                $featured = intval(get_post_meta($parent_id, '_thumbnail_id', true));
                if ($featured == $post->ID) {
                    $featured = 1;
                } else {
                    $featured = 0;
                }
            }

            $data = array(
                "post_id"   => $post->post_parent,
                "attach_id" => $attach_id,
                "guid"      => $post->guid,
                "mime_type" => $post->post_mime_type,
                "featured"  => $featured,
                "caption"   => $caption,
                "content"   => $content,
                "sizes"     => $sizes,
                "readable"  => array(
                    "name"     => basename($post->guid),
                    "type"     => $post->post_mime_type,
                    "uploaded" => date_i18n(get_option("date_format"), strtotime($post->post_date_gmt)),
                    "size"     => size_format(filesize(get_attached_file($attach_id))),
                    "length"   => null
                )
            );

            $meta = wp_get_attachment_metadata($attach_id);

            if (isset($meta["width"]) && isset($meta["height"])) {
                $data["readable"]["dimensions"] = sprintf("%d x %d", $meta["width"], $meta["height"]);
                $data["dimensions"] = $meta;
            }
            if (isset($meta["length_formatted"])) {
                $data["readable"]["length"] = $meta["length_formatted"];
            }

            return $data;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    static function get_all_cf_fields_by_cfg_id($post_id) {
        $fields = get_posts(array(
            'post_type'        => 'rtcl_cf',
            'posts_per_page'   => -1,
            'post_parent'      => $post_id,
            'post_status'      => 'any',
            'orderby'          => 'menu_order',
            'order'            => 'asc',
            'suppress_filters' => false
        ));

        return $fields;
    }

    /**
     * @param int $parent_id
     *
     * @return array
     */
    static function get_one_level_locations($parent_id = 0) {
        return self::get_sub_terms(rtcl()->location, $parent_id);
    }

    public static function get_multilevel_terms_data($terms) {
        $termList = array();
        if (!empty($terms)) {
            self::set_time_limit(0);
            foreach ($terms as $term) {
                $termItem = array(
                    'id'     => $term->term_id,
                    'name'   => $term->name,
                    'slug'   => $term->slug,
                    'parent' => $term->parent,
                    'link'   => get_term_link($term)
                );
                if ($term->taxonomy === rtcl()->category) {
                    $icon = null;
                    if ($image_id = get_term_meta($term->term_id, '_rtcl_image', true)) {
                        $image_attributes = wp_get_attachment_image_src((int)$image_id, 'medium');
                        $image = $image_attributes[0];
                        if ('' !== $image) {
                            $termItem['icon'] = sprintf('<span class="icon"><img src="%s" alt="%s" class="rtcl-cat-img" /></span>', esc_url($image), esc_attr($term->name));
                        }
                    } else if ($icon_id = get_term_meta($term->term_id, '_rtcl_icon', true)) {
                        $icon = "<span class='icon'><i class='tcl-cat-icon rtcl-icon rtcl-icon-{$icon_id}'></i></span>";
                        $termItem['icon'] = $icon;
                    }
                }
                $subTerms = Functions::get_sub_terms($term->taxonomy, $term->term_id);

                if (!empty($subTerms)) {
                    $termItem['sub'] = self::get_multilevel_terms_data($subTerms);
                }
                $termList[] = $termItem;
            }
        }

        return $termList;
    }

    /**
     * @param array $args
     * @param array $terms
     *
     * @return string
     */
    static function get_sub_terms_filter_html($args, $terms = []) {
        $current_term = !empty($args['instance']['current_taxonomy'][$args['taxonomy']]) ? (object)$args['instance']['current_taxonomy'][$args['taxonomy']] : '';
        $terms = empty($terms) ? Functions::get_sub_terms($args['taxonomy'], $args['parent']) : $terms;
        $html = '';

        if (!empty($terms)) {
            $ulCls = $args['parent'] ? 'sub-list' : 'filter-list';
            if ($args['taxonomy'] == rtcl()->location) {
                $ulCls .= ' is-collapsed';
            }
            $allTaxonomyLinkHtml = '';
            if (empty($args['parent']) && !empty($args['instance']['taxonomy_reset_link'])) {
                $allTaxonomyLink = Link::get_listings_page_link();
                $allTaxonomyLink_text = rtcl()->category === $args['taxonomy'] ? __("All Categories", "classified-listing") : __("All Locations", "classified-listing");
                if (rtcl()->category === $args['taxonomy'] && !empty($args['instance']['current_taxonomy'][rtcl()->location])) {
                    $allTaxonomyLink = get_term_link((object)$args['instance']['current_taxonomy'][rtcl()->location]);
                } elseif (rtcl()->location === $args['taxonomy'] && !empty($args['instance']['current_taxonomy'][rtcl()->category])) {
                    $allTaxonomyLink = get_term_link((object)$args['instance']['current_taxonomy'][rtcl()->category]);
                }
                $allTaxonomyLinkHtml = sprintf(
                    '<li class="all-taxonomy"><a href="%s">%s</a></li>',
                    $allTaxonomyLink,
                    apply_filters('rtcl_widget_filter_taxonomy_reset_text', $allTaxonomyLink_text, $args['taxonomy'])
                );
            }
            foreach ($terms as $term) {
                $count = Functions::get_listings_count_by_taxonomy($term->term_id, $args['taxonomy'], true);
                if (!empty($args['instance']['hide_empty']) && 0 === $count) {
                    continue;
                }
                $children = Functions::get_sub_terms($args['taxonomy'], $term->term_id);
                $args['parent'] = $term->term_id;
                $cls = $has_arrow = $sub_term_html = $cls_open = null;
                if (!empty($children)) {
                    $cls = "is-parent has-sub";
                    $has_arrow = "<span class='arrow'><i class='rtcl-icon rtcl-icon-down-open'> </i></span>";
                    $cls_open = null;
                    if (isset($current_term->taxonomy) && $args['taxonomy'] === $current_term->taxonomy) {
                        if ($term->term_id === absint($current_term->term_id)) {
                            $cls_open = " is-open";
                        } else {
                            $ids = get_ancestors($current_term->term_id, $args['taxonomy']);
                            if (!empty($ids) && in_array($term->term_id, $ids)) {
                                $cls_open = " is-open";
                            }
                        }
                    }
                    $cls = $cls . $cls_open;
                }
                $cat_img_icon = null;
                if ($args['taxonomy'] == rtcl()->category && $term->parent == 0) {
                    $cat_img = $cat_icon = null;
                    if (!empty($args['instance']['show_icon_image_for_category'])) {
                        $image_id = get_term_meta($term->term_id, '_rtcl_image', true);
                        if ($image_id) {
                            $image_attributes = wp_get_attachment_image_src((int)$image_id, 'medium');
                            $image = $image_attributes[0];
                            if ('' !== $image) {
                                $cat_img = sprintf('<img src="%s" alt="%s" class="rtcl-cat-img" />', esc_url($image), esc_attr($term->name));
                            }
                        }
                        $icon_id = get_term_meta($term->term_id, '_rtcl_icon', true);
                        if ($icon_id) {
                            $cat_icon = sprintf('<span class="rtcl-cat-icon rtcl-icon rtcl-icon-%s"></span>', $icon_id);
                        }
                    }
                    $cat_img_icon = $cat_img ? $cat_img : $cat_icon;
                }
                $term_link = get_term_link($term);
                if ("rtcl_category" === $term->taxonomy && !empty($args['instance']['current_taxonomy']['rtcl_location'])) {
                    $obj = (object)$args['instance']['current_taxonomy']['rtcl_location'];
                    $term_link = add_query_arg([
                        'rtcl_location' => $obj->slug
                    ], $term_link);
                } elseif ("rtcl_location" === $term->taxonomy && !empty($args['instance']['current_taxonomy']['rtcl_category'])) {
                    $obj = (object)$args['instance']['current_taxonomy']['rtcl_category'];
                    $term_link = add_query_arg([
                        'rtcl_category' => $obj->slug
                    ], $term_link);
                }

                if($current_term && $current_term->term_id == $term->term_id){
                    $cls .= " active";
                }
                $html .= sprintf("<li class='%s'%s>%s%s%s</li>",
                    $cls,
                    $has_arrow ? sprintf(' data-id="%d"', $term->term_id) : '',
                    sprintf('<a href="%s">%s%s%s</a>',
                        $term_link,
                        $cat_img_icon,
                        $term->name,
                        !empty($args['instance']['show_count']) ? ' (' . $count . ')' : ''
                    ),
                    $has_arrow,
                    !empty($args['instance']['ajax_load']) ? $cls_open ? self::get_sub_terms_filter_html($args, $children) : '' : self::get_sub_terms_filter_html($args, $children)
                );
            }
            if ($html && rtcl()->location === $args['taxonomy']) {
                $html .= '<li class="is-opener"><span class="rtcl-more"><i class="rtcl-icon rtcl-icon-plus-circled"></i><span class="text">' . __("Show More",
                        "classified-listing") . '</span></span></li>';
            }

            $html = $html ? sprintf("<ul class='%s'>%s%s</ul>", $ulCls, $allTaxonomyLinkHtml, $html) : '';
        }

        return $html;
    }

    /**
     * @param String $taxonomy
     * @param array  $data
     * @param Int    $parent_id
     *
     *
     * @return array
     */
    static function get_sub_terms($taxonomy, $parent_id = 0, $data = []) {

        $transient_id = '';
        $meta_query = '';
        if (!empty($data['type']) && rtcl()->category === $taxonomy) {
            $meta_query = [
                [
                    'key'   => '_rtcl_types',
                    'value' => $data['type']
                ]
            ];
            $transient_id = $data['type'];
        }
        $transient_name = rtcl()->get_transient_name($transient_id, $taxonomy, 'hierarchy_' . $parent_id);
        if (false === ($terms = get_transient($transient_name))) {
            $orderby = strtolower(self::get_option_item('rtcl_general_settings', 'taxonomy_orderby', 'name'));
            $order = strtoupper(self::get_option_item('rtcl_general_settings', 'taxonomy_order', 'DESC'));
            $args = [
                'parent'       => $parent_id,
                'hide_empty'   => 0,
                'orderby'      => $orderby,
                'order'        => ('DESC' === $order) ? 'DESC' : 'ASC',
                'hierarchical' => 1,
                'taxonomy'     => $taxonomy,
                'pad_counts'   => 1,
                'child_of'     => $parent_id,
            ];
            if ('_rtcl_order' === $orderby) {
                $args['orderby'] = 'meta_value_num';
                $args['meta_key'] = '_rtcl_order';
            }
            if ($meta_query) {
                $args['meta_query'] = $meta_query;
            }
            $args = apply_filters(
                'rtcl_sub_taxonomy_hierarchy_args',
                $args,
                $taxonomy,
                $parent_id,
                $data
            );

            self::set_time_limit(0);
            $terms = get_terms($args);
            if (!empty($terms) && !is_wp_error($terms)) {
                set_transient($transient_name, $terms, WEEK_IN_SECONDS);
                if (apply_filters('rtcl_listing_get_sub_terms_hide_empty', false) || !empty($args['hide_empty'])) {
                    $terms = wp_list_filter($terms, array('count' => 0), 'NOT');
                }
            } else {
                $terms = [];
            }
        }
        return $terms;
    }

    /**
     * @param int  $parent_id
     * @param null $type
     *
     * @return array
     */
    static function get_one_level_categories($parent_id = 0, $type = null) {
        $data = $type ? ['type' => $type] : [];
        return self::get_sub_terms(rtcl()->category, $parent_id, $data);
    }

    /**
     * @param     $capability
     * @param int $post_id
     *
     * @return bool
     */
    static function current_user_can($capability, $post_id = 0) {
        $current_user_id = get_current_user_id();
        $user_can = false;
        $listing = $post_id ? get_post(absint($post_id)) : null;
        // If editing, deleting, or reading a listing, get the post and post type object.
        if ($listing && is_object($listing) && rtcl()->post_type === $listing->post_type && $current_user_id === absint($listing->post_author) && in_array($capability, ['edit_rtcl_listing', 'delete_rtcl_listing'])) {
            $user_can = true;
        }

        return apply_filters('rtcl_current_user_can', $user_can, $capability, $post_id);

    }

    static function dropdown_terms($args = array(), $echo = true) {

        // Vars
        $args = array_merge(array(
            'show_option_none'  => '-- ' . __('Select a category', 'classified-listing') . ' --',
            'option_none_value' => '',
            'taxonomy'          => rtcl()->category,
            'name'              => 'rtcl_category',
            'class'             => 'form-control',
            'required'          => false,
            'base_term'         => 0,
            'parent'            => 0,
            'orderby'           => 'name',
            'order'             => 'ASC',
            'value_field'       => 'id',
            'selected'          => 0
        ), $args);

        if (!empty($args['selected'])) {
            $ancestors = get_ancestors($args['selected'], $args['taxonomy']);
            $ancestors = array_merge(array_reverse($ancestors), array($args['selected']));
        } else {
            $ancestors = array();
        }

        // Build data
        $html = '';

        if (isset($args['walker'])) {

            $selected = count($ancestors) >= 2 ? (int)$ancestors[1] : 0;

            $html .= '<div class="rtcl-terms">';
            $html .= sprintf('<input type="hidden" name="%s" class="rtcl-term-hidden" value="%d" />', $args['name'],
                $selected);

            $term_args = array(
                'show_option_none'  => $args['show_option_none'],
                'option_none_value' => $args['option_none_value'],
                'taxonomy'          => $args['taxonomy'],
                'child_of'          => $args['parent'],
                'orderby'           => $args['orderby'],
                'order'             => $args['order'],
                'selected'          => $selected,
                'hierarchical'      => true,
                'depth'             => 2,
                'show_count'        => false,
                'hide_empty'        => false,
                'walker'            => $args['walker'],
                'echo'              => 0
            );

            unset($args['walker']);

            $select = wp_dropdown_categories($term_args);
            $required = $args['required'] ? ' required' : '';
            $replace = sprintf('<select class="%s" data-taxonomy="%s" data-parent="%d"%s>', $args['class'],
                $args['taxonomy'], $args['parent'], $required);

            $html .= preg_replace('#<select([^>]*)>#', $replace, $select);

            if ($selected > 0) {
                $args['parent'] = $selected;
                $html .= self::dropdown_terms($args, false);
            }

            $html .= '</div>';

        } else {

            $has_children = 0;
            $child_of = 0;

            $term_args = array(
                'parent'       => $args['parent'],
                'orderby'      => 'meta_value_num',
                'meta_key'     => '_rtcl_order',
                'order'        => 'ASC',
                'hide_empty'   => false,
                'hierarchical' => false
            );
            $terms = get_terms($args['taxonomy'], $term_args);

            if (!empty($terms) && !is_wp_error($terms)) {

                if ($args['parent'] == $args['base_term']) {
                    $required = $args['required'] ? ' required' : '';
                    $sSlug = "";
                    if ($args['selected']) {
                        $sTerm = get_term_by('id', $args['selected'], $args['taxonomy']);
                        $sSlug = $sTerm->slug;
                    }
                    $html .= '<div class="rtcl-terms">';
                    $html .= sprintf('<input type="hidden" class="rtcl-term-hidden rtcl-term-%s" data-slug="%s" value="%d" />',
                        $args['taxonomy'], $sSlug, $args['selected']);
                    $html .= sprintf('<input type="hidden" name="%s" class="rtcl-term-hidden-value rtcl-term-%s" value="%s" />',
                        $args['taxonomy'], $args['taxonomy'], $sSlug);
                    $html .= sprintf('<select class="%s" data-taxonomy="%s" data-parent="%d"%s>', $args['class'],
                        $args['taxonomy'], $args['parent'], $required);
                    $html .= sprintf('<option value="%s">%s</option>', $args['option_none_value'],
                        $args['show_option_none']);
                } else {
                    $html .= sprintf('<div class="rtcl-child-terms rtcl-child-terms-%d">', $args['parent']);
                    $html .= sprintf('<select class="%s" data-taxonomy="%s" data-parent="%d">', $args['class'],
                        $args['taxonomy'], $args['parent']);
                    $html .= sprintf('<option value="%d">%s</option>', $args['parent'], '---');
                }

                foreach ($terms as $term) {
                    $selected = '';
                    if (in_array($term->term_id, $ancestors)) {
                        $has_children = 1;
                        $child_of = $term->term_id;
                        $selected = ' selected';
                    } else if ($term->term_id == $args['selected']) {
                        $selected = ' selected';
                    }
                    $html .= sprintf('<option data-slug="%s" value="%s"%s>%s</option>',
                        $term->slug,
                        ($args['value_field'] == "slug") ? $term->slug : $term->term_id,
                        $selected, $term->name);
                }

                $html .= '</select>';
                if ($has_children) {
                    $args['parent'] = $child_of;
                    $html .= self::dropdown_terms($args, false);
                }
                $html .= '</div>';

            } else {

                if ($args['parent'] == $args['base_term']) {
                    $required = $args['required'] ? ' required' : '';

                    $html .= '<div class="rtcl-terms">';
                    $html .= sprintf('<input type="hidden" name="%s" class="rtcl-term-hidden" value="%d" />',
                        $args['name'], $args['selected']);
                    $html .= sprintf('<select class="%s" data-taxonomy="%s" data-parent="%d"%s>', $args['class'],
                        $args['taxonomy'], $args['parent'], $required);
                    $html .= sprintf('<option value="%s">%s</option>', $args['option_none_value'],
                        $args['show_option_none']);
                    $html .= '</select>';
                    $html .= '</div>';
                }

            }

        }

        // Echo or Return
        if ($echo) {
            echo $html;

            return '';
        } else {
            return $html;
        }

    }

    /**
     * @param null $days
     * @param null $start_date
     *
     * @return string
     * @throws \Exception
     */
    static function dummy_expiry_date($days = null, $start_date = null) {
        $days = $days ?: self::get_default_expired_duration_days();
        $days = $days <= 0 ? apply_filters('rtcl_get_never_expires_dummy_duration_days', 999) : $days;
        if ($start_date == null) {
            $start_date = current_time('mysql');
        }
        $date = new \DateTime($start_date);
        $date->add(new \DateInterval("P{$days}D"));

        return $date->format('Y-m-d H:i:s');
    }

    static function get_decimal_separator($payment = false) {

        if ($payment) {
            $currency_settings = Functions::get_option('rtcl_payment_settings');
        } else {
            $currency_settings = Functions::get_option('rtcl_general_settings');
        }

        return isset($currency_settings['currency_decimal_separator']) ? stripslashes($currency_settings['currency_decimal_separator']) : '.';
    }

    /**
     * @return array
     */
    static function get_decimal_separator_both() {
        $payment_currency_settings = Functions::get_option('rtcl_payment_settings');
        $currency_settings = Functions::get_option('rtcl_general_settings');

        return [
            isset($payment_currency_settings['currency_decimal_separator']) ? stripslashes($payment_currency_settings['currency_decimal_separator']) : '.',
            isset($currency_settings['currency_decimal_separator']) ? stripslashes($currency_settings['currency_decimal_separator']) : '.'
        ];
    }

    public static function get_currency($payment = false) {
        if ($payment) {
            $currency = Functions::get_option_item('rtcl_payment_settings', 'currency');
        } else {
            $currency = Functions::get_option_item('rtcl_general_settings', 'currency');
        }

        return apply_filters('rtcl_get_currency', $currency, $payment);
    }

    public static function get_currency_symbol($currency = '', $payment = false) {

        if (!$currency) {
            $currency = self::get_currency($payment);
        }
        $symbols = Options::get_currency_symbols();
        $currency_symbol = isset($symbols[$currency]) ? $symbols[$currency] : '';

        return apply_filters('rtcl_get_currency_symbol', $currency_symbol, $currency, $payment);
    }

    static function get_thousands_separator($payment = false) {
        if ($payment) {
            $currency_settings = Functions::get_option('rtcl_currency_settings');
        } else {
            $currency_settings = Functions::get_option('rtcl_general_settings');
        }

        return isset($currency_settings['currency_thousands_separator']) ? stripslashes($currency_settings['currency_thousands_separator']) : ',';
    }

    static function sanitize_title_with_underscores($title) {
        return rawurldecode(str_replace('-', '_', sanitize_title_with_dashes($title)));
    }

    /**
     * @param int $category
     *
     * @return array
     * @deprecated use apply filters rtcl_listing_get_custom_field_group_ids
     */
    static function get_custom_group_ids($category = 0) {

        $group_ids = array();

        // Get category fields
        if ($category > 0) {

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
                        'terms'            => $category,
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

    static function get_custom_field_ids($category = 0) {

        $group_ids = apply_filters('rtcl_listing_get_custom_field_group_ids', [], $category);
        $field_ids = [];
        if (!empty($group_ids)) {
            foreach ($group_ids as $group_id) {
                $args = array(
                    'post_type'        => rtcl()->post_type_cf,
                    'post_status'      => 'publish',
                    'posts_per_page'   => -1,
                    'fields'           => 'ids',
                    'post_parent'      => $group_id,
                    'orderby'          => 'menu_order',
                    'order'            => 'ASC',
                    'suppress_filters' => false
                );

                $temp_ids = get_posts($args);
                $field_ids = array_merge($field_ids, $temp_ids);
            }
        }
        if (!empty($field_ids)) {
            $field_ids = array_unique($field_ids);
        }

        return $field_ids;
    }

    static function get_custom_field_html($field_id, $post_id = null) {
        if (!$post_id) {
            $post_id = get_the_ID();
        }
        $html = '';
        $field = new RtclCFGField($field_id);
        if ($field_id && $field) {
            $id = "rtcl_{$field->getType()}_{$field->getFieldId()}";
            $required_label = $required_attr = '';
            $field_attr = " data-type='{$field->getType()}' data-id='_field_{$field->getFieldId()}'";
            if (1 == $field->getRequired()) {
                $required_label = '<span class="require-star">*</span>';
                $required_attr = ' required';
            }
            $field_html = null;
            $value = $field->getValue($post_id);
            switch ($field->getType()) {
                case 'text':
                    $field_html = sprintf('<input type="text" class="rtcl-text form-control" id="%s" name="rtcl_fields[_field_%d]" placeholder="%s" value="%s"%s />',
                        $id,
                        absint($field->getFieldId()),
                        esc_attr($field->getPlaceholder()),
                        esc_attr($value),
                        $required_attr
                    );
                    break;
                case 'textarea' :
                    $field_html = sprintf('<textarea class="rtcl-textarea form-control" id="%s" name="rtcl_fields[_field_%d]" rows="%d" placeholder="%s"%s>%s</textarea>',
                        $id,
                        absint($field->getFieldId()),
                        absint($field->getRows()),
                        esc_attr($field->getPlaceholder()),
                        $required_attr,
                        esc_textarea($value)
                    );
                    break;
                case 'select' :
                    $options = $field->getOptions();
                    $choices = !empty($options['choices']) && is_array($options['choices']) ? $options['choices'] : array();
                    $options_html = null;
                    if (true) {
                        $options_html .= sprintf('<option value="">%s</option>',
                            '- ' . __('Select an Option', 'classified-listing') . ' -');
                    }
                    if (!empty($choices)) {
                        foreach ($choices as $key => $choice) {
                            $_selected = '';
                            if (trim($key) == $value) {
                                $_selected = ' selected="selected"';
                            }

                            $options_html .= sprintf('<option value="%s"%s>%s</option>', $key, $_selected, $choice);
                        }
                    }
                    $field_html = sprintf('<select name="rtcl_fields[_field_%d]" id="%s" class="rtcl-select2 form-control"%s>%s</select>',
                        absint($field->getFieldId()),
                        $id,
                        $required_attr,
                        $options_html
                    );
                    break;
                case 'checkbox' :
                    $options = $field->getOptions();
                    $value = !empty($value) && is_array($value) ? $value : array();
                    $choices = !empty($options['choices']) && is_array($options['choices']) ? $options['choices'] : array();
                    $check_options = null;
                    if (!empty($choices)) {
                        $i = 0;
                        foreach ($choices as $key => $choice) {
                            $_attr = '';
                            if (in_array($key, $value)) {
                                $_attr .= ' checked="checked"';
                            }
                            $_attr .= " data-foo='yes' " . $required_attr;

                            $check_options .= sprintf('<div class="form-check"><input class="form-check-input" id="%s" type="checkbox" name="rtcl_fields[_field_%d][]" value="%s"%s><label class="form-check-label" for="%s">%s</label></div>',
                                $id . $key,
                                absint($field->getFieldId()),
                                $key,
                                $_attr,
                                $id . $key,
                                $choice
                            );
                        }
                    }
                    $field_html = sprintf('<div class="rtcl-check-list">%s</div>', $check_options);
                    break;
                case 'radio' :
                    $options = $field->getOptions();
                    $choices = !empty($options['choices']) && is_array($options['choices']) ? $options['choices'] : array();
                    $check_options = null;
                    if (!empty($choices)) {
                        foreach ($choices as $key => $choice) {
                            $_attr = '';
                            if (trim($key) == $value) {
                                $_attr .= ' checked="checked"';
                            }
                            $_attr .= $required_attr;

                            $check_options .= sprintf('<div class="form-check"><input class="form-check-input" id="%s" type="radio" name="rtcl_fields[_field_%d]" value="%s"%s><label class="form-check-label" for="%s">%s</label></div>',
                                $id . $key,
                                absint($field->getFieldId()),
                                $key,
                                $_attr,
                                $id . $key,
                                $choice
                            );
                        }
                    }
                    $field_html = sprintf('<div class="rtcl-check-list">%s</div>', $check_options);
                    break;
                case 'number':
                    $field_html = sprintf('<input type="number" class="rtcl-number form-control" id="%s" name="rtcl_fields[_field_%d]" placeholder="%s" value="%s" step="%s" min="%s" max="%s"%s />',
                        $id,
                        absint($field->getFieldId()),
                        esc_attr($field->getPlaceholder()),
                        esc_attr($value),
                        $field->getStepSize() ? esc_attr($field->getStepSize()) : 'any',
                        !empty($field->getMin()) || $field->getMin() == 0 ? absint($field->getMin()) : '',
                        !empty($field->getMax()) ? absint($field->getMax()) : '',
                        $required_attr
                    );
                    break;
                case 'url':
                    $field_html = sprintf('<input type="url" class="rtcl-url form-control" id="%s" name="rtcl_fields[_field_%d]" placeholder="%s" value="%s"%s />',
                        $id,
                        absint($field->getFieldId()),
                        esc_attr($field->getPlaceholder()),
                        esc_url($value),
                        $required_attr
                    );
                    break;
                case 'date':
                    $date_type = $field->getDateType();
                    $date_type = $date_type && in_array($date_type, [
                        'date',
                        'date_range',
                        'date_time',
                        'date_time_range'
                    ]) ? $date_type : 'date';
                    $date_format = $field->getDateFullFormat();
                    if (($date_type == 'date_range' || $date_type == 'date_time_range') && is_array($value)) {
                        $start = isset($value['start']) && !empty($value['start']) ? date($date_format, strtotime($value['start'])) : null;
                        $end = isset($value['end']) && !empty($value['end']) ? date($date_format, strtotime($value['end'])) : null;
                        $value = $end ? $start . " - " . $end : $start;
                    } else {
                        $value = !empty($value) ? date($date_format, strtotime($value)) : '';
                    }

                    $field_html = sprintf('<input type="text" class="rtcl-date form-control%s" id="%s" name="rtcl_fields[_field_%d]" placeholder="%s" data-options="%s" value="%s"%s />',
                        ' rtcl-date_' . $date_type,
                        $id,
                        absint($field->getFieldId()),
                        esc_attr($field->getPlaceholder()),
                        htmlspecialchars(wp_json_encode($field->getDateFieldOptions())),
                        esc_html($value),
                        $required_attr
                    );
                    break;
            }

            if (isset($_REQUEST['is_admin']) && $_REQUEST['is_admin'] == 1) {
                $description = $field->getDescription();

                $html .= sprintf('<div class="form-group row"%s>
										    <label for="%s" class="col-2 col-form-label">%s %s</label>
										    <div class="col-10">
										        %s
										        <div class="help-block with-errors"></div>
										        %s
										    </div>
										</div>',
                    $field_attr,
                    $id,
                    $field->getLabel(),
                    $required_label,
                    $field_html,
                    $description ? '<small class="help-block">' . esc_html($description) . '</small>' : null
                );
            } else {
                $html .= self::get_template_html('listing-form/custom-field', array(
                    'field_attr'     => $field_attr,
                    'id'             => $id,
                    'label'          => $field->getLabel(),
                    'required_label' => $required_label,
                    'description'    => $field->getDescription(),
                    'field'          => $field_html
                ));
            }
        }

        return $html;
    }

    static function get_custom_fields_html($term_id = 0, $post_id = null) {
        $field_ids = self::get_custom_field_ids($term_id);
        $html = '';
        if (!empty($field_ids)) {
            foreach ($field_ids as $field_id) {
                $html .= self::get_custom_field_html($field_id, $post_id);
            }
        }

        return $html;
    }

    static function sort_images($images, $post_id) {
        $images_order = json_decode(get_post_meta($post_id, '_rtcl_attachments_order', true));
        if (!is_null($images_order)) {
            $post_thumbnail_id = get_post_thumbnail_id($post_id);
            if ($post_thumbnail_id) {
                array_unshift($images_order, $post_thumbnail_id);
                $images_order = array_unique($images_order);
            }
            uksort($images, array(new SortImages($images_order), "sort"));
        }

        return $images;
    }

    /**
     * @param $post_id
     *
     * @return array
     */
    static function get_listing_images($post_id) {
        $images = [];
        if ($post_id) {
            $children = get_children(apply_filters('rtcl_get_listing_images_query_args', [
                'post_parent'    => $post_id,
                'post_type'      => 'attachment',
                'posts_per_page' => -1,
                'post_status'    => 'inherit'
            ]));
            if (!empty($children)) {
                $sorted_images = Functions::sort_images($children, $post_id);
                foreach ($sorted_images as $images_id => $image) {
                    $images[] = self::get_listing_attachment_props($image->ID);
                }
            }
        }

        return $images;
    }

    /**
     * @param $post_id
     *
     * @return array
     * @deprecated deprecated since version 1.5.64
     */
    static function get_listing_image_ids($post_id) {
        return self::get_listing_images($post_id);
    }

    static function get_listing_first_image_id($listing_id) {
        $images = self::get_listing_images($listing_id);
        $id = 0;
        if (!empty($images)) {
            $images = array_slice($images, 0, 1);
            $id = $images[0]->ID;
        }

        return $id;
    }


    /**
     * Gets data about an attachment, such as alt text and captions.
     *
     * @param int|null     $attachment_id Attachment ID.
     * @param Listing|bool $listing       Listing object.
     *
     * @return object
     * @since 1.3.0
     *
     */
    static function get_listing_attachment_props($attachment_id = null, $listing = false) {
        $props = [
            'ID'      => 0,
            'title'   => '',
            'caption' => '',
            'url'     => '',
            'alt'     => '',
            'src'     => '',
            'srcset'  => false,
            'sizes'   => false,
        ];
        $attachment = get_post($attachment_id);

        if ($attachment) {
            $props['ID'] = $attachment->ID;
            $props['title'] = wp_strip_all_tags($attachment->post_title);
            $props['caption'] = wp_strip_all_tags($attachment->post_excerpt);
            $props['url'] = wp_get_attachment_url($attachment_id);

            // Alt text.
            $alt_text = array(
                wp_strip_all_tags(get_post_meta($attachment_id, '_wp_attachment_image_alt', true)),
                $props['caption'],
                wp_strip_all_tags($attachment->post_title)
            );

            if ($listing && $listing instanceof Listing) {
                $alt_text[] = wp_strip_all_tags(get_the_title($listing->get_id()));
            }

            $alt_text = array_filter($alt_text);
            $props['alt'] = isset($alt_text[0]) ? $alt_text[0] : '';
            $sizes = [];

            $sizes_list = apply_filters('rtcl_listing_attachment_sizes', [
                'full',
                'medium',
                'thumbnail',
            ]);

            foreach ($sizes_list as $size) {
                $_size = str_replace('-', '_', $size);
                $src = wp_get_attachment_image_src($attachment_id, apply_filters('rtcl_listing_attachment_size_' . $_size, $size));
                $sizes[$_size] = [
                    'src'    => $src[0],
                    'width'  => $src[1],
                    'height' => $src[2],
                ];
            }

            // Image source.
            $image_size = apply_filters('rtcl_gallery_image_size', 'rtcl-gallery');
            $src = wp_get_attachment_image_src($attachment_id, $image_size);
            $props['src'] = $src[0];
            $props['src_w'] = $src[1];
            $props['src_h'] = $src[2];
            $props['srcset'] = function_exists('wp_get_attachment_image_srcset') ? wp_get_attachment_image_srcset($attachment_id, $image_size) : false;
            $props['srcset_sizes'] = function_exists('wp_get_attachment_image_sizes') ? wp_get_attachment_image_sizes($attachment_id, $image_size) : false;
            $props['sizes'] = $sizes;

        }

        return (object)apply_filters('rtcl_listing_attachment_props', $props, $attachment_id, $listing);
    }


    public static function listing_feature_thumbnail($post_id) {

        $img_url = '';

        if (has_post_thumbnail($post_id)) {
            $img_url = get_the_post_thumbnail_url($post_id, 'rtcl-thumbnail');
        } else {
            $images = self::get_listing_image_ids($post_id);
            if (!empty($images)) {
                $images = array_slice($images, 0, 1);
                $img_url = wp_get_attachment_image_src($images[0]->ID, 'rtcl-thumbnail');
                $img_url = $img_url[0];
            }

        }

        return $img_url ? sprintf("<img class='rtcl-thumbnail' src='%s' />", $img_url) : null;

    }

    public static function get_pages() {
        $page_list = array();
        $pages = get_pages(
            array(
                'sort_column'  => 'menu_order',
                'sort_order'   => 'ASC',
                'hierarchical' => 0,
            )
        );
        foreach ($pages as $page) {
            $page_list[$page->ID] = !empty($page->post_title) ? $page->post_title : '#' . $page->ID;
        }

        return $page_list;
    }

    static function update_listing_views_count($post_id) {
        $user_ip = $_SERVER['REMOTE_ADDR']; // retrieve the current IP address of the visitor
        $key = $user_ip . '_rtcl_' . $post_id; // combine post ID & IP to form unique key
        $value = array($user_ip, $post_id); // store post ID & IP as separate values (see note)
        $visited = get_transient($key); // get transient and store in variable
        // check to see if the Post ID/IP ($key) address is currently stored as a transient
        if (false === ($visited)) {
            // store the unique key, Post ID & IP address for 12 hours if it does not exist
            set_transient($key, $value, 60 * 60 * 12);

            // now run post views function
            $count_key = '_views';
            $count = get_post_meta($post_id, $count_key, true);
            if ('' == $count) {
                update_post_meta($post_id, $count_key, 0);
            } else {
                $count = absint($count);
                $count++;
                update_post_meta($post_id, $count_key, $count);
            }

        }

    }

    static function of_kses_data($data, $allowed_Tags = array()) {
        return wp_kses($data, $allowed_Tags);
    }

    /**
     * @param $id
     *
     * @return array
     */
    static function get_option($id) {
        if (!$id) {
            return array();
        }
        $settings = get_option($id, array());

        return apply_filters($id, $settings);
    }

    /**
     * @param string $id      Setting option id
     * @param string $item    settings option item id
     * @param null   $default EXCEPT multi_checkbox you can provide default value if given option does not set any value
     * @param null   $type    checkbox, multi_checkbox, number
     *
     * @return bool|int|mixed|null
     */
    static function get_option_item($id, $item, $default = null, $type = null) {
        if (!$item) {
            return false;
        }
        $settings = self::get_option($id);

        if ($type === 'checkbox') {
            if (isset($settings[$item])) {
                return $settings[$item] === 'yes';
            }
            return $default;
        } elseif ($type === 'multi_checkbox') {
            return isset($settings[$item]) && is_array($settings[$item]) && in_array($default, $settings[$item]);
        } elseif ($type === 'number') {
            return isset($settings[$item]) ? absint($settings[$item]) : absint($default);
        }

        return isset($settings[$item]) && !empty($settings[$item]) ? $settings[$item] : $default;
    }

    public static function get_listing_types() {

        $default_types = Options::get_default_listing_types();
        $types = Functions::get_option(rtcl()->get_listing_types_option_id());
        $types = !empty($types) ? $types : $default_types;
        $types = apply_filters_deprecated('rtcl_ad_type', array($types), '1.2.17', 'rtcl_get_listing_types');

        return apply_filters('rtcl_get_listing_types', !empty($types) ? $types : $default_types);
    }


    /**
     * Make a string lowercase.
     * Try to use mb_strtolower() when available.
     *
     * @param string $string String to format.
     *
     * @return string
     * @since  1.0.0
     */
    static function strtolower($string) {
        return function_exists('mb_strtolower') ? mb_strtolower($string) : strtolower($string);
    }

    /**
     * Convert a date string to a Rtcl_DateTime.
     *
     * @param string $time_string Time string.
     *
     * @return RtclDateTime
     * @throws \Exception
     * @since  3.1.0
     */
    static function string_to_datetime($time_string) {
        // Strings are defined in local WP timezone. Convert to UTC.
        if (1 === preg_match('/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2})(Z|((-|\+)\d{2}:\d{2}))$/', $time_string, $date_bits)) {
            $offset = !empty($date_bits[7]) ? iso8601_timezone_to_offset($date_bits[7]) : self::timezone_offset();
            $timestamp = gmmktime($date_bits[4], $date_bits[5], $date_bits[6], $date_bits[2], $date_bits[3], $date_bits[1]) - $offset;
        } else {
            $timestamp = self::string_to_timestamp(get_gmt_from_date(gmdate('Y-m-d H:i:s', self::string_to_timestamp($time_string))));
        }
        $datetime = new RtclDateTime("@{$timestamp}", new DateTimeZone('UTC'));

        // Set local timezone or offset.
        if (get_option('timezone_string')) {
            $datetime->setTimezone(new DateTimeZone(self::timezone_string()));
        } else {
            $datetime->set_utc_offset(self::timezone_offset());
        }

        return $datetime;
    }

    /**
     * Convert mysql datetime to PHP timestamp, forcing UTC. Wrapper for strtotime.
     * Based on wcs_strtotime_dark_knight() from WC Subscriptions by Prospress.
     *
     * @param string   $time_string    Time string.
     * @param int|null $from_timestamp Timestamp to convert from.
     *
     * @return int
     * @since  1.0.0
     */
    static function string_to_timestamp($time_string, $from_timestamp = null) {
        $original_timezone = date_default_timezone_get();

        // @codingStandardsIgnoreStart
        date_default_timezone_set('UTC');

        if (null === $from_timestamp) {
            $next_timestamp = strtotime($time_string);
        } else {
            $next_timestamp = strtotime($time_string, $from_timestamp);
        }

        date_default_timezone_set($original_timezone);

        // @codingStandardsIgnoreEnd

        return $next_timestamp;
    }


    /**
     * Converts a string (e.g. 'yes' or 'no') to a bool.
     *
     * @param string $string String to convert.
     *
     * @return bool
     * @since 1.5.56
     */
    static function string_to_bool($string) {
        return is_bool($string) ? $string : ('yes' === strtolower($string) || 1 === $string || 'true' === strtolower($string) || '1' === $string);
    }

    /**
     * Get timezone offset in seconds.
     *
     * @return float
     * @throws \Exception
     * @since  3.0.0
     */
    static function timezone_offset() {
        $timezone = get_option('timezone_string');

        if ($timezone) {
            $timezone_object = new DateTimeZone($timezone);

            return $timezone_object->getOffset(new DateTime('now'));
        } else {
            return floatval(get_option('gmt_offset', 0)) * HOUR_IN_SECONDS;
        }
    }


    /**
     * Timezone - helper to retrieve the timezone string for a site until.
     *
     * @return string PHP timezone string for the site
     * @since 1.0.0
     */
    static function timezone_string() {
        // If site timezone string exists, return it.
        $timezone = get_option('timezone_string');
        if ($timezone) {
            return $timezone;
        }

        // Get UTC offset, if it isn't set then return UTC.
        $utc_offset = intval(get_option('gmt_offset', 0));
        if (0 === $utc_offset) {
            return 'UTC';
        }

        // Adjust UTC offset from hours to seconds.
        $utc_offset *= 3600;

        // Attempt to guess the timezone string from the UTC offset.
        $timezone = timezone_name_from_abbr('', $utc_offset);
        if ($timezone) {
            return $timezone;
        }

        // Last try, guess timezone string manually.
        foreach (timezone_abbreviations_list() as $abbr) {
            foreach ($abbr as $city) {
                if ((bool)date('I') === (bool)$city['dst'] && $city['timezone_id'] && intval($city['offset']) === $utc_offset) {
                    return $city['timezone_id'];
                }
            }
        }

        // Fallback to UTC.
        return 'UTC';
    }

    /**
     * Date Format.
     *
     * @return string
     */
    static function date_format() {
        return apply_filters('rtcl_date_format', get_option('date_format'));
    }

    /**
     * Time Format.
     *
     * @return string
     */
    static function time_format() {
        return apply_filters('rtcl_time_format', get_option('time_format'));
    }

    static function datetime($format = 'mysql', $date = null, $gmt = false) {
        if (is_null($date) || strlen($date) === 0) {
            $timestamp = current_time('timestamp', $gmt);
        } else if (is_string($date)) {
            $timestamp = strtotime($date);
        } else {
            $timestamp = $date;
        }

        switch ($format) {
            case 'mysql':
                return date('Y-m-d H:i:s', $timestamp);
            case 'timestamp':
                return $timestamp;
            case 'time-elapsed':
                return sprintf(__('%s ago', 'classified-listing'), human_time_diff(strtotime($date), current_time('timestamp', $gmt)));
            case 'rtcl':
                return date_i18n(get_option('date_format'),
                        $timestamp) . ' @ ' . date_i18n(get_option('time_format'), $timestamp);
            case 'rtcl-date':
                return date_i18n(get_option('date_format'), $timestamp);
            case 'rtcl-time':
                return date_i18n(get_option('time_format'), $timestamp);
            default:
                return date_i18n($format, $timestamp);
        }
    }

    static function set_datetime_date($datetime, $date) {
        $base_timestamp = strtotime($datetime);
        $base_year_month_day_timestamp = strtotime(date('Y-m-d', strtotime($datetime)));
        $time_of_the_day_in_seconds = $base_timestamp - $base_year_month_day_timestamp;

        $target_year_month_day_timestamp = strtotime(date('Y-m-d', strtotime($date)));

        $new_datetime_timestamp = $target_year_month_day_timestamp + $time_of_the_day_in_seconds;

        return self::datetime('mysql', $new_datetime_timestamp);
    }

    static function extend_date_to_end_of_the_day($datetime) {
        $next_day = strtotime('+ 1 days', $datetime);
        $zero_hours_next_day = strtotime(date('Y-m-d', $next_day));
        $end_of_the_day = $zero_hours_next_day - 1;

        return $end_of_the_day;
    }

    static function is_mysql_date($date) {
        $regexp = '/^\d{4}-\d{1,2}-\d{1,2}(\s\d{1,2}:\d{1,2}(:\d{1,2})?)?$/';

        return preg_match($regexp, $date) === 1;
    }

    /**
     * Wrapper for set_time_limit to see if it is enabled.
     *
     * @param int $limit Time limit.
     *
     * @since 1.5.58
     */
    static function set_time_limit($limit = 0) {
        if (function_exists('set_time_limit') && false === strpos(ini_get('disable_functions'), 'set_time_limit') && !ini_get('safe_mode')) { // phpcs:ignore PHPCompatibility.IniDirectives.RemovedIniDirectives.safe_modeDeprecatedRemoved
            @set_time_limit($limit); // @codingStandardsIgnoreLine
        }
    }

    /**
     * @param string $page
     *
     * @return int|void
     */
    public static function get_page_id($page) {
        if ('pay' === $page || 'thanks' === $page || 'promote' === $page || 'submission' === $page || 'payment-receipt' === $page || 'payment-failure' === $page) {
            $page = 'checkout';
        }
        if ('change_password' === $page || 'edit_address' === $page || 'lost_password' === $page) {
            $page = 'myaccount';
        }

        $page_id = apply_filters('rtcl_get_' . $page . '_page_id', self::get_option_item('rtcl_advanced_settings', $page, -1, 'number'));

        return apply_filters('rtcl_get_page_id', $page_id, $page);
    }

    /**
     * @return array
     */
    static function get_page_ids() {
        $pages = AddConfig::get_custom_page_list();
        $page_ids = [];
        foreach ($pages as $page_key => $page_title) {
            if ($id = self::get_page_id($page_key)) {
                $page_ids[$page_key] = $id;
            }
        }

        return apply_filters('rtcl_get_page_ids', $page_ids);
    }

    static function insert_custom_pages() {

        // Vars
        $page_settings = self::get_page_ids();
        $page_definitions = AddConfig::get_custom_page_list();
        // ...
        $pages = array();
        foreach ($page_definitions as $slug => $page) {
            $id = 0;
            if (array_key_exists($slug, $page_settings)) {
                $id = (int)$page_settings[$slug];
            }
            if (!$id) {
                $id = wp_insert_post(
                    array(
                        'post_title'     => $page['title'],
                        'post_content'   => $page['content'],
                        'post_status'    => 'publish',
                        'post_author'    => 1,
                        'post_type'      => 'page',
                        'comment_status' => 'closed'
                    )
                );
            }
            $pages[$slug] = $id;
        }

        return $pages;

    }

    static function sanitize($value, $type = null) {
        $sanitize_value = null;
        switch ($type) {
            case 'title':
                $sanitize_value = sanitize_text_field($value);
                if ($title_limit = Functions::get_title_character_limit()) {
                    $sanitize_value = mb_substr($sanitize_value, 0, $title_limit, "utf-8");
                }
                break;
            case 'content':
                if ($description_limit = Functions::get_description_character_limit()) {
                    if (strlen($value) > $description_limit) {
                        $sanitize_value = wp_filter_nohtml_kses($value);
                        $sanitize_value = mb_substr($sanitize_value, 0, $description_limit, "utf-8");
                    } else {
                        $sanitize_value = wp_kses_post($value);
                    }
                } else {
                    $sanitize_value = wp_kses_post($value);
                }

                break;
            case 'textarea' :
                $sanitize_value = esc_textarea($value);
                break;
            case 'checkbox' :
                $sanitize_value = array_map('esc_attr', is_array($value) ? $value : array());
                break;
            case 'url' :
                $sanitize_value = esc_url_raw($value);
                break;
            default:
                $sanitize_value = sanitize_text_field($value);
                break;
        }

        return apply_filters('rtcl_sanitize', $sanitize_value, $value, $type);
    }


    /**
     * Clean variables using sanitize_text_field. Arrays are cleaned recursively.
     * Non-scalar values are ignored.
     *
     * @param string|array $var Data to sanitize.
     *
     * @return string|array
     */
    static function clean($var) {
        if (is_array($var)) {
            return array_map(array(self::class, 'clean'), $var);
        } else {
            return is_scalar($var) ? sanitize_text_field($var) : $var;
        }
    }

    static function is_registration_enabled() {

        $enable = Functions::get_option_item('rtcl_account_settings', 'enable_myaccount_registration', false, 'checkbox');

        if ($enable && get_option('users_can_register')) {
            return true;
        }

        return false;

    }

    /**
     * @param array $args
     */
    static function login_form($args = array()) {

        $defaults = array(
            'message'  => '',
            'redirect' => '',
            'hidden'   => false,
        );

        $args = wp_parse_args($args, $defaults);

        Functions::get_template(apply_filters('rtcl_login_form_template_path', 'global/form-login', $args), $args);
    }


    public static function get_account_menu_items() {
        $endpoints = self::get_my_account_page_endpoints();

        $items = array();
        $menu_items = array(
            'dashboard'    => __('Dashboard', 'classified-listing'),
            'listings'     => __('My Listings', 'classified-listing'),
            'favourites'   => __('Favourites', 'classified-listing'),
            'chat'         => __('Chat', 'classified-listing'),
            'payments'     => __('Payments', 'classified-listing'),
            'edit-account' => __('Account details', 'classified-listing'),
            'logout'       => __('Logout', 'classified-listing'),
        );

        // Remove missing endpoints.
        foreach ($endpoints as $endpoint_id => $endpoint) {
            if (empty($endpoint)) {
                unset($menu_items[$endpoint_id]);
            }
        }

        // Remove unused endpoints.
        foreach ($menu_items as $item_id => $item) {
            if ($item_id == "dashboard" || in_array($item_id, array_keys($endpoints))) {
                $items[$item_id] = $item;
            }
        }

        return apply_filters('rtcl_account_menu_items', $items);
    }

    public static function get_account_menu_item_classes($endpoint) {
        global $wp;

        $classes = array(
            'rtcl-MyAccount-navigation-link',
            'rtcl-MyAccount-navigation-link--' . $endpoint,
        );

        // Set current item class.
        $current = isset($wp->query_vars[$endpoint]);
        if ('dashboard' === $endpoint && (isset($wp->query_vars['page']) || empty($wp->query_vars))) {
            $current = true; // Dashboard is not an endpoint, so needs a custom check.
        }

        if ($current) {
            $classes[] = 'is-active';
        }

        $classes = apply_filters('rtcl_account_menu_item_classes', $classes, $endpoint, $wp->query_vars);

        return implode(' ', array_map('sanitize_html_class', $classes));
    }

    public static function remove_query_arg($key, $query = false) {

        if (is_array($key)) { // removing multiple keys
            foreach ($key as $k) {
                $query = str_replace('#038;', '&', $query);
                $query = add_query_arg($k, false, $query);
            }

            return $query;
        }

        return add_query_arg($key, false, $query);

    }

    public static function locate_template($name) {
        // Look within passed path within the theme - this is priority.
        $template = [];
        // Legacy template support
        if (false !== strpos($name, 'listings/single/') || false !== strpos($name, 'listings/')) {
            $legacy_name = $name;
            $name = str_replace(['listings/single/', 'listings/'], ['listing/', 'listing/'], $name);
            $template[] = rtcl()->get_template_path() . $legacy_name . ".php";
        }
        $template[] = rtcl()->get_template_path() . $name . ".php";

        if (!$template_file = locate_template(apply_filters('rtcl_locate_template_names', $template))) {
            $template_file = RTCL_PATH . "templates/$name.php";
        }

        return apply_filters('rtcl_locate_template', $template_file, $name);
    }


    /**
     * Get template part (for templates like the shop-loop).
     *
     * RTCL_TEMPLATE_DEBUG_MODE will prevent overrides in themes from taking priority.
     *
     * @param mixed  $slug Template slug.
     * @param string $name Template name (default: '').
     */
    static function get_template_part($slug, $name = '') {
        $cache_key = sanitize_key(implode('-', array('template-part', $slug, $name, rtcl()->version())));
        $template = (string)wp_cache_get($cache_key, 'rtcl');

        if (!$template) {
            if ($name) {
                $template = RTCL_TEMPLATE_DEBUG_MODE ? '' : locate_template(
                    array(
                        "{$slug}-{$name}.php",
                        rtcl()->get_template_path() . "{$slug}-{$name}.php"
                    )
                );

                if (!$template) {
                    $fallback = rtcl()->plugin_path() . "/templates/{$slug}-{$name}.php";
                    $template = file_exists($fallback) ? $fallback : '';
                }
            }


            if (!$template) {
                // If template file doesn't exist, look in yourtheme/slug.php and yourtheme/classified-listing/slug.php.
                $template = RTCL_TEMPLATE_DEBUG_MODE ? '' : locate_template(
                    array(
                        "{$slug}.php",
                        rtcl()->get_template_path() . "{$slug}.php",
                    )
                );
            }
            wp_cache_set($cache_key, $template, 'rtcl');
        }

        // Allow 3rd party plugins to filter template file from their plugin.
        $template = apply_filters('rtcl_get_template_part', $template, $slug, $name);

        if ($template) {
            load_template($template, false);
        }
    }

    static function get_template($fileName, $args = null) {

        if (!empty($args) && is_array($args)) {
            extract($args); // @codingStandardsIgnoreLine
        }

        $located = self::locate_template($fileName);


        if (!file_exists($located)) {
            /* translators: %s template */
            self::doing_it_wrong(__FUNCTION__, sprintf(__('%s does not exist.', 'classified-listing'), '<code>' . $located . '</code>'), '1.0');

            return;
        }

        // Allow 3rd party plugin filter template file from their plugin.
        $located = apply_filters('rtcl_get_template', $located, $fileName, $args);

        do_action('rtcl_before_template_part', $fileName, $located, $args);

        include $located;

        do_action('rtcl_after_template_part', $fileName, $located, $args);

    }

    static public function get_template_html($template_name, $args = null) {
        ob_start();
        self::get_template($template_name, $args);

        return ob_get_clean();

    }

    /**
     * @param $id
     *
     * @return PaymentGateway|null
     */
    static function get_payment_gateway($id) {
        $payment_gateways = rtcl()->payment_gateways();
        $gateway = array_filter($payment_gateways, function ($gateway) use ($id) {
            return $gateway->id == $id;
        });
        if (!empty($gateway)) {
            return reset($gateway);
        }

        return null;
    }


    static function get_payment_method_list() {
        $gateways = rtcl()->payment_gateways();
        $list = array();
        foreach ($gateways as $gateway) {
            if ('yes' === $gateway->enabled) {
                $list[] = Functions::get_template_html('checkout/payment-method', $gateway);
            }

        }

        return $list;
    }


    /**
     * @param       $payment Payment
     * @param array $data
     *
     * @throws \Exception
     */
    static function rtcl_payment_completed($payment, $data = array()) {
        if (is_a($payment, Payment::class)) {

            // update order details
            wp_update_post(array(
                'ID'                => $payment->get_id(),
                'post_status'       => 'rtcl-completed',
                'post_modified'     => current_time('mysql'),
                'post_modified_gmt' => current_time('mysql', 1),
            ));
            if (!empty($data['transaction_id'])) {
                update_post_meta($payment->get_id(), 'transaction_id', $data['transaction_id']);
            } else {
                update_post_meta($payment->get_id(), 'transaction_id', wp_generate_password(12, false));
            }

            if ('publish' == get_post_status($payment->get_listing_id())) {
                $current_date = new \DateTime(current_time('mysql'));
                $visible = $payment->pricing->getVisible();
                $expiry_date = get_post_meta($payment->get_listing_id(), 'expiry_date', true);
                if ($expiry_date) {
                    $expiry_date = new \DateTime(Functions::datetime('mysql', trim(($expiry_date))));
                    if ($current_date > $expiry_date) {
                        $current_date->add(new \DateInterval("P{$visible}D"));
                        $expDate = $current_date->format('Y-m-d H:i:s');
                    } else {
                        $expiry_date->add(new \DateInterval("P{$visible}D"));
                        $expDate = $expiry_date->format('Y-m-d H:i:s');
                    }
                    update_post_meta($payment->get_listing_id(), 'expiry_date', $expDate);
                }

                if ($payment->pricing->getFeatured()) {
                    update_post_meta($payment->get_listing_id(), 'featured', 1);
                    $feature_expiry_date = get_post_meta($payment->get_listing_id(), 'feature_expiry_date', true);
                    if ($feature_expiry_date) {
                        $feature_expiry_date = new \DateTime(Functions::datetime('mysql',
                            trim(($feature_expiry_date))));
                        if ($current_date > $feature_expiry_date) {
                            delete_post_meta($payment->get_listing_id(), 'feature_expiry_date');
                        } else {
                            $feature_expiry_date->add(new \DateInterval("P{$visible}D"));
                            $featureExpDate = $feature_expiry_date->format('Y-m-d H:i:s');
                            update_post_meta($payment->get_listing_id(), 'feature_expiry_date', $featureExpDate);
                        }
                    }
                }
                update_post_meta($payment->get_id(), '_applied', 1);
            }


            // Hook for developers
            do_action('rtcl_payment_completed', $payment->get_id());

            // send emails
            if (Functions::get_option_item('rtcl_email_settings', 'notify_users', 'order_completed', 'multi_checkbox')) {
                rtcl()->mailer()->emails['Order_Completed_Email_To_Customer']->trigger($payment->get_id(), $payment);
            }
            // send emails
            if (Functions::get_option_item('rtcl_email_settings', 'notify_admin', 'order_completed', 'multi_checkbox')) {
                rtcl()->mailer()->emails['Order_Completed_Email_To_Admin']->trigger($payment->get_id(), $payment);
            }
        }

    }

    public static function get_ip_address() {
        if (isset($_SERVER['HTTP_X_REAL_IP'])) { // WPCS: input var ok, CSRF ok.
            return sanitize_text_field(wp_unslash($_SERVER['HTTP_X_REAL_IP']));  // WPCS: input var ok, CSRF ok.
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) { // WPCS: input var ok, CSRF ok.
            // Proxy servers can send through this header like this: X-Forwarded-For: client1, proxy1, proxy2
            // Make sure we always only send through the first IP in the list which should always be the client IP.
            return (string)rest_is_ip_address(trim(current(preg_split('/[,:]/',
                sanitize_text_field(wp_unslash($_SERVER['HTTP_X_FORWARDED_FOR'])))))); // WPCS: input var ok, CSRF ok.
        } elseif (isset($_SERVER['REMOTE_ADDR'])) { // @codingStandardsIgnoreLine
            return sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])); // @codingStandardsIgnoreLine
        }

        return '';
    }

    static function doing_it_wrong($function, $message, $version) {
        // @codingStandardsIgnoreStart
        $message .= ' Backtrace: ' . wp_debug_backtrace_summary();

//		if ( is_ajax() ) {
//			do_action( 'doing_it_wrong_run', $function, $message, $version );
//			error_log( "{$function} was called incorrectly. {$message}. This message was added in version {$version}." );
//		} else {
        _doing_it_wrong($function, $message, $version);
        //}
    }

    /**
     * @param        $term_id
     * @param string $taxonomy
     * @param bool   $pad_counts
     *
     * @return int
     */
    static function get_listings_count_by_taxonomy($term_id, $taxonomy = null, $pad_counts = true) {

        $taxonomy = $taxonomy ? $taxonomy : rtcl()->category;

        $args = array(
            'fields'           => 'ids',
            'posts_per_page'   => -1,
            'post_type'        => rtcl()->post_type,
            'post_status'      => 'publish',
            'suppress_filters' => false,
            'tax_query'        => array(
                array(
                    'taxonomy'         => $taxonomy,
                    'field'            => 'term_id',
                    'terms'            => $term_id,
                    'include_children' => $pad_counts
                )
            )
        );
        $q = new WP_Query(apply_filters('rtcl_listings_count_by_taxonomy_query_args', $args));

        return $q->post_count;

    }

    public static function print_notices() {
        if (!did_action('rtcl_init')) {
            Functions::doing_it_wrong(__FUNCTION__, __('This function should not be called before rtcl_init.', 'classified-listing'), '1.0');

            return;
        }

        $all_notices = rtcl()->session->get('rtcl_notices', array());
        $notice_types = apply_filters('rtcl_notice_types', array('error', 'success', 'notice'));

        foreach ($notice_types as $notice_type) {
            if (self::notice_count($notice_type) > 0) {
                Functions::get_template("notices/{$notice_type}", array(
                    'messages' => array_filter($all_notices[$notice_type])
                ));
            }
        }

        self::clear_notices();
    }

    public static function clear_notices() {
        if (!did_action('rtcl_init')) {
            Functions::doing_it_wrong(__FUNCTION__, __('This function should not be called before rtcl_init.', 'classified-listing'), '1.0');

            return;
        }
        rtcl()->session->set('rtcl_notices', null);
    }

    public static function notice_count($notice_type = '') {
        if (!did_action('rtcl_init')) {
            Functions::doing_it_wrong(__FUNCTION__, __('This function should not be called before rtcl_init.', 'classified-listing'), '1.0');

            return 0;
        }

        $notice_count = 0;
        $all_notices = rtcl()->session->get('rtcl_notices', array());

        if (isset($all_notices[$notice_type])) {

            $notice_count = count($all_notices[$notice_type]);

        } elseif (empty($notice_type)) {

            foreach ($all_notices as $notices) {
                $notice_count += count($notices);
            }
        }

        return $notice_count;
    }

    public static function has_notice($message, $notice_type = 'success') {
        if (!did_action('rtcl_init')) {
            self::doing_it_wrong(__FUNCTION__, __('This function should not be called before rtcl_init.', 'classified-listing'), '1.0');

            return false;
        }

        $notices = rtcl()->session->get('rtcl_notices', array());
        $notices = isset($notices[$notice_type]) ? $notices[$notice_type] : array();

        return array_search($message, $notices, true) !== false;
    }

    public static function add_notice($message, $notice_type = 'success') {
        if (!did_action('rtcl_init')) {
            Functions::doing_it_wrong(__FUNCTION__, __('This function should not be called before rtcl_init.', 'classified-listing'), '1.0');

            return;
        }

        $notices = rtcl()->session->get('rtcl_notices', array());

        // Backward compatibility.
        if ('success' === $notice_type) {
            $message = apply_filters('rtcl_add_message', $message);
        }

        $notices[$notice_type][] = apply_filters('rtcl_add_' . $notice_type, $message);

        rtcl()->session->set('rtcl_notices', $notices);
    }


    /**
     * Returns all queued notices, optionally filtered by a notice type.
     *
     * @param string $notice_type Optional. The singular name of the notice type - either error, success or notice.
     *
     * @return array|mixed
     * @since  1.0
     */
    public static function get_notices($notice_type = '') {
        if (!did_action('rtcl_init')) {
            self::doing_it_wrong(__FUNCTION__, __('This function should not be called before rtcl_init.', 'classified-listing'), '1.0');

            return;
        }

        $all_notices = rtcl()->session->get('rtcl_notices', array());

        if (empty($notice_type)) {
            $notices = $all_notices;
        } elseif (isset($all_notices[$notice_type])) {
            $notices = $all_notices[$notice_type];
        } else {
            $notices = array();
        }

        return $notices;
    }

    public static function add_wp_error_notices($errors) {
        if (is_wp_error($errors) && $errors->get_error_messages()) {
            foreach ($errors->get_error_messages() as $error) {
                self::add_notice($error, 'error');
            }
        }
    }

    public static function setcookie($name, $value, $expire = 0, $secure = false) {
        if (!headers_sent()) {
            setcookie($name, $value, $expire, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN, $secure, apply_filters('rtcl_cookie_httponly', false, $name, $value, $expire, $secure));
        } elseif (defined('WP_DEBUG') && WP_DEBUG) {
            headers_sent($file, $line);
            trigger_error("{$name} cookie cannot be set - headers already sent by {$file} on line {$line}", E_USER_NOTICE); // @codingStandardsIgnoreLine
        }
    }

    /**
     * @param      $userData
     *
     * @return boolean
     */
    public static function do_registration_from_listing_form($userData) {

        try {
            $userData = wp_parse_args($userData, [
                'email'    => '',
                'username' => '',
                'password' => ''
            ]);
            extract($userData);
            $validation_error = new \WP_Error();
            /**
             * @var string $email
             * @var string $username
             * @var string $password
             */
            $validation_error = apply_filters('rtcl_process_registration_errors', $validation_error, $email, $username, $password, $userData);

            if ($validation_error->get_error_code()) {
                throw new \Exception($validation_error->get_error_message());
            }
            add_filter('rtcl_registration_name_validation', __return_false());
            add_filter('rtcl_registration_phone_validation', __return_false());
            $new_user_id = Functions::create_new_user(sanitize_email($email));
            add_filter('rtcl_registration_name_validation', __return_true());
            add_filter('rtcl_registration_phone_validation', __return_true());

            if (is_wp_error($new_user_id)) {
                throw new \Exception($new_user_id->get_error_message());
            }

            if (!apply_filters('rtcl_registration_need_auth_new_user', false, $new_user_id)) {
                Functions::set_customer_auth_cookie($new_user_id);
            }

            return $new_user_id;

        } catch (\Exception $e) {
            Functions::add_notice('<strong>' . __('Error:', 'classified-listing') . '</strong> ' . $e->getMessage(), 'error');

            return false;
        }
    }


    /**
     * Determines whether the given Phone exists.
     *
     * @param string $phone Phone number.
     *
     * @return int|false The user's ID on success, and false on failure.
     */
    static function phone_exists($phone) {
        $users = get_users([
            'meta_key'    => '_rtcl_phone',
            'meta_value'  => $phone,
            'number'      => 1,
            'count_total' => false
        ]);
        if (!empty($users)) {
            return $users[0]->ID;
        }
        return false;
    }

    /**
     * @param string $email
     * @param string $username
     * @param string $password
     * @param array  $args List of arguments to pass to `wp_insert_user()`.
     *
     *
     * @return int|\WP_Error Returns WP_Error on failure, Int (user ID) on success.
     */
    public static function create_new_user($email, $username = '', $password = '', $args = array()) {

        // Check the email address.
        if (empty($email) || !is_email($email)) {
            return new \WP_Error('registration-error-invalid-email', __('Please provide a valid email address.', 'classified-listing'));
        }

        if (email_exists($email)) {
            return new \WP_Error('registration-error-email-exists', apply_filters('rtcl_registration_error_email_exists', __('An account is already registered with your email address. Please log in.', 'classified-listing'), $email));
        }

        // Handle username creation.
        if (empty($username) && apply_filters('rtcl_registration_generate_username', true)) {
            $username = self::create_new_user_username($email, $args);
        }
        $username = sanitize_user($username);

        if (empty($username) || !validate_username($username)) {
            return new \WP_Error('registration-error-invalid-username', __('Please enter a valid account username.', 'classified-listing'));
        }

        if (username_exists($username)) {
            return new \WP_Error('registration-error-username-exists', __('An account is already registered with that username. Please choose another.', 'classified-listing'));
        }

        if (apply_filters('rtcl_registration_name_validation', true)) {
            if (empty($args['first_name'])) {
                return new \WP_Error('registration-error-invalid-first_name', __('Please enter your first name.', 'classified-listing'));
            }
            if (empty($args['last_name'])) {
                return new \WP_Error('registration-error-invalid-last_name', __('Please enter your last name.', 'classified-listing'));
            }
        }
        if (apply_filters('rtcl_registration_phone_validation', true)) {
            if (empty($args['phone'])) {
                return new \WP_Error('registration-error-invalid-last_name', __('Please enter your phone.', 'classified-listing'));
            }
            if (self::phone_exists($args['phone'])) {
                return new \WP_Error('registration-error-email-exists', apply_filters('rtcl_registration_error_phone_exists', __('An account is already registered with your phone number. Please log in.', 'classified-listing'), $args['phone']));
            }
        }

        // Handle password creation.
        $password_generated = false;
        if (apply_filters('rtcl_registration_generate_password', true) && empty($password)) {
            $password = wp_generate_password();
            $password_generated = true;
        }

        if (empty($password)) {
            return new \WP_Error('registration-error-missing-password', __('Please enter an account password.', 'classified-listing'));
        }

        // Use WP_Error to handle registration errors.
        $errors = new \WP_Error();


        do_action('rtcl_register_data', $username, $email, $args, $_REQUEST, $errors);

        $errors = apply_filters('rtcl_registration_errors', $errors, $username, $email, $args, $_REQUEST);

        if ($errors->get_error_code()) {
            return $errors;
        }

        $role = Functions::get_option_item('rtcl_account_settings', 'user_role', get_option('default_role'));
        $new_user_data = apply_filters('rtcl_new_user_data',
            array_merge(
                $args,
                [
                    'user_login' => $username,
                    'user_pass'  => $password,
                    'user_email' => $email,
                    'role'       => $role
                ]
            )
        );

        $user_id = wp_insert_user($new_user_data);

        if (is_wp_error($user_id)) {
            return new \WP_Error('registration-error', '<strong>' . __('Error:', 'classified-listing') . '</strong> ' . __('Couldn&#8217;t register you&hellip; please contact us if you continue to have problems.', 'classified-listing'));
        }

        if (!empty($args['phone'])) {
            update_user_meta($user_id, '_rtcl_phone', esc_attr($args['phone']));
        }

        do_action('rtcl_new_user_created', $user_id, $new_user_data, $password_generated);

        return $user_id;
    }


    /**
     * Create a unique username for a new User.
     *
     * @param string $email         New customer email address.
     * @param array  $new_user_args Array of new user args, maybe including first and last names.
     * @param string $suffix        Append string to username to make it unique.
     *
     * @return string Generated username.
     * @since 1.5.55
     */
    static function create_new_user_username($email, $new_user_args = array(), $suffix = '') {
        $username_parts = array();

        if (isset($new_user_args['first_name'])) {
            $username_parts[] = sanitize_user($new_user_args['first_name'], true);
        }

        if (isset($new_user_args['last_name'])) {
            $username_parts[] = sanitize_user($new_user_args['last_name'], true);
        }

        // Remove empty parts.
        $username_parts = array_filter($username_parts);

        // If there are no parts, e.g. name had unicode chars, or was not provided, fallback to email.
        if (empty($username_parts)) {
            $email_parts = explode('@', $email);
            $email_username = $email_parts[0];

            // Exclude common prefixes.
            if (in_array(
                $email_username,
                array(
                    'sales',
                    'hello',
                    'mail',
                    'contact',
                    'info',
                ),
                true
            )) {
                // Get the domain part.
                $email_username = $email_parts[1];
            }

            $username_parts[] = sanitize_user($email_username, true);
        }

        $username = self::strtolower(implode('.', $username_parts));

        if ($suffix) {
            $username .= $suffix;
        }

        /**
         * WordPress 4.4 - filters the list of blacklisted usernames.
         *
         * @param array $usernames Array of blacklisted usernames.
         *
         * @since 3.7.0
         */
        $illegal_logins = (array)apply_filters('illegal_user_logins', array());

        // Stop illegal logins and generate a new random username.
        if (in_array(strtolower($username), array_map('strtolower', $illegal_logins), true)) {
            $new_args = array();

            /**
             * Filter generated customer username.
             *
             * @param string $username      Generated username.
             * @param string $email         New customer email address.
             * @param array  $new_user_args Array of new user args, maybe including first and last names.
             * @param string $suffix        Append string to username to make it unique.
             *
             * @since 3.7.0
             */
            $new_args['first_name'] = apply_filters(
                'rtcl_generated_user_username',
                'rtcl_user_' . zeroise(wp_rand(0, 9999), 4),
                $email,
                $new_user_args,
                $suffix
            );

            return self::create_new_user_username($email, $new_args, $suffix);
        }

        if (username_exists($username)) {
            // Generate something unique to append to the username in case of a conflict with another user.
            $suffix = '-' . zeroise(wp_rand(0, 9999), 4);
            return self::create_new_user_username($email, $new_user_args, $suffix);
        }

        /**
         * Filter new customer username.
         *
         * @param string $username      Customer username.
         * @param string $email         New customer email address.
         * @param array  $new_user_args Array of new user args, maybe including first and last names.
         * @param string $suffix        Append string to username to make it unique.
         *
         * @since 3.7.0
         */
        return apply_filters('rtcl_new_user_username', $username, $email, $new_user_args, $suffix);
    }


    /**
     * @param $user_id
     */
    public static function set_customer_auth_cookie($user_id) {
        global $current_user;

        $current_user = get_user_by('id', $user_id);

        wp_set_auth_cookie($user_id, true);
    }

    static function in_array_any($needles, $haystack) {
        return !!array_intersect($needles, $haystack);
    }

    static function in_array_all($needles, $haystack) {
        return !array_diff($needles, $haystack);
    }

    public static function array_insert(&$array, $position, $insert_array) {
        $first_array = array_splice($array, 0, $position + 1);
        $array = array_merge($first_array, $insert_array, $array);
    }


    static function array_insert_after($key, $array, $new_array) {

        if (array_key_exists($key, $array)) {
            $new = array();
            foreach ($array as $k => $value) {
                $new[$k] = $value;
                if ($k === $key) {
                    foreach ($new_array as $new_key => $new_value) {
                        $new[$new_key] = $new_value;
                    }
                }
            }

            return $new;
        }

        return $array;
    }

    /**
     * @param $listing_id
     *
     * @throws \Exception
     */
    static function apply_payment_pricing($listing_id) {

        $args = array(
            'post_type'        => rtcl()->post_type_payment,
            'post_status'      => 'rtcl-completed',
            'posts_per_page'   => -1,
            'suppress_filters' => false,
            'fields'           => 'ids',
            'meta_query'       => array(
                'relation' => 'AND',
                array(
                    'key'     => '_applied',
                    'compare' => 'NOT EXISTS',
                ),
                array(
                    'key'     => 'listing_id',
                    'value'   => $listing_id,
                    'compare' => '='
                )
            )
        );
        $publish_count = absint(get_post_meta($listing_id, '_rtcl_publish_count', true));
        $payment_ids = get_posts(apply_filters('rtcl_get_all_unapplied_payments_query_args', $args, $listing_id));

        if (!empty($payment_ids)) {
            $notPromotionVisible = 0;
            $hasPromotion = false;
            $promotions = [];
            foreach ($payment_ids as $payment_id) {
                $payment = rtcl()->factory->get_order($payment_id);
                $visible = absint($payment->pricing->getVisible());
                if ($payment->pricing->hasPromotion()) {
                    $hasPromotion = true;
                    if ($payment->pricing->hasFeatured()) {
                        $promotions['featured'] = (isset($promotions['featured']) ? $promotions['featured'] : 0) + $visible;
                    }
                    if ($payment->pricing->hasTop()) {
                        $promotions['_top'] = (isset($promotions['_top']) ? $promotions['_top'] : 0) + $visible;
                    }
                    if ($payment->pricing->hasBumpUp()) {
                        $promotions['_bump_up'] = (isset($promotions['_bump_up']) ? $promotions['_bump_up'] : 0) + $visible;
                    }
                } else {
                    $notPromotionVisible += $visible;
                }

                update_post_meta($payment_id, '_applied', 1);
            }
            $expiry_date = false;
            if (!empty($promotions)) {
                $promotion_status = self::update_listing_promotions($listing_id, $promotions);
                if (isset($promotion_status['expiry_date'])) {
                    $expiry_date = $promotion_status;
                }
            }

            if ($notPromotionVisible) {
                $notPromotionDate = new \DateTime(current_time('mysql'));
                $notPromotionDate->add(new \DateInterval("P{$notPromotionVisible}D"));
                if (!$hasPromotion || ($hasPromotion && $expiry_date && $notPromotionDate > $expiry_date)) {
                    $expDate = $notPromotionDate->format('Y-m-d H:i:s');
                    update_post_meta($listing_id, 'expiry_date', $expDate);
                }
            }

        } else {
            if (!$publish_count) {
                self::add_default_expiry_date($listing_id);
            }
        }
    }


    /**
     * @param int   $listing_id Listing id
     * @param array $promotions ['promotion_key' => $duration]
     *
     * @return bool | array
     */
    public static function update_listing_promotions($listing_id, $promotions) {
        try {
            $current_date = new \DateTime(current_time('mysql'));
            $old_expiry_date = get_post_meta($listing_id, 'expiry_date', true);
            $expiry_date = $old_expiry_date ? new \DateTime(self::datetime('mysql', trim($old_expiry_date))) : '';
            $needToUpdateExpiredDate = false;
            // Featured Update
            if (isset($promotions['featured']) && $feature_validate = absint($promotions['featured'])) {
                $feature_expiry_date = get_post_meta($listing_id, 'feature_expiry_date', true);
                $feature_expiry_date = $feature_expiry_date ? new \DateTime(self::datetime('mysql', trim($feature_expiry_date))) : '';
                if ($feature_expiry_date && $current_date < $feature_expiry_date) {
                    $feature_expiry_date->add(new \DateInterval("P{$feature_validate}D"));
                } else {
                    $feature_expiry_date = new \DateTime(current_time('mysql'));
                    $feature_expiry_date->add(new \DateInterval("P{$feature_validate}D"));
                }

                $featureExpDate = $feature_expiry_date->format('Y-m-d H:i:s');
                update_post_meta($listing_id, 'featured', 1);
                update_post_meta($listing_id, 'feature_expiry_date', $featureExpDate);

                if ($expiry_date) {
                    if ($feature_expiry_date > $expiry_date) {
                        $needToUpdateExpiredDate = true;
                        $expiry_date = $feature_expiry_date;
                    }
                } else {
                    $expiry_date = $feature_expiry_date;
                }

            }

            // Top Update
            if (isset($promotions['_top']) && $_top_validate = absint($promotions['_top'])) {
                $top_expiry_date = get_post_meta($listing_id, '_top_expiry_date', true);
                $top_expiry_date = $top_expiry_date ? new \DateTime(self::datetime('mysql', trim($top_expiry_date))) : '';
                if ($top_expiry_date && $current_date < $top_expiry_date) {
                    $top_expiry_date->add(new \DateInterval("P{$_top_validate}D"));
                } else {
                    $top_expiry_date = new \DateTime(current_time('mysql'));
                    $top_expiry_date->add(new \DateInterval("P{$_top_validate}D"));
                }

                $topExpDate = $top_expiry_date->format('Y-m-d H:i:s');
                update_post_meta($listing_id, '_top', 1);
                update_post_meta($listing_id, '_top_expiry_date', $topExpDate);

                if ($expiry_date) {
                    if ($top_expiry_date > $expiry_date) {
                        $needToUpdateExpiredDate = true;
                        $expiry_date = $top_expiry_date;
                    }
                } else {
                    $expiry_date = $top_expiry_date;
                }

            }

            if (isset($promotions['_bump_up']) && $_bump_up_validate = absint($promotions['_bump_up'])) {
                $bump_up_expiry_date = get_post_meta($listing_id, '_bump_up_expiry_date', true);
                $bump_up_expiry_date = $bump_up_expiry_date ? new \DateTime(self::datetime('mysql', trim($bump_up_expiry_date))) : '';

                if ($bump_up_expiry_date && $current_date < $bump_up_expiry_date) {
                    $bump_up_expiry_date->add(new \DateInterval("P{$_bump_up_validate}D"));
                } else {
                    $bump_up_expiry_date = new \DateTime(current_time('mysql'));
                    $bump_up_expiry_date->add(new \DateInterval("P{$_bump_up_validate}D"));
                }

                wp_update_post(
                    [
                        'ID'            => $listing_id, // ID of the post to update
                        'post_date'     => current_time('mysql'),
                        'post_date_gmt' => get_gmt_from_date(current_time('mysql'))
                    ]
                );
                $bumpUpExpDate = $bump_up_expiry_date->format('Y-m-d H:i:s');
                update_post_meta($listing_id, '_bump_up', 1);
                update_post_meta($listing_id, '_bump_up_expiry_date', $bumpUpExpDate);


                if ($expiry_date) {
                    if ($bump_up_expiry_date > $expiry_date) {
                        $needToUpdateExpiredDate = true;
                        $expiry_date = $bump_up_expiry_date;
                    }
                } else {
                    $expiry_date = $bump_up_expiry_date;
                }

            }

            if ((!$old_expiry_date && $expiry_date) || ($needToUpdateExpiredDate && $expiry_date)) {
                $expDate = $expiry_date->format('Y-m-d H:i:s');
                update_post_meta($listing_id, 'expiry_date', $expDate);
            }

            return [
                'expiry_date' => $expiry_date
            ];
        } catch (\Exception $e) {
            return false;
        }

    }

    /**
     * @param $listing_id
     *
     * @throws \Exception
     */
    static function add_default_expiry_date($listing_id) {
        $days = self::get_default_expired_duration_days();

        if ($days <= 0) {
            update_post_meta($listing_id, 'never_expires', 1);
            $days = apply_filters('rtcl_get_never_expires_dummy_duration_days', 999);
        } else {
            delete_post_meta($listing_id, 'never_expires');
        }

        // Calculate new date
        $date = new \DateTime(current_time('mysql'));
        $date->add(new \DateInterval("P{$days}D"));

        // return
        $expDate = $date->format('Y-m-d H:i:s');
        update_post_meta($listing_id, 'expiry_date', $expDate);

    }

    public static function get_default_expired_duration_days() {
        return apply_filters('get_default_expired_duration_days', intval(Functions::get_option_item('rtcl_moderation_settings', 'listing_duration', 15, 'number')));
    }

    /**
     * @return array|string|void
     */
    public static function get_admin_email_id_s() {
        $to = '';
        $admin_emails = self::get_option_item('rtcl_email_settings', 'admin_notice_emails');

        if (!empty($admin_emails)) {
            $admin_emails = str_replace('{admin_email}', get_option('admin_email'), $admin_emails);
            $to = explode("\n", $admin_emails);
            $to = array_map('trim', $to);
            $to = array_filter($to);
        }

        if (empty($to)) {
            $to = get_bloginfo('admin_email');
        }

        return $to;
    }

    public static function all_ids_for_remove_attachment() {
        $excluded_ids = get_posts([
            'post_type'        => rtcl()->post_type,
            'post_status'      => 'any',
            'posts_per_page'   => -1,
            'fields'           => 'ids',
            'suppress_filters' => false
        ]);

        $excluded_ids = apply_filters('rtcl_all_ids_for_remove_attachment', $excluded_ids);

        return $excluded_ids;
    }


    public static function get_max_upload() {
        $max_size = absint(self::get_option_item('rtcl_misc_settings', 'image_allowed_memory', 2));

        return $max_size * (1024 * 1024);
    }

    public static function get_wp_max_upload() {
        if (function_exists('wp_max_upload_size')) {
            return wp_max_upload_size();
        } else {
            return ini_get('upload_max_filesize');
        }
    }

    public static function formatBytes($size, $precision = 2) {
        $base = log($size, 1024);
        $suffixes = array('', 'KB', 'MB', 'GB', 'TB');

        return round(pow(1024, $base - floor($base)), $precision) . ' ' . $suffixes[floor($base)];
    }

    public static function the_offline_payment_instructions() {
        $settings = self::get_option_item('rtcl_payment_offline', 'instructions');
        echo $settings ? '<p>' . nl2br($settings) . '</p>' : null;
    }

    public static function print_html($html, $allHtml = false) {
        if ($allHtml) {
            echo stripslashes_deep($html);
        } else {
            echo wp_kses_post(stripslashes_deep($html));
        }
    }

    public static function term_has_children($term_id, $taxonomy = null) {
        if (!$term_id) {
            return false;
        }
        $taxonomy = $taxonomy ? $taxonomy : rtcl()->category;

        $termChildren = get_term_children($term_id, $taxonomy);
        if (!is_wp_error($termChildren)) {
            return count($termChildren);
        }

        return false;
    }

    public static function get_selected_cat($term_id, $taxonomy = null, $opt = array()) {
        if (!$term_id) {
            return false;
        }
        $opt = wp_parse_args(array(
            'separator' => "<span class='rtcl-icon-angle-right'></span>",
            'link'      => false,
            'format'    => false,
        ), $opt);
        $taxonomy = $taxonomy ? $taxonomy : rtcl()->category;

        return get_term_parents_list($term_id, $taxonomy, $opt);
    }

    /**
     * @param $term_id
     * @param $taxonomy
     *
     * @return array|false|\WP_Term
     */
    public static function get_term_top_most_parent_id($term_id, $taxonomy) {

        $parents = get_ancestors($term_id, $taxonomy);

        return !empty($parents) ? end($parents) : $term_id;
    }


    /**
     * @param $post_id
     * @param $taxonomy
     *
     * @return mixed
     */
    public static function get_term_top_parent_id_for_a_post($post_id, $taxonomy) {
        $terms = wp_get_object_terms($post_id, $taxonomy);
        $parent_ids = array();
        if (!empty($terms) && !is_wp_error($terms)) {
            foreach ($terms as $term) {
                $parent_ids[] = self::get_term_top_most_parent_id($term->term_id, $taxonomy);
            }
            $parent_ids = array_unique($parent_ids);
        }

        if (rtcl()->category == $taxonomy) {
            $parent_ids = !empty($parent_ids) ? $parent_ids[0] : 0;
        }

        return $parent_ids;
    }

    /**
     * @param array $terms
     *
     * @return array|int|mixed
     */
    public static function get_term_child_id_for_a_post($terms) {

        $child_ids = array();
        if (!empty($terms) && !is_wp_error($terms)) {
            foreach ($terms as $term) {
                if ($term->parent) {
                    $child_ids[] = $term->term_id;
                }
            }
            $child_ids = array_unique($child_ids);
        }
        if (empty($child_ids) && !empty($terms)) {
            $child_ids[] = $terms[0]->term_id;
        }

        return !empty($child_ids) ? $child_ids[0] : 0;
    }


    /**
     * @return mixed|void
     */
    public static function get_default_placeholder_url() {
        $placeholder_url = RTCL_URL . '/assets/images/placeholder.png';

        return apply_filters('rtcl_default_placeholder_url', $placeholder_url);
    }

    public static function comments($comment, $args, $depth) {
        $GLOBALS['comment'] = $comment; // WPCS: override ok.
        self::get_template('listing/review', array(
            'comment' => $comment,
            'args'    => $args,
            'depth'   => $depth,
        ));
    }

    /**
     * Get HTML for star rating.
     *
     * @param float $rating Rating being shown.
     * @param int   $count  Total number of ratings.
     *
     * @return string
     * @since  1.0.0
     */
    public static function get_star_rating_html($rating, $count = 0) {
        $html = '<span style="width:' . (($rating / 5) * 100) . '%">';

        if (0 < $count) {
            /* translators: 1: rating 2: rating count */
            $html .= sprintf(_n('Rated %1$s out of 5 based on %2$s customer rating', 'Rated %1$s out of 5 based on %2$s customer ratings', $count, 'classified-listing'), '<strong class="rating">' . esc_html($rating) . '</strong>', '<span class="rating">' . esc_html($count) . '</span>');
        } else {
            /* translators: %s: rating */
            $html .= sprintf(esc_html__('Rated %s out of 5', 'classified-listing'), '<strong class="rating">' . esc_html($rating) . '</strong>');
        }

        $html .= '</span>';

        return apply_filters('rtcl_get_star_rating_html', $html, $rating, $count);
    }

    /**
     * Get HTML for ratings.
     *
     * @param float $rating Rating being shown.
     * @param int   $count  Total number of ratings.
     *
     * @return string
     * @since  1.0.0
     */
    public static function get_rating_html($rating, $count = 0) {
        if (0 < $rating) {
            $title = sprintf(_n('Rated %1$s out of 5 based on %2$s customer rating', 'Rated %1$s out of 5 based on %2$s customer ratings', $count, 'classified-listing'), esc_html($rating), esc_html($count));
            $html = '<div class="star-rating" title="' . $title . '">';
            $html .= self::get_star_rating_html($rating, $count);
            $html .= '</div>';
        } else {
            $html = '';
        }

        return apply_filters('rtcl_listing_get_rating_html', $html, $rating, $count);
    }

    /**
     * Get rounding precision for internal WC calculations.
     * Will increase the precision of get_price_decimals by 2 decimals, unless RTCL_ROUNDING_PRECISION is set to a
     * higher number.
     *
     * @return int
     * @since 2.6.3
     */
    static function get_rounding_precision() {
        $precision = self::currency_decimal_count() + 2;
        if (absint(RTCL_ROUNDING_PRECISION) > $precision) {
            $precision = absint(RTCL_ROUNDING_PRECISION);
        }

        return $precision;
    }

    /**
     * Format decimal numbers ready for DB storage.
     * Sanitize, remove decimals, and optionally round + trim off zeros.
     * This function does not remove thousands - this should be done before passing a value to the function.
     *
     * @param float|string $number      Expects either a float or a string with a decimal separator only (no
     *                                  thousands).
     * @param mixed        $dp          number  Number of decimal points to use, blank to use rtcl_currency_decimal_count,
     *                                  or false to avoid all rounding.
     * @param bool         $trim_zeros  From end of string.
     *
     * @return string
     */
    static function format_decimal($number, $dp = false, $trim_zeros = false) {
        $locale = localeconv();
        $decimals = self::get_decimal_separator_both() + [$locale['decimal_point'], $locale['mon_decimal_point']];

        // Remove locale from string.
        if (!is_float($number)) {
            $number = str_replace($decimals, '.', $number);
            $number = preg_replace('/[^0-9\.,-]/', '', self::clean($number));
        }

        if (false !== $dp) {
            $dp = intval('' === $dp ? self::currency_decimal_count() : $dp);
            $number = number_format(floatval($number), $dp, '.', '');
        } elseif (is_float($number)) {
            // DP is false - don't use number format, just return a string using whatever is given. Remove scientific notation using sprintf.
            $number = str_replace($decimals, '.', sprintf('%.' . self::get_rounding_precision() . 'f', $number));
            // We already had a float, so trailing zeros are not needed.
            $trim_zeros = true;
        }

        if ($trim_zeros && strstr($number, '.')) {
            $number = rtrim(rtrim($number, '0'), '.');
        }

        return apply_filters('rtcl_format_localized_decimal', $number);
    }

    public static function check_license() {
        return apply_filters('rtcl_check_license', true);
    }

    function trim_string($string, $chars = 200, $suffix = '...') {
        if (strlen($string) > $chars) {
            if (function_exists('mb_substr')) {
                $string = mb_substr($string, 0, ($chars - mb_strlen($suffix))) . $suffix;
            } else {
                $string = substr($string, 0, ($chars - strlen($suffix))) . $suffix;
            }
        }

        return $string;
    }


    /**
     * @param bool  $type
     *
     * @param array $exclude exclude
     *
     * @return array
     */
    public static function get_user_roles($type = null, $exclude = []) {
        global $wp_roles;
        $default_roles = ['administrator', 'editor', 'author', 'contributor', 'subscriber'];
        $roles = [];
        foreach ($wp_roles->roles as $role => $value) {
            if (!empty($exclude) && is_array($exclude) && in_array($role, $exclude)) {
                continue;
            }
            if ($type && in_array($type, ['custom', 'default'])) {
                if ($type == 'custom') {
                    if (!in_array($role, $default_roles)) {
                        $roles[$role] = translate_user_role($value['name']);
                    }
                } else if ($type == 'default') {
                    if (in_array($role, $default_roles)) {
                        $roles[$role] = translate_user_role($value['name']);
                    }
                }
            } else {
                $roles[$role] = translate_user_role($value['name']);
            }
        }

        return apply_filters('rtcl_get_user_roles', $roles, $type);
    }

    static function allowed_html_for_term_and_conditions() {

        $tags = array(
            'a'      => array(
                'href'  => array(),
                'title' => array()
            ),
            'br'     => array(),
            'em'     => array(),
            'strong' => array(),
        );

        return apply_filters('rtcl_allowed_html_for_term_and_conditions', $tags);
    }

    /**
     * @return bool
     */
    public static function is_wc_active() {
        return class_exists('WooCommerce');
    }


    /**
     * Output variables to screen for debugging.
     */
    static function debug() {
        $args = func_get_args();
        $debug = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);

        echo '<pre>';
        print_r($debug[1]);
        $arg = false;

        if ($args) {
            foreach ($args as $arg) {
                print_r($arg);
                echo "\n=============================\n";
            }
        }
        echo '</pre>';

        if ($arg === true) {
            die(__FUNCTION__);
        }
    }

    /**
     * @param $url
     *
     * @return bool
     */
    public static function is_external($url) {
        $site_url = str_replace('www.', '', parse_url(site_url(), PHP_URL_HOST));
        $url = str_replace('www.', '', parse_url($url, PHP_URL_HOST));
        if (empty($url)) {
            return false;
        }
        if (strcasecmp($url, $site_url) === 0) {
            return false;
        }

        return true;
    }


    /**
     * @param $type
     * @param $post_id
     * @param $success
     *
     * @return mixed|string|null
     */
    static function get_listing_redirect_url_after_edit_post($type, $post_id, $success) {
        $redirect_url = null;
        $account_listings_url = Link::get_account_endpoint_url('listings');
        if ($success) {
            $submission_url = Link::get_regular_submission_end_point($post_id);
            if ($type == 'new') {
                $rNewListing = Functions::get_option_item('rtcl_moderation_settings', 'redirect_new_listing', 'submission');
                if ($rNewListing == 'submission') {
                    $redirect_url = $submission_url;
                } elseif ($rNewListing == 'custom') {
                    $cUrl = Functions::get_option_item('rtcl_moderation_settings', 'redirect_new_listing_custom');
                    $redirect_url = esc_url($cUrl);
                }
            } elseif ($type == 'update') {
                $rUpdateListing = Functions::get_option_item('rtcl_moderation_settings', 'redirect_update_listing', 'submission');
                if ($rUpdateListing == 'submission') {
                    $redirect_url = $submission_url;
                } elseif ($rUpdateListing == 'custom') {
                    $cUrl = Functions::get_option_item('rtcl_moderation_settings', 'redirect_update_listing_custom');
                    $redirect_url = esc_url($cUrl);
                }
            }
        }

        return apply_filters('rtcl_get_listing_redirect_url_after_edit_post',
            $redirect_url ? $redirect_url : $account_listings_url,
            $type,
            $post_id,
            $success
        );
    }

    static function touch_time($name, $date = null, $tab_index = 0) {
        global $wp_locale;
        $edit = ($date && '0000-00-00 00:00:00' != $date);

        $tab_index_attribute = '';
        if ((int)$tab_index > 0) {
            $tab_index_attribute = " tabindex=\"$tab_index\"";
        }
        $formatted_date = date_i18n(__('M j, Y @ H:i', 'classified-listing'), strtotime($date));

        $time_adj = current_time('timestamp');
        $jj = ($edit) ? mysql2date('d', $date, false) : gmdate('d', $time_adj);
        $mm = ($edit) ? mysql2date('m', $date, false) : gmdate('m', $time_adj);
        $aa = ($edit) ? mysql2date('Y', $date, false) : gmdate('Y', $time_adj);
        $hh = ($edit) ? mysql2date('H', $date, false) : gmdate('H', $time_adj);
        $mn = ($edit) ? mysql2date('i', $date, false) : gmdate('i', $time_adj);
        $ss = ($edit) ? mysql2date('s', $date, false) : gmdate('s', $time_adj);

        $cur_jj = gmdate('d', $time_adj);
        $cur_mm = gmdate('m', $time_adj);
        $cur_aa = gmdate('Y', $time_adj);
        $cur_hh = gmdate('H', $time_adj);
        $cur_mn = gmdate('i', $time_adj);

        $month = '<label><span class="screen-reader-text">' . __('Month', 'classified-listing') . '</span><select class="rtcl-mm" name="' . $name . '-mm"' . $tab_index_attribute . ">\n";
        for ($i = 1; $i < 13; $i = $i + 1) {
            $monthnum = zeroise($i, 2);
            $monthtext = $wp_locale->get_month_abbrev($wp_locale->get_month($i));
            $month .= "\t\t\t" . '<option value="' . $monthnum . '" data-text="' . $monthtext . '" ' . selected($monthnum, $mm, false) . '>';
            /* translators: 1: month number (01, 02, etc.), 2: month abbreviation */
            $month .= sprintf(__('%1$s-%2$s', 'classified-listing'), $monthnum, $monthtext) . "</option>\n";
        }
        $month .= '</select></label>';

        $day = '<label><span class="screen-reader-text">' . __('Day', 'classified-listing') . '</span><input type="text" class="rtcl-jj" name="' . $name . '-jj" value="' . $jj . '" size="2" maxlength="2"' . $tab_index_attribute . ' autocomplete="off" /></label>';
        $year = '<label><span class="screen-reader-text">' . __('Year', 'classified-listing') . '</span><input type="text" class="rtcl-aa" name="' . $name . '-aa" value="' . $aa . '" size="4" maxlength="4"' . $tab_index_attribute . ' autocomplete="off" /></label>';
        $hour = '<label><span class="screen-reader-text">' . __('Hour', 'classified-listing') . '</span><input type="text" class="rtcl-hh" name="' . $name . '-hh" value="' . $hh . '" size="2" maxlength="2"' . $tab_index_attribute . ' autocomplete="off" /></label>';
        $minute = '<label><span class="screen-reader-text">' . __('Minute', 'classified-listing') . '</span><input type="text" class="rtcl-mn" name="' . $name . '-mn" value="' . $mn . '" size="2" maxlength="2"' . $tab_index_attribute . ' autocomplete="off" /></label>';

        echo '<div class="rtcl-timestamp-wrapper">';
        echo sprintf('<span class="rtcl-timestamp">%s</span>', sprintf(__('Expired on: <b>%1$s</b>', 'classified-listing'), $formatted_date));

        echo sprintf('<a href="#edit_timestamp" class="edit-timestamp hide-if-no-js" role="button"><span
                            aria-hidden="true">%s</span> <span class="screen-reader-text">%s</span></a>',
            esc_html__('Edit', 'classified-listing'),
            esc_html__('Edit date and time', 'classified-listing')
        );
        echo '<fieldset class="rtcl-timestamp-div hide-if-js">';
        echo sprintf('<legend class="screen-reader-text">%s</legend>', esc_html__('Date and time', 'classified-listing'));
        echo '<div class="timestamp-wrap">';
        /* translators: 1: month, 2: day, 3: year, 4: hour, 5: minute */
        printf(__('%1$s %2$s, %3$s @ %4$s:%5$s', 'classified-listing'), $month, $day, $year, $hour, $minute);

        echo '</div><input type="hidden" class="rtcl-ss" name="' . $name . '-ss" value="' . $ss . '" />';


        echo "\n\n";
        $map = array(
            'mm' => array($mm, $cur_mm),
            'jj' => array($jj, $cur_jj),
            'aa' => array($aa, $cur_aa),
            'hh' => array($hh, $cur_hh),
            'mn' => array($mn, $cur_mn),
        );
        foreach ($map as $timeunit => $value) {
            list($unit, $curr) = $value;

            echo '<input type="hidden" class="rtcl-hidden_' . $timeunit . '" name="hidden_' . $name . '-' . $timeunit . '" value="' . $unit . '" />' . "\n";
            $cur_timeunit = 'cur_' . $timeunit;
            echo '<input type="hidden" class="rtcl-' . $cur_timeunit . '" name="' . $name . '-' . $cur_timeunit . '" value="' . $curr . '" />' . "\n";
        }
        ?>

        <p>
            <a href="#edit_timestamp"
               class="save-timestamp hide-if-no-js button"><?php _e('OK', 'classified-listing'); ?></a>
            <a href="#edit_timestamp"
               class="cancel-timestamp hide-if-no-js button-cancel"><?php _e('Cancel', 'classified-listing'); ?></a>
        </p>
        </fieldset>
        </div>
        <?php
    }
}
