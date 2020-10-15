<?php

namespace Rtcl\Controllers;


use WP_Error;
use WP_User;
use Rtcl\Helpers\Functions;

/**
 * Class UserAuthentication
 *
 * @package Rtcl\Controllers
 */
class UserAuthentication
{

    /**
     * @var String $secret
     */
    public $secret = "25#-asdv8+abox";
    private $version;

    function __construct() {
        $this->version = (defined('WP_DEBUG') && WP_DEBUG) ? time() : RTCL_VERSION;
        add_action('user_register', [$this, 'user_register'], 999);
        add_filter('authenticate', [$this, 'check_active_user'], 100, 2);
        add_action('wp_ajax_rtcl_resend_verify', [$this, 'rtcl_resend_verify_ajax_cb']);
        add_action('wp_ajax_nopriv_rtcl_resend_verify', [$this, 'rtcl_resend_verify_ajax_cb']);
    }

    /**
     * Resend verification link
     * Ajax callback
     */
    public function rtcl_resend_verify_ajax_cb() {
        if (!Functions::verify_nonce()) {
            wp_send_json_error([
                "message" => __("Session Error!!", "classified-listing")
            ]);
        }

        $user_id = (isset($_POST['user_id']) && !empty($_POST['user_id']) ? absint($_POST['user_id']) : null);

        if (!$user_id) {
            $user_login = (isset($_POST['user_login']) && !empty($_POST['user_login']) ? trim(esc_attr($_POST['user_login'])) : null);
            $user = get_user_by('login', $user_login) ?: get_user_by('email', $user_login);
            $user_id = $user ? (int)$user->ID : null;
        }

        if (!$user_id) {
            wp_send_json_error([
                'message' => __('Invalid request.', 'classified-listing')
            ]);
        }

        // Admin request
        if (current_user_can('edit_users')) {
            $error = false;
            $this->send_verification_link($user_id);
            $message = __('Verification link sent to user\'s email address', 'classified-listing');
        } elseif ($this->needs_validation($user_id)) {
            $attempts = (int)get_user_meta($user_id, 'rtcl_verify_link_attempts', true);
            // Avoid repetitively asking for re-send the verification link
            if ($attempts <= (int)Functions::get_option_item('rtcl_account_settings', 'verify_max_resend_allowed', null)) {
                $this->send_verification_link($user_id);
                update_user_meta($user_id, 'rtcl_verify_link_attempts', $attempts + 1);
                $error = false;
                $message = __('Verification link sent to your email address', 'classified-listing');
            } else {
                $error = true;
                $message = __('You have tried re-sending verification link too many times, please contact site administrators.', 'classified-listing');
            }

        } else {
            $error = true;
            $message = __('Your email address is already verified.', 'classified-listing');
        }
        wp_send_json([
            'success' => !$error,
            'data'    => ['message' => $message]
        ]);
    }

    /**
     * Prevents users from logging in, if they have not verified their email address
     *
     * @param WP_User $user
     * @param String  $username
     *
     * @return WP_Error|WP_User
     */
    public function check_active_user($user, $username) {
        if (is_wp_error($user) || !Functions::get_option_item('rtcl_account_settings', 'user_verification', '', 'checkbox')) {
            return $user;
        }

        $key = get_user_meta($user->ID, "rtcl_verification_key", true);

        if ($key && !empty($key)) {
            return new WP_Error('email_not_verified', sprintf(
                __('You have not verified your email address, please check your email and click on verification link we sent you, <a href="javascript:;" id="rtcl-resend-verify-link" data-login="%s">Re-send the link</a>', 'classified-listing'),
                $username
            ));
        }

        return $user;
    }

    /**
     * Creates a hash when new user registers and stores the hash as a meta value
     *
     * @param int $user_id
     */
    public function user_register($user_id) {
        if (!Functions::get_option_item('rtcl_account_settings', 'user_verification', '', 'checkbox')) {
            return; // ignore adding verification key
        }

        $this->send_verification_link($user_id);
    }

    /**
     * Lock user's account, send a verification email and ask them to verify their email address
     *
     * @param int $user_id
     */
    public function send_verification_link($user_id) {
        $user = get_user_by('id', $user_id);

        $this->lock_user($user);
        $this->send_email($user);
    }

    /**
     * Lock user
     *
     * @param WP_User $user
     */
    public function lock_user($user) {
        add_user_meta($user->ID, 'rtcl_verification_key', $this->generate_hash($user->data->user_email));
    }

    /**
     * Unlock user
     *
     * @param WP_User $user
     */
    public function unlock_user($user) {
        delete_user_meta($user->ID, 'rtcl_verification_key');
    }


    /**
     * Generate a url-friendly verification hash
     *
     * @param string $email
     *
     * @return string
     */
    public function generate_hash($email = '') {
        $key = $email . $this->secret . rand(0, 1000);

        return MD5($key);
    }


    /**
     * Send verification email
     *
     * @param WP_User $user
     */
    public function send_email($user = null) {
        if (!$user || !is_a($user, WP_User::class)) {
            return;
        }

        $reset_key = get_user_meta($user->ID, "rtcl_verification_key", true);

        // Ignore if there is no lock
        if (!$reset_key || empty($reset_key)) {
            return;
        }

        rtcl()->mailer()->emails['User_Verify_Link_Email_To_User']->trigger($user, $reset_key);

    }

    /**
     * Does user needs email validation?
     *
     * @param $user_id
     *
     * @return bool
     */
    public function needs_validation($user_id) {
        return boolval(get_user_meta($user_id, 'rtcl_verification_key', true));
    }


    /**
     * Validate hash
     */
    public function hash_valid() {
        if (empty($_GET['verify_email']) || empty($_GET['user_id']) || !preg_match('/^[a-f0-9]{32}$/', $_GET['verify_email'])) {
            Functions::add_notice(__("Your account hash, user id must be set", "classified-listing"), "error");

            return;
        }

        $user_id = absint($_GET['user_id']);

        // user already verified
        if (!$this->needs_validation($user_id)) {
            Functions::add_notice(__("Your account email address already verified", "classified-listing"), "error");

            return;
        }

        $hash = $_GET['verify_email'];

        if ($hash === get_user_meta($user_id, 'rtcl_verification_key', true)) {
            return true;
        } else {
            Functions::add_notice(__("Your account hash is not matched", "classified-listing"), "error");

            return false;
        }
    }


    /**
     * Verify user's email
     *
     * @param bool $signon
     *
     * @return bool|void
     */
    public function verify_if_valid($signon = false) {
        if (!$this->hash_valid()) {
            return;
        }

        $user_id = absint($_GET['user_id']);
        $user = get_user_by('id', $user_id);

        // Unlock user from loggin in
        $this->unlock_user($user);
        Functions::add_notice(__("Your account email address is verified", "classified-listing"), "success");
//		if ( get_option( 'dw_verify_autologin' ) ) {
//			wp_clear_auth_cookie();
//			wp_set_current_user( $user->ID );
//			wp_set_auth_cookie( $user->ID );
//		}

        return true;
    }

}