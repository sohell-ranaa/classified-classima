<?php
/**
 * @author        RadiusTheme
 * @package       classified-listing/views/public
 * @version       1.0.0
 */

use Rtcl\Models\RtclCFGField;
use Rtcl\Models\Listing;

$listing = new Listing( get_the_id() );
$items = array();
$urls  = array(); 

if ( $listing->can_show_category() ) {
	$category = $listing->get_categories();
	$category = current( $category );
	if ( $category ) {
		$items[] = array(
			'label' => esc_html__( 'Category', 'classima' ),
			'value' => $category->name,
		);		
	}
}

foreach ( $fields as $field ) {
	$field = new RtclCFGField( $field->ID );
	$value = $field->getFormattedCustomFieldValue( $listing_id );
	if ( ! empty( $value ) ) {
		if ( $field->getType() === 'url' ) {
			$nofollow = ! empty( $field->getNofollow() ) ? ' rel="nofollow"' : '';
			$urls[] = sprintf( ' <a href="%1$s" target="%2$s"%3$s>%4$s</a>', $value, $field->getTarget(), $nofollow, $field->getLabel() );
		}
		else {
			$items[] = array(
				'label' => $field->getLabel(),
				'value' => $value,
			);
		}
	}
}

if ( !$items && !$urls ) {
	return;
}
?>
<h3 class="rtin-specs-title"><?php esc_html_e('Overview', 'classima' ); ?></h3>
<div class="classima-custom-fields clearfix">
	<ul>
		<?php foreach ( $items as $item ): ?>
			<li>
				<span class="rtin-label"><?php echo esc_html( $item['label'] );?> : </span>
				<span class="rtin-title"><?php echo wp_kses_post( $item['value'] );?></span>
			</li>
		<?php endforeach; ?>
		<?php foreach ( $urls as $url ): ?>
			<li>
				<span class="rtin-label"><?php echo wp_kses_post( $url );?></span>
			</li>
		<?php endforeach; ?>
	</ul>
</div>