<?php

namespace Rtcl\Controllers\Admin\Meta;


use Rtcl\Resources\Options;

class SaveCFGData
{
    public function __construct() {
        add_action('save_post', array($this, 'save_cfg_data'), 10, 1);
        add_action('before_delete_post', array($this, 'before_cfg_delete_post'));
    }

    public function save_cfg_data($post_id) {
        if (wp_is_post_revision($post_id)) {
            return;
        }
        $post_type = get_post_type($post_id);
        if (rtcl()->post_type_cfg != $post_type) {
            return;
        }

        if (isset($_POST['associate'])) {
            $associate = !empty($_POST['associate']) && $_POST['associate'] == 'categories' ? 'categories' : 'all';
            update_post_meta($post_id, 'associate', $associate);
        }

        $fields = !empty($_REQUEST['rtcl']['fields']) ? $_REQUEST['rtcl']['fields'] : array();
        if (!empty($fields)) {

            $i = 0;
            foreach ($fields as $field_id => $field) {
                $type = get_post_meta($field_id, "_type", true);
                $type = array_key_exists($type, Options::get_custom_field_list()) ? $type : 'text';
                $options = Options::get_custom_field_list()[$type]['options'];
                if (is_array($options) && !empty($options)) {


                    foreach ($options as $meta_key => $opt) {
                        $value = null;
                        if ($meta_key == '_options') {
                            $optValue = array();
                            $optValue['default'] = null;
                            $default = !empty($field[$meta_key]['default']) && !is_array($field[$meta_key]['default']) ? esc_attr($field[$meta_key]['default']) : null;
                            if ($opt['type'] == 'checkbox') {
                                $default = !empty($field[$meta_key]['default']) && is_array($field[$meta_key]['default']) ? $field[$meta_key]['default'] : array();
                            }
                            if (!empty($field[$meta_key]['choices'])) {
                                foreach ($field[$meta_key]['choices'] as $choiceID => $choice) {
                                    if (!empty($choice['value'])) {
                                        $ct = !empty($choice['title']) ? esc_attr($choice['title']) : null;
                                        $cv = !empty($choice['value']) ? sanitize_title(esc_attr($choice['value'])) : sanitize_title($ct);
                                        $ct = $ct ? $ct : $cv;
                                        $optValue['choices'][$cv] = $ct;
                                        if ($type == 'checkbox') {
                                            if (is_array($default) && !empty($default) && in_array($choiceID,
                                                    $default)) {
                                                $optValue['default'][] = $cv;
                                            }
                                        } elseif ($type == 'select' || $type == 'radio') {
                                            if ($default && $default == $choiceID) {
                                                $optValue['default'] = $cv;
                                            }
                                        }
                                    }
                                }
                            }
                            $value = $optValue;
                        } else {
                            $value = !empty($field[$meta_key]) ? esc_attr($field[$meta_key]) : null;
                        }
                        update_post_meta($field_id, $meta_key, $value);
                    }
                    $arg = array('ID' => $field_id, 'menu_order' => $i);
                    if (get_post_status($field_id) != 'publish') {
                        $arg['post_status'] = 'publish';
                    }
                    remove_action('save_post', array($this, 'save_cfg_data'), 10);
                    wp_update_post($arg);
                    add_action('save_post', array($this, 'save_cfg_data'), 10);
                    $i++;
                }
            }

        }
    }

    public function before_cfg_delete_post($post_id) {
        global $post_type;

        if (rtcl()->post_type_cfg != $post_type) {
            return;
        }

        $args = array(
            'post_type'        => rtcl()->post_type_cf,
            'post_status'      => 'publish',
            'posts_per_page'   => -1,
            'fields'           => 'ids',
            'post_parent'      => $post_id,
            'suppress_filters' => false
        );
        $custom_fields_ids = get_posts(apply_filters('rtcl_before_cfg_delete_post_args', $args));

        if (!empty($custom_fields_ids)) {
            foreach ($custom_fields_ids as $custom_fields_id) {
                wp_delete_post($custom_fields_id, true);
            }
        }
    }
}