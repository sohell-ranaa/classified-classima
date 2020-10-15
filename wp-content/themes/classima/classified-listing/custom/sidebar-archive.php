<?php
/**
 * @author  RadiusTheme
 * @since   1.0
 * @version 1.0
 */

namespace radiustheme\Classima;
?>
<a id="classima-toggle-sidebar" href="#"><?php esc_html_e( 'Toggle Filter', 'classima' );?></a>

<aside class="sidebar-widget-area sidebar-listing-archive">
	<?php do_action( 'classima_before_sidebar' ); ?>
	<?php
	if ( is_active_sidebar( 'sidebar-archive-listing' ) ){
		dynamic_sidebar( 'sidebar-archive-listing' );
	}
	else {
		if ( class_exists( 'Rtcl\Widgets\Filter' ) ) {
			$args = array(
				'before_widget' => '<div class="widget %s">',
				'after_widget'  => '</div>',
				'before_title'  => '<h3 class="widgettitle">',
				'after_title'   => '</h3>',
			);
			$instance = array(
				'title'                        => esc_html__( 'Filter Ads', 'classima' ),
				'search_by_category'           => 1,
				'show_icon_image_for_category' => 1,
				'search_by_location'           => 1,
				'search_by_ad_type'            => 1,
				'search_by_custom_fields'      => 1,
				'search_by_price'              => 1,
				'hide_empty'                   => 0,
				'show_count'                   => 1,
				'ajax_load'					   => 0,
				'current_term'                 => '',
			);

			the_widget( 'Rtcl\Widgets\Filter', $instance, $args );
		}
	}

	do_action( 'classima_after_sidebar' );
	?>
</aside>