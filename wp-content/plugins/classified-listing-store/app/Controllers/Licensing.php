<?php

namespace RtclStore\Controllers;

use Rtcl\Helpers\Functions;
use Rtcl\Models\RtclLicense;

class Licensing
{

    // Licensing variable
    static private $store_url = 'https://www.radiustheme.com';
    static private $product_id = 86410;

    static function init() {
        add_action('admin_init', [__CLASS__, 'license']);
        add_action('rtcl_admin_settings_saved', [__CLASS__, 'update_licencing_status']);
        add_action('wp_ajax_rtcl_store_manage_licensing', [__CLASS__, 'manage_licensing']);
        add_filter('rtcl_tools_settings_options', [__CLASS__, 'add_tools_store_licensing_options']);
    }

    public static function license() {
        if (Functions::check_license()) {
            $settings = Functions::get_option('rtcl_tools_settings');
            $license_key = !empty($settings['license_store_key']) ? trim($settings['license_store_key']) : null;
            $status = !empty($settings['license_store_status']) && $settings['license_store_status'] === 'valid';
            new RtclLicense(static::$store_url, RTCL_STORE_PLUGIN_FILE, array(
                'version' => RTCL_STORE_VERSION,        // current version number
                'license' => $license_key,    // license key (used get_option above to retrieve from DB)
                'item_id' => self::$product_id,    // id of this plugin
                'author'  => RTCL_AUTHOR,    // author of this plugin
                'url'     => home_url(),
                'beta'    => false,
                'status'  => $status
            ));
        }
    }

    public static function update_licencing_status($action) {
        if ("tools_settings" == $action) {
            $settings = Functions::get_option('rtcl_tools_settings');
            $license_key = !empty($settings['license_store_key']) ? trim($settings['license_store_key']) : null;
            $status = !empty($settings['license_store_status']) && $settings['license_store_status'] === 'valid';
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
                    $err = $response->get_error_message();
                    $message = (is_wp_error($response) && !empty($err)) ? $err : __('An error occurred, please try again.', 'classified-listing-store');
                } else {
                    $license_data = json_decode(wp_remote_retrieve_body($response));
                    if (false === $license_data->success) {
                        switch ($license_data->error) {
                            case 'expired' :
                                $message = sprintf(
                                    __('Your license key expired on %s.', 'classified-listing-store'),
                                    date_i18n(get_option('date_format'),
                                        strtotime($license_data->expires, current_time('timestamp')))
                                );
                                break;
                            case 'revoked' :
                                $message = __('Your license key has been disabled.', 'classified-listing-store');
                                break;
                            case 'missing' :
                                $message = __('Invalid license.', 'classified-listing-store');
                                break;
                            case 'invalid' :
                            case 'site_inactive' :
                                $message = __('Your license is not active for this URL.', 'classified-listing-store');
                                break;
                            case 'item_name_mismatch' :
                                $message = __('This appears to be an invalid license key for Classified Listing Pro.', 'classified-listing-store');
                                break;
                            case 'no_activations_left':
                                $message = __('Your license key has reached its activation limit.', 'classified-listing-store');
                                break;
                            default :
                                $message = __('An error occurred, please try again.', 'classified-listing-store');
                                break;
                        }
                    }
                    // Check if anything passed on a message constituting a failure
                    if (empty($message) && $license_data->license === 'valid') {
                        $settings['license_store_status'] = $license_data->license;
                        update_option('rtcl_tools_settings', $settings);
                        Functions::add_notice(__('Store licence successfully activated', 'classified-listing-store'), 'success');
                    } else {
                        Functions::add_notice($message ? $message : __('Error to activation store license', 'classified-listing-store'), 'error');
                    }
                }

            } else if (!$license_key && !$status) {
                unset($settings['license_store_status']);
                update_option('rtcl_tools_settings', $settings);
            }
        }
    }

    public static function manage_licensing() {
        $error = true;
        $type = $value = $data = $message = null;
        $settings = Functions::get_option('rtcl_tools_settings');
        $license_key = !empty($settings['license_store_key']) ? trim($settings['license_store_key']) : null;
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
                $err = $response->get_error_message();
                $message = (is_wp_error($response) && !empty($err)) ? $err : __('An error occurred, please try again.', 'classified-listing-store');
            } else {
                $license_data = json_decode(wp_remote_retrieve_body($response));
                if (false === $license_data->success) {
                    switch ($license_data->error) {
                        case 'expired' :
                            $message = sprintf(
                                __('Your license key expired on %s.', 'classified-listing-store'),
                                date_i18n(get_option('date_format'),
                                    strtotime($license_data->expires, current_time('timestamp')))
                            );
                            break;
                        case 'revoked' :
                            $message = __('Your license key has been disabled.', 'classified-listing-store');
                            break;
                        case 'missing' :
                            $message = __('Invalid license.', 'classified-listing-store');
                            break;
                        case 'invalid' :
                        case 'site_inactive' :
                            $message = __('Your license is not active for this URL.', 'classified-listing-store');
                            break;
                        case 'item_name_mismatch' :
                            $message = __('This appears to be an invalid license key for Classified Listing Pro.', 'classified-listing-store');
                            break;
                        case 'no_activations_left':
                            $message = __('Your license key has reached its activation limit.', 'classified-listing-store');
                            break;
                        default :
                            $message = __('An error occurred, please try again.', 'classified-listing-store');
                            break;
                    }
                }
                // Check if anything passed on a message constituting a failure
                if (empty($message)) {
                    $settings['license_store_status'] = $license_data->license;
                    update_option('rtcl_tools_settings', $settings);
                    $error = false;
                    $type = 'license_deactivate';
                    $message = __("License successfully activated", 'classified-listing-store');
                    $value = __('Deactivate License', 'classified-listing-store');
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
            $response = wp_remote_post(self::$store_url, ['timeout' => 15, 'sslverify' => false, 'body' => $api_params]);

            // Make sure there are no errors
            if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {
                $err = $response->get_error_message();
                $message = (is_wp_error($response) && !empty($err)) ? $err : __('An error occurred, please try again.', 'classified-listing-store');
            } else {
                unset($settings['license_store_status']);
                update_option('rtcl_tools_settings', $settings);
                $error = false;
                $type = 'license_activate';
                $message = __("License successfully deactivated", 'classified-listing-store');
                $value = __('Activate License', 'classified-listing-store');
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

    public static function add_tools_store_licensing_options($options) {
        $position = array_search('license_key', array_keys($options));
        if ($position > -1) {
            $settings = Functions::get_option('rtcl_tools_settings');
            $status = !empty($settings['license_store_status']) && $settings['license_store_status'] === 'valid';
            $license_status = !empty($settings['license_store_key']) ? sprintf("<span class='license-status'>%s</span>",
                $status ? "<span data-action='rtcl_store_manage_licensing' class='button-secondary rt-licensing-btn danger license_deactivate'>" . __("Deactivate License", 'classified-listing-store') . "</span>"
                    : "<span data-action='rtcl_store_manage_licensing' class='button-secondary rt-licensing-btn button-primary license_activate'>" . __("Activate License", 'classified-listing-store') . "</span>"
            ) : ' ';
            $option = array(
                'license_store_key' => array(
                    'title'         => __('Store plugin license key', 'classified-listing-store'),
                    'type'          => 'text',
                    'wrapper_class' => 'rtcl-license-wrapper',
                    'description'   => $license_status
                )
            );
            Functions::array_insert($options, $position, $option);
        }

        return $options;
    }
}