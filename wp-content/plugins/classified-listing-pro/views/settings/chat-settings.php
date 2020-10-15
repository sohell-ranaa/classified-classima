<?php

use Rtcl\Helpers\Link;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Settings for Payment
 */
$options = array(
    'ls_section'                        => array(
        'title'       => __('Chat settings', 'classified-listing'),
        'type'        => 'title',
        'description' => sprintf(__('Regenerate Chat Table <a href="%s" onClick="return confirm(\'Do you really want to Confirm this booking\')">Click Here.</a> <span style="color:red">This will remove all chat history.</span>', 'classified-listing'), add_query_arg([
            rtcl()->nonceId         => wp_create_nonce(rtcl()->nonceText),
            'rtcl_regenerate_chat_table' => ''
        ], Link::get_current_url())),
    ),
    'enable'                            => array(
        'title'       => __('Chat', 'classified-listing'),
        'label'       => __('Enable', 'classified-listing'),
        'type'        => 'checkbox',
        'description' => __('Enable Chat option', 'classified-listing'),
    ),
    'unread_message_email'              => array(
        'title'       => __('Unread Message Email', 'classified-listing'),
        'label'       => __('Enable', 'classified-listing'),
        'type'        => 'checkbox',
        'description' => __('Enable email for unread message trace to receiver, if receiver at offline <span style="color: red">(Only for the first message)</span>.', 'classified-listing')
    ),
    'remove_inactive_conversation_duration' => array(
        'title'       => __('Delete inactive conversation (in days)', 'classified-listing'),
        'type'        => 'number',
        'default'     => 30,
        'description' => __('Auto remove inactive conversation which are last active in given days ago <span style="color: red">(Leave it blank to alive conversation forever.)</span>.', 'classified-listing'),
    )
);

return apply_filters('rtcl_chat_settings_options', $options);