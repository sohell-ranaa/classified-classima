<?php

namespace Rtcl\Shortcodes;


use Rtcl\Controllers\Shortcodes;
use Rtcl\Helpers\Functions;
use Rtcl\Helpers\Pagination;
use Rtcl\Models\Listing;

class FilterListings
{

    /**
     * Get the shortcode content.
     *
     * @param array $atts Shortcode attributes.
     *
     * @return string
     */
    public static function get($atts) {
        return Shortcodes::shortcode_wrapper(array(__CLASS__, 'output'), $atts);
    }


    /**
     * Output the shortcode.
     *
     * @param array $atts Shortcode attributes.
     *
     * @throws \Exception
     */
    public static function output($atts) {

        //		Load Scripts
        wp_enqueue_style('owl-carousel');
        wp_enqueue_script('owl-carousel');
        wp_enqueue_script('rtcl-public');
        wp_enqueue_style('rtcl-public');


        $general_settings = Functions::get_option('rtcl_general_settings');
        $atts = shortcode_atts(array(
            'title'            => __('Listings', 'classified-listing'),
            'location'         => '',
            'category'         => '',
            'authors'          => '',
            'related_listings' => 0,
            'type'             => 'all',
            'limit'            => !empty($general_settings['listings_per_page']) ? absint($general_settings['listings_per_page']) : 8,
            'orderby'          => $general_settings['orderby'],
            'order'            => $general_settings['order'],
            'view'             => 'grid',
            'columns'          => 4,
            'tab_items'        => 3,
            'mobile_items'     => 1,
            'show_image'       => 1,
            'image_position'   => 'top',
            'show_category'    => 1,
            'show_location'    => 1,
            'show_labels'      => 1,
            'show_price'       => 1,
            'show_date'        => 1,
            'show_user'        => 1,
            'show_views'       => 1,
            'pagination'       => 0,
        ), $atts);


        // WP Query
        global $post;

        $args = array(
            'post_type'      => rtcl()->post_type,
            'post_status'    => 'publish',
            'posts_per_page' => !empty($atts['limit']) ? (int)$atts['limit'] : -1
        );

        if ($atts['pagination'] && $atts['view'] === 'grid') {
            $args['paged'] = Pagination::get_page_number();
        }

        if ($atts['authors'] && $authors = explode(',', $atts['authors'])) {
            if (!empty($authors)) {
                $args['author__in'] = $authors;
            }
        }

        $tax_queries = array();
        $meta_queries = array();

        $location = !empty($atts['location']) ? explode(',', esc_attr($atts['location'])) : array();

        if ($atts['related_listings']) {

            $term_slug = get_query_var('rtcl_location');

            if ('' != $term_slug) {
                $term = get_term_by('slug', sanitize_text_field($term_slug), rtcl()->location);
                $location = array($term->term_id);
            }

        }

        if (!empty($location)) {

            $tax_queries[] = array(
                'taxonomy'         => rtcl()->location,
                'field'            => 'term_id',
                'terms'            => $location,
                'include_children' => isset($general_settings['include_results_from']) && in_array('child_locations',
                    $general_settings['include_results_from']) ? true : false,
            );

        }

        $category = !empty($atts['category']) ? explode(',', esc_attr($atts['category'])) : array();

        if ($atts['related_listings']) {

            if (is_singular(rtcl()->post_type)) {

                $term = wp_get_object_terms($post->ID, rtcl()->category);
                $category = !empty($term) ? $term[0]->term_id : 0;

                $args['post__not_in'] = array($post->ID);

            } else {

                $term_slug = get_query_var('rtcl_category');

                if ('' != $term_slug) {
                    $term = get_term_by('slug', sanitize_text_field($term_slug), rtcl()->category);
                    $category = array($term->term_id);
                }

            }

        }

        if (!empty($category)) {

            $tax_queries[] = array(
                'taxonomy'         => rtcl()->category,
                'field'            => 'term_id',
                'terms'            => $category,
                'include_children' => isset($general_settings['include_results_from']) && in_array('child_categories',
                    $general_settings['include_results_from']) ? true : false,
            );

        }

        switch ($atts['type']) {
            case "featured_only":
                $meta_queries[] = array(
                    'key'     => 'featured',
                    'value'   => 1,
                    'compare' => '='
                );
                break;

            case "top_only":
                $meta_queries[] = array(
                    'key'     => '_top',
                    'value'   => 1,
                    'compare' => '='
                );
                break;

            case "feature_top":
                $meta_queries[] = array(
                    'key'     => 'featured',
                    'value'   => 1,
                    'compare' => '='
                );
                $meta_queries[] = array(
                    'key'     => '_top',
                    'value'   => 1,
                    'compare' => '='
                );
                break;

            default:
                break;

        }

        $count_tax_queries = count($tax_queries);
        if ($count_tax_queries) {
            $args['tax_query'] = ($count_tax_queries > 1) ? array_merge(array('relation' => 'AND'),
                $tax_queries) : $tax_queries;
        }

        $count_meta_queries = count($meta_queries);
        if ($count_meta_queries) {
            $args['meta_query'] = ($count_meta_queries > 1) ? array_merge(array('relation' => 'AND'),
                $meta_queries) : $meta_queries;
        }

        $orderby = sanitize_text_field($atts['orderby']);
        $order = sanitize_text_field($atts['order']);

        switch ($orderby) {
            case 'price' :
                $args['meta_key'] = $orderby;
                $args['orderby'] = 'meta_value_num';
                $args['order'] = $order;
            case 'views' :
                $args['meta_key'] = '_views';
                $args['orderby'] = 'meta_value_num';
                $args['order'] = $order;
                break;
            case 'rand' :
                $args['orderby'] = $orderby;
                break;
            default :
                $args['orderby'] = $orderby;
                $args['order'] = $order;
        }

        $rtcl_query = new \WP_Query(apply_filters('rtcl_filter_listings_shortcode_args', $args));

        // Process Output
        $map = ($atts['view'] === 'map') ? "-map" : '';
        if ($map) {
            wp_enqueue_script('rtcl-google-map-info-box');
            wp_enqueue_script('rtcl-map');
            $items = array();
            if ($rtcl_query->have_posts()) {
                while ($rtcl_query->have_posts()) {
                    $rtcl_query->the_post();
                    $item_id = get_the_ID();
                    $listing = rtcl()->factory->get_listing($item_id);
                    $items[] = $listing->get_map_data();
                }
                wp_reset_postdata();
            }

            $atts['items'] = $items;
        }
        $atts['slider_options'] = [];

        $class[] = 'rtcl-grid-view';
        $class[] = 'columns-' . absint($atts['columns']);
        if ($atts['view'] == 'slider') {
            $class[] = 'rtcl-carousel-slider';
            $atts['slider_options'] = apply_filters('rtcl_widget_listings_slider_options', array(
                "items"        => absint($atts['columns']),
                "tab_items"    => absint($atts['tab_items']),
                "mobile_items" => absint($atts['mobile_items']),
                "margin"       => 10,
                "nav"          => true,
                "dots"         => false,
            ));
        }
        if ($atts['view'] == 'grid') {
            $class[] = 'tab-columns-' . absint($atts['tab_items']);
            $class[] = 'mobile-columns-' . absint($atts['mobile_items']);
        }

        $atts['wrapper_classes'] = implode(' ', $class);
        Functions::get_template("widgets/listings" . $map, array(
            'rtcl_query' => $rtcl_query,
            'instance'   => $atts
        ));

    }

}