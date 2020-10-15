<?php
/**
 * Listing Form
 *
 * @author    RadiusTheme
 * @package   classified-listing/templates
 * @version   1.0.0
 */

use Rtcl\Helpers\Functions;
use Rtcl\Helpers\Link;

$submit_txt = $post_id > 0 ? esc_html__( 'Update Listing', 'classima' ) : esc_html__( 'Submit Listing', 'classima' );
?>
<div class="rtcl rtcl-user rtcl-post-form-wrap">
    <?php do_action("rtcl_listing_form_before", $post_id); ?>
	<form action="" method="post" id="rtcl-post-form" class="form-vertical classima-form">
        <?php do_action("rtcl_listing_form_start", $post_id); ?>
        <div class="rtcl-post">
			<?php do_action("rtcl_listing_form", $post_id ); ?>
		</div>
		<div class="row listing-form-submit-btn-area">
			<div class="col-sm-3 col-12"></div>
			<div class="col-sm-9 col-12">
				<div class="form-group">
					<input type="submit" class="btn rtcl-submit-btn" value="<?php echo esc_attr( $submit_txt );?>"/>
				</div>
			</div>
		</div>
        <?php do_action("rtcl_listing_form_end", $post_id); ?>
	</form>
    <?php do_action("rtcl_listing_form_after", $post_id); ?>
</div>