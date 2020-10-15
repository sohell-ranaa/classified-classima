<?php
/**
 *
 * @author        RadiusTheme
 * @package       classified-listing/templates
 * @version       1.0.0
 *
 * @var WP_Query $rtcl_query
 */

use Rtcl\Helpers\Functions;
use Rtcl\Helpers\Link;
use Rtcl\Helpers\Pagination;

global $post;
?>
<div class="rtcl-payment-history-wrap">
	<?php
	if ( $rtcl_query->have_posts() ) {

		?>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                <tr>
                    <th><?php esc_html_e( '#', 'classified-listing' ); ?></th>
                    <th><?php esc_html_e( 'Total', 'classified-listing' ); ?></th>
                    <th><?php esc_html_e( 'Status', 'classified-listing' ); ?></th>
                    <th><?php esc_html_e( 'Date', 'classified-listing' ); ?></th>
                </tr>
                </thead>

                <!-- the loop -->
				<?php while ( $rtcl_query->have_posts() ) : $rtcl_query->the_post();
					$payment = rtcl()->factory->get_order( get_the_ID() ); ?>
                    <tr>
                        <td><?php printf( '<a href="%s">%d</a>', Link::get_checkout_endpoint_url( "payment-receipt", $payment->get_id() ),
								$payment->get_id() ) ?></td>
                        <td><?php
							$title = $payment->get_payment_method_title();
							printf( "%s<div class='meta small'>%s</div>",
								Functions::get_formatted_price( $payment->get_total(), true ),
								$payment->get_total() === 0 ? $title : sprintf( __( 'Pay via <strong>%s</strong>', 'classified-listing' ), $title )
							);
							?></td>
                        <td><?php echo Functions::get_status_i18n( $post->post_status ); ?></td>
                        <td><?php echo Functions::datetime( 'rtcl', $post->post_date_gmt ); ?></td>
                    </tr>
				<?php endwhile;
				wp_reset_postdata();
				?>
            </table>
        </div>
        <!-- pagination here -->
		<?php
		Pagination::pagination( $rtcl_query );

	} else {
		echo '<span>' . __( 'No Results Found.', 'classified-listing' ) . '</span>';
	} ?>
</div>
