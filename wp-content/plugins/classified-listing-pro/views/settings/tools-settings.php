<?php

use Rtcl\Helpers\Functions;
use Rtcl\Helpers\Link;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Settings for Tools
 */
$settings = Functions::get_option('rtcl_tools_settings');
$status = !empty($settings['license_status']) && $settings['license_status'] === 'valid';
$license_status = !empty($settings['license_key']) ? sprintf("<span class='license-status'>%s</span>",
    $status ? "<span data-action='rtcl_manage_licensing' class='button-secondary rt-licensing-btn danger license_deactivate'>" . __("Deactivate License", "classified-listing") . "</span>"
        : "<span data-action='rtcl_manage_licensing' class='button-secondary rt-licensing-btn button-primary license_activate'>" . __("Activate License", "classified-listing") . "</span>"
) : ' ';

$options = array(
    'data_management_section' => array(
        'title'       => __('Data Management', 'classified-listing'),
        'type'        => 'title',
        'description' => sprintf(__('You can remove all classified listing cache from here. <a href="%s">Clear all cache</a>', 'classified-listing'), add_query_arg([
            rtcl()->nonceId    => wp_create_nonce(rtcl()->nonceText),
            'clear_rtcl_cache' => ''
        ], Link::get_current_url()))
    ),
    'delete_all_data'         => array(
        'title'       => __('Delete all data', 'classified-listing'),
        'type'        => 'checkbox',
        'description' => __('Allow to delete all all listing data during delete this plugin', 'classified-listing'),
    ),
);
if (Functions::check_license()) {
    $license = array(
        'licensing_section' => array(
            'title' => __('Licensing', 'classified-listing'),
            'type'  => 'title',
        ),
        'license_key'       => array(
            'title'         => __('Main plugin license key', 'classified-listing'),
            'type'          => 'text',
            'wrapper_class' => 'rtcl-license-wrapper',
            'description'   => $license_status
        )
    );

    $options = array_merge($license, $options);
}

return apply_filters('rtcl_tools_settings_options', $options);