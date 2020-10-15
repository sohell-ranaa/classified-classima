<?php


namespace Rtcl\Helpers;


use Rtcl\Models\Roles;

class Upgrade
{

    static function init() {
        add_action('init', [__CLASS__, 'run_upgrade']);
    }

    public static function run_upgrade() {
        self::install_table_when_upgrade_to_1_5_0();
        self::upgrade_to_1_5_5();
        self::upgrade_to_1_5_59();
    }

    public static function upgrade_to_1_5_59() {
        $old_version = get_option('rtcl_pro_version');
        if ($old_version && version_compare($old_version, '1.5.59') < 0) {
            Roles::remove_default_caps();
            Roles::create_roles();
            update_option('rtcl_queue_flush_rewrite_rules', 'yes');
            self::update_rtcl_version('1.5.59');
        }
    }

    public static function upgrade_to_1_5_5() {
        $old_version = get_option('rtcl_pro_version');
        if ($old_version && version_compare($old_version, '1.5.5') < 0) {
            if ($listings_page_id = Functions::get_page_id('listings')) {
                $my_post = array(
                    'ID'           => $listings_page_id,
                    'post_content' => ''
                );
                wp_update_post($my_post);
            }
            update_option('rtcl_queue_flush_rewrite_rules', 'yes');
            self::update_rtcl_version('1.5.5');
        }
    }

    /**
     * This function will only run if the old version is less then 1.5.0
     */
    private static function install_table_when_upgrade_to_1_5_0() {

        $old_version = get_option('rtcl_pro_version');
        if ($old_version && version_compare($old_version, '1.5.0') < 0) {
            global $wpdb;

            $wpdb->hide_errors();

            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            $chat_schema = Install::get_chat_table_schema();
            dbDelta($chat_schema);
            self::update_rtcl_version('1.5.0');
        }
    }

    static function update_rtcl_version($version = '') {
        $version = $version ? $version : RTCL_VERSION;
        delete_option('rtcl_pro_version');
        add_option('rtcl_pro_version', $version);
    }
}