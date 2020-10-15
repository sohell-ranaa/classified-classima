<?php
/**
 * @author        RadiusTheme
 * @package       classified-listing/templates
 * @version       1.0.0
 *
 * @var array $pricing_options
 */


use Rtcl\Helpers\Functions;

?>

<table id="rtcl-checkout-form-data"
       class="rtcl-responsive-table rtcl-pricing-options form-group table table-hover table-stripped table-bordered">
    <tr>
        <th><?php esc_html_e("Pricing Option", "classified-listing"); ?></th>
        <th><?php esc_html_e("Description", "classified-listing"); ?></th>
        <th><?php esc_html_e("Visibility", "classified-listing"); ?></th>
        <th><?php printf(__('Price [%s %s]', 'classified-listing'),
                Functions::get_currency(true),
                Functions::get_currency_symbol(null, true)); ?></th>
    </tr>
    <?php foreach ($pricing_options as $pricing) :
        $price = get_post_meta($pricing->ID, 'price', true);
        $visible = get_post_meta($pricing->ID, 'visible', true);
        $featured = get_post_meta($pricing->ID, 'featured', true);
        $top = get_post_meta($pricing->ID, '_top', true);
        $bump_up = get_post_meta($pricing->ID, '_bump_up', true);
        $description = get_post_meta($pricing->ID, 'description', true);
        ?>
        <tr>
            <td class="rtcl-pricing-option form-check"
                data-label="<?php esc_html_e("Pricing Option:", "classified-listing"); ?>">
                <?php
                printf('<label><input type="radio" name="%s" value="%s" class="rtcl-checkout-pricing" required data-price="%s"/> %s</label>',
                    'pricing_id', esc_attr($pricing->ID), esc_attr($price), esc_html($pricing->post_title));
                ?>
            </td>
            <td class="rtcl-pricing-features" data-label="<?php esc_html_e("Description:", "classified-listing"); ?>">
                <?php echo esc_html($description); ?>
            </td>
            <td class="rtcl-pricing-visibility" data-label="<?php esc_html_e("Visibility:", "classified-listing"); ?>">
                <?php
                printf('%s %s %s %s',
                    sprintf(_n('%s Day', '%s Days', absint($visible), 'classified-listing'), number_format_i18n(absint($visible))),
                    $featured ? '<span class="badge badge-info featured-badge">' . __('Featured', 'classified-listing') . '</span>' : null,
                    $top ? '<span class="badge badge-warning top-badge">' . __('Top', 'classified-listing') . '</span>' : null,
                    $bump_up ? '<span class="badge badge-danger bump-up-badge">' . __('Bump Up', 'classified-listing') . '</span>' : null
                );
                ?>
            </td>
            <td class="rtcl-pricing-price text-right"
                data-label="<?php printf(__('Price [%s %s]:', 'classified-listing'),
                    Functions::get_currency(true),
                    Functions::get_currency_symbol(null, true)); ?>"><?php echo Functions::get_formatted_amount($price, true); ?> </td>
        </tr>
    <?php endforeach; ?>
</table>