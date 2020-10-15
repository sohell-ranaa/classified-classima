<?php
/**
 * @author  RadiusTheme
 * @since   1.0
 * @version 1.0
 */

namespace radiustheme\Classima_Core;

use radiustheme\Classima\Helper;

$keyword = isset( $_GET['q'] ) ? $_GET['q'] : '';
$class   = "rtin-{$data['theme']} rtin-style-{$data['style']}";
?>
<div class="rt-el-listing-search rtcl <?php echo esc_attr( $class );?>">
	<?php Helper::get_custom_listing_template( 'listing-search' );?>
</div>