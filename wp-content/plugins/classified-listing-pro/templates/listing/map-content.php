<?php
/**
 * Map content
 *
 * @author     RadiusTheme
 * @package    classified-listing/templates
 * @version    1.0.0
 *
 * @var $listing Listing
 */


use Rtcl\Helpers\Functions;
use Rtcl\Models\Listing;

if (!isset($listing)) {
    global $listing;
}

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
} ?>

<a href="<?php $listing->the_permalink() ?>" class="rtcl-media text-overflow"
   target="_blank"><?php $listing->the_thumbnail() ?></a>
<div class="flex-right">
    <h5 class="rtcl-map-item-title">
        <a class="text-overflow" href="<?php $listing->the_permalink() ?>"
           target="_blank"><?php $listing->the_title() ?>
        </a></h5>
    <div class="bottom-rtcl-meta flex-wrap">
        <div class="price"><?php Functions::print_html($listing->get_the_price()) ?></div>
    </div>
</div>
