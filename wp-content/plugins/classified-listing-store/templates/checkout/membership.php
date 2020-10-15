<?php
/**
 * Membership checkout
 *
 * @author     RadiusTheme
 * @package    classified-listing/templates
 * @version    1.0.0
 */


use Rtcl\Helpers\Functions;

?>

<table id="rtcl-checkout-pricing-option"
       class="rtcl-responsive-table rtcl-pricing-options form-group table table-hover table-stripped table-bordered rtcl-membership-pricing-options">
    <tr>
        <th><?php esc_html_e("Membership", "classified-listing-store"); ?></th>
        <th><?php esc_html_e("Features", "classified-listing-store"); ?></th>
        <th><?php printf(__('Price [%s %s]', 'classified-listing-store'),
                Functions::get_currency(true),
                Functions::get_currency_symbol(null, true)); ?></th>
    </tr>
    <?php if (!empty($pricing_options)) :
        foreach ($pricing_options as $option) :
            $price = get_post_meta($option->ID, 'price', true);
            ?>
            <tr>
                <td class="form-check rtcl-pricing-option"
                    data-label="<?php esc_html_e("Membership:", "classified-listing-store"); ?>">
                    <?php
                    printf('<label><input type="radio" name="%s" value="%s" class="rtcl-checkout-pricing" required data-price="%s"/> %s</label>',
                        'pricing_id', esc_attr($option->ID), esc_attr($price), esc_html($option->post_title));
                    ?>
                </td>
                <td class="rtcl-pricing-features"
                    data-label="<?php esc_html_e("Features:", "classified-listing-store"); ?>">
                    <?php do_action('rtcl_membership_features', $option->ID) ?>
                </td>
                <td class="rtcl-pricing-price text-right"
                    data-label="<?php printf(__('Price [%s %s]:', 'classified-listing-store'),
                        Functions::get_currency(true),
                        Functions::get_currency_symbol(null, true)); ?>"><?php echo Functions::get_formatted_amount($price, true); ?> </td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
</table>