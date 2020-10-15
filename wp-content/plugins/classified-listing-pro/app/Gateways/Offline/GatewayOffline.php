<?php

namespace Rtcl\Gateways\Offline;

use Rtcl\Helpers\Functions;
use Rtcl\Helpers\Link;
use Rtcl\Models\Payment;
use Rtcl\Models\PaymentGateway;
use Rtcl\Models\RtclEmail;

class GatewayOffline extends PaymentGateway {


	/**
	 * Constructor for the gateway.
	 */
	public function __construct() {
		$this->id                 = 'offline';
		$this->option             = $this->option . $this->id;
		$this->order_button_text  = __( 'Offline Payout', 'classified-listing' );
		$this->method_title       = __( 'Offline', 'classified-listing' );
		$this->method_description = __( 'Note: There\'s nothing automatic in this offline payment system, you should use this when you don\'t want to collect money automatically. So once money is in your bank account you change the status of the order manually under "Payment History" menu.',
			'classified-listing' );
		// Load the settings.
		$this->init_form_fields();

		$this->init_settings();

		// Define user set variables.
		$this->enable      = $this->get_option( 'enable' );
		$this->title       = $this->get_option( 'title' );
		$this->description = $this->get_option( 'description' );
	}

	/**
	 * Initialise Gateway Settings Form Fields.
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'enabled'      => array(
				'title' => __( 'Enable/Disable', 'classified-listing' ),
				'type'  => 'checkbox',
				'label' => __( 'Enable Offline Payment', 'classified-listing' ),
			),
			'title'        => array(
				'title'       => __( 'Title', 'classified-listing' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'classified-listing' ),
				'default'     => __( 'Direct Bank Transfer', 'classified-listing' ),
			),
			'description'  => array(
				'title'       => __( 'Description', 'classified-listing' ),
				'type'        => 'textarea',
				'class'       => 'wide-input',
				'description' => __( 'This controls the description which the user sees during checkout.',
					'classified-listing' ),
				'default'     => __( "Make your payment directly in our bank account. Please use your Order ID as payment reference. Your order won't get approved until the funds have cleared in our account.",
					'classified-listing' ),
			),
			'instructions' => array(
				'title'             => __( 'Instructions', 'classified-listing' ),
				'type'              => 'wysiwyg',
				'custom_attributes' => array( 'rows' => 13 ),
				'default'           => __( 'Make your payment directly in our bank account. Please use your Order ID as payment reference. Your order won\'t get approved until the funds have cleared in our account.
Account details :
		
Account Name : YOUR ACCOUNT NAME
Account Number : YOUR ACCOUNT NUMBER
Bank Name : YOUR BANK NAME
		
If we don\'t receive your payment within 48 hrs, we will cancel the order.', 'classified-listing' ),
				'class'             => 'wide-input',
			)
		);
	}


	/**
	 * Process the payment and return the result.
	 *
	 * @param $payment_id
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function process_payment( $payment_id ) {
		$payment = new Payment( $payment_id );
		$payment->set_transaction_id( wp_generate_password( 12, true ) );
		$payment->update_status( "rtcl-pending" );

		return array(
			'result'   => 'success',
			'redirect' => Link::get_payment_receipt_page_link( $payment_id ),
		);

	}

}