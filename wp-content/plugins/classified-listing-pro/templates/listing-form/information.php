<?php
/**
 * Login Form Information
 *
 * @author        RadiusTheme
 * @package       classified-listing/templates
 * @version       1.0.0
 *
 * @var int    $title_limit
 * @var array  $hidden_fields
 * @var string $selected_type
 * @var string $price_type
 * @var string $price
 * @var string $post_content
 * @var string $editor
 * @var int    $category_id
 * @var int    $description_limit
 */

use Rtcl\Helpers\Functions;
use Rtcl\Resources\Options;

?>
<div class="rtcl-post-details rtcl-post-section">
    <div class="rtcl-post-section-title">
        <h3>
            <i class="rtcl-icon rtcl-icon-picture"></i><?php esc_html_e("Listing Information", "classified-listing"); ?>
        </h3>
    </div>
    <div class="form-group">
        <label for="rtcl-title"><?php esc_html_e('Title', 'classified-listing'); ?><span
                    class="require-star">*</span></label>
        <input type="text"
            <?php echo $title_limit ? 'data-max-length="3" maxlength="' . $title_limit . '"' : ''; ?>
               class="rtcl-select2 form-control"
               value="<?php echo esc_attr($title); ?>"
               id="rtcl-title"
               name="title"
               required/>
        <?php
        if ($title_limit) {
            echo sprintf('<div class="rtcl-hints">%s</div>',
                apply_filters('rtcl_listing_title_character_limit_hints', sprintf(__("Character limit <span class='target-limit'>%s</span>", 'classified-listing'), $title_limit)
                ));
        }
        ?>
    </div>
    <?php if (!in_array('price', $hidden_fields) && $selected_type !== 'job'): ?>
        <div class="row" id="rtcl-form-price-wrap">
            <?php if (!in_array('price_type', $hidden_fields)): ?>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="rtcl-category"><?php esc_html_e('Price Type', 'classified-listing'); ?><span
                                    class="require-star">*</span></label>
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
            <?php endif; ?>
            <div class="col-md-<?php echo esc_attr(in_array('price_type', $hidden_fields) ? '12' : '6'); ?>">
                <div id="rtcl-price-row" class="row">
                    <div class="form-group col-12 col-md-<?php echo esc_attr(($listing && $listing->has_price_units()) || ($category_id && Functions::category_has_price_units($category_id)) ? '6' : '12'); ?>">
                        <label for="rtcl-category"><?php echo sprintf('<span class="price-label">%s [%s]</span>',
                                __("Price", 'classified-listing'),
                                Functions::get_currency_symbol()
                            ); ?><span
                                    class="require-star">*</span></label>
                        <input type="text"
                               class="form-control"
                               value="<?php echo $listing ? esc_attr($listing->get_price()) : ''; ?>" name="price"
                               id="rtcl-price"<?php echo esc_attr(!$price_type || $price_type == 'fixed' ? " required" : '') ?>>
                    </div>
                    <?php do_action('rtcl_listing_form_price_unit', $listing, $category_id); ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <div id="rtcl-custom-fields-list" data-post_id="<?php echo esc_attr($post_id); ?>">
        <?php do_action('wp_ajax_rtcl_custom_fields_listings', $post_id, $category_id); ?>
    </div>
    <?php if (!in_array('description', $hidden_fields)): ?>
        <div class="form-group">
            <label for="description"><?php esc_html_e('Description', 'classified-listing'); ?></label>
            <?php

            if ('textarea' == $editor) { ?>
                <textarea
                        id="description"
                        name="description"
                        class="form-control"
                        <?php echo $description_limit ? 'maxlength="' . $description_limit . '"' : ''; ?>
                        rows="8"><?php Functions::print_html($post_content); ?></textarea>
                <?php
            } else {
                wp_editor(
                    $post_content,
                    'description',
                    array(
                        'media_buttons' => false,
                        'editor_height' => 200
                    )
                );
            }


            if ($description_limit) {
                echo sprintf('<div class="rtcl-hints">%s</div>',
                    apply_filters('rtcl_listing_description_character_limit_hints',
                        sprintf(__("Character limit <span class='target-limit'>%s</span>", 'classified-listing'), $description_limit)
                    ));
            }

            ?>
        </div>
    <?php endif; ?>
</div>