<?php

namespace Rtcl\Controllers\Admin;

use Rtcl\Helpers\Functions;
use Rtcl\Models\RtclLicense;

class Licensing
{

    // Licensing variable
    static private $store_url = 'https://www.radiustheme.com';
    static private $product_id = 81839;

    static function init() {
        add_action('admin_init', array(__CLASS__, 'license'));
        add_action('rtcl_admin_settings_saved', array(__CLASS__, 'update_licencing_status'));
        add_action('wp_ajax_rtcl_manage_licensing', array(__CLASS__, 'rtcl_manage_licensing'));
    }

    static function license() {
        if (Functions::check_license()) {
            $settings = Functions::get_option('rtcl_tools_settings');
            $license_key = !empty($settings['license_key']) ? trim($settings['license_key']) : null;
            $status = (!empty($settings['license_status']) && $settings['license_status'] === 'valid') ? true : false;
            new RtclLicense(static::$store_url, RTCL_PLUGIN_FILE, array(
                'version' => RTCL_VERSION,        // current version number
                'license' => $license_key,    // license key (used get_option above to retrieve from DB)
                'item_id' => self::$product_id,    // id of this plugin
                'author'  => RTCL_AUTHOR,    // author of this plugin
                'url'     => home_url(),
                'beta'    => false,
                'status'  => $status
            ));
        }
    }

    static function rtcl_manage_licensing() {
        $error = true;
        $type = $value = $data = $message = null;
        $settings = Functions::get_option('rtcl_tools_settings');
        $license_key = !empty($settings['license_key']) ? trim($settings['license_key']) : null;
        if (!empty($_REQUEST['type']) && $_REQUEST['type'] == "license_activate") {
            $api_params = array(
                'edd_action' => 'activate_license',
                'license'    => $license_key,
                'item_id'    => self::$product_id,
                'url'        => home_url()
            );
            $response = wp_remote_post(self::$store_url,
                array('timeout' => 15, 'sslverify' => false, 'body' => $api_params));
            if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {
                if (is_wp_error($response) && !empty($response->get_error_message())) {
                    $message = $response->get_error_message();
                } elseif (isset($response['response']) && is_array($response['response']) && isset($response['response']['message'])) {
                    $message = $response['response']['message'];
                } else {
                    $message = __('An error occurred, please try again.', 'classified-listing');
                }
            } else {
                $license_data = json_decode(wp_remote_retrieve_body($response));
                if (false === $license_data->success) {
                    switch ($license_data->error) {
                        case 'expired' :
                            $message = sprintf(
                                __('Your license key expired on %s.', 'classified-listing'),
                                date_i18n(get_option('date_format'),
                                    strtotime($license_data->expires, current_time('timestamp')))
                            );
                            break;
                        case 'revoked' :
                            $message = __('Your license key has been disabled.', 'classified-listing');
                            break;
                        case 'missing' :
                            $message = __('Invalid license.', 'classified-listing');
                            break;
                        case 'invalid' :
                        case 'site_inactive' :
                            $message = __('Your license is not active for this URL.', 'classified-listing');
                            break;
                        case 'item_name_mismatch' :
                            $message = __('This appears to be an invalid license key for Classified Listing Pro.', 'classified-listing');
                            break;
                        case 'no_activations_left':
                            $message = __('Your license key has reached its activation limit.', 'classified-listing');
                            break;
                        default :
                            $message = __('An error occurred, please try again.', 'classified-listing');
                            break;
                    }
                }
                // Check if anything passed on a message constituting a failure
                if (empty($message)) {
                    $settings['license_status'] = $license_data->license;
                    update_option('rtcl_tools_settings', $settings);
                    $error = false;
                    $type = 'license_deactivate';
                    $value = __('Deactivate License', "classified-listing");
                    $message = __("License successfully activated", 'classified-listing');
                }
            }
        }
        if (!empty($_REQUEST['type']) && $_REQUEST['type'] == "license_deactivate") {
            $api_params = array(
                'edd_action' => 'deactivate_license',
                'license'    => $license_key,
                'item_id'    => self::$product_id,
                'url'        => home_url()
            );
            $response = wp_remote_post(self::$store_url,
                array('timeout' => 15, 'sslverify' => false, 'body' => $api_params));

            // Make sure there are no errors
            if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {
                if (is_wp_error($response) && !empty($response->get_error_message())) {
                    $message = $response->get_error_message();
                } elseif (isset($response['response']) && is_array($response['response']) && isset($response['response']['message'])) {
                    $message = $response['response']['message'];
                } else {
                    $message = __('An error occurred, please try again.', 'classified-listing');
                }
            } else {
                unset($settings['license_status']);
                update_option('rtcl_tools_settings', $settings);
                $error = false;
                $type = 'license_activate';
                $value = __('Activate License', "classified-listing");
                $message = __("License successfully deactivated", 'classified-listing');
            }
        }
        $response = array(
            'error' => $error,
            'msg'   => $message,
            'type'  => $type,
            'value' => $value,
            'data'  => $data
        );
        wp_send_json($response);
    }

    static function update_licencing_status($action) {
        if ("tools_settings" == $action) {
            $settings = Functions::get_option('rtcl_tools_settings');
            $license_key = !empty($settings['license_key']) ? trim($settings['license_key']) : null;
            $status = (!empty($settings['license_status']) && $settings['license_status'] === 'valid') ? true : false;
            if ($license_key && !$status) {
                $api_params = array(
                    'edd_action' => 'activate_license',
                    'license'    => $license_key,
                    'item_id'    => self::$product_id,
                    'url'        => home_url()
                );
                $response = wp_remote_post(self::$store_url,
                    array('timeout' => 15, 'sslverify' => false, 'body' => $api_params));
                if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {
                    if (is_wp_error($response) && !empty($response->get_error_message())) {
                        $message = $response->get_error_message();
                    } elseif (isset($response['response']) && is_array($response['response']) && isset($response['response']['message'])) {
                        $message = $response['response']['message'];
                    } else {
                        $message = __('An error occurred, please try again.', 'classified-listing');
                    }
                    Functions::add_notice($message ? $message : __('Error to activation license', 'classified-listing'), 'error');
                } else {
                    $license_data = json_decode(wp_remote_retrieve_body($response));
                    if (false === $license_data->success) {
                        switch ($license_data->error) {
                            case 'expired' :
                                $message = sprintf(
                                    __('Your license key expired on %s.', 'classified-listing'),
                                    date_i18n(get_option('date_format'),
                                        strtotime($license_data->expires, current_time('timestamp')))
                                );
                                break;
                            case 'revoked' :
                                $message = __('Your license key has been disabled.', 'classified-listing');
                                break;
                            case 'missing' :
                                $message = __('Invalid license.', 'classified-listing');
                                break;
                            case 'invalid' :
                            case 'site_inactive' :
                                $message = __('Your license is not active for this URL.', 'classified-listing');
                                break;
                            case 'item_name_mismatch' :
                                $message = __('This appears to be an invalid license key for Classified Listing Pro.', 'classified-listing');
                                break;
                            case 'no_activations_left':
                                $message = __('Your license key has reached its activation limit.', 'classified-listing');
                                break;
                            default :
                                $message = __('An error occurred, please try again.', 'classified-listing');
                                break;
                        }
                    }
                    // Check if anything passed on a message constituting a failure
                    if (empty($message) && $license_data->license === 'valid') {
                        $settings['license_status'] = $license_data->license;
                        update_option('rtcl_tools_settings', $settings);
                        Functions::add_notice(__('Successfully activated', 'classified-listing'), 'success');
                    } else {
                        Functions::add_notice($message ? $message : __('Error to activation license', 'classified-listing'), 'error');
                    }
                }

            } else if (!$license_key && !$status) {
                unset($settings['license_status']);
                update_option('rtcl_tools_settings', $settings);
            }
        }
    }
}