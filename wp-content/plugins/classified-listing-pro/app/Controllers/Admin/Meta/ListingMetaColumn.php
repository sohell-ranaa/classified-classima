<?php

namespace Rtcl\Controllers\Admin\Meta;


use Rtcl\Helpers\Functions;
use Rtcl\Resources\Options;

class ListingMetaColumn
{

    public function __construct() {
        add_action('manage_edit-' . rtcl()->post_type . '_columns', array($this, 'listing_get_columns'));
        add_action('manage_' . rtcl()->post_type . '_posts_custom_column',
            array($this, 'listing_column_content'), 10, 2);
        add_action('restrict_manage_posts', array($this, 'restrict_manage_posts'));
        add_action('before_delete_post', array($this, 'before_delete_post'));
        add_action('parse_query', array($this, 'parse_query'));

    }

    function listing_get_columns($columns) {
        $new_columns = array(
            'views'       => __('Views', 'classified-listing'),
            'featured'    => __('Featured', 'classified-listing'),
            '_top'        => __('Top', 'classified-listing'),
            'posted_date' => __('Posted Date', 'classified-listing'),
            'expiry_date' => __('Expires on', 'classified-listing'),
            'status'      => __('Status', 'classified-listing')
        );

        unset($columns['date']);

        $taxonomy_column = 'taxonomy-' . rtcl()->location;

        return Functions::array_insert_after($taxonomy_column, $columns, $new_columns);
    }

    function listing_column_content($column, $post_id) {

        switch ($column) {
            case 'views' :
                echo absint(get_post_meta($post_id, '_views', true));
                break;
            case 'featured' :
                $value = get_post_meta($post_id, 'featured', true);
                echo '<span class="rtcl-tick-cross">' . ($value == 1 ? '&#x2713;' : '&#x2717;') . '</span>';
                break;
            case '_top' :
                $value = get_post_meta($post_id, '_top', true);
                echo '<span class="rtcl-tick-cross">' . ($value == 1 ? '&#x2713;' : '&#x2717;') . '</span>';
                break;
            case 'posted_date' :
                printf(_x('%s ago', '%s = human-readable time difference', 'classified-listing'),
                    human_time_diff(get_the_time('U', $post_id), current_time('timestamp')));
                break;
            case 'expiry_date' :
                $never_expires = get_post_meta($post_id, 'never_expires', true);

                if (!empty($never_expires)) {
                    _e('Never Expires', 'classified-listing');
                } else {
                    $expiry_date = get_post_meta($post_id, 'expiry_date', true);

                    if (!empty($expiry_date)) {
                        echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'),
                            strtotime($expiry_date));
                    } else {
                        echo '-';
                    }
                }
                break;
            case 'status' :
                $listing_status = get_post_meta($post_id, 'listing_status', true);
                $listing_status = (empty($listing_status) || 'post_status' == $listing_status) ? get_post_status($post_id) : $listing_status;
                $status_list = Options::get_status_list();
                echo !empty($status_list[$listing_status]) ? $status_list[$listing_status] : "-";
                break;
        }
    }

    public function restrict_manage_posts() {

        global $typenow, $wp_query;

        if (rtcl()->post_type == $typenow) {
            $location_name = '';
            $location_id = '';
            $category_name = '';
            $category_id = '';

            if (!empty($_GET['_rtcl_location'])) {
                $location_id = absint($_GET['_rtcl_location']);
                $location = get_term_by('id', $location_id, rtcl()->location);
                $location_name = $location ? $location->name : '';
            }
            if (!empty($_GET['_rtcl_category'])) {
                $category_id = absint($_GET['_rtcl_category']);
                $category = get_term_by('id', $category_id, rtcl()->category);
                $category_name = $category ? $category->name : '';
            }

            ?>
            <select class="rtcl-ajax-select" name="_rtcl_location"
                    data-type="location"
                    data-placeholder="<?php esc_attr_e('Filter by location', 'classified-listing'); ?>"
                    data-allow_clear="true">
                <option value="<?php echo esc_attr($location_id); ?>" selected="selected">
                    <?php echo $location_name; ?>
                <option>
            </select>
            <select class="rtcl-ajax-select" name="_rtcl_category"
                    data-type="category"
                    data-placeholder="<?php esc_attr_e('Filter by category', 'classified-listing'); ?>"
                    data-allow_clear="true">
                <option value="<?php echo esc_attr($category_id); ?>" selected="selected">
                    <?php echo $category_name; ?>
                <option>
            </select>
            <?php
            // Restrict by featured
            $payment_settings = Functions::get_option('rtcl_payment_settings');
            if (!empty($payment_settings['payment']) && $payment_settings['payment'] == 1) {
                $featured = isset($_GET['featured']) ? $_GET['featured'] : 0;
                echo '<select name="featured">';
                printf('<option value="%d"%s>%s</option>', 0, selected(0, $featured, false),
                    __("All listings", 'classified-listing'));
                printf('<option value="%d"%s>%s</option>', 1, selected(1, $featured, false),
                    __("Featured only", 'classified-listing'));
                echo '</select>';

            }
            $stat = isset($_GET['post_status']) ? $_GET['post_status'] : "all";
            if ("trash" !== $stat) {
                echo '<select name="post_status">';
                $status_list = Options::get_status_list(true);
                printf('<option value="%s">%s</option>', 'all',
                    __("All Status", 'classified-listing'));
                foreach ($status_list as $key => $status) {
                    $slt = $key == $stat ? " selected" : null;
                    printf('<option value="%s"%s>%s</option>', $key, $slt, $status);
                }
                echo '</select>';
            }

        }

    }

    /**
     * @param $post_id
     *
     * @return mixed|void
     */
    function before_delete_post($post_id) {
        if (rtcl()->post_type !== get_post_type($post_id)) {
            return;
        }

//        $check = apply_filters('rtcl_before_delete_listing_attachment_check', false, $post_id, $post_type);
//        if (false !== $check) {
//            return $check;
//        }

        $children = get_children(apply_filters('rtcl_before_delete_listing_attachment_query_args', [
            'post_parent'    => $post_id,
            'post_type'      => 'attachment',
            'posts_per_page' => -1,
            'post_status'    => 'inherit',
        ], $post_id));
        if (!empty($children)) {
            foreach ($children as $child) {
                wp_delete_attachment($child->ID, true);
            }
        }

        do_action('rtcl_before_delete_listing', $post_id);
    }

    public function parse_query($query) {

        global $pagenow, $post_type;

        if ('edit.php' == $pagenow && rtcl()->post_type == $post_type) {

            $tax_query = [];
            // Convert location id to taxonomy term in query
            if (isset($_REQUEST['_rtcl_location']) && $location_id = Functions::clean(wp_unslash($_REQUEST['_rtcl_location']))) {
                $tax_query[] = [
                    'taxonomy' => rtcl()->location,
                    'field'    => 'ID',
                    'terms'    => array($location_id)
                ];
            }

            // Convert category id to taxonomy term in query
            if (isset($_REQUEST['_rtcl_category']) && $category_id = Functions::clean(wp_unslash($_REQUEST['_rtcl_category']))) {
                $tax_query[] = [
                    'taxonomy' => rtcl()->category,
                    'field'    => 'ID',
                    'terms'    => array($category_id)
                ];
            }
            if (!empty($tax_query)) {
                $query_tax_query = $query->get('tax_query');
                $query_tax_query = is_array($query_tax_query) ? $query_tax_query : [];
                $query_tax_query['relation'] = 'AND';
                $query->set('tax_query', array_merge($query_tax_query, $tax_query));
            }


            // Set featured meta in query
            if (isset($_GET['featured']) && 1 == $_GET['featured']) {
                $query->query_vars['meta_key'] = 'featured';
                $query->query_vars['meta_value'] = 1;
            }

        }

    }

}