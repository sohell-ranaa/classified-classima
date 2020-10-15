<?php


namespace Rtcl\Controllers\Ajax;


use Rtcl\Helpers\Functions;

class AjaxListingType
{

    function __construct() {
        add_action('wp_ajax_rtcl_ajax_add_listing_type', array($this, 'rtcl_ajax_add_listing_type'));
        add_action('wp_ajax_rtcl_ajax_delete_listing_type', array($this, 'rtcl_ajax_delete_listing_type'));
        add_action('wp_ajax_rtcl_ajax_update_listing_type', array($this, 'rtcl_ajax_update_listing_type'));
    }

    function rtcl_ajax_add_listing_type() {
        $success = false;
        $type = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : '';
        $message = __("Type field is required", "classified-listing");
        $data = array();
        if (Functions::verify_nonce()) {
            if ($type) {
                $id = Functions::sanitize_title_with_underscores($type);
                $types = Functions::get_listing_types();
                if (isset($types[$id])) {
                    $message = __("This type already exist.", "classified-listing");
                } else {
                    $types[$id] = $type;
                    update_option(rtcl()->get_listing_types_option_id(), $types);
                    $data = array(
                        'id'   => $id,
                        'name' => $type
                    );
                    $success = true;
                    $message = __("Successfully added.", "classified-listing");
                }

            }
        } else {
            $message = __("Session expired.", "classified-listing");
        }

        wp_send_json(array(
            "success" => $success,
            "message" => $message,
            "data"    => $data
        ));
    }

    function rtcl_ajax_update_listing_type() {
        $success = false;
        $old_id = isset($_POST['old_id']) ? sanitize_text_field($_POST['old_id']) : '';
        $id = isset($_POST['id']) ? sanitize_text_field($_POST['id']) : '';
        $name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
        $message = __("Type id and name is required", "classified-listing");
        $data = array(
            'id'   => '',
            'name' => ''
        );
        if (Functions::verify_nonce()) {
            if ($old_id && $id && $name) {
                $old_id = Functions::sanitize_title_with_underscores($old_id);
                $id = Functions::sanitize_title_with_underscores($id);
                $types = Functions::get_listing_types();
                if (!empty($types)) {
                    $old_exist = false;
                    if (isset($types[$old_id])) {
                        $data = array(
                            'id'   => $old_id,
                            'name' => $types[$old_id]
                        );
                        $old_exist = true;
                    }
                    if ($old_id === $id && $types[$old_id] === $name) {
                        $message = __("No change found.", "classified-listing");
                    } else if ($old_id === $id && $old_exist) {
                        $types[$id] = $name;
                        $data = array(
                            'id'   => $id,
                            'name' => $name
                        );
                        $success = true;
                        update_option(rtcl()->get_listing_types_option_id(), $types);
                        $message = __("Successfully updated.", "classified-listing");
                    } else if ($old_id !== $id && $old_exist && $types[$id]) {
                        $message = __("This type is already exist.", "classified-listing");
                    } else if ($old_id !== $id && $old_exist) {
                        $new_types = array();
                        foreach ($types as $typeId => $type) {
                            if ($typeId == $old_id) {
                                $new_types[$id] = $name;
                                $data = array(
                                    'id'   => $id,
                                    'name' => $name
                                );
                            } else {
                                $new_types[$typeId] = $type;
                            }
                        }
                        update_option(rtcl()->get_listing_types_option_id(), $new_types);
                        $success = true;
                        $message = __("Successfully updated", "classified-listing");
                    } else {
                        $message = __("Unknown Error!", "classified-listing");
                    }
                } else {
                    $message = __("No types found.", "classified-listing");
                }

            }
        } else {
            $message = __("Session expired.", "classified-listing");
        }

        wp_send_json(array(
            "success" => $success,
            "message" => $message,
            "data"    => $data
        ));
    }

    function rtcl_ajax_delete_listing_type() {
        $success = false;
        $id = isset($_POST['id']) ? sanitize_text_field($_POST['id']) : '';
        $message = __("Type id is required", "classified-listing");
        $data = array();
        if (Functions::verify_nonce()) {
            if ($id) {
                $types = Functions::get_listing_types();
                if (isset($types[$id])) {
                    unset($types[$id]);
                    update_option(rtcl()->get_listing_types_option_id(), $types);
                    $success = true;
                    $message = __("Successfully deleted", "classified-listing");
                } else {
                    $message = __("No type found to delete", "classified-listing");
                }

            }
        } else {
            $message = __("Session expired.", "classified-listing");
        }

        wp_send_json(array(
            "success" => $success,
            "message" => $message,
            "data"    => $data
        ));
    }

}