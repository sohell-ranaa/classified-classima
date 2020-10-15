<?php
/**
 * @author  RadiusTheme
 * @since   1.0
 * @version 1.0
 */

namespace radiustheme\Classima_Core;
?>
<div class="rt-el-testimonial-2 owl-wrap rt-el-testimonial-nav">
	<div class="owl-theme owl-carousel rt-owl-carousel" data-carousel-options="<?php echo esc_attr( $data['owl_data'] );?>">
		<?php foreach ( $data['items'] as $item ): ?>
			<div class="rtin-item">
				<p class="rtin-content"><?php echo esc_html( $item['content'] );?></p>
				<?php if ( $item['image'] ): ?>
					<div class="rtin-thumb"><?php echo wp_get_attachment_image( $item['image']['id'], 'thumbnail' );?></div>
				<?php endif; ?>
				<h3 class="rtin-name"><?php echo esc_html( $item['name'] );?></h3>
				<?php if ( $item['designation'] ): ?>
					<div class="rtin-designation"><?php echo esc_html( $item['designation'] );?></div>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	</div>
</div>