<?php

namespace Rtcl\Controllers\Admin;


use Rtcl\Helpers\Functions;
use Rtcl\Log\Logger;

class RestApi
{

    public function __construct() {
        add_action('rest_api_init', array($this, 'add_custom_users_api'));
    }

    function add_custom_users_api() {
        register_rest_route('rtcl/v1', '/receive-payment/', array(
            'methods'             => 'POST',
            'callback'            => array($this, 'payment_receipt_api_callback'),
            'permission_callback' => __return_true()
        ));

        register_rest_route('rtcl/v1', '/chat/conversations', array(
            'methods'             => 'GET',
            'callback'            => array($this, 'chat_conversation_api_callback'),
            'permission_callback' => __return_true()
        ));
    }

    function payment_receipt_api_callback($data) {
        $getData = wp_unslash($_GET);
        if (!empty($getData['id'])) {
            $gateway = Functions::get_payment_gateway($getData['id']);
            if ($gateway) {
                $gateway->check_callback_response();
            }
        }
    }

    function chat_conversation_api_callback($data) {

        echo json_encode($data);
//        $posted = wp_unslash($_POST);
    }

}