<?php


namespace Rtcl\Emails;


use Rtcl\Models\Listing;
use Rtcl\Models\Payment;
use Rtcl\Models\RtclEmail;
use Rtcl\Helpers\Functions;

class OrderCreatedEmailToAdmin extends RtclEmail
{

    protected $listing = null;

    function __construct() {

        $this->id = 'order_created_admin';
        $this->template_html = 'emails/order-created-email-to-admin';

        // Call parent constructor.
        parent::__construct();
    }


    /**
     * Get email subject.
     *
     * @return string
     */
    public function get_default_subject() {
        return __('[{site_title}]: New order #{order_number}', 'classified-listing');
    }

    /**
     * Get email heading.
     *
     * @return string
     */
    public function get_default_heading() {
        return __('New Order: #{order_number}', 'classified-listing');
    }

    /**
     * Trigger the sending of this email.
     *
     * @param               $order_id
     * @param Payment|false $order Payment
     *
     * @return void
     * @throws \Exception
     */
    public function trigger($order_id, $order = false) {

        $this->setup_locale();

        if ($order_id && !is_a($order, Payment::class)) {
            $order = rtcl()->factory->get_order($order_id);
        }

        if (is_a($order, Payment::class)) {
            $this->object = $order;
            $this->placeholders = wp_parse_args(array(
                '{order_number}' => $order->get_order_number()
            ), $this->placeholders);
            $this->set_recipient(Functions::get_admin_email_id_s());
        }

        if ($this->get_recipient()) {
            $this->send();
        }

        $this->restore_locale();

    }


    /**
     * Get content html.
     *
     * @access public
     * @return string
     */
    public function get_content_html() {
        return Functions::get_template_html(
            $this->template_html, array(
                'order'         => $this->object,
                'email'         => $this,
                'sent_to_admin' => true,
            )
        );
    }

}