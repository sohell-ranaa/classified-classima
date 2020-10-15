<?php
/**
 * Listing meta
 *
 * @author     RadiusTheme
 * @package    classified-listing/templates
 * @version    1.0.0
 */

global $listing;

if ( ! $listing->can_show_date() && ! $listing->can_show_user() && ! $listing->can_show_category() && ! $listing->can_show_location() && ! $listing->can_show_views() ) {
	return;
}
?>

<ul class="rtcl-listing-meta-data">
	<?php if ( $listing->can_show_date() ): ?>
        <li class="updated"><i class="rtcl-icon rtcl-icon-clock"></i>&nbsp;<?php $listing->the_time(); ?></li>
	<?php endif; ?>
	<?php if ( $listing->can_show_user() ): ?>
        <li class="author">
	        &nbsp;<?php esc_html_e( 'by ', 'classified-listing' ); ?>
			<?php $listing->the_author(); ?>
        </li>
	<?php endif; ?>
	<?php if ( $listing->has_category() && $listing->can_show_category() ):
		$category = $listing->get_categories();
		$category = end( $category );
		?>
        <li class="rt-categories">
            <i class="rtcl-icon rtcl-icon-tags"></i>
            &nbsp;<?php echo esc_html( $category->name ) ?>
        </li>
	<?php endif; ?>
	<?php if ( $listing->has_location() && $listing->can_show_location() ):
		?>
        <li class="rt-location">
            <i class="rtcl-icon rtcl-icon-location"></i> <?php $listing->the_locations() ?>
        </li>
	<?php endif; ?>
	<?php if ( $listing->can_show_views() ): ?>
        <li class="rt-views">
            <i class="rtcl-icon rtcl-icon-eye"> </i>
			<?php echo sprintf( _n( "%s view", "%s views", $listing->get_view_counts(), 'classified-listing' ), number_format_i18n( $listing->get_view_counts() ) ); ?>
        </li>
	<?php endif; ?>
</ul>
