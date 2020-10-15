<?php


namespace Rtcl\Models;


use Rtcl\Emails\ListingUpdateEmailToAdmin;
use Rtcl\Emails\OrderCompletedEmailToAdmin;
use Rtcl\Emails\UnreadMessageEmail;
use Rtcl\Emails\UserNewRegistrationEmailToAdmin;
use Rtcl\Emails\UserNewRegistrationEmailToUser;
use Rtcl\Emails\UserResetPasswordEmailToUser;
use Rtcl\Emails\UserVerifyLinkEmailToUser;
use Rtcl\Helpers\Functions;
use Rtcl\Traits\SingletonTrait;
use Rtcl\Emails\ReportAbuseEmailToAdmin;
use Rtcl\Emails\OrderCreatedEmailToAdmin;
use Rtcl\Emails\ListingContactEmailToAdmin;
use Rtcl\Emails\ListingContactEmailToOwner;
use Rtcl\Emails\ListingExpiredEmailToAdmin;
use Rtcl\Emails\ListingExpiredEmailToOwner;
use Rtcl\Emails\ListingRenewalEmailToOwner;
use Rtcl\Emails\ListingSubmittedEmailToAdmin;
use Rtcl\Emails\ListingSubmittedEmailToOwner;
use Rtcl\Emails\OrderCompletedEmailToCustomer;
use Rtcl\Emails\OrderCreatedEmailToCustomer;
use Rtcl\Emails\ListingPublishedEmailToOwner;
use Rtcl\Emails\ListingModerationEmailToOwner;
use Rtcl\Emails\ListingRenewalReminderEmailToOwner;

class RtclEmails
{

    use SingletonTrait;


    /**
     * Cloning is forbidden.
     *
     * @since 1.0
     */
    public function __clone() {
        Functions::doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?', 'classified-listing'), '1.0');
    }

    /**
     * Universalizing instances of this class is forbidden.
     *
     * @since 1.0
     */
    public function __wakeup() {
        Functions::doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?', 'classified-listing'), '1.0');
    }

    /**
     * Array of email notification classes
     *
     * @var RtclEmail[]
     */
    public $emails = array();


    /**
     * Constructor for the email class hooks in all emails that can be sent.
     */
    public function __construct() {

        $this->init();

        // Email Header, Footer and content hooks.
        add_action('rtcl_email_header', array($this, 'email_header'));
        add_action('rtcl_email_footer', array($this, 'email_footer'));

        // Email Order details
        add_action('rtcl_email_order_details', array($this, 'order_details'), 10, 3);

        // Email Order user details
        add_action('rtcl_email_order_customer_details', array($this, 'order_customer_details'), 10, 3);

        // Let 3rd parties unhook the above via this hook.
        do_action('rtcl_email', $this);
    }

    /**
     * Init email classes.
     */
    public function init() {

        $this->emails['Listing_Submitted_Email_To_Owner'] = new ListingSubmittedEmailToOwner();
        $this->emails['Listing_Published_Email_To_Owner'] = new ListingPublishedEmailToOwner();
        $this->emails['Listing_Update_Email_To_Admin'] = new ListingUpdateEmailToAdmin();
        $this->emails['Listing_Contact_Email_To_Owner'] = new ListingContactEmailToOwner();
        $this->emails['Listing_Contact_Email_To_Admin'] = new ListingContactEmailToAdmin();
        $this->emails['Report_Abuse_Email_To_Admin'] = new ReportAbuseEmailToAdmin();
        $this->emails['Listing_Submitted_Email_To_Admin'] = new ListingSubmittedEmailToAdmin();
        $this->emails['Listing_Moderation_Email_To_Owner'] = new ListingModerationEmailToOwner();
        $this->emails['Listing_Renewal_Email_To_Owner'] = new ListingRenewalEmailToOwner();
        $this->emails['Listing_Expired_Email_To_Owner'] = new ListingExpiredEmailToOwner();
        $this->emails['Listing_Expired_Email_To_Admin'] = new ListingExpiredEmailToAdmin();
        $this->emails['Listing_Renewal_Reminder_Email_To_Owner'] = new ListingRenewalReminderEmailToOwner();
        $this->emails['Order_Created_Email_To_Customer'] = new OrderCreatedEmailToCustomer();
        $this->emails['Order_Created_Email_To_Admin'] = new OrderCreatedEmailToAdmin();
        $this->emails['Order_Completed_Email_To_Customer'] = new OrderCompletedEmailToCustomer();
        $this->emails['Order_Completed_Email_To_Admin'] = new OrderCompletedEmailToAdmin();
        $this->emails['User_New_Registration_Email_To_Admin'] = new UserNewRegistrationEmailToAdmin();
        $this->emails['User_New_Registration_Email_To_User'] = new UserNewRegistrationEmailToUser();
        $this->emails['User_Reset_Password_Email_To_User'] = new UserResetPasswordEmailToUser();
        $this->emails['User_Verify_Link_Email_To_User'] = new UserVerifyLinkEmailToUser();
        $this->emails['Unread_Message_Email'] = new UnreadMessageEmail();

        $this->emails = apply_filters('rtcl_email_services', $this->emails);
    }


    /**
     * Get the email header.
     *
     * @param $email RtclEmail
     */
    public function email_header($email) {
        Functions::get_template('emails/email-header', array('email' => $email));
    }

    /**
     * Get the email footer.
     *
     * @param $email RtclEmail
     */
    public function email_footer($email) {
        Functions::get_template('emails/email-footer', array('email' => $email));
    }


    /**
     * Wraps a message in the classified-listing mail template.
     *
     * @param string $message Email message.
     * @param string $email   RtclEmail
     *
     * @return string
     */
    public function wrap_message($message, $email = RtclEmail::class) {
        // Buffer.
        ob_start();

        do_action('rtcl_email_header', $email);

        echo wpautop(wptexturize($message)); // WPCS: XSS ok.

        do_action('rtcl_email_footer', $email);

        // Get contents.
        $message = ob_get_clean();

        return $message;
    }

    /**
     * @param      $order Payment
     * @param bool $sent_to_admin
     * @param null $email
     */
    public function order_details($order, $sent_to_admin = false, $email = null) {

        $item_details_fields = apply_filters('rtcl_email_order_item_details_fields', array(
            'item_title'           => array(
                'type'  => 'title',
                'label' => esc_html(apply_filters('rtcl_email_order_item_details_title', get_the_title($order->get_listing_id()), $order))
            ),
            'payment_option_title' => array(
                'label' => esc_html__('Payment Option ', 'classified-listing'),
                'value' => esc_html($order->pricing->getTitle())
            ),
            'features'             => array(
                'label' => esc_html__('Features ', 'classified-listing'),
                'value' => sprintf('<strong>%d %s</strong> %s %s',
                    absint($order->pricing->getVisible()),
                    esc_html__('Days', 'classified-listing'),
                    $order->pricing->getFeatured() ? '<strong>' . esc_html__('Featured', 'classified-listing') . '</strong>' : null,
                    $order->pricing->getTop() ? '<strong>' . esc_html__('Top', 'classified-listing') . '</strong>' : null
                )
            )

        ), $order, $sent_to_admin, $email);

        Functions::get_template(
            'emails/email-order-details',
            array(
                'order'               => $order,
                'sent_to_admin'       => $sent_to_admin,
                'email'               => $email,
                'item_details_fields' => $item_details_fields,
            )
        );
    }

    public function order_customer_details($order, $sent_to_admin = false, $email = null) {
        if (!is_a($order, Payment::class)) {
            return;
        }

        $fields = array_filter(apply_filters('rtcl_email_order_customer_details_fields', array(), $sent_to_admin, $order));

        if (!empty($fields)) {
            Functions::get_template('emails/email-order-customer-details', array('fields' => $fields));
        }
    }


    /**
     * Send the email.
     *
     * @param mixed  $to          Receiver.
     * @param mixed  $subject     Email subject.
     * @param mixed  $message     Message.
     * @param string $headers     Email headers (default: "Content-Type: text/html\r\n").
     * @param string $attachments Attachments (default: "").
     *
     * @return bool
     * @throws \Exception
     */
    public function send($to, $subject, $message, $headers = "Content-Type: text/html\r\n", $attachments = '') {
        // Send.
        $email = new RtclEmail();

        $email
            ->set_headers($headers)
            ->set_recipient($to)
            ->set_subject($subject)
            ->set_content($this->wrap_message($message, $email))
            ->set_attachments($attachments);

        return $email->send();
    }


}