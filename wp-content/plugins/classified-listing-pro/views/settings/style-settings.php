<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Settings for Style
 */
$options = array(
    'gs_section'          => array(
        'title'       => __('Global Style', 'classified-listing'),
        'type'        => 'title',
        'description' => '',
    ),
    'primary'             => array(
        'title'   => __('Primary', 'classified-listing'),
        'type'    => 'color',
    ),
    'link'                => array(
        'title' => __('Link color', 'classified-listing'),
        'type'  => 'color',
    ),
    'link_hover'          => array(
        'title' => __('Link color on hover', 'classified-listing'),
        'type'  => 'color',
    ),
    'button'              => array(
        'title' => __('Button color', 'classified-listing'),
        'type'  => 'color',
    ),
    'button_hover'        => array(
        'title' => __('Button color on hover', 'classified-listing'),
        'type'  => 'color',
    ),
    'button_text'         => array(
        'title' => __('Button text color', 'classified-listing'),
        'type'  => 'color',
    ),
    'button_hover_text'   => array(
        'title' => __('Button text color on hover', 'classified-listing'),
        'type'  => 'color',
    ),
    'single_page_section' => array(
        'title' => __('Single listing page style', 'classified-listing'),
        'type'  => 'title',
    ),
    'lbl_section'         => array(
        'title' => __('Label Style', 'classified-listing'),
        'type'  => 'title',
    ),
    'top'                 => array(
        'title' => __('Top label background color', 'classified-listing'),
        'type'  => 'color',
    ),
    'top_text'            => array(
        'title' => __('Top label text color', 'classified-listing'),
        'type'  => 'color',
    ),
    'feature'             => array(
        'title' => __('Feature label background color', 'classified-listing'),
        'type'  => 'color',
    ),
    'feature_text'        => array(
        'title' => __('Feature label text color', 'classified-listing'),
        'type'  => 'color',
    ),
    'popular'             => array(
        'title' => __('Popular label background color', 'classified-listing'),
        'type'  => 'color',
    ),
    'popular_text'        => array(
        'title' => __('Popular label text color', 'classified-listing'),
        'type'  => 'color',
    ),
    'new'                 => array(
        'title' => __('New label background color', 'classified-listing'),
        'type'  => 'color',
    ),
    'new_text'            => array(
        'title' => __('New label text color', 'classified-listing'),
        'type'  => 'color',
    ),
    'bump_up'             => array(
        'title' => __('BumpUp label background color', 'classified-listing'),
        'type'  => 'color',
    ),
    'bump_up_text'        => array(
        'title' => __('BumpUp text color', 'classified-listing'),
        'type'  => 'color',
    ),
);

return apply_filters('rtcl_style_settings_options', $options);
