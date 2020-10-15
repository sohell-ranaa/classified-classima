<?php
/**
 *
 * @author        RadiusTheme
 * @package    classified-listing/templates
 * @version     1.0.0
 */


if ( $has_map ):?>
    <div class="embed-responsive embed-responsive-16by9 mt-3">
        <div class="rtcl-map embed-responsive-item">
            <div class="marker" data-latitude="<?php echo esc_attr($latitude); ?>" data-longitude="<?php echo esc_attr($longitude); ?>" data-address="<?php echo esc_attr($address); ?>"><?php echo esc_html($address); ?></div>
        </div>
    </div>
<?php endif;