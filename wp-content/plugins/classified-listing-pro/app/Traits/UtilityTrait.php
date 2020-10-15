<?php


namespace Rtcl\Traits;


use DateTime;
use Rtcl\Controllers\Hooks\Comments;
use Rtcl\Controllers\Hooks\TemplateHooks;
use Rtcl\Gateways\WooPayment\GatewayWooPayment;
use Rtcl\Helpers\Breadcrumb;
use Rtcl\Helpers\Cache;
use Rtcl\Helpers\Functions;
use Rtcl\Helpers\Text;
use Rtcl\Models\Listing;
use stdClass;
use WP_Comment;
use WP_Query;

trait UtilityTrait
{

    static function get_filter_form_url() {
        if (self::is_listing_taxonomy() && $obj = get_queried_object()) {
            $url = get_term_link($obj);
        } else {
            $url = get_permalink(Functions::get_page_id('listings'));
        }

        return apply_filters('rtcl_get_filter_form_url', $url);
    }

    /**
     * Check if an endpoint is showing.
     *
     * @param string|false $endpoint Whether endpoint.
     *
     * @return bool
     */
    static function is_endpoint_url($endpoint = false) {
        global $wp;

        $rtcl_endpoints = rtcl()->query->get_query_vars();

        if (false !== $endpoint) {
            if (!isset($rtcl_endpoints[$endpoint])) {
                return false;
            } else {
                $endpoint_var = $rtcl_endpoints[$endpoint];
            }

            return isset($wp->query_vars[$endpoint_var]);
        } else {
            foreach ($rtcl_endpoints as $key => $value) {
                if (isset($wp->query_vars[$key])) {
                    return true;
                }
            }

            return false;
        }
    }

    static function get_top_listings_query() {
        $post_per_pare = Functions::get_option_item('rtcl_moderation_settings', 'listing_top_per_page', 2);
        $query_args = [
            'post_type'      => rtcl()->post_type,
            'post_status'    => 'publish',
            'posts_per_page' => absint($post_per_pare),
            'orderby'        => 'rand'
        ];
        /**
         * @var $query WP_Query
         */
        $query = $GLOBALS['wp_query'];

        if (isset($_GET['q'])) {
            $query_args['s'] = Functions::clean($_GET['q']);
        }
        if (!empty($query->tax_query->queries)) {
            $query_args['tax_query'] = $query->tax_query->queries;
        }
        if (!empty($query->tax_query->queries)) {
            $query_args['meta_query'] = $query->meta_query->queries;
        }
        $query_args['meta_query'][] = [
            'key'     => '_top',
            'value'   => 1,
            'compare' => '='
        ];

        if (count($query_args['meta_query']) > 1) {
            $query_args['meta_query']['relation'] = "AND";
        }
        $query = new WP_Query(apply_filters('rtcl_top_listings_query_args', $query_args));

        return apply_filters('rtcl_top_listings_query', $query);
    }

    static function query_string_form_fields($values = null, $exclude = array(), $current_key = '', $return = false) {
        if (is_null($values)) {
            $values = $_GET; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        } elseif (is_string($values)) {
            $url_parts = wp_parse_url($values);
            $values = array();

            if (!empty($url_parts['query'])) {
                // This is to preserve full-stops, pluses and spaces in the query string when ran through parse_str.
                $replace_chars = array(
                    '.' => '{dot}',
                    '+' => '{plus}',
                );

                $query_string = str_replace(array_keys($replace_chars), array_values($replace_chars), $url_parts['query']);

                // Parse the string.
                parse_str($query_string, $parsed_query_string);

                // Convert the full-stops, pluses and spaces back and add to values array.
                foreach ($parsed_query_string as $key => $value) {
                    $new_key = str_replace(array_values($replace_chars), array_keys($replace_chars), $key);
                    $new_value = str_replace(array_values($replace_chars), array_keys($replace_chars), $value);
                    $values[$new_key] = $new_value;
                }
            }
        }
        $html = '';

        foreach ($values as $key => $value) {
            if (in_array($key, $exclude, true)) {
                continue;
            }
            if ($current_key) {
                $key = $current_key . '[' . $key . ']';
            }
            if (is_array($value)) {
                $html .= self::query_string_form_fields($value, $exclude, $key, true);
            } else {
                $html .= '<input type="hidden" name="' . esc_attr($key) . '" value="' . esc_attr(wp_unslash($value)) . '" />';
            }
        }

        if ($return) {
            return $html;
        }

        echo $html;
    }

    static function _get_cached_listing_terms($listing_id, $taxonomy, $args = array()) {
        $cache_key = 'rtcl_' . $taxonomy . md5(wp_json_encode($args));
        $cache_group = Cache::get_cache_prefix('listing_' . $listing_id) . $listing_id;
        $terms = wp_cache_get($cache_key, $cache_group);

        if (false !== $terms) {
            return $terms;
        }

        $terms = wp_get_post_terms($listing_id, $taxonomy, $args);

        wp_cache_add($cache_key, $terms, $cache_group);

        return $terms;
    }

    static function get_listing_terms($listing_id, $taxonomy, $args = array()) {
        if (!taxonomy_exists($taxonomy)) {
            return array();
        }

        return apply_filters('rtcl_get_listing_terms', self::_get_cached_listing_terms($listing_id, $taxonomy, $args), $listing_id, $taxonomy, $args);
    }

    public static function breadcrumb($args = array()) {
        $args = wp_parse_args(
            $args,
            apply_filters(
                'rtcl_breadcrumb_defaults',
                array(
                    'delimiter'   => '&nbsp;&#47;&nbsp;',
                    'wrap_before' => '<nav class="rtcl-breadcrumb">',
                    'wrap_after'  => '</nav>',
                    'before'      => '',
                    'after'       => '',
                    'home'        => _x('Home', 'breadcrumb', 'classified-listing'),
                )
            )
        );

        $breadcrumbs = new Breadcrumb();

        if (!empty($args['home'])) {
            $breadcrumbs->add_crumb($args['home'], apply_filters('rtcl_breadcrumb_home_url', home_url()));
        }

        $args['breadcrumb'] = $breadcrumbs->generate();

        do_action('rtcl_breadcrumb', $breadcrumbs, $args);

        Functions::get_template('global/breadcrumb', $args);
    }

    /**
     * Output the pagination.
     */
    static function pagination() {
        if (!self::get_loop_prop('is_paginated')) {
            return;
        }

        $args = array(
            'total'   => self::get_loop_prop('total_pages'),
            'current' => self::get_loop_prop('current_page'),
            'base'    => esc_url_raw(add_query_arg('listing-page', '%#%', false)),
            'format'  => '?listing-page=%#%',
        );

        if (!self::get_loop_prop('is_shortcode')) {
            $args['format'] = '';
            $args['base'] = esc_url_raw(str_replace(999999999, '%#%', get_pagenum_link(999999999, false)));
        }

        Functions::get_template('listing/loop/pagination', $args);
    }


    /**
     * Gets a property from the rtcl_loop global.
     *
     * @param string $prop    Prop to get.
     * @param string $default Default if the prop does not exist.
     *
     * @return mixed
     * @since 1.5.5
     */
    static function get_loop_prop($prop, $default = '') {
        self::setup_loop(); // Ensure shop loop is setup.

        return isset($GLOBALS['rtcl_loop'], $GLOBALS['rtcl_loop'][$prop]) ? $GLOBALS['rtcl_loop'][$prop] : $default;
    }

    /**
     * Sets a property in the rtcl_loop global.
     *
     * @param string $prop  Prop to set.
     * @param string $value Value to set.
     *
     * @since 1.5.5
     */
    static function set_loop_prop($prop, $value = '') {
        if (!isset($GLOBALS['rtcl_loop'])) {
            self::setup_loop();
        }
        $GLOBALS['rtcl_loop'][$prop] = $value;
    }

    /**
     * Resets the rtcl_loop global.
     *
     * @since 1.5.5
     */
    static function reset_loop() {
        unset($GLOBALS['rtcl_loop']);
    }

    /**
     * Sets up the rtcl_loop global from the passed args or from the main query.
     *
     * @param array $args Args to pass into the global.
     *
     * @since 1.5.5
     */
    static function setup_loop($args = array()) {
        $default_args = array(
            'loop'         => 0,
            'name'         => '',
            'is_shortcode' => false,
            'is_paginated' => true,
            'is_search'    => false,
            'total'        => 0,
            'total_pages'  => 0,
            'per_page'     => 0,
            'current_page' => 1,
        );

        // If this is a main RTCL query, use global args as defaults.
        if ($GLOBALS['wp_query']->get('rtcl_query')) {
            $default_args = array_merge(
                $default_args,
                array(
                    'is_search'    => $GLOBALS['wp_query']->is_search(),
                    'total'        => $GLOBALS['wp_query']->found_posts,
                    'total_pages'  => $GLOBALS['wp_query']->max_num_pages,
                    'per_page'     => $GLOBALS['wp_query']->get('posts_per_page'),
                    'current_page' => max(1, $GLOBALS['wp_query']->get('paged', 1)),
                )
            );
        }

        // Merge any existing values.
        if (isset($GLOBALS['rtcl_loop'])) {
            $default_args = array_merge($default_args, $GLOBALS['rtcl_loop']);
        }

        $GLOBALS['rtcl_loop'] = wp_parse_args($args, $default_args);
    }

    static function get_permalink_structure() {

        $saved_permalinks = [];
        if ($listing_base = Functions::get_option_item('rtcl_advanced_settings', 'permalink')) {
            $saved_permalinks['listing_base'] = untrailingslashit($listing_base);
        }
        if ($category_base = Functions::get_option_item('rtcl_advanced_settings', 'category_base')) {
            $saved_permalinks['category_base'] = untrailingslashit($category_base);
        }
        if ($location_base = Functions::get_option_item('rtcl_advanced_settings', 'location_base')) {
            $saved_permalinks['location_base'] = untrailingslashit($location_base);
        }

        $permalinks = wp_parse_args(
            array_filter($saved_permalinks),
            array(
                'listing_base'  => _x('listing', 'slug', 'classified-listing'),
                'category_base' => _x('listing-category', 'slug', 'classified-listing'),
                'location_base' => _x('listing-location', 'slug', 'classified-listing'),
            )
        );

        return apply_filters('rtcl_permalink_structure', $permalinks);
    }

    static function is_online($author_id) {
        $online_status = get_user_meta($author_id, 'online_status', true);

        return !empty($online_status) && $online_status >= current_time('timestamp');
    }

    static function is_woo_payment_enabled() {
        return self::is_woo_activated() && self::get_option_item('rtcl_payment_woo-payment', 'enabled', false, 'checkbox');
    }

    static function is_woo_order_autocomplete_disable() {
        return self::get_option_item('rtcl_payment_woo-payment', 'order_autocomplete_disable', false, 'checkbox');
    }

    /**
     * Get type of items are supported in course curriculum (post types).
     * Default: [rtcl_listing]
     *
     * @return mixed
     * @since 1.5.4
     *
     */
    static function get_listing_item_types() {
        return apply_filters('rtcl_listing_item_type', array(
            rtcl()->post_type
        ));
    }

    /**
     * Set header is 404
     */
    static function set_404() {
        global $wp_query;
        if (!empty($_REQUEST['debug-404'])) {
            self::debug(debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, $_REQUEST['debug-404']));
        }
        $wp_query->set_404();
        status_header(404);
    }

    /**
     * WooCommerce is activated
     *
     * @return boolean
     */
    static function is_woo_activated() {
        if (!function_exists('is_plugin_active')) {
            include_once(ABSPATH . 'wp-admin/includes/plugin.php');
        }

        return is_plugin_active('woocommerce/woocommerce.php');
    }

    /**
     * @param \WP_User $user
     *
     * @return string
     */
    static function get_author_name($user) {
        $author_name = '';
        if (is_object($user)) {
            $author[] = $user->first_name;
            $author[] = $user->last_name;
            $author = array_filter($author);
            if (!empty($author)) {
                $author_name = implode(' ', $author);
            } else {
                $author_name = $user->display_name;
            }
        }

        return $author_name;
    }

    static function format_content($raw_string) {
        return apply_filters('rtcl_format_content', apply_filters('rtcl_short_description', $raw_string), $raw_string);
    }

    static function format_listing_short_description($content) {
        // Add support for Jetpack Markdown.
        if (class_exists('WPCom_Markdown')) {
            $markdown = \WPCom_Markdown::get_instance();

            return wpautop(
                $markdown->transform(
                    $content,
                    array(
                        'unslash' => false,
                    )
                )
            );
        }

        return $content;
    }

    static function do_oembeds($content) {
        global $wp_embed;

        $content = $wp_embed->autoembed($content);

        return $content;
    }

    static function replace_policy_and_terms_page_link_placeholders($text) {
        $privacy_page_id = Functions::get_privacy_policy_page_id();
        $terms_page_id = Functions::get_terms_and_conditions_page_id();
        $privacy_link = $privacy_page_id ? '<a href="' . esc_url(get_permalink($privacy_page_id)) . '" class="rtcl-privacy-policy-link" target="_blank">' . __('privacy policy', 'classified-listing') . '</a>' : __('privacy policy', 'classified-listing');
        $terms_link = $terms_page_id ? '<a href="' . esc_url(get_permalink($terms_page_id)) . '" class="rtcl-terms-and-conditions-link" target="_blank">' . __('terms and conditions', 'classified-listing') . '</a>' : __('terms and conditions', 'classified-listing');

        $find_replace = array(
            '[terms]'          => $terms_link,
            '[privacy_policy]' => $privacy_link,
        );

        $updated_text = str_replace(array_keys($find_replace), array_values($find_replace), $text);

        return apply_filters('rtcl_replace_policy_and_terms_page_link_placeholders', $updated_text, $text, $privacy_page_id, $privacy_link, $terms_page_id, $terms_link);
    }

    static function privacy_policy_text($type = 'checkout') {

        if (!Functions::get_privacy_policy_page_id()) {
            return;
        }
        echo '<div class="rtcl-privacy-policy-text">';
        echo wp_kses_post(wpautop(Functions::replace_policy_and_terms_page_link_placeholders(Text::get_privacy_policy_text($type))));
        echo '</div>';
    }

    static function terms_and_conditions_checkbox_enabled() {
        $page_id = Functions::get_terms_and_conditions_page_id();
        $page = $page_id ? get_post($page_id) : false;
        $return = $page && Text::get_terms_and_conditions_checkbox_text();

        return apply_filters('rtcl_terms_and_conditions_checkbox_enabled', $return);
    }

    static function terms_and_conditions_checkbox_text() {
        $text = Text::get_terms_and_conditions_checkbox_text();

        if (!$text) {
            return;
        }

        echo wp_kses_post(Functions::replace_policy_and_terms_page_link_placeholders($text));
    }


    static function switch_to_site_locale() {
        if (function_exists('switch_to_locale')) {
            switch_to_locale(get_locale());

            // Filter on plugin_locale so load_plugin_textdomain loads the correct locale.
            add_filter('plugin_locale', 'get_locale');

            // Init WC locale.
            rtcl()->load_plugin_textdomain();
        }
    }

    static function restore_locale() {
        if (function_exists('restore_previous_locale')) {
            restore_previous_locale();

            // Remove filter.
            remove_filter('plugin_locale', 'get_locale');

            // Init WC locale.
            rtcl()->load_plugin_textdomain();
        }
    }


    static function get_theme_template_path($template) {
        return trailingslashit(get_stylesheet_directory()) . trailingslashit(rtcl()->get_template_path()) . $template;
    }

    static function get_theme_template_file($template) {
        return trailingslashit(basename(get_stylesheet_directory())) . trailingslashit(rtcl()->get_template_path()) . $template;
    }

    static function get_plugin_template_file($template) {
        return RTCL_SLUG . '/templates/' . $template;
    }

    static function get_blogname() {
        return wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
    }

    static function get_home_url() {
        return wp_parse_url(home_url(), PHP_URL_HOST);
    }


    //    Color
    static function rgb_from_hex($color) {
        $color = str_replace('#', '', $color);
        // Convert shorthand colors to full format, e.g. "FFF" -> "FFFFFF".
        $color = preg_replace('~^(.)(.)(.)$~', '$1$1$2$2$3$3', $color);

        $rgb = array();
        $rgb['R'] = hexdec($color[0] . $color[1]);
        $rgb['G'] = hexdec($color[2] . $color[3]);
        $rgb['B'] = hexdec($color[4] . $color[5]);

        return $rgb;
    }

    static function hex_lighter($color, $factor = 30) {
        $base = self::rgb_from_hex($color);
        $color = '#';

        foreach ($base as $k => $v) {
            $amount = 255 - $v;
            $amount = $amount / 100;
            $amount = round($amount * $factor);
            $new_decimal = $v + $amount;

            $new_hex_component = dechex($new_decimal);
            if (strlen($new_hex_component) < 2) {
                $new_hex_component = '0' . $new_hex_component;
            }
            $color .= $new_hex_component;
        }

        return $color;
    }

    static function hex_darker($color, $factor = 30) {
        $base = self::rgb_from_hex($color);
        $color = '#';

        foreach ($base as $k => $v) {
            $amount = $v / 100;
            $amount = round($amount * $factor);
            $new_decimal = $v - $amount;

            $new_hex_component = dechex($new_decimal);
            if (strlen($new_hex_component) < 2) {
                $new_hex_component = '0' . $new_hex_component;
            }
            $color .= $new_hex_component;
        }

        return $color;
    }

    static function hex_is_light($color) {
        $hex = str_replace('#', '', $color);

        $c_r = hexdec(substr($hex, 0, 2));
        $c_g = hexdec(substr($hex, 2, 2));
        $c_b = hexdec(substr($hex, 4, 2));

        $brightness = (($c_r * 299) + ($c_g * 587) + ($c_b * 114)) / 1000;

        return $brightness > 155;
    }

    static function light_or_dark($color, $dark = '#000000', $light = '#FFFFFF') {
        return self::hex_is_light($color) ? $dark : $light;
    }

    static function format_product_short_description($content) {
        // Add support for Jetpack Markdown.
        if (class_exists('WPCom_Markdown')) {
            $markdown = \WPCom_Markdown::get_instance();

            return wpautop(
                $markdown->transform(
                    $content,
                    array(
                        'unslash' => false,
                    )
                )
            );
        }

        return $content;
    }

    static function get_category_depth_limit() {
        return apply_filters('rtcl_category_depth_limit', 0);
    }

    static function get_location_depth_limit() {
        return apply_filters('rtcl_location_depth_limit', 3);
    }

    static function get_custom_field_save_date_format() {
        return wp_parse_args(apply_filters('rtcl_custom_field_save_date_format', [
            'date' => 'Y-m-d',
            'time' => 'H:i:s'
        ]), [
            'date' => 'Y-m-d',
            'time' => 'H:i:s'
        ]);
    }

    /**
     * @param        $values
     * @param string $type
     *
     * @return array|string
     * @deprecated
     *
     */
    static function get_formatted_date_from_raw_input($values, $type = '') {

        return $values;
    }

    public static function get_theme_slug_for_templates() {
        return apply_filters('rtcl_theme_slug_for_templates', get_option('template'));
    }

    /**
     * Display the classes for the listing div.
     *
     * @param string|array         $class      One or more classes to add to the class list.
     * @param int|\WP_Post|Listing $listing_id Listing ID or product object.
     *
     * @since 1.5.4
     */
    static function listing_class($class = [], $listing_id = null) {
        $classes = self::get_listing_class($class, $listing_id);
        if (!empty($classes)) {
            echo 'class="' . esc_attr(implode(' ', $classes)) . '"';
        }
    }

    /**
     * Display the classes for the listing div
     *
     * @param string|array $classes One or more classes to add to the class list.
     *
     * @since 1.5.4
     */
    static function listing_loop_start_class($classes = []) {
        $classes[] = 'rtcl-listings';
        if (isset($_GET['view']) && in_array($_GET['view'], ['grid', 'list'], true)) {
            $view = esc_attr($_GET['view']);
        } else {
            $view = Functions::get_option_item('rtcl_general_settings', 'default_view', 'list');
        }
        $classes[] = 'grid' === $view ? 'rtcl-grid-view' : 'rtcl-list-view';
        $classes[] = apply_filters('rtcl_listings_grid_columns_class', 'columns-4');

        $classes = array_map('esc_attr', array_unique(array_filter($classes)));
        $classes = apply_filters('rtcl_listing_loop_start_class', $classes);
        if (!empty($classes)) {
            echo 'class="' . esc_attr(implode(' ', $classes)) . '"';
        }
    }


    /**
     * Display the classes for the listing div
     *
     * @param string|array $classes One or more classes to add to the class list.
     *
     * @since 1.5.4
     */
    static function top_listings_wrap_class($classes = []) {
        $classes[] = 'rtcl-listings';
        if (isset($_GET['view']) && in_array($_GET['view'], ['grid', 'list'], true)) {
            $view = esc_attr($_GET['view']);
        } else {
            $view = Functions::get_option_item('rtcl_general_settings', 'default_view', 'list');
        }
        $classes[] = 'grid' === $view ? 'rtcl-grid-view' : 'rtcl-list-view';
        $classes[] = apply_filters('rtcl_top_listings_grid_columns', 'columns-4');

        $classes = array_map('esc_attr', array_unique(array_filter($classes)));
        $classes = apply_filters('rtcl_top_listings_wrap_class', $classes);
        if (!empty($classes)) {
            echo 'class="' . esc_attr(implode(' ', $classes)) . '"';
        }
    }


    /**
     * Get listing taxonomy HTML classes.
     *
     * @param array  $term_ids Array of terms IDs or objects.
     * @param string $taxonomy Taxonomy.
     *
     * @return array
     * @since 1.5.4
     */
    static function get_listing_taxonomy_class($term_ids, $taxonomy) {
        $classes = [];
        if ($term_ids) {
            foreach ($term_ids as $term_id) {
                $term = get_term($term_id, $taxonomy);

                if (empty($term->slug)) {
                    continue;
                }

                $term_class = sanitize_html_class($term->slug, $term->term_id);
                if (is_numeric($term_class) || !trim($term_class, '-')) {
                    $term_class = $term->term_id;
                }

                $classes[] = sanitize_html_class($taxonomy . '-' . $term_class, $taxonomy . '-' . $term->term_id);
            }
        }

        return $classes;
    }

    /**
     * Retrieves the classes for the post div as an array.
     *
     * @param string|array         $class   One or more classes to add to the class list.
     * @param int|\WP_Post|Listing $listing Listing ID or listing object.
     *
     * @return array
     * @since 1.5.4
     */
    static function get_listing_class($class = '', $listing = null) {
        if (is_null($listing) && !empty($GLOBALS['listing'])) {
            // Product was null so pull from global.
            $listing = $GLOBALS['listing'];
        }

        if ($listing && !is_a($listing, Listing::class)) {
            $listing = rtcl()->factory->get_listing($listing);
        }

        if ($class) {
            if (!is_array($class)) {
                $class = preg_split('#\s+#', $class);
            }
        } else {
            $class = array();
        }

        $post_classes = array_map('esc_attr', $class);

        if (!$listing) {
            return $post_classes;
        }


        $post_classes = apply_filters('post_class', $post_classes, $class, $listing->get_id());

        $classes = array_merge(
            $post_classes,
            array(
                'post-' . $listing->get_id(),
                'status-' . $listing->get_status()
            ),
            $listing->get_label_class(),
            self::get_listing_taxonomy_class($listing->get_category_ids(), rtcl()->category),
            self::get_listing_taxonomy_class($listing->get_location_ids(), rtcl()->location)
        );

        return array_map('esc_attr', array_unique(array_filter($classes)));
    }

    static function listing_data_attr_options($options = []) {
        global $listing, $rtcl_has_map_data;
        if ($rtcl_has_map_data && $listing->has_map()) {
            $options = wp_parse_args($listing->get_map_data('content'), $options);
        }
        $data_options = apply_filters('rtcl_single_listing_data_options', $options);
        if (!empty($data_options)) {
            echo 'data-options="' . htmlspecialchars(wp_json_encode($data_options)) . '"';
        }
    }

    public static function listing_loop_start($echo = true) {
        Functions::set_loop_prop('loop', 0);
        $loop_start = apply_filters('rtcl_listing_loop_start', Functions::get_template_html('listing/loop/loop-start'));

        if ($echo) {
            echo $loop_start; // WPCS: XSS ok.
        } else {
            return $loop_start;
        }
    }

    public static function listing_loop_end($echo = true) {

        $loop_end = apply_filters('rtcl_listing_loop_end', Functions::get_template_html('listing/loop/loop-end'));

        if ($echo) {
            echo $loop_end; // WPCS: XSS ok.
        } else {
            return $loop_end;
        }
    }

    /**
     * Get an order note.
     *
     * @param int|WP_Comment $data Note ID (or WP_Comment instance for internal use only).
     *
     * @return stdClass|null        Object with order note details or null when does not exists.
     * @since  1.4.0
     */
    static function get_payment_note($data) {
        if (is_numeric($data)) {
            $data = get_comment($data);
        }

        if (!is_a($data, 'WP_Comment')) {
            return null;
        }

        return (object)apply_filters(
            'rtcl_get_payment_note',
            array(
                'id'            => (int)$data->comment_ID,
                'date_created'  => self::string_to_datetime($data->comment_date),
                'content'       => $data->comment_content,
                'customer_note' => (bool)get_comment_meta($data->comment_ID, 'is_customer_note', true),
                'added_by'      => __('RtclListing', 'classified-listing') === $data->comment_author ? 'system' : $data->comment_author,
            ),
            $data
        );
    }

    /**
     * Get order notes.
     *
     * @param array $args          Query arguments {
     *                             Array of query parameters.
     *
     * @type string $limit         Maximum number of notes to retrieve.
     *                                 Default empty (no limit).
     * @type int    $order_id      Limit results to those affiliated with a given order ID.
     *                                 Default 0.
     * @type array  $order__in     Array of order IDs to include affiliated notes for.
     *                                 Default empty.
     * @type array  $order__not_in Array of order IDs to exclude affiliated notes for.
     *                                 Default empty.
     * @type string $orderby       Define how should sort notes.
     *                                 Accepts 'date_created', 'date_created_gmt' or 'id'.
     *                                 Default: 'id'.
     * @type string $order         How to order retrieved notes.
     *                                 Accepts 'ASC' or 'DESC'.
     *                                 Default: 'DESC'.
     * @type string $type          Define what type of note should retrieve.
     *                                 Accepts 'customer', 'internal' or empty for both.
     *                                 Default empty.
     * }
     * @return stdClass[]              Array of stdClass objects with order notes details.
     * @since  1.4.0
     */
    public static function get_payment_notes($args) {
        $key_mapping = array(
            'limit'         => 'number',
            'order_id'      => 'post_id',
            'order__in'     => 'post__in',
            'order__not_in' => 'post__not_in',
        );

        foreach ($key_mapping as $query_key => $db_key) {
            if (isset($args[$query_key])) {
                $args[$db_key] = $args[$query_key];
                unset($args[$query_key]);
            }
        }

        // Define orderby.
        $orderby_mapping = array(
            'date_created'     => 'comment_date',
            'date_created_gmt' => 'comment_date_gmt',
            'id'               => 'comment_ID',
        );

        $args['orderby'] = !empty($args['orderby']) && in_array($args['orderby'], array(
            'date_created',
            'date_created_gmt',
            'id'
        ), true) ? $orderby_mapping[$args['orderby']] : 'comment_ID';

        // Set Classified Listing payment note type.
        if (isset($args['type']) && 'customer' === $args['type']) {
            $args['meta_query'] = array( // WPCS: slow query ok.
                array(
                    'key'     => 'is_customer_note',
                    'value'   => 1,
                    'compare' => '=',
                ),
            );
        } elseif (isset($args['type']) && 'internal' === $args['type']) {
            $args['meta_query'] = array( // WPCS: slow query ok.
                array(
                    'key'     => 'is_customer_note',
                    'compare' => 'NOT EXISTS',
                ),
            );
        }

        // Set correct comment type.
        $args['type'] = 'rtcl_payment_note';

        // Always approved.
        $args['status'] = 'approve';

        // Does not support 'count' or 'fields'.
        unset($args['count'], $args['fields']);

        remove_filter('comments_clauses', array(Comments::class, 'exclude_payment_comments'), 10);

        $notes = get_comments($args);

        add_filter('comments_clauses', array(Comments::class, 'exclude_payment_comments'), 10, 1);

        return array_filter(array_map(array(self::class, 'get_payment_note'), $notes));
    }


    /**
     * Sanitize a string destined to be a tooltip.
     *
     * @param string $var Data to sanitize.
     *
     * @return string
     * @since  1.4.0 Tooltips are encoded with htmlspecialchars to prevent XSS. Should not be used in conjunction with esc_attr()
     */
    static function sanitize_tooltip($var) {
        return htmlspecialchars(
            wp_kses(
                html_entity_decode($var),
                array(
                    'br'     => array(),
                    'em'     => array(),
                    'strong' => array(),
                    'small'  => array(),
                    'span'   => array(),
                    'ul'     => array(),
                    'li'     => array(),
                    'ol'     => array(),
                    'p'      => array(),
                )
            )
        );
    }

    /**
     * Display a classified Listing help tip.
     *
     * @param string $tip        Help tip text.
     * @param bool   $allow_html Allow sanitized HTML if true or escape.
     *
     * @return string
     * @since  2.5.0
     *
     */
    static function help_tip($tip, $allow_html = false) {
        if ($allow_html) {
            $tip = self::sanitize_tooltip($tip);
        } else {
            $tip = esc_attr($tip);
        }

        return '<span class="rtcl-help-tip" data-tip="' . $tip . '"></span>';
    }

    public static function get_wp_dropdown_categories($taxonomy, $args = array()) {

    }

    public static function is_active_elementor_widget($id_base) {
        global $wp_registered_widgets;
        if (!empty($wp_registered_widgets)) {
            foreach ($wp_registered_widgets as $widget_id => $widget) {
                if ($id_base === _get_widget_id_base($widget_id)) {
                    return true;
                }
            }
        }
        return false;
    }
}
