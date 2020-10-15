<?php
/**
 *
 * @author        RadiusTheme
 * @package    classified-listing/templates
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

use Rtcl\Models\Listing;
use Rtcl\Helpers\Functions;
use radiustheme\Classima\RDTheme;
use radiustheme\Classima\Helper;

$listing = new Listing( $post->ID );
?>
<?php get_header(); ?>
<div id="primary" class="content-area classima-listing-single rtcl">
	<div class="container">
		<?php do_action( 'classima_header_top' );?>
		<div class="row">
			<?php
			if ( RDTheme::$layout == 'left-sidebar' ) {
				Helper::get_custom_listing_template( 'sidebar-single' );
			}
			?>
			<div class="col-xl-9 col-lg-8 col-sm-12 col-12">
				<?php
				if ( RDTheme::$options['single_listing_style'] == '2' ) {
					Helper::get_custom_listing_template( 'content-single-2' );
				} else if ( RDTheme::$options['single_listing_style'] == '3' ) {
                    Helper::get_custom_listing_template( 'content-single-3' );
                }
				else {
					Helper::get_custom_listing_template( 'content-single' );
				}
				?>

                <div class="classima-listing-single-mob classima-listing-single-sidebar sidebar-widget-area">
                    <div class="content-block-gap"></div>
                    <?php Helper::get_custom_listing_template( 'seller-info' ); ?>
                </div>

				<?php do_action( 'classima_single_listing_after_product' ); ?>
				<?php $listing->the_map(); ?>
				<?php do_action( 'classima_single_listing_after_location' ); ?>
				
				<?php
				if ( RDTheme::$options['listing_related'] ) {
					$listing->the_related_listings();
				}
				?>
				
				<?php do_action( 'classima_single_listing_after_related' );?>
				<?php
				while ( have_posts() ) : the_post();
					if ( comments_open() || get_comments_number() ){
						comments_template();
					}
				endwhile;
				?>
			</div>
			<?php
			if ( RDTheme::$layout != 'left-sidebar' ) {
				Helper::get_custom_listing_template( 'sidebar-single' );
			}
			?>
		</div>
	</div>
</div>
<?php get_footer(); ?>