<?php

namespace Rtcl\Gateways\Stripe;


use Rtcl\Helpers\Link;
use Rtcl\Log\Logger;
use Rtcl\Models\Payment;
use Rtcl\Helpers\Functions;
use Rtcl\Models\PaymentGateway;
use Rtcl\Gateways\Stripe\lib\Charge;
use Rtcl\Gateways\Stripe\lib\Stripe;
use Rtcl\Gateways\Stripe\lib\Customer;

class GatewayStripe extends PaymentGateway {

    public function __construct()
    {

        $this->id = 'stripe';
        $this->option = $this->option . $this->id;
        $this->icon = plugins_url('images/stripe.png', __FILE__);
        $this->has_fields = true;
        $this->method_title = __('Stripe Cards Settings', "classified-listing");
        $this->init_form_fields();
        $this->init_settings();

        $this->title = $this->get_option('stripe_title');
        $this->stripe_description = $this->get_option('stripe_description');

        $this->stripe_testpublickey = $this->get_option('stripe_testpublickey');
        $this->stripe_testsecretkey = $this->get_option('stripe_testsecretkey');
        $this->stripe_livepublickey = $this->get_option('stripe_livepublickey');
        $this->stripe_livesecretkey = $this->get_option('stripe_livesecretkey');
        $this->stripe_sandbox = $this->get_option('stripe_sandbox');
        $this->stripe_authorize_only = $this->get_option('stripe_authorize_only');
        $this->stripe_statementdescriptor = $this->get_option('stripe_statementdescriptor');
        $this->stripe_cardtypes = $this->get_option('stripe_cardtypes');
        //$this->stripe_createcustomer      = $this->get_option( 'stripe_createcustomer' );
        $this->stripe_meta_cartspan = $this->get_option('stripe_meta_cartspan');

        $this->stripe_receipt_email = $this->get_option('stripe_receipt_email');
        $this->stripe_saved_cards = $this->get_option('stripe_saved_cards');
        //$this->stripe_shipping_address = $this->get_option( 'stripe_shipping_address' );
        $this->stripe_zerocurrency = array(
            "BIF",
            "CLP",
            "DJF",
            "GNF",
            "JPY",
            "KMF",
            "KRW",
            "MGA",
            "PYG",
            "RWF",
            "VND",
            "VUV",
            "XAF",
            "XOF",
            "XPF"
        );

//		if ( ! defined( "STRIPE_CUSTOMER" ) ) {
//			define( "STRIPE_CUSTOMER", ( $this->stripe_createcustomer == 'yes' ? true : false ) );
//		}

        if (!defined("STRIPE_TRANSACTION_MODE")) {
            define("STRIPE_TRANSACTION_MODE", ($this->stripe_authorize_only == 'yes' ? false : true));
        }

        add_action('admin_notices', array($this, 'do_ssl_check'));
        if ('yes' == $this->stripe_sandbox) {
            Stripe::setApiKey($this->stripe_testsecretkey);
        } else {
            Stripe::setApiKey($this->stripe_livesecretkey);
        }

    }

    public function admin_options()
    {
        ?>
        <h3><?php _e('Stripe Credit cards payment gateway addon for Classified listing', 'classified-listing'); ?></h3>
        <p><?php _e('Stripe is a company that provides a way for individuals and businesses to accept payments over the Internet.', 'classified-listing'); ?></p>
        <table class="form-table">
            <?php $this->generate_settings_html(); ?>
            <script type="text/javascript">

                jQuery('#rtcl_stripe_stripe_statementdescriptor').on('keypress', function () {
                    if (jQuery('#rtcl_stripe_stripe_statementdescriptor').val().length > 22) {
                        alert('Statement Descriptor Accepts only 22 Characters.When you close this popup field will be emptied please make sure not to enter more than 22 Characters.');
                        jQuery('#rtcl_stripe_stripe_statementdescriptor').val('');
                    }
                })
                jQuery('#rtcl_stripe_stripe_sandbox').on('change', function () {
                    var sandbox = jQuery('#rtcl_stripe_stripe_testsecretkey, #rtcl_stripe_stripe_testpublickey').closest('tr'),
                        production = jQuery('#rtcl_stripe_stripe_livesecretkey, #rtcl_stripe_stripe_livepublickey').closest('tr');

                    if (jQuery(this).is(':checked')) {
                        sandbox.show();
                        production.hide();
                    } else {
                        sandbox.hide();
                        production.show();
                    }
                }).change();
            </script>
        </table>
        <?php
    }

    public function init_form_fields()
    {

        $this->form_fields = array(
            'enabled'      => array(
                'title' => __('Enable/Disable', 'classified-listing'),
                'type'  => 'checkbox',
                'label' => __('Enable Stripe', 'classified-listing'),
            ),
            'stripe_title' => array(
                'title'       => __('Title', 'classified-listing'),
                'type'        => 'text',
                'description' => __('This controls the title which the user sees during checkout.',
                    'classified-listing'),
                'default'     => __('Credit Card', 'classified-listing'),
            ),

            'stripe_description' => array(
                'title'       => __('Description', 'classified-listing'),
                'type'        => 'textarea',
                'description' => __('This controls the description which the user sees during checkout.',
                    'classified-listing'),
                'default'     => __('All cards are stored by &copy;Stripe servers we do not store any card details',
                    'classified-listing'),
            ),

            'stripe_testsecretkey' => array(
                'title'       => __('Test Secret Key', 'classified-listing'),
                'type'        => 'text',
                'description' => __('This is the Secret Key found in API Keys in Account Dashboard.',
                    'classified-listing'),
                'default'     => '',
                'placeholder' => 'Stripe Test Secret Key'
            ),

            'stripe_testpublickey' => array(
                'title'       => __('Test Publishable Key', 'classified-listing'),
                'type'        => 'text',
                'description' => __('This is the Publishable Key found in API Keys in Account Dashboard.',
                    'classified-listing'),
                'default'     => '',
                'placeholder' => 'Stripe Test Publishable Key'
            ),

            'stripe_livesecretkey' => array(
                'title'       => __('Live Secret Key', 'classified-listing'),
                'type'        => 'text',
                'description' => __('This is the Secret Key found in API Keys in Account Dashboard.',
                    'classified-listing'),
                'default'     => '',
                'placeholder' => 'Stripe Live Secret Key'
            ),

            'stripe_livepublickey' => array(
                'title'       => __('Live Publishable Key', 'classified-listing'),
                'type'        => 'text',
                'description' => __('This is the Publishable Key found in API Keys in Account Dashboard.',
                    'classified-listing'),
                'default'     => '',
                'placeholder' => 'Stripe Live Publishable Key'
            ),

            'stripe_sandbox' => array(
                'title'       => __('Stripe Sandbox', 'classified-listing'),
                'type'        => 'checkbox',
                'label'       => __('Enable stripe sandbox (Sandbox mode if checked)', 'classified-listing'),
                'description' => __('If checked its in sanbox mode and if unchecked its in live mode',
                    'classified-listing'),
                'default'     => 'no',
            ),

            'stripe_authorize_only' => array(
                'title'       => __('Authorize Only', 'classified-listing'),
                'type'        => 'checkbox',
                'label'       => __('Enable Authorize Only Mode (Authorize only mode if checked)',
                    'classified-listing'),
                'description' => __('If checked will only authorize the credit card only upon checkout.',
                    'classified-listing'),
                'default'     => 'no',
            ),

            'stripe_statementdescriptor' => array(
                'title'       => __('Statement Descriptor', 'classified-listing'),
                'type'        => 'text',
                'description' => __('Extra information about a charge. This will appear on your customer’s credit card statement.Maximum 22 Chars',
                    'classified-listing'),
                'default'     => __('Online Shopping', 'classified-listing'),

            ),

            'stripe_cardtypes' => array(
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
                    'dinersclub' => 'Diners Club',
                ),
                'default'  => array('mastercard', 'visa', 'discover', 'amex'),
            ),

            'stripe_meta_cartspan' => array(
                'title'       => __('Enable CartSpan', 'classified-listing'),
                'type'        => 'checkbox',
                'label'       => __('Enable <a href="http://www.cartspan.com/">CartSpan</a> to Stores Last4 & Brand of Card (Active If Checked)',
                    'classified-listing'),
                'description' => __('If checked will store last4 and card brand in local db from charge object.',
                    'classified-listing'),
                'default'     => 'no',
            ),


            'stripe_receipt_email' => array(
                'title'       => __('Enable stripe receipt email', 'classified-listing'),
                'type'        => 'checkbox',
                'label'       => __('Enable receipt email from Stripe (Active If Checked)', 'classified-listing'),
                'description' => __('If checked will send stripe receipt email to billing email in live mode only',
                    'classified-listing'),
                'default'     => 'no',
            )
        );

    }

    public function get_description()
    {
        return apply_filters('rtcl_gateway_description',
            wpautop(wptexturize(trim($this->stripe_description))), $this->id);
    }

    /*Is Avalaible*/
    public function is_available()
    {
        if (!in_array(Functions::get_currency(true), apply_filters('stripe_rtcl_supported_currencies',
            array(
                'AED',
                'ALL',
                'ANG',
                'ARS',
                'AUD',
                'AWG',
                'BBD',
                'BDT',
                'BIF',
                'BMD',
                'BND',
                'BOB',
                'BRL',
                'BSD',
                'BWP',
                'BZD',
                'CAD',
                'CHF',
                'CLP',
                'CNY',
                'COP',
                'CRC',
                'CVE',
                'CZK',
                'DJF',
                'DKK',
                'DOP',
                'DZD',
                'EGP',
                'ETB',
                'EUR',
                'FJD',
                'FKP',
                'GBP',
                'GIP',
                'GMD',
                'GNF',
                'GTQ',
                'GYD',
                'HKD',
                'HNL',
                'HRK',
                'HTG',
                'HUF',
                'IDR',
                'ILS',
                'INR',
                'ISK',
                'JMD',
                'JPY',
                'KES',
                'KHR',
                'KMF',
                'KRW',
                'KYD',
                'KZT',
                'LAK',
                'LBP',
                'LKR',
                'LRD',
                'MAD',
                'MDL',
                'MNT',
                'MOP',
                'MRO',
                'MUR',
                'MVR',
                'MWK',
                'MXN',
                'MYR',
                'NAD',
                'NGN',
                'NIO',
                'NOK',
                'NPR',
                'NZD',
                'PAB',
                'PKR',
                'PLN',
                'PYG',
                'QAR',
                'RUB',
                'SAR',
                'SBD',
                'SCR',
                'SEK',
                'SGD',
                'SHP',
                'SLL',
                'SOS',
                'STD',
                'SVC',
                'SZL',
                'THB',
                'TOP',
                'TTD',
                'TWD',
                'TZS',
                'UAH',
                'UGX',
                'USD',
                'UYU',
                'UZS',
                'VND',
                'VUV',
                'WST',
                'XAF',
                'XOF',
                'XPF',
                'YER',
                'ZAR',
                'AFN',
                'AMD',
                'AOA',
                'AZN',
                'BAM',
                'BGN',
                'CDF',
                'GEL',
                'KGS',
                'LSL',
                'MGA',
                'MKD',
                'MZN',
                'RON',
                'RSD',
                'RWF',
                'SRD',
                'TJS',
                'TRY',
                'XCD',
                'ZMW'
            )))) {
            return false;
        }


        if ('yes' == $this->stripe_sandbox && (empty($this->stripe_testpublickey) || empty($this->stripe_testsecretkey))) {
            return false;
        }

        if ('no' == $this->stripe_sandbox && (empty($this->stripe_livepublickey) || empty($this->stripe_livesecretkey))) {
            return false;
        }

        return true;
    }

    public function do_ssl_check()
    {
        $payment_options = Functions::get_option('rtcl_payment_settings');
        $use_https = !empty($payment_options['use_https']) ? $payment_options['use_https'] : 'no';
        if ('yes' != $this->stripe_sandbox && "no" == $use_https && "yes" == $this->enabled) {
            echo "<div class=\"error\"><p>" . sprintf(__("<strong>%s</strong> is enabled and WooCommerce is not forcing the SSL on your checkout page. Please ensure that you have a valid SSL certificate and that you are <a href=\"%s\">forcing the checkout pages to be secured.</a>", "classified-listing"),
                    $this->method_title,
                    admin_url('admin.php?page=rtcl-settings&tab=payment')) . "</p></div>";
        }
    }

    public function load_stripe_scripts()
    {

        wp_enqueue_script('stripe', 'https://js.stripe.com/v2/', false, '2.0', true);

        wp_enqueue_script('stripertcljs', plugins_url('assets/js/stripertcl.js', __FILE__),
            array('stripe', 'rtcl-credit-card-form'), '', true);
        if (is_user_logged_in()) {
            $user = wp_get_current_user();
            $billing_name = $user->user_firstname . ' ' . $user->user_lastname;
        } else {
            $billing_name = "Rtcl Listing";
        }
        $stripe_array = array(
            'stripe_publishablekey' => $this->stripe_sandbox == 'yes' ? $this->stripe_testpublickey : $this->stripe_livepublickey,
            'billing_name'          => $billing_name
        );


        wp_localize_script('stripertcljs', 'rtcl_stripe_array', $stripe_array);

    }

    //Function to check IP
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


    /*Get Icon*/
    public function get_icon()
    {
        $icon = '';
        if (is_array($this->stripe_cardtypes)) {
            foreach ($this->stripe_cardtypes as $card_type) {

                if ($url = $this->stripe_get_active_card_logo_url($card_type)) {

                    $icon .= '<img width="40" src="' . esc_url($url) . '" alt="' . esc_attr(strtolower($card_type)) . '" />';
                }
            }
        } else {
            $icon .= '<img src="' . esc_url(plugins_url('images/stripe.png',
                    __FILE__)) . '" alt="Stripe Gateway" />';
        }

        return apply_filters('rtcl_stripe_icon', $icon, $this->id);
    }

    public function stripe_get_active_card_logo_url($type)
    {

        $image_type = strtolower($type);

        return plugins_url('images/' . $image_type . '.png', __FILE__);
    }


    /*Start of credit card form */
    public function payment_fields()
    {
        $html = apply_filters('rtcl_stripe_description',
            wpautop(wp_kses_post(wptexturize(trim($this->stripe_description)))));
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
        <fieldset id="rtcl-<?php echo esc_attr($this->id); ?>-cc-form"
                  class='rtcl-credit-card-form rtcl-payment-form'>
            <?php do_action('rtcl_credit_card_form_start', $this->id); ?>
            <div class="form-group">
                <label for="<?php echo esc_attr($this->id) ?>-card-number"><?php _e('Card Number', 'classified-listing') ?>
                    <span class="required">*</span></label>
                <input id="<?php echo esc_attr($this->id) ?>-card-number"
                       class="input-text rtcl-credit-card-number form-control" type="text" maxlength="20"
                       autocomplete="off"
                       placeholder="&bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull;"
                    <?php echo $this->field_name('card-number') ?>
                       required/>
            </div>
            <div class="form-row">
                <div class="col form-group">
                    <label for="<?php echo esc_attr($this->id) ?>-card-expiry"><?php _e('Expiry (MM/YY)', 'classified-listing') ?>
                        <span class="required">*</span></label>
                    <input id="<?php echo esc_attr($this->id) ?>-card-expiry"
                           class="input-text rtcl-credit-card-expiry form-control" type="text" autocomplete="off"
                           placeholder="<?php echo esc_attr_e('MM / YY',
                               'classified-listing') ?>" <?php echo $this->field_name('card-expiry') ?> required/>
                </div>
                <div class="col form-group">
                    <label for="<?php echo esc_attr($this->id) ?>-card-cvc"><?php esc_html_e('Card Code', 'classified-listing') ?>
                        <span class="required">*</span></label>
                    <input id="<?php echo esc_attr($this->id) ?>-card-cvc"
                           class="input-text rtcl-credit-card-cvc form-control" type="text" autocomplete="off"
                           placeholder="<?php esc_attr_e('CVC',
                               'classified-listing') ?>" <?php echo $this->field_name('card-cvc') ?> required/>
                </div>
            </div>
            <?php do_action('rtcl_credit_card_form_end', $this->id); ?>
        </fieldset>
        <?php
        return ob_get_clean();
    }
    /*End of credit card form*/

    /*Process Payment*/
    public function process_payment($order_id)
    {

        $rtcl_order = new Payment($order_id);
        $result = 'error';
        $message = null;
        $redirect = null;

        // Create Token for Card or Customer
        $token_id = sanitize_text_field($_POST['stripe_token']);
        try {


            // create customer for each order
//			if ( true == STRIPE_CUSTOMER ) {
//				$cust        = Customer::create( array(
//					'source'      => $token_id,
//					'email'       => $rtcl_order->get_customer_email(),
//					'description' => $rtcl_order->get_id()
//				) );
//				$chargeparam = $this->charge_array( $rtcl_order, '', $cust->id );
//				$charge      = Charge::create( $chargeparam );
//			}//  create customer for each order
//			else {
            $chargeparam = $this->charge_array($rtcl_order, $token_id, '');
            $charge = Charge::create($chargeparam);
            //}


            if ('' != $token_id) {

                if ($charge->paid == true) {

                    $timestamp = date('Y-m-d H:i:s A e', $charge->created);

                    if ($charge->source->object == "card") {
                        $rtcl_order->add_note('Charge ' . $charge->status . ' at ' . $timestamp . ',Charge ID=' . $charge->id . ',Card=' . $charge->source->brand . ' : ' . $charge->source->last4 . ' : ' . $charge->source->exp_month . '/' . $charge->source->exp_year);
                    }

                    $rtcl_order->payment_complete($charge->id);

                    if ('yes' == $this->stripe_meta_cartspan) {
                        $stripe_metas_for_cartspan = array(
                            'cc_type'     => $charge->source->brand,
                            'cc_last4'    => $charge->source->last4,
                            'cc_trans_id' => $charge->id,
                        );
                        add_post_meta($order_id, '_stripe_metas_for_cartspan', $stripe_metas_for_cartspan);
                    }


                    if (true == $charge->captured && true == $charge->paid) {
                        add_post_meta($order_id, '_stripe_charge_status', 'charge_auth_captured');
                    }

                    if (false == $charge->captured && true == $charge->paid) {
                        add_post_meta($order_id, '_stripe_charge_status', 'charge_auth_only');
                    }

                    return array(
                        'result'   => 'success',
                        'redirect' => $this->get_return_url($rtcl_order),
                    );
                } else {
                    $message = sprintf("%s %s",esc_html__('Charge','classified-listing'), $charge->status);
                }

            } else {
                $message = esc_html__("Strip token not set", "classified-listing");
            }
        }//end ot try block
        catch (\Exception $e) {

            $body = $e->getJsonBody();
            $error = $body['error']['message'];
            $message = esc_html($error);
        }

        return array(
            'result'   => $result,
            'message'  => $message,
            'redirect' => $redirect,
        );

    } // end of function process_payment()

    /**
     * @param $rtcl_order
     * @param $token_id
     * @param $cust_id
     *
     * @return array
     */
    private function charge_array($rtcl_order, $token_id, $cust_id)
    {

        $chargearray = array(
            'amount'               => $this->stripe_order_total($rtcl_order),
            'currency'             => Functions::get_currency(true),
            'capture'              => STRIPE_TRANSACTION_MODE,
            'statement_descriptor' => $this->stripe_statementdescriptor,
            'metadata'             => array(
                'Order #'       => $rtcl_order->get_id(),
                'Customer IP'   => $this->get_client_ip(),
                'WP customer #' => $rtcl_order->get_customer_id(),
                'Billing Email' => $rtcl_order->get_customer_email(),
            ),
            'description'          => get_bloginfo('blogname') . ' Order #' . $rtcl_order->get_id(),
        );

        if ('yes' == $this->stripe_receipt_email) {
            $chargearray['receipt_email'] = $rtcl_order->get_customer_email();
        }


        if (!empty($cust_id) && empty($token_id)) {
            $chargearray['customer'] = $cust_id;
        } else {
            $chargearray['card'] = $token_id;
        }

//		echo '<pre>'; print_r($chargearray);die;
        return $chargearray;

    }

    private function stripe_order_total($rtcl_order)
    {
        $grand_total = $rtcl_order->get_total();
        $currency = Functions::get_currency();

        if (in_array($currency, $this->stripe_zerocurrency)) {
            $amount = number_format($grand_total, 0, ".", "");
        } else {
            $amount = $grand_total * 100;
        }

        return $amount;
    }

}