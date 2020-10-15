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
<div class="listing-grid-each listing-grid-each-6<?php echo esc_attr( $class ); ?>">
    <div class="rtin-item">
        <div class="rtin-thumb">
            <a class="rtin-thumb-inner rtcl-media" href="<?php the_permalink(); ?>"><?php $listing->the_thumbnail(); ?></a>
            <?php if ( $display['type'] ): ?>
                <div class="rtin-type"><span><?php echo sprintf(__("For %s", 'classima'), $type['label']); ?></span></div>
            <?php endif; ?>
        </div>
        <div class="rtin-content">

            <?php if ( $display['cat'] ): ?>
                <a class="rtin-cat" href="<?php echo esc_url( Link::get_category_page_link( $category ) ); ?>"><?php echo esc_html( $category->name ); ?></a>
            <?php endif; ?>

            <h3 class="rtin-title listing-title" title="<?php the_title(); ?>"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>

            <?php
            if ( $display['fields'] ) {
                $listing->the_listable_fields();
            }
            ?>

            <ul class="rtin-meta">
                <?php if ( $display['date'] ): ?>
                    <li><i class="fa fa-fw fa-clock-o" aria-hidden="true"></i><?php $listing->the_time(); ?></li>
                <?php endif; ?>
                <?php if ( $display['location'] ): ?>
                    <li><i class="fa fa-fw fa-map-marker" aria-hidden="true"></i><?php $listing->the_locations( true, false ); ?></li>
                <?php endif; ?>
                <?php if ( $display['views'] ): ?>
                    <li><i class="fa fa-fw fa-eye" aria-hidden="true"></i><?php echo sprintf( esc_html__( '%1$s Views', 'classima' ) , number_format_i18n( $listing->get_view_counts() ) ); ?></li>
                <?php endif; ?>
            </ul>
            <?php if ( $display['price'] ): ?>
                <div class="rtin-price"><?php $listing->the_price(); ?></div>
            <?php endif; ?>
        </div>
    </div>
</div>