<?php
/**
 * Search widgets admin view
 * @var array $instance
 */
?>
<p>
    <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php esc_html_e( 'Title', 'classified-listing' ); ?></label>
    <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>"
           name="<?php echo $this->get_field_name( 'title' ); ?>" type="text"
           value="<?php echo esc_attr( $instance['title'] ); ?>">
</p>
<p>
    <label for="<?php echo $this->get_field_id( 'style' ); ?>"><?php esc_html_e( 'Style', 'classified-listing' ); ?></label>
    <select class="widefat" id="<?php echo $this->get_field_id( 'style' ); ?>"
            name="<?php echo $this->get_field_name( 'style' ); ?>">
		<?php
		foreach ( $this->style as $key => $value ) {
			printf( '<option value="%s"%s>%s</option>', $key, selected( $key, $instance['style'] ),
				$value );
		}
		?>
    </select>
</p>

<p>
    <label for="<?php echo $this->get_field_id( 'orientation_vertical' ); ?>"> <?php esc_html_e( 'Orientation', 'classified-listing' ); ?> </label><br>
    <label for="<?php echo $this->get_field_id( 'orientation_vertical' ); ?>">
        <input class="" id="<?php echo $this->get_field_id( 'orientation_vertical' ); ?>"
               name="<?php echo $this->get_field_name( 'orientation' ); ?>" type="radio"
               value="vertical" <?php if ( $instance['orientation'] === 'vertical' ) {
			echo 'checked="checked"';
		} ?> />
		<?php esc_html_e( 'Vertical', 'classified-listing' ); ?>
    </label><br>
    <label for="<?php echo $this->get_field_id( 'orientation_inline' ); ?>">
        <input class="" id="<?php echo $this->get_field_id( 'orientation_inline' ); ?>"
               name="<?php echo $this->get_field_name( 'orientation' ); ?>" type="radio"
               value="inline" <?php if ( $instance['orientation'] === 'inline' ) {
			echo 'checked="checked"';
		} ?> />
		<?php esc_html_e( 'Inline', 'classified-listing' ); ?>
    </label>
</p>

<p>
    <input <?php checked( $instance['search_by_category'] ); ?>
            id="<?php echo $this->get_field_id( 'search_by_category' ); ?>"
            name="<?php echo $this->get_field_name( 'search_by_category' ); ?>" type="checkbox"/>
    <label for="<?php echo $this->get_field_id( 'search_by_category' ); ?>"><?php esc_html_e( 'Search by Category', 'classified-listing' ); ?></label>
</p>

<p>
    <input <?php checked( $instance['search_by_location'] ); ?>
            id="<?php echo $this->get_field_id( 'search_by_location' ); ?>"
            name="<?php echo $this->get_field_name( 'search_by_location' ); ?>" type="checkbox"/>
    <label for="<?php echo $this->get_field_id( 'search_by_location' ); ?>"><?php esc_html_e( 'Search by Location', 'classified-listing' ); ?></label>
</p>

<p>
    <input <?php checked( $instance['search_by_listing_types'] ); ?>
            id="<?php echo $this->get_field_id( 'search_by_listing_types' ); ?>"
            name="<?php echo $this->get_field_name( 'search_by_listing_types' ); ?>" type="checkbox"/>
    <label for="<?php echo $this->get_field_id( 'search_by_listing_types' ); ?>"><?php esc_html_e( 'Search by types', 'classified-listing' ); ?></label>
</p>

<p>
    <input <?php checked( $instance['search_by_price'] ); ?>
            id="<?php echo $this->get_field_id( 'search_by_price' ); ?>"
            name="<?php echo $this->get_field_name( 'search_by_price' ); ?>" type="checkbox"/>
    <label for="<?php echo $this->get_field_id( 'search_by_price' ); ?>"><?php esc_html_e( 'Search by Price', 'classified-listing' ); ?></label>
</p>