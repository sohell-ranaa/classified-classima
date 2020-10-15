<?php
/**
 * Lost password reset form.
 *
 * @author 		RadiusTheme
 * @package 	classified-listing/templates
 * @version     1.0.0
 */

use Rtcl\Helpers\Functions;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

Functions::print_notices(); ?>

<form method="post" id="rtcl-password-reset-form" class="rtcl-lost-reset-password">

	<p><?php echo apply_filters( 'rtcl_reset_password_message', esc_html__( 'Enter a new password below.', 'classified-listing' ) ); ?></p><?php // @codingStandardsIgnoreLine ?>

    <div class="alert alert-info" role="alert">
		<?php echo wp_get_password_hint(); ?>
    </div>

    <div class="form-group">
        <label for="password-1" class="control-label"><?php _e( 'New password', 'classified-listing' ); ?></label>
            <input type="password" name="password_1" id="password-1" class="form-control" autocomplete="off" required />
    </div>

    <div class="form-group">
        <label for="password-2" class="control-label"><?php _e( 'Re-enter new password', 'classified-listing' ); ?></label>
            <input type="password" name="password_2" id="password-2" class="form-control" autocomplete="off" data-rule-equalTo="#password-1" required />
    </div>

	<input type="hidden" name="reset_key" value="<?php echo esc_attr( $args['key'] ); ?>" />
	<input type="hidden" name="reset_login" value="<?php echo esc_attr( $args['login'] ); ?>" />

	<?php do_action( 'rtcl_resetpassword_form' ); ?>

    <div class="form-group">
        <input type="submit" name="rtcl-reset-password" class="btn btn-primary" value="<?php _e( 'Reset Password', 'classified-listing' ); ?>" />
    </div>
	<?php wp_nonce_field( 'reset_password' ); ?>

</form>
