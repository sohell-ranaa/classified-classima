<?php

namespace Rtcl\Shortcodes;


use Rtcl\Helpers\Functions;

class Categories
{

    public static function output($atts) {

        $settings = shortcode_atts(array(
            'view'         => 'grid',
            'orderby'      => 'date',
            'order'        => 'DESC',
            'columns'      => 4,
            'types'        => '',
            'description'  => true,
            'show_count'   => true,
            'icon'         => true,
            'image'        => false,
            'hide_empty'   => false,
            'pad_counts'   => true,
            'equal_height' => true
        ), $atts);

        // Enqueue dependencies
        wp_enqueue_style('rtcl-public');
        wp_enqueue_script('rtcl-public');

        $args = array(
            'taxonomy'     => rtcl()->category,
            'orderby'      => $settings['orderby'],
            'order'        => $settings['order'],
            'hide_empty'   => !empty($settings['hide_empty']) ? 1 : 0,
            'parent'       => 0,
            'hierarchical' => false,
        );
        if ($settings['orderby'] == 'custom') {
            $args['orderby'] = 'meta_value_num';
            $args['meta_key'] = '_rtcl_order';
        }
        if ($settings['types'] && $types = explode(',', $settings['types'])) {
            if (is_array($types) && !empty($types)) {
                $args['meta_query'] = array(
                    array(
                        'key'     => '_rtcl_types',
                        'value'   => $types,
                        'compare' => 'IN'
                    )
                );
            }
        }

        $terms = get_terms($args);

        if (!empty($terms) && !is_wp_error($terms)) {

            Functions::get_template("categories/categories-{$settings['view']}", array(
                'settings' => $settings,
                'terms'    => $terms
            ));

        } else {
            echo '<span>' . __('No Results Found.', 'classified-listing') . '</span>';
        }

    }

}