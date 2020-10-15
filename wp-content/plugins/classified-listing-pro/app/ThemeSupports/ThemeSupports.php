<?php

namespace Rtcl\ThemeSupports;

class ThemeSupports
{
    /**
     * Current Theme name
     *
     * @var string
     */
    private static $current_theme = '';

    static function init() {
        self::$current_theme = get_template();
        do_action('rtcl_add_theme_support', self::$current_theme);
    }

}