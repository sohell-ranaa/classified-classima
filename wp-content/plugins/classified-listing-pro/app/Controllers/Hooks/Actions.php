<?php

namespace Rtcl\Controllers\Hooks;

use Rtcl\Controllers\ChatController;

class Actions
{

    public static function init() {
        Hooks::init();
        Comments::init();
        AppliedBothEndHooks::init();
        NotificationHook::init();
        WooPaymentHooks::init();
        ChatController::init();
    }

}