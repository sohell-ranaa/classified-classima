<?php
/**
 * @author  RadiusTheme
 * @since   1.0
 * @version 1.0
 */

namespace radiustheme\Classima_Core;
use radiustheme\Classima\Helper;

use Elementor\Plugin;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Custom_Widget_Init {

	public function __construct() {
		add_action( 'elementor/widgets/widgets_registered',     array( $this, 'init' ) );
		add_action( 'elementor/elements/categories_registered', array( $this, 'widget_categoty' ) );
		add_action( 'elementor/editor/after_enqueue_styles',    array( $this, 'editor_style' ) );
	}

	public function editor_style() {
		$img = plugins_url( 'icon.png', __FILE__ );
		wp_add_inline_style( 'elementor-editor', '.elementor-element .icon .rdtheme-el-custom{content: url('.$img.');width: 28px;}' );
        wp_add_inline_style( 'elementor-editor', '.elementor-panel .select2-container {min-width: 100px !important; min-height: 30px !important;}' );
	}

	public function init() {
		require_once __DIR__ . '/base.php';

		// Widgets -- dirname=>classname /@dev
		$widgets = array(
			'title'          => 'Title',
			'title-animated' => 'Title_Animated',
			'info-box'       => 'Info_Box',
			'text-button'    => 'Text_Button',
			'post'           => 'Post',
			'cta'            => 'CTA',
			'app-banner'     => 'APP_BANNER',
			'counter'        => 'Counter',
			'pricing-box'    => 'Pricing_Box',
			'accordian'      => 'Accordian',
			'testimonial-1'  => 'Testimonial_1',
			'testimonial-2'  => 'Testimonial_2',
			'contact'        => 'Contact',
			'google-map'     => 'Google_Map',
		);

		if ( class_exists( 'Rtcl' ) ) {
			$widgets += array(
				'listing-search'          => 'Listing_Search',
				'listing-grid'            => 'Listing_Grid',
				'listing-list'            => 'Listing_List',
				'listing-slider'          => 'Listing_Slider',
				'listing-isotope'         => 'Listing_Isotope',
				'listing-term-list'       => 'Listing_Term_List',
				'listing-category-slider' => 'Listing_Category_Slider',
				'listing-category-box'    => 'Listing_Category_Box',
				'listing-location-box'    => 'Listing_Location_Box',
				'listing-location-box-2'  => 'Listing_Location_Box_2',
				'listing-store-grid'      => 'Listing_Store_Grid',
				'listing-store-list'      => 'Listing_Store_List',
				'listing-ad-type'      => 'Listing_Ad_Type',
			);

			if ( class_exists( 'RtclStore' ) ) {
				$widgets += array(
					'listing-store-list' => 'Listing_Store_List',
				);
			}
		}

		foreach ( $widgets as $dirname => $class ) {
			$template_name = '/elementor-custom/' . $dirname . '/class.php';
			if ( file_exists( STYLESHEETPATH . $template_name ) ) {
				$file = STYLESHEETPATH . $template_name;
			}
			elseif ( file_exists( TEMPLATEPATH . $template_name ) ) {
				$file = TEMPLATEPATH . $template_name;
			}
			else {
				$file = __DIR__ . '/' . $dirname . '/class.php';
			}

			require_once $file;
			
			$classname = __NAMESPACE__ . '\\' . $class;
			Plugin::instance()->widgets_manager->register_widget_type( new $classname );
		}
	}

	public function widget_categoty( $class ) {
		$id         = CLASSIMA_CORE_THEME_PREFIX . '-widgets'; // Category /@dev
		$properties = array(
			'title' => __( 'RadiusTheme Elements', 'classima-core' ),
		);

		Plugin::$instance->elements_manager->add_category( $id, $properties );
	}
}

new Custom_Widget_Init();