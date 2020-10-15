<?php

namespace Rtcl\Controllers\Hooks;

use Rtcl\Helpers\Functions;

class Hooks {

    public static function init()
    {
        add_action('ajax_query_attachments_args', array(__CLASS__, 'remove_ajax_query_attachments_args'));
        add_action('load-upload.php', array(__CLASS__, 'remove_attachments_load_media'));
    }


    public static function remove_attachments_load_media()
    {
        add_action('pre_get_posts', array(__CLASS__, 'hide_media'), 10, 1);
    }

	/**
	 * @param $query \WP_Query
	 *
	 * @return mixed
	 */
	public static function hide_media($query)
    {
        global $pagenow;

        // there is no need to check for update.php as we are already hooking to it, but anyway
        if ('upload.php' == $pagenow && is_admin() && $query->is_main_query()) {
            if (!empty($excluded_ids = Functions::all_ids_for_remove_attachment())) {
                $query->set('post_parent__not_in', $excluded_ids);
            }
        }

        return $query;
    }

	/**
	 * @param $query \WP_Query
	 *
	 * @return mixed
	 */
	public static function remove_ajax_query_attachments_args($query)
    {
        if ($query['post_type'] == 'attachment') {
            if (!empty($excluded_ids = Functions::all_ids_for_remove_attachment())) {
                $query['post_parent__not_in'] = $excluded_ids;
            }
        }
        return $query;
    }

}