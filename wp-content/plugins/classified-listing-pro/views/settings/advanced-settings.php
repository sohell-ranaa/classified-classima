<?php

use Rtcl\Helpers\Functions;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Settings for misc
 */
$options = array(
    'permalink_section'                 => array(
        'title'       => __('Permalink slugs', 'classified-listing'),
        'type'        => 'title',
        'description' => __('NOTE: Just make sure that, after updating the fields in this section, you flush the rewrite rules by visiting Settings > Permalinks. Otherwise you\'ll still see the old links.',
            'classified-listing'),
    ),
    'permalink'                         => array(
        'title'       => __('Listing base', 'classified-listing'),
        'type'        => 'text',
        'default'     => rtcl()->post_type,
        'description' => __('Listing base permalink. Default "rtcl_listing"', 'classified-listing'),
    ),
    'category_base'                     => array(
        'title'       => __('Listing category base', 'classified-listing'),
        'type'        => 'text',
        'default'     => _x('listing-category', 'slug', 'classified-listing'),
        'description' => __('Listing category base permalink.', 'classified-listing'),
    ),
    'location_base'                     => array(
        'title'       => __('Listing location base', 'classified-listing'),
        'type'        => 'text',
        'default'     => _x('listing-location', 'slug', 'classified-listing'),
        'description' => __('Listing location base permalink.', 'classified-listing'),
    ),
    'page_setup'                        => array(
        'title'       => __('Page setup', 'classified-listing'),
        'type'        => 'title',
        'description' => __('These pages need to be set so that listing endpoint.', 'classified-listing'),
    ),
    'listings'                          => array(
        'title'       => __('Listings page', 'classified-listing'),
        'options'     => Functions::get_pages(),
        'type'        => 'select',
        'description' => __('This is the page where all the active listings are displayed.',
            'classified-listing'),
        'class'       => 'rtcl-select2',
        'blank_text'  => __("Select a page", 'classified-listing'),
        'css'         => 'min-width:300px;',
    ),
    'listing_form'                      => array(
        'title'       => __('Listing form page', 'classified-listing'),
        'type'        => 'select',
        'class'       => 'rtcl-select2',
        'blank_text'  => __("Select a page", 'classified-listing'),
        'options'     => Functions::get_pages(),
        'css'         => 'min-width:300px;',
        'description' => __('This is the listing form page used to add or edit listing details. The [rtcl_listing_form] short code must be on this page.',
            'classified-listing')
    ),
    'myaccount'                         => array(
        'title'       => __('My account', 'classified-listing'),
        'type'        => 'select',
        'class'       => 'rtcl-select2',
        'blank_text'  => __("Select a page", 'classified-listing'),
        'options'     => Functions::get_pages(),
        'css'         => 'min-width:300px;',
        'description' => __('This is the page where the users can view/edit their account info. The [rtcl_my_account] short code must be on this page.',
            'classified-listing')
    ),
    'checkout'                          => array(
        'title'       => __('Checkout page', 'classified-listing'),
        'type'        => 'select',
        'class'       => 'rtcl-select2',
        'blank_text'  => __("Select a page", 'classified-listing'),
        'options'     => Functions::get_pages(),
        'description' => __('This is the checkout page where users will complete their purchases. The [rtcl_checkout] short code must be on this page.',
            'classified-listing'),
        'css'         => 'min-width:300px;',
    ),
    'account_endpoints'                 => array(
        'title'       => __('Account endpoints', 'classified-listing'),
        'type'        => 'title',
        'description' => __('Endpoints are appended to your page URLs to handle specific actions on the accounts pages. They should be unique and can be left blank to disable the endpoint.',
            'classified-listing'),
    ),
    'myaccount_listings_endpoint'       => array(
        'title'   => __('My Listings', 'classified-listing'),
        'type'    => 'text',
        'default' => 'listings'
    ),
    'myaccount_favourites_endpoint'     => array(
        'title'   => __('Favourites', 'classified-listing'),
        'type'    => 'text',
        'default' => 'favourites'
    ),
    'myaccount_chat_endpoint'           => array(
        'title'   => __('Chat', 'classified-listing'),
        'type'    => 'text',
        'default' => 'chat'
    ),
    'myaccount_edit_account_endpoint'   => array(
        'title'   => __('Edit Account', 'classified-listing'),
        'type'    => 'text',
        'default' => 'edit-account'
    ),
    'myaccount_payments_endpoint'       => array(
        'title'   => __('Payments', 'classified-listing'),
        'type'    => 'text',
        'default' => 'payments'
    ),
    'myaccount_lost_password_endpoint'  => array(
        'title'   => __('Lost Password', 'classified-listing'),
        'type'    => 'text',
        'default' => 'lost-password'
    ),
    'myaccount_verify'                  => array(
        'title'   => __('Account verify', 'classified-listing'),
        'type'    => 'text',
        'default' => 'verify'
    ),
    'myaccount_logout_endpoint'         => array(
        'title'   => __('Logout', 'classified-listing'),
        'type'    => 'text',
        'default' => 'logout'
    ),
    'checkout_endpoints'                => array(
        'title'       => __('Checkout endpoints', 'classified-listing'),
        'type'        => 'title',
        'description' => __('Endpoints are appended to your page URLs to handle specific actions during the checkout process. They should be unique.',
            'classified-listing'),
    ),
    'checkout_submission_endpoint'      => array(
        'title'   => __('Submission', 'classified-listing'),
        'type'    => 'text',
        'default' => 'submission'
    ),
    'checkout_promote_endpoint'         => array(
        'title'   => __('Promote', 'classified-listing'),
        'type'    => 'text',
        'default' => 'promote'
    ),
    'checkout_payment_receipt_endpoint' => array(
        'title'   => __('Payment Receipt', 'classified-listing'),
        'type'    => 'text',
        'default' => 'payment-receipt'
    ),
    'checkout_payment_failure_endpoint' => array(
        'title'   => __('Payment Failure', 'classified-listing'),
        'type'    => 'text',
        'default' => 'payment-failure'
    ),

);

return apply_filters('rtcl_advanced_settings_options', $options);
