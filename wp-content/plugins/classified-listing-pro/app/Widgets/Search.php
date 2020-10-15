<?php

namespace Rtcl\Widgets;


use Rtcl\Helpers\Functions;

class Search extends \WP_Widget {

	protected $style = [];

	protected $widget_slug;

	public function __construct() {
		$this->style = [
			'popup'      => __( 'Popup', 'classified-listing' ),
			'suggestion' => __( 'Auto Suggestion', 'classified-listing' ),
			'dependency' => __( 'Dependency Selection', 'classified-listing' ),
			'standard'   => __( 'Standard', 'classified-listing' )
		];

		$this->widget_slug = 'rtcl-widget-search';

		parent::__construct(
			$this->widget_slug,
			__( 'Classified Listing Search', 'classified-listing' ),
			array(
				'classname'   => 'rtcl ' . $this->widget_slug,
				'description' => __( 'A Search feature', 'classified-listing' )
			)
		);

	}

	public function widget( $args, $instance ) {
		$data = [
			'id'                          => wp_rand(),
			'style'                       => isset( $instance['style'] ) && array_key_exists( $instance['style'], $this->style ) ? $instance['style'] : 'suggestion',
			'orientation'                 => isset( $instance['orientation'] ) && ! empty( $instance['orientation'] ) ? $instance['orientation'] : 'inline',
			'can_search_by_category'      => ! empty( $instance['search_by_category'] ) ? 1 : 0,
			'can_search_by_location'      => ! empty( $instance['search_by_location'] ) ? 1 : 0,
			'can_search_by_listing_types' => ! empty( $instance['search_by_listing_types'] ) ? 1 : 0,
			'can_search_by_price'         => ! empty( $instance['search_by_price'] ) ? 1 : 0,
			'selected_location'           => false,
			'selected_category'           => false
		];

		if ( get_query_var( 'rtcl_location' ) && $location = get_term_by( 'slug', get_query_var( 'rtcl_location' ), rtcl()->location ) ) {
			$data['selected_location'] = $location;
		}

		if ( get_query_var( 'rtcl_category' ) && $location = get_term_by( 'slug', get_query_var( 'rtcl_category' ), rtcl()->category ) ) {
			$data['selected_category'] = $location;
		}

		$data['active_count'] = $data['can_search_by_category'] + $data['can_search_by_location'] + $data['can_search_by_listing_types'] + $data['can_search_by_price'];

		$data['classes']  = [
			'rtcl',
			'rtcl-widget-search',
			'rtcl-widget-search-' . $data['orientation'],
			'rtcl-widget-search-style-' . $data['style'],
		];
		$data['instance'] = $instance;
		$data['args']     = $args;
		$data['data']     = $data;

		echo $args['before_widget'];

		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}
		Functions::get_template( "widgets/search", $data );

		echo $args['after_widget'];

	}

	public function update( $new_instance, $old_instance ) {

		$instance = $old_instance;

		$instance['title']                   = ! empty( $new_instance['title'] ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['style']                   = ! empty( $new_instance['style'] ) && array_key_exists( $new_instance['style'], $this->style ) ? strip_tags( $new_instance['style'] ) : 'suggestion';
		$instance['orientation']             = isset( $new_instance['orientation'] ) && ! empty( $new_instance['orientation'] ) ? strip_tags( $new_instance['orientation'] ) : 'inline';
		$instance['search_by_category']      = isset( $new_instance['search_by_category'] ) ? 1 : 0;
		$instance['search_by_location']      = isset( $new_instance['search_by_location'] ) ? 1 : 0;
		$instance['search_by_listing_types'] = isset( $new_instance['search_by_listing_types'] ) ? 1 : 0;
		$instance['search_by_price']         = isset( $new_instance['search_by_price'] ) ? 1 : 0;

		return $instance;

	}

	public function form( $instance ) {

		// Define the array of defaults
		$defaults = array(
			'title'                   => __( 'Search Listings', 'classified-listing' ),
			'style'                   => 'popup',
			'orientation'             => 'inline',
			'search_by_category'      => 1,
			'search_by_location'      => 1,
			'search_by_listing_types' => 0,
			'search_by_price'         => 0
		);

		// Parse incoming $instance into an array and merge it with $defaults
		$instance = wp_parse_args(
			(array) $instance,
			$defaults
		);

		// Display the admin form
		include( RTCL_PATH . "views/widgets/search.php" );

	}

}