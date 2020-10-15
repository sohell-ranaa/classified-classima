<?php

namespace Rtcl\Models;


use Rtcl\Helpers\Functions;
use Rtcl\Log\Logger;
use Rtcl\Resources\Options;

class Payment
{

    protected $id;
    protected $payment;
    protected $status;
    protected $date_created;

    /**
     * @var PaymentGateway
     */
    public $gateway;

    /**
     * Payment Pricing Option
     *
     * @var Pricing Object
     */
    public $pricing = null;

    protected $data = array(
        // Abstract order props.
        'parent_id'           => 0,
        'amount'              => 0,
        '_applied'            => null,

        // Order props.
        'customer_id'         => 0,
        'listing_id'          => 0,
        '_order_key'          => '',
        'payment_method'      => '',
        '_payment_method'     => '',
        'payment_type'        => '',
        'payment_option_id'   => 0,
        '_pricing_id'         => 0,
        'transaction_id'      => '',
        'customer_ip_address' => '',
        'created_via'         => '',
        'date_completed'      => null,
        'date_paid'           => null
    );

    function __construct($payment_id) {
        $post = get_post($payment_id);
        if (is_object($post) && $post->post_type == rtcl()->post_type_payment) {
            $this->setData($post);
        }
    }

    /**
     * Course is exists if the post is not empty
     *
     * @return bool
     */
    public function exists() {
        return rtcl()->post_type_payment === $this->payment->post_type;
    }


    private function setData($post) {
        $this->id = $post->ID;
        $this->payment = $post;
        $this->status = $post->post_status;
        $this->date_created = $post->date_created;
        $this->pricing = rtcl()->factory->get_pricing($this->get_pricing_id());
        $this->setGateWay();
    }

    private function setGateWay() {
        $this->gateway = Functions::get_payment_gateway($this->get_payment_method());
    }


    private function get_prop($prop) {

        if (array_key_exists($prop, $this->data)) {
            return get_post_meta($this->get_id(), $prop, true);
        }

        return null;
    }

    private function set_prop($prop, $value = null) {
        if (array_key_exists($prop, $this->data)) {
            return update_post_meta($this->id, $prop, $value);
        }

        return false;
    }

    public function is_applied() {
        return $this->get_prop('_applied') ? true : false;
    }

    /**
     * @return bool
     */
    public function is_membership() {
        return "membership" == get_post_meta($this->get_id(), 'payment_type', true);
    }

    public function set_applied() {
        $this->set_prop('_applied', 1);
    }

    public function get_customer_ip_address() {
        return $this->get_prop('customer_ip_address');
    }

    public function get_transaction_id() {
        return $this->get_prop('transaction_id');
    }

    /**
     * @return mixed
     */
    public function get_payment_method() {
        if ($this->get_prop('_payment_method')) {
            return $this->get_prop('_payment_method');
        }

        // @deprecated
        return $this->get_prop('payment_method');
    }

    public function get_payment_method_title() {

        $title = get_post_meta($this->get_id(), '_payment_method_title', true);
        $title = $title ? $title : ($this->gateway ? $this->gateway->method_title : '');

        return apply_filters('rtcl_display_payment_method_title', $title, $this);
    }

    /**
     * @return mixed
     */
    public function get_pricing_id() {
        if ($this->get_prop('_pricing_id')) {
            return $this->get_prop('_pricing_id');
        }

        // @deprecated
        return $this->get_prop('payment_option_id');
    }

    public function get_date_paid() {
        return $this->get_prop('date_paid');
    }

    public function set_date_paid($date) {
        $this->set_prop('date_paid', $date);
    }

    public function get_id() {
        return $this->id;
    }

    public function get_order_key() {
        return $this->get_prop('_order_key');
    }

    /**
     * Return Listing ID
     *
     * @return mixed|null
     */
    public function get_listing_id() {
        return $this->get_prop('listing_id');
    }

    public function get_customer_id() {
        return absint($this->get_prop('customer_id'));
    }

    public function get_edit_order_url() {
        return apply_filters('rtcl_get_order_edit_url', get_admin_url(null, 'post.php?post=' . $this->get_id() . '&action=edit'), $this);
    }

    /**
     * Gets the order number for display (by default, order ID).
     *
     * @return string
     */
    public function get_order_number() {
        return (string)apply_filters('rtcl_get_order_number', $this->get_id(), $this);
    }

    public function get_customer_email() {
        $user_id = $this->get_customer_id();
        $user_info = get_userdata($user_id);

        return $user_info->user_email;
    }


    public function get_customer_full_name() {
        $user_id = $this->get_customer_id();
        $user_info = get_userdata($user_id);

        /* translators: 1: first name 2: last name */

        return sprintf(_x('%1$s %2$s', 'full name', 'classified-listing'), $user_info->first_name, $user_info->last_name);
    }

    public function has_status($status) {
        return is_array($status) && in_array($this->get_status(), $status) || $this->get_status() === $status;
    }

    public function needs_payment() {
        $valid_payment_statuses = array('rtcl-pending', 'rtcl-failed');

        return $this->has_status($valid_payment_statuses) && $this->get_total() > 0;

    }


    public function get_total() {
        return $this->get_prop('amount');
    }

    public function get_listing_title() {
        return get_the_title($this->get_listing_id());
    }


    /**
     * @return mixed|void
     */
    public function get_status() {
        $status = $this->status;
        if (empty($this->status)) {
            $status = apply_filters('rtcl_default_order_status', 'rtcl-pending');
        }

        return $status;
    }


    /**
     * Before set It need to check a valid status
     *
     * @param $new_status
     *
     * @return array
     */
    public function set_status($new_status) {
        $old_status = $this->get_status();
        $new_status = 'rtcl-' === substr($new_status, 0, 5) ? $new_status : 'rtcl-' . $new_status;
        $status_list = array_keys(Options::get_payment_status_list());
        if (!in_array($new_status, $status_list)) {
            $new_status = 'rtcl-pending';
        }

        return array(
            'from' => $old_status,
            'to'   => $new_status
        );
    }

    /**
     * Updates status of order immediately. Order must exist.
     *
     * @param string $new_status Status to change the order to. No internal wc- prefix is required.
     * @param bool   $manual
     *
     * @return bool
     * @uses Payment::set_status()
     */
    public function update_status($new_status, $manual = false) {
        try {
            if (!$this->get_id()) {
                return false;
            }

            $this->status_transition($new_status);
        } catch (\Exception $e) {
            $logger = new Logger();
            $logger->error(sprintf('Update status of order #%d failed!', $this->get_id()), array(
                'order' => $this,
                'error' => $e,
            ));

            return false;
        }

        return true;
    }

    /**
     * Handle the status transition.
     *
     * @param $new_status
     */
    protected function status_transition($new_status) {

        $result = $this->set_status($new_status);
        if (is_array($result) && !empty($result['from']) && $result['to'] && ($result['from'] !== $result['to'])) {
            wp_update_post(array(
                'ID'                => $this->get_id(),
                'post_status'       => $result['to'],
                'post_modified'     => current_time('mysql'),
                'post_modified_gmt' => current_time('mysql', 1),
            ));
        }

    }

    public function payment_complete($transaction_id = '') {
        try {
            if (!$this->get_id()) {
                return false;
            }

            if ($this->has_status(array(
                'rtcl-created',
                'rtcl-on-hold',
                'rtcl-pending',
                'rtcl-failed',
                'rtcl-cancelled'
            ))) {
                if (!empty($transaction_id)) {
                    $this->set_transaction_id($transaction_id);
                }
                if (!$this->get_date_paid()) {
                    $this->set_date_paid(Functions::datetime('mysql'));
                }
                $this->update_status('rtcl-completed');
            }
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    public function set_transaction_id($transaction_id) {
        $this->set_prop('transaction_id', $transaction_id);
    }

    public function set_order_key() {
        $this->set_prop('_order_key', '' . apply_filters('rtcl_generate_order_key', uniqid('rtcl_order_')));
    }

    public function get_details() {
        ob_start();
        ?>
        <table border="0" cellspacing="0" cellpadding="7" style="border:1px solid #CCC;">
            <tr style="background-color:#F0F0F0;">
                <th colspan="2"><?php echo get_the_title($this->get_listing_id()); ?> (<span
                            class="listing-id"><?php _e("ID#", 'classified-listing');
                        echo absint($this->get_listing_id()) ?></span>)
                </th>
            </tr>
            <tr>
                <td style="border-right:1px solid #CCC; border-bottom:1px solid #CCC; text-align:right;"><?php _e('Pricing ',
                        'classified-listing'); ?></td>
                <td><?php echo $this->pricing->getTitle(); ?></td>
            </tr>
            <tr>
                <td style="border-right:1px solid #CCC; border-bottom:1px solid #CCC; text-align:right;"><?php _e('Visible ',
                        'classified-listing'); ?></td>
                <td><?php
                    printf('%d %s%s%s',
                        absint($this->pricing->getVisible()),
                        __('Days', 'classified-listing'),
                        $this->pricing->hasFeatured() ? ' <span class="badge badge-info">' . __('Featured',
                                'classified-listing') . '</span>' : null,
                        $this->pricing->hasTop() ? ' <span class="badge badge-warning">' . __('Top',
                                'classified-listing') . '</span>' : null
                    ); ?></td>
            </tr>
            <tr>
                <td style="border-right:1px solid #CCC; border-bottom:1px solid #CCC; text-align:right;"><?php _e('Amount ',
                        'classified-listing'); ?></td>
                <td><?php echo Functions::get_formatted_price($this->pricing->getPrice(), true); ?></td>
            </tr>
        </table>
        <?php
        return ob_get_clean();
    }

    function add_note($note, $is_customer_note = 0, $added_by_user = false) {
        if (!$this->get_id()) {
            return 0;
        }

        if (is_user_logged_in() && current_user_can('manage_rtcl_options', $this->get_id()) && $added_by_user) {
            $user = get_user_by('id', get_current_user_id());
            $comment_author = $user->display_name;
            $comment_author_email = $user->user_email;
        } else {
            $comment_author = __('RtclListing', 'classified-listing');
            $comment_author_email = strtolower(__('RtclListing', 'classified-listing')) . '@';
            $comment_author_email .= isset($_SERVER['HTTP_HOST']) ? str_replace('www.', '', sanitize_text_field(wp_unslash($_SERVER['HTTP_HOST']))) : 'noreply.com'; // WPCS: input var ok.
            $comment_author_email = sanitize_email($comment_author_email);
        }

        $commentdata = apply_filters('rtcl_new_order_note_data',
            array(
                'comment_post_ID'      => $this->get_id(),
                'comment_author'       => $comment_author,
                'comment_author_email' => $comment_author_email,
                'comment_author_url'   => '',
                'comment_content'      => $note,
                'comment_agent'        => 'RtclListing',
                'comment_type'         => 'rtcl_payment_note',
                'comment_parent'       => 0,
                'comment_approved'     => 1,
            ),
            array(
                'order_id'         => $this->get_id(),
                'is_customer_note' => $is_customer_note,
            )
        );

        $comment_id = wp_insert_comment($commentdata);

        if ($is_customer_note) {
            add_comment_meta($comment_id, 'is_customer_note', 1);

            do_action('rtcl_new_customer_note', array(
                'order_id'      => $this->get_id(),
                'customer_note' => $commentdata['comment_content'],
            ));
        }

        return $comment_id;
    }
}