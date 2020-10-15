<?php
/**
 *
 * @author        RadiusTheme
 * @package    classified-listing/templates
 * @version     1.0.0
 */

use Rtcl\Models\Listing;
use radiustheme\Classima\RDTheme;
use radiustheme\Classima\Helper;

$count = $rtcl_related_query->post_count;

if ( !$count ) {
    return;
}

$owl_data = array( 
    'nav'                => false,
    'dots'               => false,
    'autoplay'           => true,
    'autoplayTimeout'    => 5000,
    'autoplaySpeed'      => 2000,
    'autoplayHoverPause' => true,
    'loop'               => true,
    'margin'             => 20,
    'responsive'         => array(
        '0'    => array( 'items'=> 1 ),
        '500'  => array( 'items'=> min( 2, $count ) ),
        '1200' => array( 'items'=> min( 3, $count ) ),
    )
);
$owl_data = json_encode( $owl_data );

$layout = RDTheme::$options['listing_related_style'];

$display = array(
    'user' => false,
);
?>
<?php if ( $rtcl_related_query->have_posts() ) : ?>
    <div class="content-block-gap"></div>
    <div class="site-content-block classima-single-related owl-wrap">
        <div class="main-title-block">
            <h3 class="main-title"><?php esc_html_e( 'Related Ads', 'classima' );?></h3>
            <div class="owl-related-nav owl-custom-nav">
                <div class="owl-prev"><i class="fa fa-angle-left"></i></div><div class="owl-next"><i class="fa fa-angle-right"></i></div>
            </div>
        </div>
        <div class="main-content">
            <div class="owl-theme owl-carousel rt-owl-carousel" data-carousel-options="<?php echo esc_attr( $owl_data );?>">
                <?php while ( $rtcl_related_query->have_posts() ) : $rtcl_related_query->the_post(); ?>
                    <?php Helper::get_template_part( 'classified-listing/custom/grid', compact( 'layout', 'display' ) );?>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
<?php endif;