<?php

namespace RtclStore\Controllers\Ajax;

use Rtcl\Controllers\Hooks\Filters;
use Rtcl\Helpers\Functions;
use Rtcl\Helpers\Link;
use Rtcl\Resources\Options as RtclOptions;
use RtclStore\Helpers\Functions as RtclFunctions;
use RtclStore\Models\Store;

class FrontEnd
{

    function __construct() {
        add_action('wp_ajax_rtcl_update_store_data', [$this, 'rtcl_update_store_data']);
        add_action('wp_ajax_rtcl_ajax_store_banner_upload', [$this, 'rtcl_ajax_store_banner_upload']);
        add_action('wp_ajax_rtcl_ajax_store_banner_delete', [$this, 'rtcl_ajax_store_banner_delete']);
        add_action('wp_ajax_rtcl_ajax_store_logo_upload', [$this, 'rtcl_ajax_store_logo_upload']);
        add_action('wp_ajax_rtcl_ajax_store_logo_delete', [$this, 'rtcl_ajax_store_logo_delete']);

        add_action('wp_ajax_rtcl_send_mail_to_store_owner', [$this, 'rtcl_send_mail_to_store_owner']);
        add_action('wp_ajax_nopriv_rtcl_send_mail_to_store_owner', array($this, 'rtcl_send_mail_to_store_owner'));

        add_action('wp_ajax_rtcl_store_ajax_membership_promotion', [__CLASS__, 'membership_promotion_action']);
    }

    public static function membership_promotion_action() {
        if (!Functions::verify_nonce()) {
            wp_send_json_error(esc_html__("Authentication error!!", "classified-listing-store"));
        }
        $membership = rtclStore()->factory->get_membership();

        $promotion_data = apply_filters('rtcl_membership_promotion_process_data', [
            'promotions' => Functions::clean($_POST['_rtcl_membership_promotions']),
            'listing_id' => Functions::clean($_POST['listing_id'])
        ], $membership);
        $errors = new \WP_Error();
        do_action('rtcl_membership_promotion_process_data', $promotion_data, $membership, $_REQUEST, $errors);
        $errors = apply_filters('rtcl_membership_promotion_validation_errors', $errors, $promotion_data, $membership, $_REQUEST);
        $response = [];
        if ($membership) {
            $response = $membership->apply_promotion($promotion_data, $errors);
        } else {
            $errors->add('rtcl_membership_promotion_no_membership', __("You have no membership.", "classified-listing-store"));
        }
        if (is_wp_error($errors) && $errors->has_errors()) {
            wp_send_json_error(apply_filters('rtcl_membership_promotion_error_data', $errors->get_error_message(), $errors));
        } else {
            if (!empty($response['success'])) {
                wp_send_json_success(apply_filters('rtcl_membership_promotion_success_data', [
                    'redirect_url' => Link::get_my_account_page_link('listings'),
                    'message'      => esc_html__("Your promotion Successful applied.", "classified-listing-store")
                ]));
            }
        }
    }


    function rtcl_send_mail_to_store_owner() {
        $error = true;
        $msg = null;
        if (Functions::verify_nonce()) {
            if (Functions::is_human('store_contact')) {
                $store_id = isset($_POST['store_id']) ? absint($_POST['store_id']) : 0;
                $name = isset($_POST['name']) ? esc_html($_POST['name']) : '';
                $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
                $phone = isset($_POST['phone']) ? esc_html($_POST['phone']) : '';
                $message = isset($_POST['message']) ? sanitize_textarea_field($_POST['message']) : '';
                $store = new Store($store_id);
                if (is_a($store, Store::class) && $store->get_post_type() == rtclStore()->post_type) {
                    if ($name && $email && $message) {
                        $data = [
                            'name'    => $name,
                            'email'   => $email,
                            'phone'   => $phone,
                            'message' => $message,
                            'store'   => $store
                        ];
                        $msg = __("Error to sent mail!!!", "classified-listing-store");
                        if (rtcl()->mailer()->emails['Store_Contact_Email_To_Owner']->trigger($store_id, $data)) {
                            $error = false;
                            $msg = __("Your e-mail has been sent!", "classified-listing-store");
                        }
                    } else {
                        $msg = __("Please fill up all the required field.", "classified-listing-store");
                    }
                } else {
                    $msg = __("Store is not selected.", "classified-listing-store");
                }
            } else {
                $msg = __('Invalid Captcha: Please try again.', 'classified-listing-store');
            }
        } else {
            $msg = __("Your session have been expired.", "classified-listing-store");
        }
        wp_send_json(array(
            'error'   => $error,
            'message' => $msg,
        ));
    }

    function rtcl_ajax_store_logo_delete() {
        $error = true;
        $message = null;
        if ($store = RtclFunctions::get_current_user_store()) {
            $logo_id = absint(get_post_meta($store->ID, 'logo_id', true));
            if ($logo_id && wp_delete_attachment($logo_id)) {
                delete_post_meta($store->ID, 'logo_id');
                $error = false;
                $message = esc_html__("Successfully deleted", "classified-listing-store");
            } else {
                $message = __("File could not be deleted.", "classified-listing-store");
            }
        } else {
            $message = __("No store found to remove logo", "classified-listing-store");
        }

        wp_send_json(array(
            'error'   => $error,
            'message' => $message
        ));
    }

    function rtcl_ajax_store_banner_delete() {
        $error = false;
        $message = null;
        if ($store = RtclFunctions::get_current_user_store()) {
            $banner_id = absint(get_post_meta($store->ID, 'banner_id', true));
            if ($banner_id && wp_delete_attachment($banner_id)) {
                delete_post_meta($store->ID, 'banner_id');
                $error = false;
                $message = esc_html__("Successfully deleted", "classified-listing-store");
            } else {
                $message = __("File could not be deleted.", "classified-listing-store");
            }
        } else {
            $message = __("No store found to remove banner", "classified-listing-store");
        }

        wp_send_json(array(
            'error'   => $error,
            'message' => $message
        ));
    }

    function rtcl_ajax_store_banner_upload() {
        if (!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }
        $msg = $data = null;
        $error = true;
        if (isset($_FILES['banner'])) {
            Filters::beforeUpload();
            $status = wp_handle_upload($_FILES['banner'], array(
                'test_form' => false
            ));
            Filters::afterUpload();
            if ($status && !isset($status['error'])) {
                // $filename should be the path to a file in the upload directory.
                $filename = $status['file'];

                // The ID of the post this attachment is for.
                $store_id = 0;
                if ($store = RtclFunctions::get_current_user_store()) {
                    $store_id = $store->ID;
                }
                // Check the type of tile. We'll use this as the 'post_mime_type'.
                $filetype = wp_check_filetype(basename($filename), null);

                // Get the path to the upload directory.
                $wp_upload_dir = wp_upload_dir();

                // Prepare an array of post data for the attachment.
                $attachment = array(
                    'guid'           => $wp_upload_dir['url'] . '/' . basename($filename),
                    'post_mime_type' => $filetype['type'],
                    'post_title'     => preg_replace('/\.[^.]+$/', '', basename($filename)),
                    'post_content'   => '',
                    'post_status'    => 'inherit'
                );
                $store_owner_id = wp_get_current_user()->ID;
                // Create post if does not exist
                if ($store_id < 1) {

                    add_filter("post_type_link", "__return_empty_string");

                    $store_id = wp_insert_post(apply_filters("rtcl_insert_post", array(
                        'post_title'      => '',
                        'post_content'    => '',
                        'post_status'     => 'publish',
                        'post_author'     => 1,
                        'post_type'       => rtclStore()->post_type,
                        'comments_status' => 'closed',
                        'meta_input'      => array(
                            'store_owner_id' => $store_owner_id
                        )
                    )));

                    remove_filter("post_type_link", "__return_empty_string");
                }

                // Insert the attachment.
                $attach_id = wp_insert_attachment($attachment, $filename, $store_id);
                if (!is_wp_error($attach_id)) {
                    if ($existing_banner = get_post_meta($store_id, 'banner_id', true)) {
                        wp_delete_attachment($existing_banner);
                    }
                    update_post_meta($store_id, 'banner_id', $attach_id);
                    wp_update_attachment_metadata($attach_id, wp_generate_attachment_metadata($attach_id, $filename));
                    $src = wp_get_attachment_image_src($attach_id, 'rtcl-store-banner');
                    $data = array(
                        'banner_id' => $attach_id,
                        'src'       => $src[0]
                    );
                    $error = false;
                    $msg = esc_html__("Successfully updated.", "classified-listing-store");
                    do_action('rtcl_store_meta_data_saved', $store_owner_id, get_post($store_id), $_REQUEST);
                }
            } else {
                $msg = $status['error'];
            }
        } else {
            $msg = esc_html__("Banner image should be selected", "classified-listing-store");
        }


        wp_send_json(array(
            'message' => $msg,
            'error'   => $error,
            'data'    => $data
        ));

    }

    function rtcl_ajax_store_logo_upload() {
        if (!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }
        $msg = $data = null;
        $error = true;
        if (isset($_FILES['logo'])) {
            Filters::beforeUpload();
            $status = wp_handle_upload($_FILES['logo'], array(
                'test_form' => false
            ));
            Filters::afterUpload();
            if ($status && !isset($status['error'])) {
                // $filename should be the path to a file in the upload directory.
                $filename = $status['file'];

                // The ID of the post this attachment is for.
                $store_id = 0;
                if ($store = RtclFunctions::get_current_user_store()) {
                    $store_id = $store->ID;
                }
                // Check the type of tile. We'll use this as the 'post_mime_type'.
                $filetype = wp_check_filetype(basename($filename), null);

                // Get the path to the upload directory.
                $wp_upload_dir = wp_upload_dir();

                // Prepare an array of post data for the attachment.
                $attachment = array(
                    'guid'           => $wp_upload_dir['url'] . '/' . basename($filename),
                    'post_mime_type' => $filetype['type'],
                    'post_title'     => preg_replace('/\.[^.]+$/', '', basename($filename)),
                    'post_content'   => '',
                    'post_status'    => 'inherit'
                );

                $store_owner_id = wp_get_current_user()->ID;
                // Create post if does not exist
                if ($store_id < 1) {

                    add_filter("post_type_link", "__return_empty_string");

                    $store_id = wp_insert_post(apply_filters("rtcl_insert_post", array(
                        'post_title'      => '',
                        'post_content'    => '',
                        'post_status'     => 'publish',
                        'post_author'     => 1,
                        'post_type'       => rtclStore()->post_type,
                        'comments_status' => 'closed',
                        'meta_input'      => array(
                            'store_owner_id' => wp_get_current_user()->ID
                        )
                    )));

                    remove_filter("post_type_link", "__return_empty_string");
                }


                // Insert the attachment.
                $attach_id = wp_insert_attachment($attachment, $filename, $store_id);
                if (!is_wp_error($attach_id)) {
                    if ($existing_logo = get_post_meta($store_id, 'logo_id', true)) {
                        wp_delete_attachment($existing_logo);
                    }
                    update_post_meta($store_id, 'logo_id', $attach_id);
                    wp_update_attachment_metadata($attach_id, wp_generate_attachment_metadata($attach_id, $filename));
                    $src = wp_get_attachment_image_src($attach_id, 'rtcl-store-logo');
                    $data = array(
                        'logo_id' => $attach_id,
                        'src'     => $src[0]
                    );
                    $error = false;
                    $msg = esc_html__("Successfully updated.", "classified-listing-store");
                    do_action('rtcl_store_meta_data_saved', $store_owner_id, get_post($store_id), $_REQUEST);
                }
            } else {
                $msg = $status['error'];
            }
        } else {
            $msg = esc_html__("Banner image should be selected", "classified-listing-store");
        }


        wp_send_json(array(
            'message' => $msg,
            'error'   => $error,
            'data'    => $data
        ));

    }

    function rtcl_update_store_data() {
        $error = true;
        $data = [];
        $msg = $getStore = null;
        if (Functions::verify_nonce()) {
            parse_str($_REQUEST['data'], $data);
            $title = isset($data['name']) ? esc_html($data['name']) : null;
            $slug = isset($data['id']) ? esc_attr($data['id']) : null;
            $content = isset($data['details']) ? esc_textarea($data['details']) : " ";

            if ($title && ((isset($data['id']) && $slug) || (!isset($data['id']) && !$slug))) {
                $store_arg = array(
                    'post_title'   => $title,
                    'post_name'    => $slug,
                    'post_content' => $content
                );
                $store_id = null;
                $meta = array();
                if (isset($data['meta']) && !empty($data['meta'])) {
                    foreach ($data['meta'] as $mKey => $mValue) {
                        if ('address' == $mKey) {
                            $meta[$mKey] = esc_textarea($mValue);
                        } else if ('website' == $mKey) {
                            $meta[$mKey] = esc_url_raw($mValue);
                        } else if ('email' == $mKey) {
                            $meta[$mKey] = sanitize_email($mValue);
                        } else if ('social_media' == $mKey) {
                            $mValue = array_filter($mValue);
                            $meta[$mKey] = !empty($mValue) ? array_map('esc_url_raw', $mValue) : '';
                        } else if ('oh_type' == $mKey) {
                            $meta[$mKey] = in_array($mValue, array(
                                'selected',
                                'always'
                            )) ? esc_attr($mValue) : 'selected';
                        } else {
                            $meta[$mKey] = Functions::clean($mValue);
                        }
                    }
                }

                $meta = apply_filters('rtcl_store_mata_data_before_update', $meta, $data, $_REQUEST);
                $store_owner_id = get_current_user_id();
                if ($store = RtclFunctions::get_current_user_store()) {
                    $store_id = $store->ID;
                    $error = false;
                    $store_arg['ID'] = $store_id;
                    wp_update_post($store_arg);
                    if (!empty($meta)) {
                        foreach ($meta as $mKey => $mValue) {
                            update_post_meta($store_id, sanitize_key($mKey), $mValue);
                        }
                    }
                    $store_owner_id = absint(get_post_meta($store->ID, 'store_owner_id', true));
                    $msg = esc_html__("Your store is successfully updated.", 'classified-listing-store');
                    do_action('rtcl_store_meta_data_saved', $store_owner_id, $store, $_REQUEST);
                } else {
                    $meta['store_owner_id'] = get_current_user_id();
                    $store_arg['meta_input'] = $meta;
                    $store_arg['post_status'] = 'publish';
                    $store_arg['post_type'] = rtclStore()->post_type;
                    $store_arg['post_author'] = 1;
                    $store_id = wp_insert_post($store_arg);
                    if (!is_wp_error($store_id)) {
                        $error = false;
                        $msg = esc_html__("Your store is successfully updated.", 'classified-listing-store');
                        do_action('rtcl_store_meta_data_saved', $store_owner_id, get_post($store_id), $_REQUEST);
                    } else {
                        $store_id = null;
                        //there was an error in the post insertion,
                        $msg = $store_id->get_error_message();
                    }

                }

            } else {
                $msg = esc_html__("Please Select required field.", 'classified-listing-store');
            }

        } else {
            $msg = "error";
        }

        wp_send_json(array(
            'error'    => $error,
            'message'  => $msg,
            'response' => $getStore,
            'data'     => $data,
        ));
    }

}