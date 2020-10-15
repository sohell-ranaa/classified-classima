<?php
/**
 * @var string $category_filter
 * @var string $location_filter
 * @var string $ad_type_filter
 * @var string $custom_field_filter
 * @var string $price_filter
 * @var Filter $object
 */

use Rtcl\Helpers\Functions;
use Rtcl\Widgets\Filter;

?>

<div class="panel-block">
    <form class="rtcl-filter-form"
          action="<?php echo esc_url(Functions::get_filter_form_url()) ?>">
        <?php do_action('rtcl_widget_before_filter_form', $object) ?>
        <div class="ui-accordion">
            <?php Functions::print_html($ad_type_filter, true); ?>
            <?php Functions::print_html($category_filter, true); ?>
            <?php Functions::print_html($location_filter, true); ?>
            <?php Functions::print_html($custom_field_filter, true); ?>
            <?php Functions::print_html($price_filter, true); ?>
            <?php do_action('rtcl_widget_filter_form', $object) ?>
        </div>
        <?php do_action('rtcl_widget_after_filter_form', $object) ?>
    </form>
</div>
