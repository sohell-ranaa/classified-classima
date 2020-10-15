<?php

use Rtcl\Helpers\Functions;
use Rtcl\Resources\Options;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings for Payment
 */
$options = array(
	'ls_section' => array(
		'title'       => __( 'Listing settings', 'classified-listing' ),
		'type'        => 'title',
		'description' => '',
	),

	'load_bootstrap'               => array(
		'title'       => __( 'Bootstrap options', 'classified-listing' ),
		'type'        => 'multi_checkbox',
		'default'     => array( 'css', 'js' ),
		'description' => __( "This plugin uses bootstrap 4. Disable these options if your theme already include them.",
			"classified-listing" ),
		'options'     => array(
			'css' => __( 'Include bootstrap CSS', 'classified-listing' ),
			'js'  => __( 'Include bootstrap javascript libraries', 'classified-listing' )
		)
	),
	'include_results_from'         => array(
		'title'   => __( 'Include results from', 'classified-listing' ),
		'type'    => 'multi_checkbox',
		'default' => array( 'child_categories', 'child_locations' ),
		'options' => array(
			'child_categories' => __( 'Child categories', 'classified-listing' ),
			'child_locations'  => __( 'Child locations', 'classified-listing' )
		)
	),
	'listings_per_page'            => array(
		'title'       => __( 'Listings per page', 'classified-listing' ),
		'type'        => 'number',
		'default'     => 20,
		'css'         => 'width:50px',
		'description' => __( 'Number of listings to show per page. Use a value of "0" to show all listings.',
			'classified-listing' )
	),
	'related_posts_per_page'       => array(
		'title'       => __( 'Number of listing for Related Listing', 'classified-listing' ),
		'type'        => 'number',
		'default'     => 4,
		'css'         => 'width:50px',
		'description' => __( 'Number of listings to show as related listing', 'classified-listing' )
	),
	'default_view'                 => array(
		'title'   => __( 'Default Listing view', 'classified-listing' ),
		'type'    => 'select',
		'default' => 'list',
		'options' => array(
			'list' => __( 'List view', 'classified-listing' ),
			'grid' => __( 'Grid view', 'classified-listing' )
		)
	),
	'orderby'                      => array(
		'title'   => __( 'Order Listing by', 'classified-listing' ),
		'type'    => 'select',
		'default' => 'date',
		'options' => array(
			'title' => __( 'Title', 'classified-listing' ),
			'date'  => __( 'Date posted', 'classified-listing' ),
			'price' => __( 'Price', 'classified-listing' ),
			'views' => __( 'Views count', 'classified-listing' )
		)
	),
	'order'                        => array(
		'title'   => __( 'Sort listings by', 'classified-listing' ),
		'type'    => 'select',
		'default' => 'desc',
		'options' => array(
			'asc'  => __( 'Ascending', 'classified-listing' ),
			'desc' => __( 'Descending', 'classified-listing' )
		)
	),
	'taxonomy_orderby'             => array(
		'title'   => __( 'Category / Location Order by', 'classified-listing' ),
		'type'    => 'select',
		'default' => 'title',
		'options' => array(
			'name'        => __( 'Name', 'classified-listing' ),
			'id'          => __( 'Id', 'classified-listing' ),
			'count'       => __( 'Count', 'classified-listing' ),
			'slug'        => __( 'Slug', 'classified-listing' ),
			'_rtcl_order' => __( 'Custom Order', 'classified-listing' ),
			'none'        => __( 'None', 'classified-listing' ),
		),
	),
	'taxonomy_order'               => array(
		'title'   => __( 'Category / Location Sort by', 'classified-listing' ),
		'type'    => 'select',
		'default' => 'asc',
		'options' => array(
			'asc'  => __( 'Ascending', 'classified-listing' ),
			'desc' => __( 'Descending', 'classified-listing' )
		)
	),
	'text_editor'                  => array(
		'title'       => __( 'Text Editor', 'classified-listing' ),
		'type'        => 'radio',
		'default'     => 'wp_editor',
		'options'     => array(
			'wp_editor' => __( 'Wp Editor', 'classified-listing' ),
			'textarea'  => __( 'Textarea', 'classified-listing' )
		),
		'description' => __( 'Listing form Editor style', 'classified-listing' ),
	),
	'location_section'             => array(
		'title'       => __( 'Location settings', 'classified-listing' ),
		'type'        => 'title',
		'description' => '',
	),
	'location_level_first'         => array(
		'title'   => __( 'First level location', 'classified-listing' ),
		'type'    => 'text',
		'default' => __( 'State', 'classified-listing' ),
	),
	'location_level_second'        => array(
		'title'   => __( 'Second level location', 'classified-listing' ),
		'type'    => 'text',
		'default' => __( 'City', 'classified-listing' ),
	),
	'location_level_third'         => array(
		'title'   => __( 'Third level location', 'classified-listing' ),
		'type'    => 'text',
		'default' => __( 'Town', 'classified-listing' ),
	),
	'currency_section'             => array(
		'title'       => __( 'Currency Options', 'classified-listing' ),
		'type'        => 'title',
		'description' => __( 'The following options affect how prices are displayed on the frontend.',
			'classified-listing' ),
	),
	'currency'                     => array(
		'title'   => __( 'Currency', 'classified-listing' ),
		'type'    => 'select',
		'class'   => 'rtcl-select2',
		'options' => Options::get_currencies(),
	),
	'currency_position'            => array(
		'title'   => __( 'Currency position', 'classified-listing' ),
		'type'    => 'select',
		'class'   => 'rtcl-select2',
		'options' => Options::get_currency_positions()
	),
	'currency_thousands_separator' => array(
		'title'       => __( 'Thousands separator', 'classified-listing' ),
		'type'        => 'text',
		'css'         => 'width:50px',
		'description' => __( 'The symbol (usually , or .) to separate thousands.', 'classified-listing' ),
		'default'     => ','
	),
	'currency_decimal_separator'   => array(
		'title'       => __( 'Decimal separator', 'classified-listing' ),
		'type'        => 'text',
		'css'         => 'width:50px',
		'description' => __( 'The symbol (usually , or .) to separate decimal points.',
			'classified-listing' ),
		'default'     => '.'
	)
);

return apply_filters( 'rtcl_general_settings_options', $options );