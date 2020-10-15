<?php

namespace Rtcl\Controllers\Hooks;

use Rtcl\Gateways\WooPayment\WooPayment;
use Rtcl\Helpers\Functions;

class WooPaymentHooks
{
    static function init() {
        add_action('init', [__CLASS__, "wc_payment_support"]);
    }

    static function wc_payment_support() {
        if (Functions::is_woo_payment_enabled()) {
            new WooPayment();
        }
    }
}