<?php
/**
 * @wordpress-plugin
 * Plugin Name:       Classified Listing Pro
 * Plugin URI:        https://radiustheme.com/demo/wordpress/classifiedpro
 * Description:       Classified Listing is a fully responsive WordPress plugin by using this plugin you can create a classified listing website easily.
 * Version:           1.5.68
 * Author:            RadiusTheme
 * Author URI:        https://radiustheme.com
 * Text Domain:       classified-listing
 * Domain Path:       /languages
 */

if (!defined('ABSPATH')) {
    exit;
}

// Define RTCL_PLUGIN_FILE.
if (!defined('RTCL_PLUGIN_FILE')) {
    define('RTCL_PLUGIN_FILE', __FILE__);
}

// Define RTCL_VERSION.
if (!defined('RTCL_VERSION')) {
    $plugin_data = get_file_data(__FILE__, array('version' => 'Version', 'author' => 'Author'), false);
    define('RTCL_VERSION', $plugin_data['version']);
}

if (!class_exists('Rtcl')) {
    require_once("app/Rtcl.php");
}