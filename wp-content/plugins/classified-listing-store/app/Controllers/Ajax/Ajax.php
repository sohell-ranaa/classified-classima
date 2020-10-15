<?php

namespace RtclStore\Controllers\Ajax;

class Ajax {

    public static function init()
    {
        new Admin();
        new FrontEnd();
        Membership::init();
        APIRequest::init();
        LoadMore::init();
    }

}