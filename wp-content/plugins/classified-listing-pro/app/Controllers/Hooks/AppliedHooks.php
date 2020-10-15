<?php

namespace Rtcl\Controllers\Hooks;


use Rtcl\Controllers\UserAuthentication;
use Rtcl\Helpers\Functions;

class AppliedHooks
{

    public static function init() {
        add_filter('rtcl_listing_get_the_price', [__CLASS__, 'price_for_job_type'], 10, 2);
        add_filter('rtcl_price_trim_zeros', [__CLASS__, 'remove_price_trim_zeros'], 10, 2);
        add_filter('rtcl_registration_need_auth_new_user', [__CLASS__, 'rtcl_registration_need_auth_new_user'], 100, 2);
    }

    /**
     * @param $auth
     * @param $user_id
     *
     * @return mixed
     */
    public static function rtcl_registration_need_auth_new_user($auth, $user_id) {
        if (Functions::get_option_item('rtcl_account_settings', 'user_verification', '', 'checkbox')) {
            $userAuth = new UserAuthentication();
            if ($userAuth->needs_validation($user_id)) {
                return true;
            }
        }
        return $auth;
    }


    /**
     * @param $trim    boolean
     * @param $payment boolean it payment type price || true , false
     *
     * @return mixed|string
     */
    public static function remove_price_trim_zeros($trim, $payment) {
        return $payment ? false : $trim;
    }

    /**
     * @param $html_price
     * @param $listing_id
     *
     * @return mixed|string
     */
    public static function price_for_job_type($html_price, $listing_id) {
        $ad_type = get_post_meta($listing_id, 'ad_type', true);
        if ($ad_type == 'job') {
            $html_price = '';
        }
        return $html_price;
    }


}