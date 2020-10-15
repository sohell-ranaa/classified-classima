<?php

use Rtcl\Helpers\Text;
use Rtcl\Helpers\Functions;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Settings for Account page
 */
$options = array(
    'enable_myaccount_registration'      => array(
        'title'       => __('Account creation', 'classified-listing'),
        'type'        => 'checkbox',
        'default'     => 'yes',
        'description' => __('Allow visitor to create an account on the "My account" page', 'classified-listing'),
    ),
    'disable_name_phone_registration'    => array(
        'title'       => __('Hide Name and phone number', 'classified-listing'),
        'type'        => 'checkbox',
        'description' => __('Hide Name and phone number at registration form', 'classified-listing'),
    ),
    'user_role'                          => array(
        'title'      => __('New User Default Role', 'classified-listing'),
        'type'       => 'select',
        'class'      => 'rtcl-select2',
        'blank_text' => __("Default Role as wordpress", 'classified-listing'),
        'options'    => Functions::get_user_roles('', ['administrator']),
        'css'        => 'min-width:300px;'
    ),
    'allowed_core_permission_roles'      => array(
        'title'       => __('Admin Menu Access role', 'classified-listing'),
        'type'        => 'multi_checkbox',
        'options'     => Functions::get_user_roles('', ['administrator', 'rtcl_manager']),
        'description' => __('Allowed all Classified Listing Admin Menu access to a user role as like Administrator. [<span style="color: red;">NOT RECOMMENDED</span>]', 'classified-listing'),
    ),
    'enable_post_for_unregister'         => array(
        'title'       => __('Allow post for unregister user', 'classified-listing'),
        'type'        => 'checkbox',
        'description' => __('Allow visitor to create a post and account will create automatically', 'classified-listing'),
    ),
    'user_verification'                  => array(
        'title'       => __('User Verification', 'classified-listing'),
        'type'        => 'checkbox',
        'description' => __('User Registration will be pending and a verification email will send to the user email.', 'classified-listing'),
    ),
    'social_login_shortcode'             => array(
        'title'       => __('Social Login shortcode', 'classified-listing'),
        'type'        => 'text',
        'css'         => 'width:100%;',
        'description' => __('Add your social login shortcode, which will run at <em style="color:red">rtcl_login_form</em> hook. <br><strong style="color: green;">We will support shortcode from any third party plugin.</strong><br> <strong>Example: [TheChamp-Login], [miniorange_social_login theme="default"]</strong>', 'classified-listing'),
    ),
    'verify_max_resend_allowed'          => array(
        'title'             => __('Max Re-send attempts', 'classified-listing'),
        'type'              => 'number',
        'default'           => 5,
        'css'               => 'width:50px',
        'wrapper_class'     => Functions::get_option_item('rtcl_account_settings', 'user_verification', null, 'checkbox') ? '' : 'hidden',
        'custom_attributes' => array(
            'step' => "1",
            'min'  => "1",
            'max'  => "15"
        ),
        'description'       => __('Max number of re-send requests a user can make, more than that, his account will be locked.', 'classified-listing'),
    ),
    'terms_conditions_section'           => array(
        'title'       => __('Terms and conditions', 'classified-listing'),
        'type'        => 'title',
        'description' => '',
    ),
    'enable_listing_terms_conditions'    => array(
        'title'       => __('Enable Listing Terms and conditions', 'classified-listing'),
        'type'        => 'checkbox',
        'description' => __("Display and require user agreement to Terms and Conditions for Listing form.", 'classified-listing')
    ),
    'enable_checkout_terms_conditions'   => array(
        'title'       => __('Enable Terms and conditions at checkout page', 'classified-listing'),
        'type'        => 'checkbox',
        'description' => __("Display and require user agreement to Terms and Conditions at checkout page.", 'classified-listing')
    ),
    'page_for_terms_and_conditions'      => array(
        'title'       => esc_html__('Terms and conditions page', 'classified-listing'),
        'description' => esc_html__("Choose a page to act as your Terms and conditions.", 'classified-listing'),
        'type'        => 'select',
        'class'       => 'rtcl-select2',
        'blank_text'  => __("Select a page", 'classified-listing'),
        'options'     => Functions::get_pages(),
        'css'         => 'min-width:300px;'
    ),
    'terms_and_conditions_checkbox_text' => array(
        'title'       => __('Terms and conditions', 'classified-listing'),
        'type'        => 'textarea',
        'default'     => Text::get_default_terms_and_conditions_checkbox_text(),
        'description' => __('Optionally add some text for the terms checkbox that customers must accept.', 'classified-listing')
    ),
    'privacy_policy_section'             => array(
        'title'       => esc_html__('Privacy policy', 'classified-listing'),
        'type'        => 'title',
        'description' => esc_html__("This section controls the display of your website privacy policy. The privacy notices below will not show up unless a privacy page is first set.", 'classified-listing'),
    ),
    'page_for_privacy_policy'            => array(
        'title'       => esc_html__('Privacy page', 'classified-listing'),
        'description' => esc_html__("Choose a page to act as your privacy policy.", 'classified-listing'),
        'type'        => 'select',
        'class'       => 'rtcl-select2',
        'blank_text'  => __("Select a page", 'classified-listing'),
        'options'     => Functions::get_pages(),
        'css'         => 'min-width:300px;'
    ),
    'registration_privacy_policy_text'   => array(
        'title'       => esc_html__('Registration privacy policy', 'classified-listing'),
        'type'        => 'textarea',
        'description' => esc_html__("Optionally add some text about your store privacy policy to show on account registration forms.", 'classified-listing'),
        'default'     => Text::get_default_registration_privacy_policy_text()
    ),
    'checkout_privacy_policy_text'       => array(
        'title'       => esc_html__('Checkout privacy policy', 'classified-listing'),
        'type'        => 'textarea',
        'description' => esc_html__("Optionally add some text about your store privacy policy to show during checkout.", 'classified-listing'),
        'default'     => Text::get_default_checkout_privacy_policy_text(),
    )
);

return apply_filters('rtcl_account_settings_options', $options);