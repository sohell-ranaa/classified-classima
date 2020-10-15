<?php

namespace RtclStore\Resources;

class Options
{

    static function get_store_orderby_options() {
        $options = array(
            'title-desc' => __("Z to A ( title )", 'classified-listing'),
            'title-asc'  => __("A to Z ( title )", 'classified-listing'),
            'date-desc'  => __("Recently added ( latest )", 'classified-listing'),
            'date-asc'   => __("Date added ( oldest )", 'classified-listing')
        );

        return apply_filters('rtcl_store_orderby_options', $options);
    }

    static function store_social_media_options() {
        $options = [
            'facebook' => __("Facebook", 'classified-listing-store'),
            'twitter'  => __("Twitter", 'classified-listing-store'),
            'youtube'  => __("Youtube", 'classified-listing-store'),
            'linkedin' => __("LinkedIn", 'classified-listing-store')
        ];

        return apply_filters('rtcl_store_social_media_options', $options);
    }

    public static function store_open_hour_days() {
        $days = [
            'sunday'    => __("Sunday", "classified-listing-store"),
            'monday'    => __("Monday", "classified-listing-store"),
            'tuesday'   => __("Tuesday", "classified-listing-store"),
            'wednesday' => __("Wednesday", "classified-listing-store"),
            'thursday'  => __("Thursday", "classified-listing-store"),
            'friday'    => __("Friday", "classified-listing-store"),
            'saturday'  => __("Saturday", "classified-listing-store"),
        ];

        return apply_filters('rtcl_store_open_hour_days', $days);
    }

}