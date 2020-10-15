<?php
/**
 * Custom Field
 *
 * @author     RadiusTheme
 * @package    classified-listing/templates
 * @version    1.0.0
 *
 * @var string $field
 * @var string $field_attr
 * @var string $label
 * @var string $required_label
 */

use Rtcl\Helpers\Functions;

?>
<div class="form-group row"<?php echo esc_attr($field_attr) ?>>
    <label for="<?php echo esc_attr($id) ?>"
           class="col-md-2 col-12 col-form-label"><?php echo esc_html($label); Functions::print_html($required_label); ?></label>
    <div class='col-md-10 col-12'>
        <?php Functions::print_html($field, true); ?>
        <div class='help-block with-errors'></div>
        <?php if ($description) : ?>
            <small class='help-block'><?php echo esc_html($description); ?></small>
        <?php endif; ?>
    </div>
</div>