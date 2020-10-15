<?php
/**
 * @author  RadiusTheme
 * @since   1.0
 * @version 1.0
 */

namespace radiustheme\Classima;

use Rtcl\Helpers\Link;
use Rtcl\Helpers\Functions;
?>
<div class="listing-grid-each listing-grid-each-5<?php echo esc_attr( $class ); ?>">
	<a class="rtin-item" href="<?php the_permalink(); ?>">
        <div class="rtin-thumb"><?php $listing->the_thumbnail(); ?></div>
		<div class="rtin-content">
			
			<h3 class="rtin-title listing-title" title="<?php the_title(); ?>"><?php the_title(); ?></h3>

            <?php
            if ( $display['fields'] ) {
                $listing->the_listable_fields();
            }
            ?>

			<div class="rtin-meta-area">
				<div class="rtin-meta"><?php $listing->the_time();?> / <?php $listing->the_locations( true, false ); ?></div>

				<?php if ( $display['price'] ): ?>
					<div class="rtin-price"><?php $listing->the_price(); ?></div>
				<?php endif; ?>
			</div>

		</div>
	</a>
</div>