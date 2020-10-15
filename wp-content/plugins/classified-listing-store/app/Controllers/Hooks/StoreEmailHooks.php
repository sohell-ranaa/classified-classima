<?php


namespace RtclStore\Controllers\Hooks;


use Rtcl\Helpers\Functions;
use Rtcl\Models\Payment;
use Rtcl\Models\RtclEmail;
use RtclStore\Emails\StoreContactEmailToOwner;
use RtclStore\Emails\StoreUpdateEmailToAdmin;

class StoreEmailHooks
{

    public static function init() {
        add_filter('rtcl_email_services', array(__CLASS__, 'add_store_email_services'), 10);
        add_filter('rtcl_email_order_item_details_fields', array(__CLASS__, 'rtcl_email_order_item_details_fields'), 10, 4);
    }

    static function add_store_email_services($services) {
        $services['Store_Update_Email_To_Admin'] = new StoreUpdateEmailToAdmin();
        $services['Store_Contact_Email_To_Owner'] = new StoreContactEmailToOwner();

        return $services;
    }

    /**
     * @param $fields
     * @param $order Payment
     * @param $sent_to_admin
     * @param $email RtclEmail
     *
     * @return array
     */
    static function rtcl_email_order_item_details_fields($fields, $order, $sent_to_admin, $email) {

        if ($order->is_membership()) {
            $visible = $order->pricing->getVisible();
            $regular_ads = $order->pricing->get_regular_ads();
            $description = $order->pricing->getDescription();
            $features = sprintf("<ul>%s%s%s</ul>",
                sprintf(_n('%s Day', '%s Days', absint($visible), 'classified-listing-store'), number_format_i18n(absint($visible))),
                sprintf(_n('%s Regular ad', '%s Regular ads', absint($regular_ads), 'classified-listing-store'), number_format_i18n(absint($regular_ads))),
                $description ? sprintf('<div id="feature-description">%s</div>', $description) : null
            );

            $fields['item_title']['label'] = apply_filters('rtcl_email_order_item_details_title', __("Membership Order", 'classified-listing-store'), $order);
            $fields['features']['value'] = $features;
        }

        return $fields;
    }
}