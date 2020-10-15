<?php

namespace Rtcl\Resources;


use Rtcl\Helpers\Functions;
use Rtcl\Helpers\Text;
use Rtcl\Models\Listing;
use Rtcl\Models\TermWalkerDropDown;

class ListingDetails
{

    static function static_report($post) {
        $moderator_notification = absint(get_post_meta($post->ID, '_notification_by_moderator', true));
        $visitor_notification = absint(get_post_meta($post->ID, '_notification_by_visitor', true));
        $aReport_notification = absint(get_post_meta($post->ID, '_abuse_report_by_visitor', true));
        ?>
        <div class="rtcl-action-wrap">
            <div class="send-user-notification">
                <a id="send-email-to-user"
                   class="button button-primary button-large"><?php _e("Send Email to User", 'classified-listing') ?></a>
            </div>
        </div>
        <div class="rtcl-report-wrap">
            <ul>
                <li><?php _e("Notification by Moderator", 'classified-listing') ?>:
                    <strong><?php echo $moderator_notification; ?></strong>
                </li>
                <li><?php _e("Notification by Visitor", 'classified-listing') ?>:
                    <strong><?php echo $visitor_notification; ?></strong></li>
                <li><?php _e("Abuse Report by Visitor", 'classified-listing') ?>:
                    <strong><?php echo $aReport_notification; ?></strong></li>
            </ul>
        </div>
        <?php
    }

    static function listing_details($post = null) {
        $price = get_post_meta($post->ID, 'price', true);
        $listing = rtcl()->factory->get_listing($post->ID);
        $ad_type_selected = Functions::is_ad_type_disabled() ? null : $listing->get_ad_type();
        $price_type = get_post_meta($post->ID, 'price_type', true);
        $child_cat_id = 0;
        $category_id = 0;
        if (!Functions::is_ad_type_disabled()):
            ?>
            <div class="form-group row">
                <label for="rtcl-ad-type"
                       class="col-md-2 col-12 col-form-label"><?php esc_html_e('Listing Type', 'classified-listing'); ?><span class="require-star">*</span></label>
                <div class="col-md-10 col-12">
                    <select class="rtcl-select2 form-control" id="rtcl-ad-type" name="ad_type" required>
                        <option value=""><?php esc_html_e("Select a type", "classified-listing") ?></option>
                        <?php
                        $adTypes = Functions::get_listing_types();
                        foreach ($adTypes as $ad_type_id => $ad_type) {
                            $slt = $ad_type_id === $ad_type_selected ? ' selected' : null;
                            $ad_type_text = Text::string_translation($ad_type);
                            echo "<option value='{$ad_type_id}'{$slt}>{$ad_type_text}</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
        <?php endif; ?>
        <div class="form-group row">
            <label for="rtcl-category"
                   class="col-md-2 col-12 col-form-label"><?php esc_html_e('Category', 'classified-listing'); ?><span class="require-star">*</span></label>
            <div class="col-md-10 col-12">
                <div id="rtcl-category-wrap">
                    <?php
                    $selected_cat_ids = $listing->get_ancestors_category_ids_with_last_child();
                    $parents_cats = Functions::get_one_level_categories(0, $ad_type_selected);
                    $parent_cat_id = 0;
                    $current_category = $listing->get_current_selected_category();
                    $current_category_id = $current_category ? $current_category->term_id : null;
                    ?>
                    <select class="form-control" name="rtcl-category-of-type" id="rtcl-category-of-type" required>
                        <option value=""><?php echo esc_html(Text::get_select_category_text()) ?></option>
                        <?php
                        if(!empty($parents_cats)) {
                            foreach ($parents_cats as $cat) {
                                $slt = '';
                                if (in_array($cat->term_id, $selected_cat_ids)) {
                                    $slt = ' selected';
                                    $parent_cat_id = $cat->term_id;
                                }
                                echo "<option value='{$cat->term_id}'{$slt}>{$cat->name}</option>";
                            }
                        }
                        ?>
                    </select>
                    <?php
                    while ($parent_cat_id > 0) {
                        $cats = Functions::get_one_level_categories($parent_cat_id);
                        $old_cat = $parent_cat_id;
                        $parent_cat_id = 0;
                        if (!empty($cats)) {
                            echo '<select class="form-control" id="rtcl-category-of-' . $old_cat . '" name="rtcl-category-of-' . $old_cat . '" required>';
                            echo '<option value="">' . esc_html(Text::get_select_category_text()) . '</option>';
                            $parent_cat_id = 0;
                            foreach ($cats as $cat) {
                                $slt = '';
                                if (in_array($cat->term_id, $selected_cat_ids)) {
                                    $slt = ' selected';
                                    $parent_cat_id = $cat->term_id;
                                }
                                echo "<option value='{$cat->term_id}'{$slt}>{$cat->name}</option>";
                            }
                            echo '</select>';
                        }
                    }
                    ?>

                </div>
                <input type="hidden" value="<?php echo esc_attr($current_category_id); ?>" name="rtcl_category" id="rtcl-category-input">
            </div>
        </div>
        <?php if (!Functions::is_price_disabled()): ?>
            <div id="rtcl-form-price-wrap"
                 style="<?php echo(!Functions::is_price_disabled() && $ad_type_selected === "job" ? esc_attr("display:none") : null) ?>">
                <div class="form-group row">
                    <label for="rtcl-price-type"
                           class="col-md-2 col-12 col-form-label"><?php esc_html_e("Price Type", 'classified-listing'); ?></label>
                    <div class="col-md-10 col-12">
                        <select class="form-control" id="rtcl-price-type" name="price_type">
                            <?php
                            $price_types = Options::get_price_types();
                            foreach ($price_types as $key => $type) {
                                $slt = $price_type == $key ? " selected" : null;
                                echo "<option value='{$key}'{$slt}>{$type}</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="row" id="rtcl-price-row">
                    <div id="rtcl-price-wrap"
                         class="form-group col-12 col-md-<?php echo esc_attr(($listing && $listing->has_price_units()) || ($category_id && Functions::category_has_price_units($category_id)) ? '6' : '12'); ?>">
                        <label for="rtcl-category"><?php echo sprintf('<span class="price-label">%s [%s]</span>',
                                esc_html__("Price", 'classified-listing'),
                                Functions::get_currency_symbol()
                            ); ?><span class="require-star">*</span></label>
                        <input type="text"
                               class="form-control"
                               value="<?php echo $listing->get_price(); ?>"
                               name="price"
                               id="rtcl-price"<?php echo esc_attr(!$price_type || $price_type == 'fixed' ? " required" : '') ?>>
                    </div>
                    <?php do_action('rtcl_listing_form_price_unit', $listing, $current_category_id); ?>
                </div>
            </div>
        <?php endif; ?>
        <div id="rtcl-custom-fields-list" data-post_id="<?php echo $post->ID; ?>">
            <?php
            do_action('wp_ajax_rtcl_custom_fields_listings', $post->ID, $current_category_id); ?>
        </div>

        <?php
    }

    static function contact_details($post = null) {
        $fields = Options::getContactDetailsFields();
        $location_id = $sub_location_id = $sub_sub_location_id = 0;
        $selected_locations = array();
        if ($post) {
            $selected_locations = wp_get_object_terms($post->ID, rtcl()->location, array('fields' => 'ids'));
        }
        $state_text = Text::location_level_first();
        $city_text = Text::location_level_second();
        $town_text = Text::location_level_third();
        $hidden_fields = Functions::get_option_item('rtcl_moderation_settings', 'hide_form_fields', array());
        if (!in_array('location', $hidden_fields)):
            ?>
            <div class="form-group row" id="rtcl-location-row">
                <label for='rtcl-location'
                       class='col-md-2 col-12 col-form-label'><?php echo $state_text; ?><span
                            class="require-star">*</span></label>
                <div class='col-md-10 col-12'>
                    <select id="rtcl-location" name="location"
                            class="rtcl-select2 rtcl-select form-control rtcl-map-field" required>
                        <option value="">--<?php _e('Select location', 'classified-listing') ?>--</option>
                        <?php
                        $locations = Functions::get_one_level_locations();
                        if (!empty($locations)) {
                            foreach ($locations as $location) {
                                $slt = '';
                                if (in_array($location->term_id, $selected_locations)) {
                                    $location_id = $location->term_id;
                                    $slt = " selected";
                                }
                                echo "<option value='{$location->term_id}'{$slt}>{$location->name}</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>
            <?php
            $sub_locations = array();
            if ($location_id) {
                $sub_locations = Functions::get_one_level_locations($location_id);
            }
            ?>
            <div class="form-group row<?php echo empty($sub_locations) ? ' rtcl-hide' : ''; ?>"
                 id="sub-location-row">
                <label for='rtcl-sub-location'
                       class='col-md-2 col-12 col-form-label'><?php echo $city_text ?><span
                            class="require-star">*</span></label>
                <div class='col-md-10 col-12'>
                    <select id="rtcl-sub-location" name="sub_location"
                            class="rtcl-select2 rtcl-select form-control rtcl-map-field" required>
                        <option value="">--<?php _e('Select location', 'classified-listing') ?>--</option>
                        <?php
                        if (!empty($sub_locations)) {
                            foreach ($sub_locations as $location) {
                                $slt = '';
                                if (in_array($location->term_id, $selected_locations)) {
                                    $sub_location_id = $location->term_id;
                                    $slt = " selected";
                                }
                                echo "<option value='{$location->term_id}'{$slt}>{$location->name}</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>
            <?php
            $sub_sub_locations = array();
            if ($sub_location_id) {
                $sub_sub_locations = Functions::get_one_level_locations($sub_location_id);
            }
            ?>
            <div class="form-group row<?php echo empty($sub_sub_locations) ? ' rtcl-hide' : ''; ?>"
                 id="sub-sub-location-row">
                <label for='rtcl-sub-sub-location'
                       class='col-md-2 col-12 col-form-label'><?php echo $town_text ?><span
                            class="require-star">*</span></label>
                <div class='col-md-10 col-12'>
                    <select id="rtcl-sub-sub-location" name="sub_sub_location"
                            class="rtcl-select2 rtcl-select form-control rtcl-map-field" required>
                        <option value="">--<?php _e('Select location', 'classified-listing') ?>--</option>
                        <?php
                        if (!empty($sub_sub_locations)) {
                            foreach ($sub_sub_locations as $location) {
                                $slt = '';
                                if (in_array($location->term_id, $selected_locations)) {
                                    $slt = " selected";
                                }
                                echo "<option value='{$location->term_id}'{$slt}>{$location->name}</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>
        <?php endif; ?>
        <?php
        foreach ($fields as $key => $field):
            if (!in_array($key, $hidden_fields)):
                $value = $post ? esc_attr(get_post_meta($post->ID, $key, true)) : '';
                $label = !empty($field['label']) ? $field['label'] : null;
                $id = !empty($field['id']) ? $field['id'] : $key;
                $type = !empty($field['type']) ? $field['type'] : 'text';
                $class = !empty($field['class']) ? " " . $field['class'] : '';
                $holderId = !empty($field['holderId']) ? "id='" . $field['holderId'] . "'" : '';
                $holderClass = !empty($field['holderClass']) ? " " . $field['holderClass'] : '';
                ?>
                <div class="form-group row<?php echo esc_attr($holderClass); ?>" <?php echo $holderId; ?>>
                    <label for='<?php echo $id; ?>'
                           class='col-md-2 col-12 col-form-label'><?php echo esc_html($label) ?></label>
                    <div class='col-md-10 col-12'>
                        <?php
                        switch ($type) {
                            case 'text':
                            case 'url':
                            case 'email':
                                echo "<input type='{$type}' id='{$id}' name='{$key}' class='rtcl-text form-control{$class}' value='{$value}' />";
                                break;
                            case 'textarea':
                                echo "<textarea class='rtcl-textarea form-control{$class}' id='{$id}' name='{$key}' rows='2'>{$value}</textarea>";
                                break;
                            case 'select':
                                echo "<select id='{$id}' name='{$key}' class='rtcl-select2 rtcl-select form-control{$class}'>";
                                if (!empty($field['blank'])) {
                                    echo "<option value=''> --" . $field['blank'] . "-- </option>";
                                }
                                if (!empty($field['options'])) {
                                    foreach ($field['options'] as $option) {
                                        $slt = $option->id == $value ? " selected" : '';
                                        echo "<option value='{$option->id}'{$slt}>{$option->name}</option>";
                                    }
                                }
                                echo "</select>";
                                break;
                        }
                        ?>
                    </div>
                </div>
            <?php
            endif;
        endforeach;
        if (Functions::get_option_item('rtcl_moderation_settings', 'has_map', false, 'checkbox')):
            $hide_map = get_post_meta($post->ID, 'hide_map', true);
            $latitude = get_post_meta($post->ID, 'latitude', true);
            $longitude = get_post_meta($post->ID, 'longitude', true);
            $address = get_post_meta($post->ID, 'address', true);
            ?>
            <div class="rtcl-map" data-type="input">
                <div class="marker" data-latitude="<?php echo esc_attr($latitude); ?>"
                     data-longitude="<?php echo esc_attr($longitude); ?>"
                     data-address="<?php echo esc_attr($address); ?>"><?php echo esc_html($address); ?></div>
            </div>
            <div class="rtcl-form-check">
                <input class="rtcl-form-check-input" id="rtcl-hide-map"
                       type="checkbox" name="hide_map" value="1" <?php checked($hide_map, 1); ?>>
                <label class="rtcl-form-check-label" for="rtcl-hide-map"><?php _e("Don't show the Map",
                        "classified-listing") ?></label>
            </div>
            <!-- Map Hidden field-->
            <input type="hidden" name="latitude" value="<?php echo esc_attr($latitude); ?>" id="rtcl-latitude"/>
            <input type="hidden" name="longitude" value="<?php echo esc_attr($longitude); ?>" id="rtcl-longitude"/>
        <?php endif;
    }
}