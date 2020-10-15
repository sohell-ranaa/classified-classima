<?php

namespace RtclStore\Emails;

use Rtcl\Helpers\Functions;
use Rtcl\Models\RtclEmail;
use RtclStore\Models\Store;

class StoreContactEmailToOwner extends RtclEmail
{

    protected $data = array();

    function __construct() {
        $this->id = 'store_contact_to_owner';
        $this->template_html = 'emails/store-contact-email-to-owner';

        // Call parent constructor.
        parent::__construct();
    }


    /**
     * Get email subject.
     *
     * @return string
     */
    public function get_default_subject() {
        return __('[{site_title}] Contact via : Store', 'classified-listing-store');
    }

    /**
     * Get email heading.
     *
     * @return string
     */
    public function get_default_heading() {
        return __('Store contact mail', 'classified-listing-store');
    }

    /**
     * Trigger the sending of this email.
     *
     * @param          $store_id
     * @param array    $data
     *
     * @return bool
     * @throws \Exception
     */
    public function trigger($store_id, $data = array()) {
        $trigger = false;
        if (!$store_id || !isset($data['store']) || !is_a($data['store'], Store::class)) {
            return false;
        }
        /** @var Store $store */
        $store = $data['store'];
        $this->data = $data;
        $this->setup_locale();
        $this->object = $store;

        $this->set_recipient($store->get_email());

        if ($this->get_recipient()) {
            $trigger = $this->send();
        }

        $this->restore_locale();
        return $trigger;
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
                'store' => $this->object,
                'data'  => $this->data,
                'email' => $this,
            )
        );
    }

}