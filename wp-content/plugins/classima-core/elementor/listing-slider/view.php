<?php
/**
 * @author  RadiusTheme
 * @since   1.0
 * @version 1.0
 */

namespace radiustheme\Classima_Core;

use radiustheme\Classima\Helper;

$layout = $data['style'];
$display = array(
	'cat'   => $data['cat_display'] ? true : false,
    'views'   => $data['views_display'] ? true : false,
    'fields'   => $data['field_display']==='yes' ? true : false,
	'label' => false,
);

if ( $data['style'] == 6 ) {
    $display['type'] = true;
}

$query = $data['query'];
?>
<div class="rt-el-listing-slider owl-wrap rtin-<?php echo esc_attr( $data['style'] );?>">
	<div class="owl-custom-nav-area">
		<h3 class="owl-custom-nav-title"><?php echo esc_html( $data['sec_title'] );?></h3>
		<div class="owl-custom-nav">
			<div class="owl-prev"><i class="fa fa-angle-left"></i></div><div class="owl-next"><i class="fa fa-angle-right"></i></div>
		</div>
	</div>
	<?php if ( $query->have_posts() ) :?>
		<div class="owl-theme owl-carousel rt-owl-carousel" data-carousel-options="<?php echo esc_attr( $data['owl_data'] );?>">
			<?php while ( $query->have_posts() ) : $query->the_post();?>
				<?php Helper::get_template_part( 'classified-listing/custom/grid', compact( 'layout', 'display' ) );?>
			<?php endwhile;?>
		</div>
	<?php endif;?>
	<?php wp_reset_postdata();?>
</div>