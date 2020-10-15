<?php

use Rtcl\Helpers\Functions;
use Rtcl\Resources\Options;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Settings for misc
 */
$options = array(
    'img_gallery_section'          => array(
        'title' => __('Image Sizes', 'classified-listing'),
        'type'  => 'title',
    ),
    'image_size_gallery'           => array(
        'title'       => __('Galley Slider', 'classified-listing'),
        'type'        => 'image_size',
        'default'     => array('width' => 800, 'height' => 380, 'crop' => 'yes'),
        'options'     => array(
            'width'  => __('Width', 'classified-listing'),
            'height' => __('Height', 'classified-listing'),
            'crop'   => __('Hard Crop', 'classified-listing'),
        ),
        'description' => __('This image size is being used in the image slider on Listing details pages.', "classified-listing")
    ),
    'image_size_gallery_thumbnail' => array(
        'title'       => __('Gallery Thumbnail', 'classified-listing'),
        'type'        => 'image_size',
        'default'     => array('width' => 150, 'height' => 105, 'crop' => 'yes'),
        'options'     => array(
            'width'  => __('Width', 'classified-listing'),
            'height' => __('Height', 'classified-listing'),
            'crop'   => __('Hard Crop', 'classified-listing'),
        ),
        'description' => __('Gallery thumbnail image size', "classified-listing")
    ),
    'image_size_thumbnail'         => array(
        'title'       => __('Thumbnail', 'classified-listing'),
        'type'        => 'image_size',
        'default'     => array('width' => 300, 'height' => 240, 'crop' => 'yes'),
        'options'     => array(
            'width'  => __('Width', 'classified-listing'),
            'height' => __('Height', 'classified-listing'),
            'crop'   => __('Hard Crop', 'classified-listing'),
        ),
        'description' => __('Listing thumbnail size will use all listing page', "classified-listing")
    ),
    'image_allowed_type'           => array(
        'title'   => __('Allowed Image type', 'classified-listing'),
        'type'    => 'multi_checkbox',
        'default' => array('png', 'jpeg', 'jpg'),
        'options' => array(
            'png'  => __('PNG', 'classified-listing'),
            'jpg'  => __('JPG', 'classified-listing'),
            'jpeg' => __('JPEG', 'classified-listing'),
        )
    ),
    'image_allowed_memory'         => array(
        'title'       => __('Allowed Image memory size', 'classified-listing'),
        'type'        => 'number',
        'default'     => 2,
        'description' => sprintf(__('Enter the image memory size, like 2 for 2 MB (only number with out MB) <br><span style="color: red">Your hosting allowed maximum %s</span>',
            'classified-listing'), Functions::formatBytes(Functions::get_wp_max_upload()))
    ),
    'image_edit_cap'               => array(
        'title'   => __('User can edit image', 'classified-listing'),
        'type'    => 'checkbox',
        'default' => 'yes',
        'label'   => __('User can edit image size , can crop , can make feature', 'classified-listing')
    ),
    'placeholder_image'            => array(
        'title' => __('Place holder image', 'classified-listing'),
        'type'  => 'image',
        'label' => __('Select an Image to display as placeholder if have no image.', 'classified-listing')
    ),
    'single_listing_section'       => array(
        'title' => __('Single Listing', 'classified-listing'),
        'type'  => 'title',
    ),
    'disable_gallery_slider'       => array(
        'title' => __('Disable gallery slider', 'classified-listing'),
        'type'  => 'checkbox',
        'label' => __('Disable', 'classified-listing'),
    ),
    'disable_gallery_zoom'         => array(
        'title' => __('Disable gallery zoom', 'classified-listing'),
        'type'  => 'checkbox',
        'label' => __('Disable', 'classified-listing'),
    ),
    'disable_gallery_photoswipe'   => array(
        'title'       => __('Disable gallery lightbox', 'classified-listing'),
        'type'        => 'checkbox',
        'label'       => __('Disable', 'classified-listing'),
        'description' => __('Disable gallery lightbox (PopUp lightbox Gallery)', 'classified-listing'),
    ),
    'social_section'               => array(
        'title' => __('Social Share buttons', 'classified-listing'),
        'type'  => 'title',
    ),
    'social_services'              => array(
        'title'   => __('Enable services', 'classified-listing'),
        'type'    => 'multi_checkbox',
        'default' => array('facebook', 'twitter', 'gplus'),
        'options' => array(
            'facebook'  => __('Facebook', 'classified-listing'),
            'twitter'   => __('Twitter', 'classified-listing'),
            'linkedin'  => __('Linkedin', 'classified-listing'),
            'pinterest' => __('Pinterest', 'classified-listing'),
            'whatsapp'  => __('WhatsApp (Only at mobile)', 'classified-listing'),
            'gplus'     => __('Google plus', 'classified-listing'),
        )
    ),
    'social_pages'                 => array(
        'title'   => __('Show buttons in', 'classified-listing'),
        'type'    => 'multi_checkbox',
        'default' => array('listing'),
        'options' => array(
            'listing'    => __('Listing detail page', 'classified-listing'),
            'listings'   => __('Listings page', 'classified-listing'),
            'categories' => __('Categories page', 'classified-listing'),
            'locations'  => __('Locations page', 'classified-listing')
        )
    ),
    'recaptcha_section'            => array(
        'title' => __('reCAPTCHA settings', 'classified-listing'),
        'type'  => 'title',
    ),
    'recaptcha_forms'              => array(
        'title'   => __('Enable reCAPTCHA in', 'classified-listing'),
        'type'    => 'multi_checkbox',
        'options' => Options::get_recaptcha_form_list()
    ),
    'recaptcha_site_key'           => array(
        'title' => __('Site key', 'classified-listing'),
        'type'  => 'text',
    ),
    'recaptcha_secret_key'         => array(
        'title' => __('Secret key', 'classified-listing'),
        'type'  => 'text'
    ),
    'map_section'                  => array(
        'title' => __('Map settings', 'classified-listing'),
        'type'  => 'title',
    ),
    'map_api_key'                  => array(
        'title' => __('Map API key', 'classified-listing'),
        'type'  => 'text',
    ),
    'map_zoom_level'               => array(
        'title' => __('Map zoom level', 'classified-listing'),
        'type'  => 'number',
    ),
);

return apply_filters('rtcl_misc_settings_options', $options);
