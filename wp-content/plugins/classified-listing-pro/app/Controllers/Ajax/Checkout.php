<?php

namespace Rtcl\Controllers\Ajax;


use Rtcl\Helpers\Functions;
use Rtcl\Helpers\Link;
use Rtcl\Models\Payment;

/**
 * Class Checkout
 *
 * @package Rtcl\Controllers\Ajax
 */
class Checkout
{

    function __construct() {
        add_action('wp_ajax_rtcl_ajax_checkout_action', array($this, 'rtcl_ajax_checkout_action'));
    }

    function rtcl_ajax_checkout_action() {
        Functions::clear_notices();
        $success = false;
        $redirect_url = $gateway_id = null;

        if (isset($_POST['rtcl_checkout_nonce']) && wp_verify_nonce($_POST['rtcl_checkout_nonce'], 'rtcl_checkout')) {
            $pricing_id = isset($_REQUEST['pricing_id']) ? absint($_REQUEST['pricing_id']) : 0;
            $payment_method = isset($_REQUEST['payment_method']) ? sanitize_key($_POST['payment_method']) : '';
            $checkout_data = apply_filters('rtcl_checkout_process_data', wp_parse_args($_REQUEST, [
                'type'           => '',
                'listing_id'     => 0,
                'pricing_id'     => $pricing_id,
                'payment_method' => $payment_method
            ]));
            $pricing = rtcl()->factory->get_pricing($checkout_data['pricing_id']);
            $gateway = Functions::get_payment_gateway($checkout_data['payment_method']);
            // Use WP_Error to handle checkout errors.
            $errors = new \WP_Error();
            do_action('rtcl_checkout_data', $checkout_data, $pricing, $gateway, $_REQUEST, $errors);
            $errors = apply_filters('rtcl_checkout_validation_errors', $errors, $checkout_data, $pricing, $gateway, $_REQUEST);
            if (is_wp_error($errors) && $errors->has_errors()) {
                Functions::add_notice($errors->get_error_message(), 'error');
            } else {
                $new_payment_args = array(
                    'post_title'  => __('Order on', 'classified-listing') . ' ' . current_time("l jS F Y h:i:s A"),
                    'post_status' => 'rtcl-created',
                    'post_parent' => '0',
                    'ping_status' => 'closed',
                    'post_author' => 1,
                    'post_type'   => rtcl()->post_type_payment,
                    'meta_input'  => [
                        'customer_id'           => get_current_user_id(),
                        'customer_ip_address'   => Functions::get_ip_address(),
                        '_order_key'            => apply_filters('rtcl_generate_order_key', uniqid('rtcl_oder_')),
                        '_pricing_id'           => $pricing->getId(),
                        'amount'                => $pricing->getPrice(),
                        '_payment_method'       => $gateway->id,
                        '_payment_method_title' => $gateway->method_title,
                    ]
                );

                $payment_id = wp_insert_post(apply_filters('rtcl_checkout_process_new_payment_args', $new_payment_args, $pricing, $gateway, $checkout_data));

                if ($payment_id) {
                    $payment = rtcl()->factory->get_order($payment_id);
                    do_action('rtcl_checkout_process_new_payment_created', $payment_id);
                    // process payment
                    if ($payment->get_total() > 0) {

                        $result = $gateway->process_payment($payment->get_id());
                        $result = apply_filters('rtcl_checkout_process_payment_result', $result, $payment);
                        $redirect_url = isset($result['redirect']) ? $result['redirect'] : null;
                        // Redirect to success/confirmation/payment page
                        if (isset($result['result']) && 'success' === $result['result']) {
                            $success = true;
                            do_action('rtcl_checkout_process_success', $payment, $result);
                        } else {
                            wp_delete_post($payment->get_id(), true);
                            if (!empty($result['message'])) {
                                Functions::add_notice($result['message'], 'error');
                            }
                            do_action('rtcl_checkout_process_error', $payment, $result);
                        }

                    } else {
                        $success = true;
                        $gateway = Functions::get_payment_gateway('offline');
                        update_post_meta($payment->get_id(), '_payment_method', $gateway->id);
                        update_post_meta($payment->get_id(), '_payment_method_title', $gateway->method_title);
                        $payment->payment_complete(wp_generate_password(12, true));
                        $redirect_url = Link::get_payment_receipt_page_link($payment_id);
                        Functions::add_notice(__("Payment successfully made.", "classified-listing"), 'success');
                        do_action('rtcl_checkout_process_success_no_amount', $payment);
                    }
                } else {
                    Functions::add_notice(__("Error to create payment.", "classified-listing"), 'error');
                }
            }

        } else {
            Functions::add_notice(__("Session error", "classified-listing"), 'error');
        }

        $error_message = Functions::get_notices('error');
        $success_message = Functions::get_notices('success');
        if (!$success) {
            Functions::clear_notices();
        }
        wp_send_json(apply_filters('rtcl_checkout_process_ajax_response_args', array(
            'error_message'   => $error_message,
            'success_message' => $success_message,
            'success'         => $success,
            'redirect_url'    => $redirect_url,
            'gateway_id'      => $gateway_id
        )));

    }

}