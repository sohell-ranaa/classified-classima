<?php
/**
 * @author  RadiusTheme
 * @since   1.5
 * @version 1.5
 */

if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

use radiustheme\Classima\Helper;
use radiustheme\Classima\RDTheme;
use radiustheme\Classima\Listing_Functions;
use Rtcl\Models\Listing;

if ( !isset( $listing  ) ) {
	$listing = new Listing( get_the_ID() );
}

$listing_post = $listing->get_listing();

$category = $listing->get_categories();
$category = end( $category );

$type = Listing_Functions::get_listing_type( $listing );

$class  = ' rtcl-listing-item';
$class .= isset( $top_listing ) ? ' rtin-top' : '';
$class .= $listing->is_featured() ? ' featured-listing' : '';

if ( !isset( $layout ) ) {
	$layout = RDTheme::$options['listing_grid_style'];
}

if ( !isset( $display ) ) {
	$display = array();
}

if ( !isset( $map ) ) {
	$map = false;
}

$fields = isset(RDTheme::$options['listing_custom_fields']) ? RDTheme::$options['listing_custom_fields'] : false;

$display_defaults = array(
	'cat'      => $listing->can_show_category(),
	'excerpt'  => $listing->can_show_excerpt(),
	'date'     => $listing->can_show_date(),
	'user'     => $listing->can_show_user(),
	'location' => $listing->can_show_location(),
	'views'    => $listing->can_show_views(),
	'price'    => $listing->can_show_price(),
    'fields'   => $fields,
	'label'    => true,
	'type'     => false,
);

$display_defaults = apply_filters( 'classima_grid_view_display_default_args', $display_defaults, $layout );

$display = wp_parse_args( $display, $display_defaults );
$display = apply_filters( 'classima_grid_view_display_args', $display, $layout );

if ( !$category ) {
	$display['cat'] = false;
}

Helper::get_custom_listing_template( 'list-items/archive-grid-' . $layout, true, compact( 'listing', 'listing_post', 'category', 'class', 'display', 'type', 'map' ) );