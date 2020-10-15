<?php
/**
 * @var         $orientation
 * @var         $style [classic , modern]
 * @var array   $classes
 * @var int     $active_count
 * @var WP_Term $selected_location
 * @var WP_Term $selected_category
 * @var boolean $can_search_by_location
 * @var boolean $can_search_by_category
 * @var boolean $can_search_by_listing_types
 * @var boolean $can_search_by_price
 */

use Rtcl\Helpers\Functions;
use Rtcl\Helpers\Text;

?>

<div class="<?php echo esc_attr(join(' ', $classes)); ?>">
    <form action="<?php echo esc_url(Functions::get_filter_form_url()) ?>"
          class="form-vertical rtcl-widget-search-form">
        <div class="row rtcl-no-margin active-<?php echo esc_attr($active_count); ?>">
            <?php if ($can_search_by_location) : ?>
                <div class="form-group ws-item ws-location col-sm-6 col-12">
                    <label for="rtcl-search-category-<?php echo esc_attr($id); ?>"><?php echo esc_html(Text::get_select_location_text()); ?></label>
                    <?php if ($style === 'suggestion') { ?>
                        <input type="text" data-type="location"
                               class="rtcl-autocomplete rtcl-location form-control"
                               placeholder="<?php echo esc_html(Text::get_select_location_text()) ?>"
                               value="<?php echo $selected_location ? $selected_location->name : '' ?>">
                        <input type="hidden" name="rtcl_location"
                               value="<?php echo $selected_location ? $selected_location->slug : '' ?>">
                        <?php
                    } elseif ($style === 'standard') {
                        wp_dropdown_categories(array(
                            'show_option_none'  => Text::get_select_location_text(),
                            'option_none_value' => '',
                            'taxonomy'          => rtcl()->location,
                            'name'              => 'rtcl_location',
                            'id'                => 'rtcl-location-search-' . $id,
                            'class'             => 'form-control rtcl-location-search',
                            'selected'          => get_query_var('rtcl_location'),
                            'hierarchical'      => true,
                            'value_field'       => 'slug',
                            'depth'             => Functions::get_location_depth_limit(),
                            'show_count'        => false,
                            'hide_empty'        => false,
                        ));

                    } elseif ($style === 'dependency') {

                        Functions::dropdown_terms(array(
                            'show_option_none' => Text::get_select_location_text(),
                            'taxonomy'         => rtcl()->location,
                            'name'             => 'l',
                            'class'            => 'form-control',
                            'selected'         => $selected_location ? $selected_location->term_id : 0
                        ));
                    } elseif ($style == 'popup') {
                        ?>
                        <div class="rtcl-search-input-button rtcl-search-input-location btn btn-primary">
                            <span class="search-input-label location-name">
                                <?php echo $selected_location ? esc_html($selected_location->name) : esc_html(Text::get_select_location_text()) ?>
                            </span>
                            <input type="hidden" class="rtcl-term-field" name="rtcl_location"
                                   value="<?php echo $selected_location ? esc_attr($selected_location->slug) : '' ?>">
                        </div>
                    <?php } ?>
                </div>
            <?php endif; ?>

            <?php if ($can_search_by_category) : ?>
                <div class="form-group ws-item ws-category col-sm-6 col-12">
                    <label><?php echo esc_html(Text::get_select_category_text()) ?></label>
                    <?php if ($style === 'standard' || $style === 'suggestion') {
                        wp_dropdown_categories(array(
                            'show_option_none'  => Text::get_select_category_text(),
                            'option_none_value' => '',
                            'taxonomy'          => rtcl()->category,
                            'name'              => 'rtcl_category',
                            'id'                => 'rtcl-category-search-' . $id,
                            'class'             => 'form-control rtcl-category-search',
                            'selected'          => get_query_var('rtcl_category'),
                            'hierarchical'      => true,
                            'value_field'       => 'slug',
                            'depth'             => Functions::get_category_depth_limit(),
                            'show_count'        => false,
                            'hide_empty'        => false,
                        ));
                    } elseif ($style === 'dependency') {
                        Functions::dropdown_terms(array(
                            'show_option_none'  => Text::get_select_category_text(),
                            'option_none_value' => -1,
                            'taxonomy'          => rtcl()->category,
                            'name'              => 'c',
                            'class'             => 'form-control rtcl-category-search',
                            'selected'          => $selected_category ? $selected_category->term_id : 0
                        ));
                    } elseif ($style == 'popup') { ?>
                        <div class="rtcl-search-input-button rtcl-search-input-category btn btn-primary">
                            <span class="search-input-label category-name">
                                <?php echo $selected_category ? esc_html($selected_category->name) : esc_html(Text::get_select_category_text()); ?>
                            </span>
                            <input type="hidden" name="rtcl_category" class="rtcl-term-field"
                                   value="<?php echo $selected_category ? esc_attr($selected_category->slug) : '' ?>">
                        </div>
                    <?php } ?>
                </div>
            <?php endif; ?>

            <?php if ($can_search_by_listing_types) : ?>
                <div class="form-group ws-item ws-type col-sm-6 col-12">
                    <label for="rtcl-search-type-<?php echo esc_attr($id); ?>"><?php esc_html_e('Select type', 'classified-listing'); ?></label>
                    <select class="form-control" id="rtcl-search-type-<?php echo esc_attr($id); ?>"
                            name="filters[ad_type]">
                        <option value=""><?php esc_html_e('Select type', 'classified-listing'); ?></option>
                        <?php
                        $listing_types = Functions::get_listing_types();
                        if (!empty($listing_types)) {
                            foreach ($listing_types as $key => $listing_type) {
                                ?>
                                <option value="<?php echo esc_attr($key) ?>" <?php echo isset($_GET['filters']['ad_type']) && trim($_GET['filters']['ad_type']) == $key ? ' selected' : null ?>><?php echo esc_html($listing_type) ?></option>
                                <?php
                            }
                        }
                        ?>
                    </select>
                </div>
            <?php endif; ?>

            <?php if ($can_search_by_price) : ?>
                <div class="form-group ws-item ws-price col-sm-6  col-12">
                    <label for="rtcl-search-price-range-<?php echo esc_attr($id); ?>"><?php esc_html_e('Price Range', 'classified-listing'); ?></label>
                    <div class="row" id="rtcl-search-price-range-<?php echo esc_attr($id); ?>">
                        <div class="col-md-6 col-xs-6">
                            <input type="text" name="filters[price][min]" class="form-control"
                                   placeholder="<?php esc_html_e('min', 'classified-listing'); ?>"
                                   value="<?php if (isset($_GET['filters']['price'])) {
                                       echo esc_attr($_GET['filters']['price']['min']);
                                   } ?>">
                        </div>
                        <div class="col-md-6 col-xs-6">
                            <input type="text" name="filters[price][max]" class="form-control"
                                   placeholder="<?php esc_html_e('max', 'classified-listing'); ?>"
                                   value="<?php if (isset($_GET['filters']['price'])) {
                                       echo esc_attr($_GET['filters']['price']['max']);
                                   } ?>">
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="form-group ws-item ws-text col-sm-6">
                <div class="rt-autocomplete-wrapper">
                    <input type="text" name="q" data-type="listing" class="rtcl-autocomplete form-control"
                           placeholder="<?php esc_html_e('Enter your keyword here ...', 'classified-listing'); ?>"
                           value="<?php if (isset($_GET['q'])) {
                               echo esc_attr($_GET['q']);
                           } ?>">
                </div>
            </div>

            <div class="form-group ws-item ws-button  col-sm-6">
                <div class="rtcl-action-buttons text-right">
                    <button type="submit"
                            class="btn btn-primary"><?php esc_html_e('Search', 'classified-listing'); ?></button>
                </div>
            </div>
        </div>
        <?php do_action('rtcl_widget_search_' . $orientation . '_form', $data) ?>
    </form>
</div>
