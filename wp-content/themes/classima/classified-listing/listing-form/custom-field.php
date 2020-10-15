<?php
/**
 * Custom Field
 *
 * @author     RadiusTheme
 * @package    classified-listing/templates
 * @version    1.0.0
 */

use Rtcl\Helpers\Functions;

$required_label = $required_label ? '<span> *</span>' : '';
$label .= $required_label;
?>
<div class="row">
	<div class="col-sm-3 col-12">
		<label class="control-label"><?php echo wp_kses_post( $label ); ?></label>
	</div>
	<div class="col-sm-9 col-12">
		<div class="form-group">
			<?php Functions::print_html( $field, true ); ?>
			<div class='help-block with-errors'></div>
			<?php if ( $description ) : ?>
				<small class='help-block'><?php echo esc_html( $description ); ?></small>
			<?php endif; ?>
		</div>
	</div>
</div>