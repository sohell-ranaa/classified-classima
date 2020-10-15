<?php
/**
 * @author  RadiusTheme
 * @since   1.0
 * @version 1.5.3
 */

namespace radiustheme\Classima;

use Rtcl\Helpers\Functions;
use Rtcl\Helpers\Link;
use Rtcl\Helpers\Text;

$count = 0;
foreach ( RDTheme::$options['listing_search_items'] as $key => $value ) {
	if ( !empty($value) ) {
		$count++;
	}
}

if ( !$count ) {
	return;
}

if ( $count == 1 ) {
	$loc_class = $cat_class = $key_class = $typ_class = 'col-lg-8 col-md-6 col-sm-6 col-12';
	$btn_class = 'col-lg-4 col-md-6 col-sm-6 col-12';
}
elseif ( $count == 2 ) {
	$loc_class = $cat_class = $key_class = $typ_class = 'col-lg-5 col-md-6 col-sm-6 col-12';
	$btn_class = 'col-lg-2 col-md-12 col-sm-12 col-12';
}
elseif ( $count == 3 ) {
	$loc_class = $cat_class = $typ_class = $key_class = 'col-lg-3 col-md-6 col-sm-6 col-12';

	if ( empty( RDTheme::$options['listing_search_items']['keyword'] ) ) {
		$typ_class = 'col-lg-4 col-md-6 col-sm-6 col-12';
	}
	else {
		$key_class = 'col-lg-4 col-md-6 col-sm-6 col-12';
	}
	
	$btn_class = 'col-lg-2 col-md-6 col-sm-6 col-12';
}
elseif ( $count == 4 ) {
	$loc_class = $cat_class = $typ_class = 'col-xl-2 col-md-6 col-sm-6 col-12';
	$key_class = 'col-xl-4 col-md-6 col-sm-6 col-12';
	$btn_class = 'col-xl-2 col-md-12 col-sm-12 col-12';
}
else {
	$loc_class = $cat_class = $key_class = $typ_class = $btn_class = 'col-12';
}

$loc_text = esc_attr__( 'Select Location', 'classima' );
$cat_text = esc_attr__( 'Select Category', 'classima' );
$typ_text = esc_attr__( 'Select Type', 'classima' );

$selected_location = $selected_category = false;

if ( get_query_var( 'rtcl_location' ) && $location = get_term_by( 'slug', get_query_var( 'rtcl_location' ), rtcl()->location ) ) {
	$selected_location = $location;
}

if ( get_query_var( 'rtcl_category' ) && $category = get_term_by( 'slug', get_query_var( 'rtcl_category' ), rtcl()->category ) ) {
	$selected_category = $category;
}

$style = RDTheme::$options['listing_search_style'];
?>
<form action="<?php echo esc_url( Functions::get_filter_form_url() ); ?>" class="form-vertical rtcl-widget-search-form rtcl-search-inline-form classima-listing-search-form rtin-style-<?php echo esc_attr( $style );?> rtin-count-<?php echo esc_attr( $count );?>">
	<div class="row">
		<?php if ( !empty( RDTheme::$options['listing_search_items']['location'] ) ): ?>
			<div class="<?php echo esc_attr( $loc_class );?>">
				<div class="form-group">
					<?php if ( $style == 'suggestion' ): ?>
						<div class="rtcl-search-input-button classima-search-style-2 rtin-location">
	                        <input type="text" data-type="location" class="rtcl-autocomplete rtcl-location" placeholder="<?php echo esc_attr( $loc_text ); ?>" value="<?php echo $selected_location ? $selected_location->name : '' ?>">
	                        <input type="hidden" name="rtcl_location" value="<?php echo $selected_location ? $selected_location->slug : '' ?>">
                    	</div>
                    <?php elseif ( $style == 'standard' ): ?>
                    	<div class="rtcl-search-input-button classima-search-style-2 rtin-location">
							<?php
							wp_dropdown_categories( array(
								'show_option_none'  => $loc_text,
								'option_none_value' => '',
								'taxonomy'          => rtcl()->location,
								'name'              => 'rtcl_location',
								'id'                => 'rtcl-location-search-' . wp_rand(),
								'class'             => 'form-control rtcl-location-search',
								'selected'          => get_query_var( 'rtcl_location' ),
								'hierarchical'      => true,
								'value_field'       => 'slug',
								'depth'             => Functions::get_location_depth_limit(),
								'show_count'        => false,
								'hide_empty'        => false,
							) );
							?>
						</div>
					<?php elseif ( $style == 'dependency' ): ?>
						<div class="rtcl-search-input-button classima-search-style-2 rtin-location">
							<?php
							Functions::dropdown_terms( array(
								'show_option_none' => $loc_text,
								'taxonomy'         => rtcl()->location,
								'name'             => 'l',
								'class'            => 'form-control',
								'selected'         => $selected_location ? $selected_location->term_id : 0
							) );
							?>
						</div>
					<?php else: ?>
						<div class="rtcl-search-input-button rtcl-search-input-location">
							<span class="search-input-label location-name">
								<?php echo $selected_location ? esc_html( $selected_location->name ) : esc_html( $loc_text ) ?>
							</span>
							<input type="hidden" class="rtcl-term-field" name="rtcl_location" value="<?php echo $selected_location ? esc_attr( $selected_location->slug ) : '' ?>">
						</div>						
					<?php endif; ?>

				</div>
			</div>
		<?php endif; ?>

		<?php if ( !empty( RDTheme::$options['listing_search_items']['category'] ) ): ?>
			<div class="<?php echo esc_attr( $cat_class );?>">
				<div class="form-group">
					<?php if ( $style == 'suggestion' || $style == 'standard' ): ?>
						<div class="rtcl-search-input-button classima-search-style-2 rtin-category">
							<?php
							wp_dropdown_categories( array(
								'show_option_none'  => $cat_text,
								'option_none_value' => '',
								'taxonomy'          => rtcl()->category,
								'name'              => 'rtcl_category',
								'id'                => 'rtcl-category-search-' . wp_rand(),
								'class'             => 'form-control rtcl-category-search',
								'selected'          => get_query_var( 'rtcl_category' ),
								'hierarchical'      => true,
								'value_field'       => 'slug',
								'depth'             => Functions::get_category_depth_limit(),
								'show_count'        => false,
								'hide_empty'        => false,
							) );
							?>
						</div>
					<?php elseif ( $style == 'dependency' ): ?>
						<div class="rtcl-search-input-button classima-search-style-2 classima-search-dependency rtin-category">
							<?php
							Functions::dropdown_terms( array(
								'show_option_none'  => $cat_text,
								'option_none_value' => - 1,
								'taxonomy'          => rtcl()->category,
								'name'              => 'c',
								'class'             => 'form-control rtcl-category-search',
								'selected'          => $selected_category ? $selected_category->term_id : 0
							) );
							?>
						</div>
					<?php else: ?>
						<div class="rtcl-search-input-button rtcl-search-input-category">
							<span class="search-input-label category-name">
								<?php echo $selected_category ? esc_html( $selected_category->name ) : esc_html( $cat_text ); ?>
							</span>
							<input type="hidden" name="rtcl_category" class="rtcl-term-field" value="<?php echo $selected_category ? esc_attr( $selected_category->slug ) : '' ?>">
						</div>
					<?php endif; ?>

				</div>
			</div>
		<?php endif; ?>

		<?php if ( !empty( RDTheme::$options['listing_search_items']['type'] ) ): ?>
			<div class="<?php echo esc_attr( $typ_class );?>">
				<div class="form-group">
					<div class="rtcl-search-input-button rtcl-search-input-type">
						<?php
						$listing_types = Functions::get_listing_types();
						$listing_types = empty( $listing_types ) ? array() : $listing_types;
						?>
						<div class="dropdown classima-listing-search-dropdown">
							<button class="btn dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?php esc_html_e( 'Select Type', 'classima' ); ?></button>
							<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
								<a class="dropdown-item" href="#" data-adtype=""><?php echo esc_html( $typ_text ); ?></a>
								<?php foreach ( $listing_types as $key => $listing_type ): ?>
									<a class="dropdown-item" href="#" data-adtype="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $listing_type ); ?></a>
								<?php endforeach; ?>
							</div>
							<input type="hidden" name="filters[ad_type]">
						</div>
					</div>
				</div>
			</div>
		<?php endif; ?>

		<?php if ( !empty( RDTheme::$options['listing_search_items']['keyword'] ) ): ?>
			<div class="<?php echo esc_attr( $key_class );?>">
				<div class="form-group">
					<div class="rtcl-search-input-button rtin-keyword">
						<input type="text" data-type="listing" name="q" class="rtcl-autocomplete" placeholder="<?php esc_html_e('Enter Keyword here ...', 'classima'); ?>" value="<?php if (isset($_GET['q'])) {echo esc_attr($_GET['q']);} ?>" />
					</div>
				</div>
			</div>
		<?php endif; ?>

		<div class="<?php echo esc_attr( $btn_class );?> rtin-btn-holder">
			<button type="submit" class="rtin-search-btn rdtheme-button-1"><i class="fa fa-search" aria-hidden="true"></i><?php esc_html_e( 'Search', 'classima' );?></button>
		</div>
	</div>
</form>