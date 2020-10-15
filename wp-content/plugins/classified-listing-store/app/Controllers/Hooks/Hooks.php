<?php

namespace RtclStore\Controllers\Hooks;

class Hooks
{

    public static function init() {
        Init::init();
        CustomHook::init();
        StoreReviews::init();
        GatewayHook::init();
        MembershipHook::init();
        StatusChange::init();
        TemplateRedirect::init();
        NotificationHook::init();
        StoreEmailHooks::init();
    }

}