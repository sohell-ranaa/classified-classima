<?php
/**
 * @author  RadiusTheme
 * @since   1.0
 * @version 1.0
 */

namespace radiustheme\Classima_Core;

use radiustheme\Classima\Helper;

$col_class = "col-xl-{$data['col_xl']} col-lg-{$data['col_lg']} col-md-{$data['col_md']} col-sm-{$data['col_sm']} col-{$data['col_mobile']}" ;

$layout = $data['style'];
$display = array(
	'cat'   => $data['cat_display'] ? true : false,
	'views'   => $data['views_display'] ? true : false,
    'fields'   => $data['field_display']==='yes' ? true : false,
	'label' => false,
);

if ( $data['style'] == 1 ) {
	$display['views'] = false;
}

if ( $data['style'] == 6 ) {
    $display['type'] = true;
}

$query = $data['query'];
?>
<div class="rt-el-listing-grid">
	<?php if ( $query->have_posts() ) :?>
		<div class="row auto-clear">
			<?php while ( $query->have_posts() ) : $query->the_post();?>
				<div class="<?php echo esc_attr( $col_class );?>">
					<?php Helper::get_template_part( 'classified-listing/custom/grid', compact( 'layout', 'display' ) );?>
				</div>
			<?php endwhile;?>
		</div>
	<?php endif;?>
	<?php wp_reset_postdata();?>
</div>