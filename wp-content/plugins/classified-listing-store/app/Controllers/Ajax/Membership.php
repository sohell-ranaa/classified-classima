<?php

namespace RtclStore\Controllers\Ajax;

use Rtcl\Helpers\Functions;
use Rtcl\Helpers\Link;
use RtclStore\Helpers\Functions as StoreFunctions;
use RtclStore\Models\Membership as MMembership;

class Membership
{

    public static function init() {
        if (StoreFunctions::is_enable_free_ads()) {
            add_filter('rtcl_ajax_category_selection_before_post', array(
                __CLASS__,
                'is_valid_to_post_at_category'
            ));
        }

    }

    static function is_valid_to_post_at_category($response) {
        $cat_id = isset($response['cat_id']) ? absint($response['cat_id']) : 0;
        if ($cat_id) {
            $member = new MMembership(get_current_user_id());
            $cat = get_term_by('id', $cat_id, rtcl()->category);

            if ($member->has_membership()) {
                if (!$member->is_valid_to_post_at_category($cat_id)) {
                    if ($member->is_valid_for_free($cat_id)) {
                        if (!$member->is_valid_to_post_as_free() && Functions::get_option_item('rtcl_membership_settings', 'unlimited_free_ads_membership', false, 'checkbox')) {
                            $response['success'] = false;
                            $response['message'] = array_merge($response['message'], array(
                                apply_filters('rtcl_category_error_message', sprintf(
                                    __('You are not allow to post at %s category. <a href="%s">Update your subscription</a>.', "classified-listing-store"),
                                    $cat ? $cat->name : '--',
                                    Link::get_checkout_endpoint_url('membership')
                                ), $cat)
                            ));
                        }

                    } else {
                        $response['success'] = false;
                        $response['message'] = array_merge($response['message'], array(
                            apply_filters('rtcl_category_error_message', sprintf(
                                __('You are not allow to post at %s category. <a href="%s">Update your subscription</a>.', "classified-listing-store"),
                                $cat ? $cat->name : '--',
                                Link::get_checkout_endpoint_url('membership')
                            ), $cat)
                        ));
                    }

                }
            } else {
                if (!$member->is_valid_to_post_at_category_as_free($cat_id)) {
                    $response['success'] = false;
                    $response['message'] = array_merge($response['message'], array(
                        apply_filters('rtcl_category_error_message_free', sprintf(
                            __('You are not allow to post at %s category as free. <a href="%s">Buy a subscription</a>.', "classified-listing-store"),
                            $cat ? $cat->name : '--',
                            Link::get_checkout_endpoint_url('membership')
                        ), $cat)
                    ));
                }
            }

        }

        return $response;
    }

}