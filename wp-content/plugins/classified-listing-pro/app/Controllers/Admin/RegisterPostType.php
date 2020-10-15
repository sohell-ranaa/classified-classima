<?php

namespace Rtcl\Controllers\Admin;

use Rtcl\Helpers\Functions;

class RegisterPostType
{

    public static function init() {
        add_action('init', [__CLASS__, 'register_taxonomies'], 5);
        add_action('init', [__CLASS__, 'register_post_types'], 5);
        add_action('init', [__CLASS__, 'register_post_status'], 9);
        add_action('init', [__CLASS__, 'support_jetpack_omnisearch']);
        add_filter('rest_api_allowed_post_types', [__CLASS__, 'rest_api_allowed_post_types']);
        add_action('rtcl_after_register_post_type', [__CLASS__, 'maybe_flush_rewrite_rules']);
        add_action('rtcl_flush_rewrite_rules', [__CLASS__, 'flush_rewrite_rules']);
        add_filter('gutenberg_can_edit_post_type', [__CLASS__, 'gutenberg_can_edit_post_type'], 10, 2);
        add_filter('use_block_editor_for_post_type', [__CLASS__, 'gutenberg_can_edit_post_type'], 10, 2);
    }

    public static function register_taxonomies() {
        if (!is_blog_installed() || post_type_exists(rtcl()->post_type)) {
            return;
        }
        do_action('rtcl_register_taxonomy');

        $permalinks = Functions::get_permalink_structure();

        $cat_labels = array(
            'name'                       => _x('Listing Categories', 'Taxonomy General Name', 'classified-listing'),
            'singular_name'              => _x('Category', 'Taxonomy Singular Name', 'classified-listing'),
            'menu_name'                  => __('Categories', 'classified-listing'),
            'all_items'                  => __('All Categories', 'classified-listing'),
            'parent_item'                => __('Parent Category', 'classified-listing'),
            'parent_item_colon'          => __('Parent Category:', 'classified-listing'),
            'new_item_name'              => __('New Category Name', 'classified-listing'),
            'add_new_item'               => __('Add New Category', 'classified-listing'),
            'edit_item'                  => __('Edit Category', 'classified-listing'),
            'update_item'                => __('Update Category', 'classified-listing'),
            'view_item'                  => __('View Category', 'classified-listing'),
            'separate_items_with_commas' => __('Separate Categories with commas', 'classified-listing'),
            'add_or_remove_items'        => __('Add or remove Categories', 'classified-listing'),
            'choose_from_most_used'      => __('Choose from the most used', 'classified-listing'),
            'popular_items'              => null,
            'search_items'               => __('Search Categories', 'classified-listing'),
            'not_found'                  => __('Not Found', 'classified-listing'),
        );

        $cat_args = array(
            'labels'            => $cat_labels,
            'hierarchical'      => true,
            'public'            => true,
            'show_ui'           => true,
            'show_admin_column' => true,
            'show_in_nav_menus' => true,
            'show_tagcloud'     => false,
            'query_var'         => true,
            'capabilities'      => array(
                'manage_terms' => 'manage_rtcl_options',
                'edit_terms'   => 'manage_rtcl_options',
                'delete_terms' => 'manage_rtcl_options',
                'assign_terms' => 'edit_' . rtcl()->post_type . 's'
            ),
            'rewrite'           => array(
                'slug'         => $permalinks['category_base'],
                'with_front'   => false,
                'hierarchical' => true,
            )
        );

        register_taxonomy(rtcl()->category, rtcl()->post_type, apply_filters('rtcl_register_listing_category_args', $cat_args));

        $location_labels = array(
            'name'                       => _x('Listing Locations', 'Taxonomy General Name', 'classified-listing'),
            'singular_name'              => _x('Location', 'Taxonomy Singular Name', 'classified-listing'),
            'menu_name'                  => __('Locations', 'classified-listing'),
            'all_items'                  => __('All Locations', 'classified-listing'),
            'parent_item'                => __('Parent Location', 'classified-listing'),
            'parent_item_colon'          => __('Parent Location:', 'classified-listing'),
            'new_item_name'              => __('New Location Name', 'classified-listing'),
            'add_new_item'               => __('Add New Location', 'classified-listing'),
            'edit_item'                  => __('Edit Location', 'classified-listing'),
            'update_item'                => __('Update Location', 'classified-listing'),
            'view_item'                  => __('View Location', 'classified-listing'),
            'separate_items_with_commas' => __('Separate Locations with commas', 'classified-listing'),
            'add_or_remove_items'        => __('Add or remove Locations', 'classified-listing'),
            'choose_from_most_used'      => __('Choose from the most used', 'classified-listing'),
            'popular_items'              => null,
            'search_items'               => __('Search Locations', 'classified-listing'),
            'not_found'                  => __('Not Found', 'classified-listing'),
        );

        $location_args = array(
            'labels'            => $location_labels,
            'hierarchical'      => true,
            'public'            => true,
            'show_ui'           => true,
            'show_admin_column' => true,
            'show_in_nav_menus' => true,
            'show_tagcloud'     => false,
            'query_var'         => true,
            'capabilities'      => array(
                'manage_terms' => 'manage_rtcl_options',
                'edit_terms'   => 'manage_rtcl_options',
                'delete_terms' => 'manage_rtcl_options',
                'assign_terms' => 'edit_' . rtcl()->post_type . 's'
            ),
            'rewrite'           => array(
                'slug'         => $permalinks['location_base'],
                'with_front'   => false,
                'hierarchical' => true,
            )
        );

        register_taxonomy(rtcl()->location, rtcl()->post_type, apply_filters('rtcl_register_listing_location_args', $location_args));


        do_action('rtcl_after_register_taxonomy');
    }

    public static function register_post_types() {

        if (!is_blog_installed() || post_type_exists(rtcl()->post_type)) {
            return;
        }

        do_action('rtcl_register_post_type');

        $permalinks = Functions::get_permalink_structure();

        $labels = array(
            'name'               => _x('Classified Listings', 'post type general name', 'classified-listing'),
            'singular_name'      => _x('Classified Listing', 'post type singular name', 'classified-listing'),
            'add_new'            => _x('Add New', 'post', 'classified-listing'),
            'add_new_item'       => __('Add New Listing', 'classified-listing'),
            'edit_item'          => __('Edit Listing', 'classified-listing'),
            'new_item'           => __('New Listing', 'classified-listing'),
            'all_items'          => __('All Listings', 'classified-listing'),
            'view_item'          => __('View Listing', 'classified-listing'),
            'search_items'       => __('Search Listing', 'classified-listing'),
            'not_found'          => __('No Listings found', 'classified-listing'),
            'not_found_in_trash' => __('No Listing found in the Trash', 'classified-listing'),
            'name_admin_bar'     => __('Listing', 'classified-listing'),
            'update_item'        => __('Update Listing', 'classified-listing'),
            'parent_item_colon'  => '',
            'menu_name'          => __('Classified Listing', 'classified-listing')
        );
        $listing_support = array('title', 'editor', 'author');
        $moderation_settings = Functions::get_option('rtcl_moderation_settings');
        if (!empty($moderation_settings['has_comment_form'])) {
            array_push($listing_support, 'comments');
        }
        $listings_page_id = Functions::get_page_id('listings');

        if (current_theme_supports('rtcl')) {
            $has_archive = $listings_page_id && get_post($listings_page_id) ? urldecode(get_page_uri($listings_page_id)) : 'listings';
        } else {
            $has_archive = false;
        }


        // If theme support changes, we may need to flush permalinks since some are changed based on this flag.
        $theme_support = current_theme_supports('rtcl') ? 'yes' : 'no';
        if (get_option('current_theme_supports_rtcl') !== $theme_support && update_option('current_theme_supports_rtcl', $theme_support)) {
            update_option('rtcl_queue_flush_rewrite_rules', 'yes');
        }

        $args = array(
            'labels'              => $labels,
            'public'              => true,
            'menu_icon'           => RTCL_URL . '/assets/images/icon-20x20.png',
            'show_ui'             => true,
            'show_in_menu'        => true,
            'menu_position'       => 5,
            'supports'            => $listing_support,
            'hierarchical'        => false,
            'rewrite' => $permalinks['listing_base'] ? array(
                'slug'       => $permalinks['listing_base'],
                'with_front' => false,
                'feeds'      => true,
            ) : false,
            'show_in_admin_bar'   => true,
            'show_in_nav_menus'   => true,
            'can_export'          => true,
            'has_archive'         => $has_archive,
            'exclude_from_search' => false,
            'publicly_queryable'  => true,
            'capability_type'     => rtcl()->post_type,
            'map_meta_cap'        => true
        );

        register_post_type(rtcl()->post_type, apply_filters('rtcl_register_listing_post_type_args', $args));

        $cf_group_labels = array(
            'name'               => __('Custom Fields', 'classified-listing'),
            'singular_name'      => __('Custom Fields', 'classified-listing'),
            'add_new'            => __('Add New', 'classified-listing'),
            'add_new_item'       => __('Add New Field Group', 'classified-listing'),
            'edit_item'          => __('Edit Field Group', 'classified-listing'),
            'new_item'           => __('New Field Group', 'classified-listing'),
            'view_item'          => __('View Field Group', 'classified-listing'),
            'search_items'       => __('Search Field Groups', 'classified-listing'),
            'not_found'          => __('No Field Groups found', 'classified-listing'),
            'not_found_in_trash' => __('No Field Groups found in Trash', 'classified-listing'),
        );

        register_post_type(rtcl()->post_type_cfg,
            apply_filters('rtcl_register_custom_field_group_args',
                array(
                    'labels'       => $cf_group_labels,
                    'public'       => false,
                    'show_ui'      => true,
                    '_builtin'     => false,
                    'hierarchical' => true,
                    'taxonomies'   => array('rtcl_category'),
                    'rewrite'      => false,
                    'query_var'    => "rtcl_cfg",
                    'supports'     => array(
                        'title',
                    ),
                    'show_in_menu' => 'edit.php?post_type=' . rtcl()->post_type,
                    'capabilities' => array(
                        'edit_post'          => 'manage_rtcl_options',
                        'read_post'          => 'manage_rtcl_options',
                        'delete_post'        => 'manage_rtcl_options',
                        'edit_posts'         => 'manage_rtcl_options',
                        'edit_others_posts'  => 'manage_rtcl_options',
                        'delete_posts'       => 'manage_rtcl_options',
                        'publish_posts'      => 'manage_rtcl_options',
                        'read_private_posts' => 'manage_rtcl_options'
                    )
                )
            )
        );

        register_post_type(rtcl()->post_type_cf,
            apply_filters('rtcl_register_listing_custom_field_args',
                array(
                    'label'        => __('Custom Field', 'classified-listing'),
                    'public'       => false,
                    'hierarchical' => false,
                    'supports'     => false,
                    'rewrite'      => false,
                    'capabilities' => array(
                        'edit_post'          => 'manage_rtcl_options',
                        'read_post'          => 'manage_rtcl_options',
                        'delete_post'        => 'manage_rtcl_options',
                        'edit_posts'         => 'manage_rtcl_options',
                        'edit_others_posts'  => 'manage_rtcl_options',
                        'delete_posts'       => 'manage_rtcl_options',
                        'publish_posts'      => 'manage_rtcl_options',
                        'read_private_posts' => 'manage_rtcl_options'
                    ),
                )
            )
        );

        $payment_labels = array(
            'name'               => _x('Payment History', 'Post Type General Name', 'classified-listing'),
            'singular_name'      => _x('Payment', 'Post Type Singular Name', 'classified-listing'),
            'menu_name'          => __('Payment History', 'classified-listing'),
            'name_admin_bar'     => __('Payment', 'classified-listing'),
            'all_items'          => __('Payment History', 'classified-listing'),
            'add_new_item'       => __('Add New Payment', 'classified-listing'),
            'add_new'            => __('Add New', 'classified-listing'),
            'new_item'           => __('New Payment', 'classified-listing'),
            'edit_item'          => __('Edit Payment', 'classified-listing'),
            'update_item'        => __('Update Payment', 'classified-listing'),
            'view_item'          => __('View Payment', 'classified-listing'),
            'search_items'       => __('Search Payment', 'classified-listing'),
            'not_found'          => __('No payments found', 'classified-listing'),
            'not_found_in_trash' => __('No payments found in Trash', 'classified-listing'),
        );

        $payment_args = array(
            'label'               => __('Payments', 'classified-listing'),
            'description'         => __('Post Type Description', 'classified-listing'),
            'labels'              => $payment_labels,
            'supports'            => array('title', 'comments', 'custom-fields'),
            'taxonomies'          => array(''),
            'hierarchical'        => false,
            'public'              => false,
            'show_ui'             => true,
            'show_in_menu'        => 'edit.php?post_type=' . rtcl()->post_type,
            'show_in_admin_bar'   => true,
            'show_in_nav_menus'   => true,
            'can_export'          => true,
            'has_archive'         => false,
            'exclude_from_search' => true,
            'publicly_queryable'  => false,
            'capability_type'     => rtcl()->post_type_payment,
            'map_meta_cap'        => true
        );

        $pricing_labels = array(
            'name'               => _x('Pricing', 'Post Type General Name', 'classified-listing'),
            'singular_name'      => _x('Pricing', 'Post Type Singular Name', 'classified-listing'),
            'menu_name'          => __('Pricing', 'classified-listing'),
            'name_admin_bar'     => __('Pricing', 'classified-listing'),
            'all_items'          => __('Pricing', 'classified-listing'),
            'add_new_item'       => __('Add New Pricing', 'classified-listing'),
            'add_new'            => __('Add New', 'classified-listing'),
            'new_item'           => __('New Pricing', 'classified-listing'),
            'edit_item'          => __('Edit Pricing', 'classified-listing'),
            'update_item'        => __('Update Pricing', 'classified-listing'),
            'view_item'          => __('View Pricing', 'classified-listing'),
            'search_items'       => __('Search Pricing', 'classified-listing'),
            'not_found'          => __('No Pricing found', 'classified-listing'),
            'not_found_in_trash' => __('No Pricing found in Trash', 'classified-listing'),
        );

        $pricing_args = array(
            'labels'            => $pricing_labels,
            'public'            => false,
            'show_ui'           => true,
            'supports'          => array('title', 'page-attributes'),
            'show_in_menu'      => 'edit.php?post_type=' . rtcl()->post_type,
            'show_in_admin_bar' => true,
            'has_archive'       => false,
            'capability_type'   => rtcl()->post_type_pricing,
            'map_meta_cap'      => true
        );
        $payment_settings = Functions::get_option('rtcl_payment_settings');

        if (!empty($payment_settings['payment']) && $payment_settings['payment'] == 'yes') {
            register_post_type(rtcl()->post_type_payment, apply_filters('rtcl_register_payment_post_type_args', $payment_args));
            register_post_type(rtcl()->post_type_pricing, apply_filters('rtcl_register_pricing_post_type_args', $pricing_args));
        }

        do_action('rtcl_after_register_post_type');
    }

    public static function register_post_status() {
        register_post_status('rtcl-reviewed', array(
            'label'       => _x('Reviewed', 'post', 'classified-listing'),
            'public'      => is_admin(),
            'internal'    => false,
            'label_count' => _n_noop('Review <span class="count">(%s)</span>',
                'Review <span class="count">(%s)</span>', 'classified-listing')
        ));

        register_post_status('rtcl-expired', array(
            'label'       => _x('Expired', 'post', 'classified-listing'),
            'public'      => is_admin(),
            'internal'    => false,
            'label_count' => _n_noop('Expired <span class="count">(%s)</span>',
                'Expired <span class="count">(%s)</span>', 'classified-listing')
        ));

        register_post_status('rtcl-temp', array(
            'label'                  => _x('Temporary', 'post', 'classified-listing'),
            'public'                 => false,
            'internal'               => false,
            'show_in_admin_all_list' => false,
            'label_count'            => _n_noop('Temporary <span class="count">(%s)</span>',
                'Temporary <span class="count">(%s)</span>', 'classified-listing')
        ));

        register_post_status('rtcl-pending', array(
            'label'                     => _x('Pending payment', 'pending status payment', 'classified-listing'),
            'public'                    => is_admin(),
            'exclude_from_search'       => false,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            'label_count'               => _n_noop('Pending payment <span class="count">(%s)</span>',
                'Pending payment <span class="count">(%s)</span>', 'classified-listing'),
        ));

        register_post_status('rtcl-created', array(
            'label'                     => _x('Created', 'Payment status', 'classified-listing'),
            'public'                    => is_admin(),
            'exclude_from_search'       => false,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            'label_count'               => _n_noop('Created <span class="count">(%s)</span>',
                'Created <span class="count">(%s)</span>', 'classified-listing'),
        ));

        register_post_status('rtcl-completed', array(
            'label'                     => _x('Completed', 'Payment status', 'classified-listing'),
            'public'                    => is_admin(),
            'exclude_from_search'       => false,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            'label_count'               => _n_noop('Completed <span class="count">(%s)</span>',
                'Completed <span class="count">(%s)</span>', 'classified-listing'),
        ));

        register_post_status('rtcl-failed', array(
            'label'                     => _x('Failed', 'Payment status', 'classified-listing'),
            'public'                    => is_admin(),
            'exclude_from_search'       => false,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            'label_count'               => _n_noop('Failed <span class="count">(%s)</span>',
                'Failed <span class="count">(%s)</span>', 'classified-listing'),
        ));

        register_post_status('rtcl-cancelled', array(
            'label'                     => _x('Cancelled', 'Payment status', 'classified-listing'),
            'public'                    => is_admin(),
            'exclude_from_search'       => false,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            'label_count'               => _n_noop('Cancelled <span class="count">(%s)</span>',
                'Cancelled <span class="count">(%s)</span>', 'classified-listing'),
        ));

        register_post_status('rtcl-refunded', array(
            'label'                     => _x('Refunded', 'Payment status', 'classified-listing'),
            'public'                    => is_admin(),
            'exclude_from_search'       => false,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            'label_count'               => _n_noop('Refunded <span class="count">(%s)</span>',
                'Refunded <span class="count">(%s)</span>', 'classified-listing'),
        ));

        register_post_status('rtcl-on-hold', array(
            'label'                     => _x('On hold', 'Payment status', 'classified-listing'),
            'public'                    => is_admin(),
            'exclude_from_search'       => false,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            'label_count'               => _n_noop('On hold <span class="count">(%s)</span>',
                'Refunded <span class="count">(%s)</span>', 'classified-listing'),
        ));
        register_post_status('rtcl-processing', array(
            'label'                     => _x('Processing', 'Payment status', 'classified-listing'),
            'public'                    => is_admin(),
            'exclude_from_search'       => false,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            'label_count'               => _n_noop('Processing <span class="count">(%s)</span>',
                'Refunded <span class="count">(%s)</span>', 'classified-listing'),
        ));
    }


    /**
     * Add Product Support to Jetpack Omnisearch.
     */
    public static function support_jetpack_omnisearch() {
        if (class_exists('Jetpack_Omnisearch_Posts')) {
            new Jetpack_Omnisearch_Posts(rtcl()->post_type);
        }
    }

    /**
     * Added product for Jetpack related posts.
     *
     * @param array $post_types Post types.
     *
     * @return array
     */
    public static function rest_api_allowed_post_types($post_types) {
        $post_types[] = rtcl()->post_type;

        return $post_types;
    }

    /**
     * Flush rewrite rules.
     */
    public static function flush_rewrite_rules() {
        flush_rewrite_rules();
    }

    /**
     * Flush rules if the event is queued.
     *
     */
    public static function maybe_flush_rewrite_rules() {
        if ('yes' === get_option('rtcl_queue_flush_rewrite_rules')) {
            update_option('rtcl_queue_flush_rewrite_rules', 'no');
            self::flush_rewrite_rules();
        }
    }

    /**
     * Disable Gutenberg for products.
     *
     * @param bool   $can_edit  Whether the post type can be edited or not.
     * @param string $post_type The post type being checked.
     *
     * @return bool
     */
    public static function gutenberg_can_edit_post_type($can_edit, $post_type) {
        return rtcl()->post_type === $post_type ? false : $can_edit;
    }

}
