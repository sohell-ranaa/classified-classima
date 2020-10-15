<?php

namespace Rtcl\Controllers\Hooks;

use Rtcl\Helpers\Functions;
use Rtcl\Models\Payment;

class NotificationHook
{
    public static function init() {
        add_action('rtcl_checkout_process_success', [__CLASS__, 'checkout_process_mail'], 20);
        add_action('rtcl_checkout_process_success_no_amount', [__CLASS__, 'checkout_process_mail'], 10);
    }


    /**
     * @param Payment $payment
     */
    static function checkout_process_mail($payment) {
        if ($payment && $payment->exists()) {
            if (Functions::get_option_item('rtcl_email_settings', 'notify_admin', 'order_created', 'multi_checkbox')) {
                rtcl()->mailer()->emails['Order_Created_Email_To_Admin']->trigger($payment->get_id(), $payment);
            }

            if (Functions::get_option_item('rtcl_email_settings', 'notify_users', 'order_created', 'multi_checkbox')) {
                rtcl()->mailer()->emails['Order_Created_Email_To_Customer']->trigger($payment->get_id(), $payment);
            }
        }
    }

}