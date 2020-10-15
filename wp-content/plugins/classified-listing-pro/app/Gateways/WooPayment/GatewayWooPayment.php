<?php


namespace Rtcl\Gateways\WooPayment;


use Rtcl\Models\PaymentGateway;

class GatewayWooPayment extends PaymentGateway
{

    public $id = 'woo-payment';
    private $enable = false;

    function __construct() {

        $this->option = $this->option . $this->id;
        $this->order_button_text = __('WooCommerce Payout', 'classified-listing');
        $this->method_title = __('WooCommerce Payment', 'classified-listing');
        $this->method_description = __('Make a payment with WooCommerce payment methods.', 'classified-listing');
        // Load the settings.
        $this->init_form_fields();

        $this->init_settings();

        // Define user set variables.
        $this->enable = $this->get_option('enable');
        $this->title = $this->get_option('title');
        $this->description = $this->get_option('description');
    }

    public function init_form_fields() {
        $available_payment_html = '';
        $payment_gateways = WC()->payment_gateways()->payment_gateways();
        ob_start();
        if ($payment_gateways) {
            foreach ($payment_gateways as $payment_gateway) {
                $title = sprintf(
                    __('This payment is %s, please click the link beside to enable/disable.', 'classified-listing'),
                    $payment_gateway->enabled == 'yes' ? 'enabled' : 'disabled'
                );
                ?>
                <li>
                    <label>
                        <span title="<?php echo $title; ?>"
                              class="dashicons <?php echo $payment_gateway->enabled == 'yes' ? 'dashicons-yes' : 'dashicons-dismiss'; ?>"></span>
                        <a href="<?php echo admin_url('admin.php?page=wc-settings&tab=checkout&section=wc_gateway_' . $payment_gateway->id); ?>"
                           target="_blank"> <?php echo($payment_gateway->method_title); ?> </a>
                    </label>
                </li>
                <?php
            }
        }
        $available_payment_html .= ob_get_clean();
        $this->form_fields = [
            'enabled'                    => [
                'title'       => __('Enable', 'classified-listing'),
                'type'        => 'checkbox',
                'label'       => __('Enable WooCommerce Payment', 'classified-listing'),
                'description' => __('<span style="color: red">If <strong>WooCommerce Payment</strong> is enabled you can not use other payments provided by ClassifiedListing.</span>', 'classified-listing')
            ],
            'available_payments'         => [
                'title'       => __('WooCommerce Payments', 'classified-listing'),
                'type'        => 'html',
                'html'        => $available_payment_html ? sprintf('<ul class="rtcl-woo-payments">%s</ul>', $available_payment_html) : '',
                'description' => __('List of all available payment gateways installed and activated for WooCommerce. Click on a payment method to go to <strong>WooCommerce Payment</strong> settings.', 'classified-listing'),
            ],
//            'order_autocomplete_disable' => [
//                'label'       => __('Disable', 'classified-listing'),
//                'title'       => __('Order To Autocomplete', 'classified-listing'),
//                'type'        => 'checkbox',
//                'description' => __('Autocomplete WooCommerce Orders', 'classified-listing')
//            ],
        ];
    }

}