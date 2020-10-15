<?php
/**
 * @wordpress-plugin
 * Plugin Name:       Classified Listing Store
 * Plugin URI:        https://radiustheme.com/demo/wordpress/classifiedpro
 * Description:       This is the AddOn plugin for classified listing pro. By using this Addon you can create store and able to create membership.
 * Version:           1.3.31
 * Author:            RadiusTheme
 * Author URI:        https://radiustheme.com
 * Text Domain:       classified-listing-store
 * Domain Path:       /languages
 */

if (!defined('ABSPATH')) {
    exit;
}

// Define RTCL_STORE_PLUGIN_FILE
if (!defined('RTCL_STORE_PLUGIN_FILE')) {
    define('RTCL_STORE_PLUGIN_FILE', __FILE__);
}


// Define RTCL_STORE_VERSION.
if (!defined('RTCL_STORE_VERSION')) {
    $plugin_data = get_file_data(__FILE__, array('version' => 'Version', 'author' => 'Author'), false);
    define('RTCL_STORE_VERSION', $plugin_data['version']);
}

if (class_exists('Rtcl') && !class_exists('RtclStore')) {
    require_once("app/RtclStore.php");
}
