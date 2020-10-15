<?php

namespace Rtcl\Controllers\Admin\Meta;


use Rtcl\Helpers\Functions;
use Rtcl\Resources\Options;

class ListingSubmitBoxMice {

	public function __construct() {
		add_action( 'post_submitbox_misc_actions', array( $this, 'post_submitbox_misc_actions' ) );
		add_action( 'admin_footer-post.php', array( $this, 'jc_append_post_status_list' ) );
	}

	public function post_submitbox_misc_actions() {

		global $post, $post_type;

		if ( rtcl()->post_type == $post_type ) {
			$never_expires = ! empty( get_post_meta( $post->ID, 'never_expires', true ) ) ? 1 : 0;
			$expiry_date   = get_post_meta( $post->ID, 'expiry_date', true );
			$expiry_date   = $expiry_date ? $expiry_date : Functions::dummy_expiry_date();
			$featured       = get_post_meta( $post->ID, 'featured', true );
			$top            = get_post_meta( $post->ID, '_top', true );
			$_bump_up       = get_post_meta( $post->ID, '_bump_up', true );
			$_views         = get_post_meta( $post->ID, '_views', true );
			wp_nonce_field( rtcl()->nonceText, rtcl()->nonceId );
			?>
            <div class="misc-pub-section misc-pub-rtcl-expiration-time"<?php echo $never_expires ? ' style="display: none;"' : '' ?>>
				<?php Functions::touch_time( 'expiry_date', $expiry_date ); ?>
            </div>
            <div class="misc-pub-section misc-pub-rtcl-overwrite">
                <label>
                    <input type="checkbox" id="rtcl-overwrite" name="overwrite"
                           value="1">
                    <strong><?php _e( "Overwrite Default", 'classified-listing' ); ?></strong>
                </label>
            </div>
            <div class="misc-pub-section misc-pub-rtcl-never-expires">
                <label>
                    <input disabled type="checkbox" name="never_expires"
                           value="1" <?php if ( isset( $never_expires ) ) {
						checked( $never_expires, 1 );
					} ?>>
                    <strong><?php _e( "Never Expires", 'classified-listing' ); ?></strong>
                </label>
            </div>

            <div class="misc-pub-section misc-pub-rtcl-featured">
                <label>
                    <input disabled type="checkbox" name="featured" value="1" <?php if ( isset( $featured ) ) {
						checked( $featured, 1 );
					} ?>>
					<?php _e( "Mark as", 'classified-listing' ); ?>
                    <strong><?php _e( "Featured", 'classified-listing' ); ?></strong>
                </label>
            </div>
            <div class="misc-pub-section misc-pub-rtcl-top">
                <label>
                    <input disabled type="checkbox" name="_top" value="1" <?php if ( isset( $top ) ) {
						checked( $top, 1 );
					} ?>>
					<?php _e( "Mark as", 'classified-listing' ); ?>
                    <strong><?php _e( "Top", 'classified-listing' ); ?></strong>
                </label>
            </div>
            <div class="misc-pub-section misc-pub-rtcl-bump-up">
                <label>
                    <input disabled type="checkbox" name="_bump_up" value="1" <?php if ( isset( $_bump_up ) ) {
						checked( $_bump_up, 1 );
					} ?>>
					<?php _e( "Mark as", 'classified-listing' ); ?>
                    <strong><?php _e( "Bump Up", 'classified-listing' ); ?></strong>
                </label>
            </div>
            <div class="misc-pub-section misc-pub-rtcl-top">
                <label for="rtcl-views">
                    <strong><?php _e( "View", 'classified-listing' ); ?></strong>
                    <input type="number" id="rtcl-views" name="_views" value="<?php echo absint( $_views ); ?>">
                </label>
            </div>

            <div class="misc-pub-section misc-pub-rtcl-action rtcl">
                <div class="form-group row">
                    <label for="rtcl-listing-status"
                           class="col-sm-2 col-form-label"><?php _e( "Status", "classified-listing" ) ?></label>
                    <div class="col-sm-10">
                        <select name="post_status" class="form-control rtcl-select2">
							<?php
							$status_list = Options::get_status_list();
							$c_status    = get_post_status( $post->ID );
							foreach ( $status_list as $status_id => $status ) {
								printf( "<option value='%s'%s>%s</option>",
									$status_id,
									$status_id === $c_status ? " selected" : null,
									$status
								);
							}
							?>
                        </select>
                    </div>
                </div>
            </div>
			<?php
		}

	}

	function jc_append_post_status_list() {
		global $post;
		if ( $post->post_type == rtcl()->post_type ) {
			$status_opt  = null;
			$status_list = Options::get_status_list();
			$label       = null;
			foreach ( $status_list as $status_key => $status ) {
				$slt = '';
				if ( $status_key == $post->post_status ) {
					$slt   = " selected";
					$label = $status;
				}
				$status_opt .= "<option value='{$status_key}'{$slt}>{$status}</option>";
			}
            $update_text = __("Update", "classified-listing");
            echo '
                  <script>
                  jQuery(document).ready(function($){
                       $("select#post_status").html("' . $status_opt . '");
                       $("#publish").val("' . $update_text . '");
                  });
                  </script>
                  ';
			if ( $label ) {
				echo '
                  <script>
                  jQuery(document).ready(function($){
                       $("#post-status-display").text("' . $label . '");
                  });
                  </script>
                  ';
			}
		}
	}
}