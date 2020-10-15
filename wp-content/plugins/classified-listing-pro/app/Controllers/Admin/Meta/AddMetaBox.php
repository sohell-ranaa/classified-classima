<?php

namespace Rtcl\Controllers\Admin\Meta;

use Rtcl\Resources\FieldGroup;
use Rtcl\Resources\Gallery;
use Rtcl\Resources\ListingDetails;
use Rtcl\Resources\PaymentOptions;
use Rtcl\Resources\PricingOptions;

class AddMetaBox {

	function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'listing_details_meta_box' ) );
		add_action( 'edit_form_after_title', array( $this, 'prevent_nested' ) );
		add_action( 'add_meta_boxes', array( $this, 'pricing_meta_box' ) );
		add_action( 'add_meta_boxes', array( $this, 'payment_meta_box' ) );
		add_filter( 'postbox_classes_' . rtcl()->post_type . '_rtcl_listing_details', array(
			$this,
			'add_metabox_classes'
		) );
		add_filter( 'postbox_classes_' . rtcl()->post_type . '_rtcl_listing_contact_details', array(
			$this,
			'add_metabox_classes'
		) );

		add_filter( 'postbox_classes_' . rtcl()->post_type . '_rtcl_gallery', array(
			$this,
			'add_metabox_classes'
		) );

		add_filter( 'postbox_classes_' . rtcl()->post_type_pricing . '_rtcl_pricing', array(
			$this,
			'add_metabox_classes'
		) );
	}

	function add_metabox_classes( $classes = array() ) {
		array_push( $classes, sanitize_html_class( 'rtcl' ) );

		return $classes;
	}

	function listing_details_meta_box() {
		add_meta_box(
			'rtcl_listing_details',
			__( 'Listing Details', 'classified-listing' ),
			array( ListingDetails::class, 'listing_details' ),
			rtcl()->post_type,
			'normal',
			'high'
		);
		add_meta_box(
			'rtcl_listing_contact_details',
			__( 'Contact Details', 'classified-listing' ),
			array( ListingDetails::class, 'contact_details' ),
			rtcl()->post_type,
			'normal',
			'high'
		);
		add_meta_box(
			'rtcl_listing_moderation',
			__( 'Static Report', 'classified-listing' ),
			array( ListingDetails::class, 'static_report' ),
			rtcl()->post_type,
			'side',
			'default'
		);
		add_meta_box(
			'rtcl_gallery',
			__( 'Gallery', 'classified-listing' ),
			array( Gallery::class, 'rtcl_gallery_content' ),
			rtcl()->post_type,
			'normal',
			'high'
		);
	}

	function prevent_nested( $post ) {
		if ( $post->post_type == rtcl()->post_type_cfg ) {
			FieldGroup::rtcl_cfg_content( $post );
		}
	}

	function pricing_meta_box() {
		add_meta_box(
			'rtcl_pricing',
			__( 'Pricing Options', 'classified-listing' ),
			array( PricingOptions::class, 'rtcl_pricing_option' ),
			rtcl()->post_type_pricing,
			'normal',
			'high'
		);
	}

	function payment_meta_box() {
		add_meta_box(
			'rtcl-payment-data',
			__( 'Payment Data', 'classified-listing' ),
			array( PaymentOptions::class, 'payment_data' ),
			rtcl()->post_type_payment,
			'normal',
			'high'
		);

		add_meta_box(
			'rtcl-payment-items',
			__( 'Items', 'classified-listing' ),
			array( PaymentOptions::class, 'payment_items' ),
			rtcl()->post_type_payment,
			'normal',
			'high'
		);

		add_meta_box(
			'rtcl-payment-actions',
			__( 'Payment Actions', 'classified-listing' ),
			array( PaymentOptions::class, 'payment_action' ),
			rtcl()->post_type_payment,
			'side',
			'high'
		);
		add_meta_box(
			'rtcl-payment-notes',
			__( 'Payment notes', 'classified-listing' ),
			array( PaymentOptions::class, 'payment_notes' ),
			rtcl()->post_type_payment,
			'side',
			'default'
		);
	}

}