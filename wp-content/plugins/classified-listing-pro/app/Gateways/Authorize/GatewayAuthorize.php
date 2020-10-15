<?php

namespace Rtcl\Gateways\Authorize;


use Rtcl\Helpers\Functions;
use Rtcl\Helpers\Link;
use Rtcl\Log\Logger;
use Rtcl\Models\Payment;
use Rtcl\Models\PaymentGateway;
use Rtcl\Gateways\Authorize\lib\AuthorizeNetAIM;

class GatewayAuthorize extends PaymentGateway {

    public function __construct()
    {
        $this->id = 'authorizenet';
        $this->option = $this->option . $this->id;
        $this->icon = plugins_url('images/authorizenet.png', __FILE__);
        $this->has_fields = true;
        $this->method_title = 'Authorize.Net Cards Settings';
        $this->init_form_fields();
        $this->init_settings();
        $this->supports = array('products', 'refunds');
        $this->authorizenet_description = $this->get_option('authorizenet_description');

        $this->title = $this->get_option('authorizenet_title');
        $this->authorizenet_apilogin = $this->get_option('authorizenet_apilogin'); // "43j733Z8wKz";//
        $this->authorizenet_transactionkey = $this->get_option('authorizenet_transactionkey'); // 5329wuCMF2FDY8ga
        $this->authorizenet_sandbox = $this->get_option('authorizenet_sandbox');
        $this->authorizenet_authorize_only = $this->get_option('authorizenet_authorize_only');
        $this->authorizenet_cardtypes = $this->get_option('authorizenet_cardtypes');
        $this->authorizenet_meta_cartspan = $this->get_option('authorizenet_meta_cartspan');

        if (!defined("AUTHORIZE_NET_SANDBOX")) {
            define("AUTHORIZE_NET_SANDBOX", ($this->authorizenet_sandbox == 'yes' ? true : false));
        }
        if (!defined("AUTHORIZENET_TRANSACTION_MODE")) {
            define("AUTHORIZENET_TRANSACTION_MODE",
                ($this->authorizenet_authorize_only == 'yes' ? true : false));
        }


        if ('yes' == AUTHORIZE_NET_SANDBOX) {
            if (!defined("AUTHORIZENET_API_LOGIN_ID")) {
                define("AUTHORIZENET_API_LOGIN_ID", $this->authorizenet_apilogin);
            }
            if (!defined("AUTHORIZENET_TRANSACTION_KEY")) {
                define("AUTHORIZENET_TRANSACTION_KEY", $this->authorizenet_transactionkey);
            }
            if (!defined("AUTHORIZENET_SANDBOX")) {
                define("AUTHORIZENET_SANDBOX", true);
            }

        } else {
            if (!defined("AUTHORIZENET_API_LOGIN_ID")) {
                define("AUTHORIZENET_API_LOGIN_ID", $this->authorizenet_apilogin);
            }
            if (!defined("AUTHORIZENET_TRANSACTION_KEY")) {
                define("AUTHORIZENET_TRANSACTION_KEY", $this->authorizenet_transactionkey);
            }
            if (!defined("AUTHORIZENET_SANDBOX")) {
                define("AUTHORIZENET_SANDBOX", false);
            }
        }
    }

    public function init_form_fields()
    {

        $this->form_fields = array(
            'enabled'                  => array(
                'title'   => __('Enable/Disable', 'classified-listing'),
                'type'    => 'checkbox',
                'label'   => __('Enable Authorize.Net', 'classified-listing'),
            ),
            'authorizenet_title'       => array(
                'title'       => __('Title', 'classified-listing'),
                'type'        => 'text',
                'description' => __('This controls the title which the buyer sees during checkout.',
                    'classified-listing'),
                'default'     => __('Authorize.Net', 'classified-listing'),
            ),
            'authorizenet_description' => array(
                'title'       => __('Description', 'classified-listing'),
                'type'        => 'textarea',
                'description' => __('This controls the description which the user sees during checkout.',
                    'classified-listing'),
                'default'     => __('All cards are charged by &copy;Authorize.Net &#174;&#8482; servers.',
                    'classified-listing'),
            ),

            'authorizenet_apilogin' => array(
                'title'       => __('API Login ID', 'classified-listing'),
                'type'        => 'text',
                'description' => __('This is the API Login ID Authorize.net.', 'classified-listing'),
                'default'     => '',
                'placeholder' => 'Authorize.Net API Login ID'
            ),

            'authorizenet_transactionkey' => array(
                'title'       => __('Transaction Key', 'classified-listing'),
                'type'        => 'text',
                'description' => __('This is the Transaction Key of Authorize.Net.', 'classified-listing'),
                'default'     => '',
                'placeholder' => 'Authorize.Net Transaction Key'
            ),

            'authorizenet_sandbox' => array(
                'title'       => __('Authorize.Net sandbox', 'classified-listing'),
                'type'        => 'checkbox',
                'label'       => __('Enable Authorize.Net sandbox (Live Mode if Unchecked)', 'classified-listing'),
                'description' => __('If checked its in sanbox mode and if unchecked its in live mode',
                    'classified-listing'),
                'default'     => 'no'
            ),

            'authorizenet_authorize_only' => array(
                'title'       => __('Authorize Only', 'classified-listing'),
                'type'        => 'checkbox',
                'label'       => __('Enable Authorize Only Mode (Authorize & Capture If Unchecked).<span style="color:red;">Make sure to keep <b>Unchecked</b> if your Address Verification Service (AVS) is set to hold transaction for review.</span>',
                    'classified-listing'),
                'description' => __('If checked will only authorize the credit card only upon checkout.',
                    'classified-listing'),
                'default'     => 'no',
            ),

            'authorizenet_meta_cartspan' => array(
                'title'       => __('Authorize.Net + Cartspan', 'classified-listing'),
                'type'        => 'checkbox',
                'label'       => __('Enable Authorize.Net Metas for Cartspan', 'classified-listing'),
                'description' => __('If checked will store last4 and card brand in local db from Transaction response', 'classified-listing'),
                'default'     => 'no',
            ),

            'authorizenet_cardtypes' => array(
                'title'    => __('Accepted Cards', 'classified-listing'),
                'type'     => 'multiselect',
                'class'    => 'rtcl-select2',
                'css'      => 'width: 350px;',
                'desc_tip' => __('Select the card types to accept.', 'classified-listing'),
                'options'  => array(
                    'mastercard' => 'MasterCard',
                    'visa'       => 'Visa',
                    'discover'   => 'Discover',
                    'amex'       => 'American Express',
                    'jcb'        => 'JCB',
                    'dinersclub' => 'Dinners Club',
                ),
                'default'  => array('mastercard', 'visa', 'discover', 'amex'),
            ),


        );
    }

    public function payment_fields()
    {
        $html = null;
        $html .= apply_filters('rtcl_authorizenet_description',
            wpautop(wp_kses_post(wptexturize(trim($this->authorizenet_description)))));
        $html .= $this->form();

        return $html;
    }

    public function field_name($name)
    {
        return $this->supports('tokenization') ? '' : ' name="' . esc_attr($this->id . '-' . $name) . '" ';
    }

    public function form()
    {
        $this->load_stripe_scripts();

        ob_start();
        ?>

        <fieldset id="wc-<?php echo esc_attr($this->id); ?>-cc-form" class='rtcl-credit-card-form rtcl-payment-form'>
            <?php do_action('rtcl_credit_card_form_start', $this->id); ?>
            <div class="form-group">
                <label for="<?php esc_attr($this->id) ?>-card-number"><?php _e('Card Number', 'classified-listing') ?>
                    <span class="required">*</span></label>
                <input id="<?php esc_attr($this->id) ?>-card-number"
                       class="input-text rtcl-credit-card-number form-control" type="text" maxlength="20"
                       autocomplete="off"
                       placeholder="&bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull;" <?php echo $this->field_name('card-number') ?> />
            </div>
            <div class="form-row">
                <div class="col form-group">
                    <label for="<?php esc_attr($this->id) ?>-card-expiry"><?php _e('Expiry (MM/YY)', 'classified-listing') ?>
                        <span class="required">*</span></label>
                    <input id="<?php esc_attr($this->id) ?>-card-expiry"
                           class="input-text rtcl-credit-card-expiry form-control" type="text" autocomplete="off"
                           placeholder="<?php esc_attr_e('MM / YY',
                               'classified-listing') ?>" <?php echo $this->field_name('card-expiry') ?> />
                </div>
                <div class="col form-group">
                    <label for="<?php esc_attr($this->id) ?>-card-cvc"><?php _e('Card Code', 'classified-listing') ?>
                        <span class="required">*</span></label>
                    <input id="<?php esc_attr($this->id) ?>-card-cvc"
                           class="input-text rtcl-credit-card-cvc form-control" type="text" autocomplete="off"
                           placeholder="<?php esc_attr_e('CVC',
                               'classified-listing') ?>" <?php echo $this->field_name('card-cvc') ?> />
                </div>
            </div>
            <?php do_action('rtcl_credit_card_form_end', $this->id); ?>
            <div class="clear"></div>
        </fieldset>
        <?php
        return ob_get_clean();
    }

    public function process_payment($order_id)
    {
        $message = null;
        $result = 'error';
        $log = new Logger();
        $rtcl_payment = new Payment($order_id);
        $redirect = null;
        $log->info("redirect url" . $redirect . " " . $rtcl_payment->get_listing_id());
        $cardtype = $this->get_card_type(sanitize_text_field(str_replace(' ', '',
            $_POST['authorizenet-card-number'])));

        if (!in_array($cardtype, $this->authorizenet_cardtypes)) {
            $log->info('Merchant do not support accepting in ' . $cardtype);
            $message = sprintf(__('Merchant do not support accepting in %s', 'classified-listing'), $cardtype);

            return array(
                'result'   => $result,
                'message'  => $message,
                'redirect' => $redirect,
            );
        }


        $card_num = sanitize_text_field(str_replace(' ', '', $_POST['authorizenet-card-number']));
        $exp_date = explode("/", sanitize_text_field($_POST['authorizenet-card-expiry']));
        $exp_month = str_replace(' ', '', !empty($exp_date[0]) ? $exp_date[0] : null);
        $exp_year = str_replace(' ', '', !empty($exp_date[1]) ? $exp_date[1] : null);

        if (strlen($exp_year) == 2) {
            $exp_year += 2000;
        }
        $cvc = sanitize_text_field($_POST['authorizenet-card-cvc']);


        $sale = new AuthorizeNetAIM;
        $sale->amount = $rtcl_payment->get_total();
        $sale->card_num = $card_num;
        $sale->exp_date = $exp_year . '/' . $exp_month;
        $sale->card_code = $cvc;
        $sale->addLineItem($rtcl_payment);


        if ('yes' == AUTHORIZENET_TRANSACTION_MODE) {
            $response = $sale->authorizeOnly();
        } else {
            $response = $sale->authorizeAndCapture();
        }


        if ($response) {

            if ((1 == $response->approved) || (1 == $response->held)) {

                $log->info($response->response_reason_text . ' on ' . date("d-M-Y h:i:s e") . ' with Transaction ID = ' . $response->transaction_id . ' using ' . strtoupper($response->transaction_type) . ' and authorization code ' . $response->authorization_code);
                $rtcl_payment->payment_complete(Functions::clean($response->transaction_id));
                $transactionmetas = array(
                    'approved'             => $response->approved,
                    'declined'             => $response->declined,
                    'error'                => $response->error,
                    'held'                 => $response->held,
                    'response_code'        => $response->response_code,
                    'response_subcode'     => $response->response_subcode,
                    'response_reason_code' => $response->response_reason_code,
                    'authorization_code'   => $response->authorization_code,
                    'card_type'            => $response->card_type,
                    'transaction_type'     => $response->transaction_type,
                    'account_number'       => $response->account_number,
                    'cavv_response'        => $response->cavv_response,
                    'card_code_response'   => $response->card_code_response
                );

                add_post_meta($order_id, '_' . $order_id . '_' . $response->transaction_id . '_metas',
                    $transactionmetas);

                if ('yes' == $this->authorizenet_meta_cartspan) {
                    $authorizenet_metas_for_cartspan = array(
                        'cc_type'     => $response->card_type,
                        'cc_last4'    => $response->account_number,
                        'cc_trans_id' => $response->transaction_id,
                    );
                    add_post_meta($order_id, '_authorizenet_metas_for_cartspan',
                        $authorizenet_metas_for_cartspan);
                }

                if (1 == $response->approved && "auth_capture" == $response->transaction_type) {
                    add_post_meta($order_id, '_authorizenet_charge_status', 'charge_auth_captured');
                }

                if (1 == $response->approved && "auth_only" == $response->transaction_type) {
                    add_post_meta($order_id, '_authorizenet_charge_status', 'charge_auth_only');
                }

                if (1 == $response->held) {
                    add_post_meta($order_id, '_authorizenet_charge_status', 'charge_auth_only');
                }
                $result = 'success';
                $redirect = $this->get_return_url($rtcl_payment);
            } else {
                $message = $response->response_reason_text;
                $log->info($response->response_reason_text . '---' . $response->error_message . ' on ' . date("d-M-Y h:i:s e") . ' using ' . strtoupper($response->transaction_type));
            }


        } else {
            $log->info($response->response_reason_text . '---' . $response->error_message . ' on ' . date("d-M-Y h:i:s e") . ' using ' . strtoupper($response->transaction_type));
            $message = $response->response_reason_text;
        }

        return array(
            'result'   => $result,
            'message'  => $message,
            'redirect' => $redirect,
        );
    } // end of function process_payment()

    /*Get Icon*/
    public function get_icon()
    {
        $icon = '';
        if (is_array($this->authorizenet_cardtypes)) {
            foreach ($this->authorizenet_cardtypes as $card_type) {

                if ($url = $this->get_payment_method_image_url($card_type)) {

                    $icon .= '<img width="45" src="' . esc_url($url) . '" alt="' . esc_attr(strtolower($card_type)) . '" />';
                }
            }
        } else {
            $icon .= '<img src="' . esc_url(plugins_url('images/authorizenet.png',
                    __FILE__)) . '" alt="Authorize.Net Payment Gateway" />';
        }

        return apply_filters('rtcl_authorizenet_icon', $icon, $this->id);
    }

    public function get_payment_method_image_url($type)
    {

        $image_type = strtolower($type);

        return plugins_url('images/' . $image_type . '.png', __FILE__);
    }

    /*Get Icon*/

    public function load_stripe_scripts()
    {
        wp_enqueue_script('rtcl-credit-card-form');
    }

    /*Get Card Types*/
    function get_card_type($number)
    {

        $number = preg_replace('/[^\d]/', '', $number);
        if (preg_match('/^3[47][0-9]{13}$/', $number)) {
            return 'amex';
        } elseif (preg_match('/^3(?:0[0-5]|[68][0-9])[0-9]{11}$/', $number)) {
            return 'dinersclub';
        } elseif (preg_match('/^6(?:011|5[0-9][0-9])[0-9]{12}$/', $number)) {
            return 'discover';
        } elseif (preg_match('/^(?:2131|1800|35\d{3})\d{11}$/', $number)) {
            return 'jcb';
        } elseif (preg_match('/^5[1-5][0-9]{14}$/', $number)) {
            return 'mastercard';
        } elseif (preg_match('/^4[0-9]{12}(?:[0-9]{3})?$/', $number)) {
            return 'visa';
        } else {
            return 'unknown card';
        }
    }// End of getcard type function


    // Function to check IP

    function get_client_ip()
    {
        $ipaddress = '';
        if (getenv('HTTP_CLIENT_IP')) {
            $ipaddress = getenv('HTTP_CLIENT_IP');
        } else if (getenv('HTTP_X_FORWARDED_FOR')) {
            $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        } else if (getenv('HTTP_X_FORWARDED')) {
            $ipaddress = getenv('HTTP_X_FORWARDED');
        } else if (getenv('HTTP_FORWARDED_FOR')) {
            $ipaddress = getenv('HTTP_FORWARDED_FOR');
        } else if (getenv('HTTP_FORWARDED')) {
            $ipaddress = getenv('HTTP_FORWARDED');
        } else if (getenv('REMOTE_ADDR')) {
            $ipaddress = getenv('REMOTE_ADDR');
        } else {
            $ipaddress = '0.0.0.0';
        }

        return $ipaddress;
    }

    // End function to check IP

}