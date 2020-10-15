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
	'gs_section'                  => array(
		'title'       => __( 'General settings', 'classified-listing' ),
		'type'        => 'title',
		'description' => '',
	),
	'notify_admin'                => array(
		'title'   => __( 'Notify admin via email when', 'classified-listing' ),
		'type'    => 'multi_checkbox',
		'default' => array( 'register_new_user', 'listing_submitted', 'order_created', 'payment_received' ),
		'options' => Options::get_admin_email_notification_options()
	),
	'notify_users'                => array(
		'title'   => __( 'Notify users via email when their', 'classified-listing' ),
		'type'    => 'multi_checkbox',
		'default' => array(
			'listing_submitted',
			'listing_published',
			'listing_renewal',
			'listing_expired',
			'remind_renewal',
			'order_created',
			'order_completed'
		),
		'options' => Options::get_user_email_notification_options()
	),
	'email_sender_option_section' => array(
		'title'       => __( 'Email sender option', 'classified-listing' ),
		'type'        => 'title',
		'description' => '',
	),
	'from_name'                   => array(
		'title'       => __( 'From name', 'classified-listing' ),
		'type'        => 'text',
		'default'     => Functions::get_blogname(),
		'description' => __( 'The name system generated emails are sent from. This should probably be your site or directory name.',
			'classified-listing' )
	),
	'from_email'                  => array(
		'title'       => __( 'From email', 'classified-listing' ),
		'type'        => 'text',
		'default'     => get_option( 'admin_email' ),
		'description' => __( "The sender email address should belong to the site domain.", 'classified-listing' )
	),
	'admin_notice_emails'         => array(
		'title'       => __( 'Admin notification emails', 'classified-listing' ),
		'type'        => 'textarea',
		'css'         => 'max-width:400px; height: 75px;',
		'default'     => get_option( 'admin_email' ),
		'description' => __( 'Enter the email address(es) that should receive admin notification emails, one per line.',
			'classified-listing' )
	),
	'email_template_section'      => array(
		'title'       => __( 'Email template', 'classified-listing' ),
		'type'        => 'title',
		'description' => sprintf( '<strong>%s</strong>', __( "You can use the following placeholders", "classified-listing" ) ) . '<br>' .
		                 '{site_name} - ' . __( 'Your site name', 'classified-listing' ) . '<br>' .
		                 '{site_link} - ' . __( 'Your site name with link', 'classified-listing' ) . '<br>' .
		                 '{site_url} - ' . __( 'Your site url with link', 'classified-listing' ) . '<br>' .
		                 '{admin_email} - ' . __( 'Administration Email Address', 'classified-listing' ) . '<br>' .
		                 '{renewal_link} - ' . __( 'Link to renewal page', 'classified-listing' ) . '<br>' .
		                 '{today} - ' . __( 'Current date', 'classified-listing' ) . '<br>' .
		                 '{now} - ' . __( 'Current time', 'classified-listing' ) . '<br><br>' .
		                 sprintf( __( 'This section lets you customize the Classified Listing emails. <a href="%s" target="_blank">Click here to preview your email template.</a>', "classified-listing" ), wp_nonce_url( admin_url( '?preview_rtcl_mail=true' ), 'preview-mail' ) )
	),
	'email_content_type'          => array(
		'title'       => __( 'Email Content Type', 'classified-listing' ),
		'type'        => 'select',
		'default'     => 'html',
		'class'       => 'rtcl-select2',
		'description' => __( 'Choose which format of email to send.', 'classified-listing' ),
		'options'     => Options::get_email_type_options()
	),
	'email_header_image'          => array(
		'title' => __( 'Header image', 'classified-listing' ),
		'type'  => 'image'
	),
	'email_footer_text'           => array(
		'title'       => __( 'Footer text', 'classified-listing' ),
		'description' => __( 'The text to appear in the footer of WooCommerce emails.', 'classified-listing' ) . ' ' . sprintf( __( 'Available placeholders: %s', 'classified-listing' ), '{site_title}' ),
		'css'         => 'max-width:400px; height: 75px;',
		'placeholder' => __( 'N/A', 'classified-listing' ),
		'type'        => 'textarea',
		'default'     => '{site_title}'
	),
	'email_base_color'            => array(
		'title'       => __( 'Base color', 'classified-listing' ),
		'description' => sprintf( __( 'The base color for WooCommerce email templates. Default %s.', 'classified-listing' ), '<code>#0071bd</code>' ),
		'type'        => 'color',
		'css'         => 'width:6em;',
		'default'     => '#0071bd',
	),
	'email_background_color'      => array(
		'title'       => __( 'Background color', 'classified-listing' ),
		'description' => sprintf( __( 'The background color for WooCommerce email templates. Default %s.', 'classified-listing' ), '<code>#f7f7f7</code>' ),
		'type'        => 'color',
		'css'         => 'width:6em;',
		'default'     => '#f7f7f7',
	),
	'email_body_background_color' => array(
		'title'       => __( 'Body background color', 'classified-listing' ),
		'description' => sprintf( __( 'The main body background color. Default %s.', 'classified-listing' ), '<code>#ffffff</code>' ),
		'type'        => 'color',
		'css'         => 'width:6em;',
		'default'     => '#ffffff',
	),
	'email_text_color'            => array(
		'title'       => __( 'Body text color', 'classified-listing' ),
		'description' => sprintf( __( 'The main body text color. Default %s.', 'classified-listing' ), '<code>#3c3c3c</code>' ),
		'type'        => 'color',
		'css'         => 'width:6em;',
		'default'     => '#3c3c3c',
	),
	'listing_submitted_section'   => array(
		'title'       => __( 'Listing submitted email ( confirmation )', 'classified-listing' ),
		'type'        => 'title',
		'description' => file_exists( Functions::get_theme_template_path( 'emails/listing-submitted-email-to-owner.php' ) ) ?
			sprintf( esc_html__( "Template is override at %s", 'classified-listing' ), '<code>' . Functions::get_theme_template_file( 'emails/listing-submitted-email-to-owner.php' ) . '</code>' )
			:
			sprintf( esc_html__( 'To override and edit this email template copy %1$s to your theme folder: %2$s.', 'classified-listing' ), '<code>' . esc_html( Functions::get_plugin_template_file( 'emails/listing-submitted-email-to-owner.php' ) ) . '</code>', '<code>' . esc_html( Functions::get_theme_template_file( 'emails/listing-submitted-email-to-owner.php' ) ) . '</code>'
			)
	),
	'listing_submitted_subject'   => array(
		'title'   => __( 'Subject', 'classified-listing' ),
		'type'    => 'text',
		'default' => __( '[{site_title}] Listing "{listing_title}" is received', 'classified-listing' ),
	),
	'listing_submitted_heading'   => array(
		'title'   => __( 'Heading', 'classified-listing' ),
		'type'    => 'text',
		'default' => __( 'Your listing is received', 'classified-listing' ),
	),
	'listing_published_section'   => array(
		'title' => __( 'Listing published/approved email', 'classified-listing' ),
		'type'  => 'title',
	),
	'listing_published_subject'   => array(
		'title'   => __( 'Subject', 'classified-listing' ),
		'type'    => 'text',
		'default' => __( '[{site_title}] Listing "{listing_title}" is published', 'classified-listing' ),
	),
	'listing_published_heading'   => array(
		'title'   => __( 'Heading', 'classified-listing' ),
		'type'    => 'text',
		'default' => __( 'Your listing is published', 'classified-listing' )
	),
	'renewal_section'             => array(
		'title' => __( 'Listing renewal email', 'classified-listing' ),
		'type'  => 'title',
	),
	'renewal_email_threshold'     => array(
		'title'       => __( 'Listing renewal email threshold (in days)', 'classified-listing' ),
		'type'        => 'number',
		'default'     => 3,
		'description' => __( 'Configure how many days before listing expiration is the renewal email sent.', 'classified-listing' )
	),
	'renewal_subject'             => array(
		'title'   => __( 'Subject', 'classified-listing' ),
		'type'    => 'text',
		'default' => __( '[{site_name}] {listing_title} - Expiration notice', 'classified-listing' ),
	),
	'renewal_heading'             => array(
		'title'   => __( 'Heading', 'classified-listing' ),
		'type'    => 'text',
		'default' => __( 'Expiration notice', 'classified-listing' ),
	),
	'expired_section'             => array(
		'title' => __( 'Listing expired email', 'classified-listing' ),
		'type'  => 'title',
	),
	'expired_subject'             => array(
		'title'   => __( 'Subject', 'classified-listing' ),
		'type'    => 'text',
		'default' => __( '[{site_title}] {listing_title} - Expiration notice', 'classified-listing' ),
	),
	'expired_heading'             => array(
		'title'   => __( 'Heading', 'classified-listing' ),
		'type'    => 'text',
		'default' => __( 'Expiration notice', 'classified-listing' ),
	),
	'renewal_reminder_section'    => array(
		'title' => __( 'Renewal reminder email', 'classified-listing' ),
		'type'  => 'title',
	),
	'renewal_reminder_threshold'  => array(
		'title'       => __( 'Listing renewal reminder email threshold (in days)', 'classified-listing' ),
		'type'        => 'number',
		'default'     => 3,
		'description' => __( 'Configure how many days after the expiration of a listing an email reminder should be sent to the owner.', 'classified-listing' )
	),
	'renewal_reminder_subject'    => array(
		'title'   => __( 'Subject', 'classified-listing' ),
		'type'    => 'text',
		'default' => __( '[{site_title}] {listing_title} - Renewal reminder', 'classified-listing' ),
	),
	'renewal_reminder_heading'    => array(
		'title'   => __( 'Heading', 'classified-listing' ),
		'type'    => 'text',
		'default' => __( 'Renewal reminder', 'classified-listing' ),
	),
	'order_created_section'       => array(
		'title' => __( 'New Order', 'classified-listing' ),
		'type'  => 'title',
	),
	'order_created_subject'       => array(
		'title'   => __( 'Subject', 'classified-listing' ),
		'type'    => 'text',
		'default' => __( '[{site_title}] #{order_number} Thank you for your order', 'classified-listing' )
	),
	'order_created_heading'       => array(
		'title'   => __( 'Heading', 'classified-listing' ),
		'type'    => 'text',
		'default' => __( 'New Order: #{order_number}', 'classified-listing' )
	),
	'order_completed_section'     => array(
		'title' => __( 'Order completed email', 'classified-listing' ),
		'type'  => 'title',
	),
	'order_completed_subject'     => array(
		'title'   => __( 'Subject', 'classified-listing' ),
		'type'    => 'text',
		'default' => __( '[{site_title}] : #{order_number} Order is completed.', 'classified-listing' )
	),
	'order_completed_heading'     => array(
		'title'   => __( 'Heading', 'classified-listing' ),
		'type'    => 'text',
		'default' => __( 'Payment is completed: #{order_number}', 'classified-listing' )
	),
	'listing_contact_section'     => array(
		'title' => __( 'Listing contact email', 'classified-listing' ),
		'type'  => 'title',
	),
	'contact_subject'             => array(
		'title'   => __( 'Subject', 'classified-listing' ),
		'type'    => 'text',
		'default' => __( '[{site_title}] Contact via "{listing_title}"', 'classified-listing' )
	),
	'contact_heading'             => array(
		'title'   => __( 'Heading', 'classified-listing' ),
		'type'    => 'text',
		'default' => __( 'Thank you for mail', 'classified-listing' )
	)
);

return apply_filters( 'rtcl_email_settings_options', $options );