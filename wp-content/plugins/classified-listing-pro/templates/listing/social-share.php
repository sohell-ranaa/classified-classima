<?php

/**
 *
 * @author        RadiusTheme
 * @package    classified-listing/templates
 * @version     1.0.0
 *
 * @var array $misc_settings
 */

if ( in_array( 'facebook', $misc_settings['social_services'] ) ) : ?>
	<a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo esc_url($url); ?>" target="_blank" rel="nofollow"><span class="rtcl-icon rtcl-icon-facebook"></span></a>
<?php endif; ?>

<?php if ( in_array( 'twitter', $misc_settings['social_services'] ) )  : ?>
	<a href="https://twitter.com/intent/tweet?text=<?php echo urlencode($title); ?>&amp;url=<?php echo esc_url($url); ?>" target="_blank" rel="nofollow"><span class="rtcl-icon rtcl-icon-twitter"></span></a>
<?php endif; ?>

<?php if ( in_array( 'linkedin', $misc_settings['social_services'] ) )  : ?>
	<a href="https://www.linkedin.com/shareArticle?url=<?php echo esc_url($url); ?>&amp;title=<?php echo urlencode($title); ?>" target="_blank" rel="nofollow"><span class="rtcl-icon rtcl-icon-linkedin"></span></a>
<?php endif; ?>

<?php if ( in_array( 'pinterest', $misc_settings['social_services'] ) )  : ?>
	<a href="https://pinterest.com/pin/create/button/?url=<?php echo esc_url($url); ?>&amp;media=<?php echo esc_url($thumbnail); ?>&amp;description=<?php echo urlencode($title); ?>" target="_blank" rel="nofollow"><span class="rtcl-icon rtcl-icon-pinterest-circled"></span></a>
<?php endif; ?>

<?php if ( in_array( 'whatsapp', $misc_settings['social_services'] ) && wp_is_mobile())  :?>
	<a href="https://wa.me/?text=<?php echo urlencode($title . ' '.$url); ?>" data-action="share/whatsapp/share" target="_blank" rel="nofollow"><i class="rtcl-icon rtcl-icon-whatsapp"></i></a>
<?php endif; ?>

<?php if ( in_array( 'gplus', $misc_settings['social_services'] ) )  : ?>
    <a href="https://plus.google.com/share?url=<?php echo esc_url($url); ?>" target="_blank" rel="nofollow"><span class="rtcl-icon rtcl-icon-gplus"></span></a>
<?php endif; ?>
