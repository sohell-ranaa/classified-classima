<?php
/**
 * @author  RadiusTheme
 * @since   1.0
 * @version 1.0
 */

namespace radiustheme\Classima;

Helper::requires( 'class-tgm-plugin-activation.php' );
Helper::requires( 'tgm-config.php' );
Helper::requires( 'activation.php' );
Helper::requires( 'options/init.php' );
Helper::requires( 'rdtheme.php' );
Helper::requires( 'general.php' );
Helper::requires( 'scripts.php' );
Helper::requires( 'layout-settings.php' );
Helper::requires( 'ad-management.php' );

if ( class_exists( 'Rtcl' ) ) {
	Helper::requires( 'custom/functions.php', 'classified-listing' );
}