<?php
/**
 * @author  RadiusTheme
 * @since   1.0
 * @version 1.5
 */

namespace radiustheme\Classima;

use radiustheme\Classima\Helper;
use radiustheme\Classima\RDTheme;
use Rtcl\Helpers\Functions;
use Rtcl\Helpers\Pagination;
use RtclStore\Controllers\Hooks\TemplateHooks;

class Listing_Functions {

	protected static $instance = null;

	public function __construct() {
		add_action( 'after_setup_theme',                        array( $this, 'theme_support' ) );
		add_filter( 'get_the_archive_title',                    array( $this, 'archive_title' ) );

		add_filter( 'template_include',                         array( $this, 'template_include' ) ); // override page template
		add_action( 'save_post',                                array( $this, 'listing_form_save' ), 12, 2 ); // save extra listing form fields
		add_filter( 'rtcl_default_placeholder_url',             array( $this, 'placeholder_img_url' ) ); // change placeholder image
        add_action( 'classima_listing_list_view_after_content', array( $this, 'my_account_listing_contents' ) ); // my account contents
		add_action( 'classima_listing_list_view_after_content', array( $this, 'fav_listing_delete_btn' ) ); // delete from fav button
		add_action( 'template_redirect',                        array( $this, 'store_enable_pagination' ), 0 ); // store enable pagination by force

        // Store Filter
		add_filter('rtcl_stores_grid_columns_class', array( $this, 'classima_rtcl_stores_grid_columns_class' ) );
		
		
		add_filter( 'classima_single_listing_time_format', array( $this, 'classima_change_listing_time_format'), 20, 1 );
        add_filter( 'classima_listing_grid_col_class', array( $this, 'classima_listing_archive_grid' ), 10, 2 );

        add_filter( 'rtcl_store_time_options', array( $this, 'classima_rtcl_store_time_options_rt_cb') );
		add_filter( 'rtcl_add_price_type_at_price', '__return_empty_string' ); // Remove price type from single listing

		// Override plugin options
		add_filter( 'rtcl_general_settings',                    array( $this, 'override_general_settings' ) );
		add_filter( 'rtcl_style_settings',                      array( $this, 'override_style_settings' ) );
		add_filter( 'rtcl_moderation_settings_options',         array( $this, 'form_fields_options' ) );

		// Remove Licensing
		add_filter( 'rtcl_check_license', '__return_false' );
		
		// Remove Store Archive Action
		remove_action('rtcl_store_loop_item', array(TemplateHooks::class, 'loop_item_content_start'), 5);
		remove_action('rtcl_store_loop_item', array(TemplateHooks::class, 'loop_item_content_end'), 100);
		
	}

	public static function instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	public function theme_support() {
		add_theme_support( 'rtcl' );
	}

	public function archive_title( $title ) {
		if ( is_post_type_archive( 'rtcl_listing' ) || is_tax( 'rtcl_category' ) || is_tax( 'rtcl_location' ) ) {
			$id = Functions::get_page_id( 'listings' );
			$title = get_the_title( $id );
		}
		return $title;
	}
	
	public function classima_rtcl_stores_grid_columns_class() {
		return 'columns-6';
	}

	public function placeholder_img_url() {
		return Helper::get_img( 'noimage-listing-thumb.jpg' );
	}

	public function override_general_settings( $settings ){
		$settings['load_bootstrap'] = '';
		return $settings;
	}

	public function override_style_settings( $settings ){
		$primary_color    = Helper::get_primary_color(); // #1aa78e
		$secondary_color  = Helper::get_secondary_color(); // #fcaf01

		$args = array(
			'primary'           => $primary_color,
			'link'              => $primary_color,
			'link_hover'        => $secondary_color,
			'button'            => $primary_color,
			'button_hover'      => $secondary_color,
			'button_text'       => '#ffffff',
			'button_hover_text' => '#ffffff',
		);

		$settings = wp_parse_args( $args, $settings );
		
		return $settings;
	}

	public function classima_rtcl_store_time_options_rt_cb( $data ) {

        $format = isset(RDTheme::$options['time_format']) ? RDTheme::$options['time_format'] : true;

        if( $format == false) {
            $data['showMeridian'] = false;
        }

        return $data;
    }

    public function classima_listing_archive_grid( $col_class, $map ) {

        $col_class = isset(RDTheme::$options['grid_desktop_column']) ? 'col-xl-'.RDTheme::$options['grid_desktop_column'] : 'col-xxl-4 col-xl-4';
        $col_class .= isset(RDTheme::$options['grid_tablet_column']) ? ' col-md-'.RDTheme::$options['grid_tablet_column'] . ' col-sm-'.RDTheme::$options['grid_tablet_column'] : ' col-md-6 col-sm-6';
        $col_class .= isset(RDTheme::$options['grid_mobile_column']) ? ' col-'.RDTheme::$options['grid_mobile_column'] : ' col-12';

        return $col_class;
	}

    public function classima_change_listing_time_format( $string ) {

        $time_format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
        if( empty($time_format) ) {
            $time_format = $string;
        }
        return $time_format;
    }

	public function form_fields_options( $options ){
		$options['hide_form_fields']['options']['features'] = esc_html__( 'Features', 'classima' );
		return $options;
	}

	private function is_listings_map_page(){
		global $post;
		$pattern = '/\[rtcl_listings\s+map=1(\s+)?\]/'; // catches [rtcl_listings map=1]
		$result  = preg_match( $pattern, $post->post_content );
		return $result;
	}

	public function template_include( $template ){
		if( Functions::is_account_page() ){
			$new_template  = Helper::get_custom_listing_template( 'listing-account', false );
			$new_template = locate_template( array( $new_template ) );
			return $new_template;
		}

		return $template;
	}


	public function my_account_listing_contents( $listing ){
		if( Functions::is_account_page( 'listings' ) ){
			Helper::get_custom_listing_template( 'myaccount-contents', true, compact( 'listing' ) );
		}
	}

	public function fav_listing_delete_btn( $listing ){
		if( !Functions::is_account_page( 'favourites' ) ) return;
		?>
		<div class="rtin-action-btn">
			<a href="#" class="btn rtcl-delete-favourite-listing" data-id="<?php echo esc_attr( $listing->get_id() ); ?>"><?php esc_html_e( 'Remove from Favourites', 'classima' ) ?></a>
		</div>
		<?php
	}

	public function listing_form_save( $post_id ){
		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
			return;
		}

		if ( !Functions::current_user_can( 'edit_' . rtcl()->post_type, $post_id ) ) {
			return;
		}

		if ( !Functions::verify_nonce() ) {
			return;
		}

		if ( isset( $_POST['classima_spec_info'] ) ) {
			update_post_meta( $post_id, 'classima_spec_info', stripslashes_deep( $_POST['classima_spec_info'] ) );
		}
	}

	public function store_enable_pagination() {
		if( is_singular( 'store' ) ) {
			remove_action( 'template_redirect', 'redirect_canonical' );
		}
	}

	public static function listing_count_text( $post_num ) {
		if ( $post_num ) {
			if ( $post_num['total'] == 1 ) {
				$post_num_text = esc_html__( 'Showing 1 result', 'classima' );
			}
			else {
				$post_num_text = sprintf( esc_html__( 'Showing %1$d–%2$d of %3$d results', 'classima' ), $post_num['first'], $post_num['last'], $post_num['total'] );
			}
		}
		else {
			$post_num_text = esc_html__( 'Showing 0 result', 'classima' );
		}
		return $post_num_text;
	}

	public static function listing_post_num( $rtcl_query ){

		$total = $rtcl_query->found_posts;
		$current = $rtcl_query->post_count;

		if ( $current ) {
			$posts_per_page = $rtcl_query->query_vars['posts_per_page'];
			$paged = !empty( $rtcl_query->query['paged'] ) ? $rtcl_query->query['paged'] : 1;
			$num_of_skipped_items = $posts_per_page * ($paged - 1);

			$first = $num_of_skipped_items + 1;
			$last = $num_of_skipped_items + $current;

			$result = array(
				'first' => $first,
				'last'  => $last,
				'total' => $total,
			);
		}
		else {
			$result = false;
		}

		return $result;
	}

	public static function listing_query( $view, $rtcl_query, $rtcl_top_query = false, $map = false ){
		if ( $view == 'grid' ) { ?>
			<div class="row auto-clear">
				<?php
				$col_class =  $map ? 'col-xxl-6 col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12' : 'col-xxl-4 col-xl-4 col-lg-6 col-md-6 col-sm-6 col-12';
				$col_class = apply_filters( 'classima_listing_grid_col_class', $col_class, $map );

                if (Functions::is_enable_top_listings()) {
                    if (is_object($rtcl_top_query) && $rtcl_top_query->have_posts()) {
                        $top_listing = true;
                        while ($rtcl_top_query->have_posts()): $rtcl_top_query->the_post(); ?>
                            <div class="<?php echo esc_attr($col_class); ?>">
                                <?php Functions::get_template('custom/grid', compact('top_listing', 'map')); ?>
                            </div>
                        <?php
                        endwhile;
                        wp_reset_postdata();
                    }
                }

				while ( $rtcl_query->have_posts() ): $rtcl_query->the_post();?>
					<div class="<?php echo esc_attr( $col_class );?>">
						<?php Functions::get_template( 'custom/grid', compact( 'map' ) );?>
					</div>
					<?php
				endwhile;
				wp_reset_postdata(); ?>
			</div>
			<?php
		}
		else {
			$layout  = NULL;
			$display = array();
			if ( $map ) {
				$layout = 'map';
				$display = array(
					'excerpt'  => false,
				);
			}
            if (Functions::is_enable_top_listings()) {
                if (is_object($rtcl_top_query) && $rtcl_top_query->have_posts()) {
                    $top_listing = true;
                    while ($rtcl_top_query->have_posts()) : $rtcl_top_query->the_post();
                        Functions::get_template('custom/list', compact('top_listing', 'map', 'layout', 'display'));
                    endwhile;
                    wp_reset_postdata();
                }
            }

			while ( $rtcl_query->have_posts() ) : $rtcl_query->the_post();
				Functions::get_template( 'custom/list', compact( 'map', 'layout', 'display' ) );
			endwhile; wp_reset_postdata();			
		}
	}

	public static function get_single_contact_address( $listing ){

		$listing_id = $listing->get_id();
		$listing_locations = $listing->get_locations();

		$render = $loc = '';

		$address = get_post_meta( $listing_id, 'address', true );
		$address = $address && Functions::get_option_item( 'rtcl_moderation_settings', 'display_options_detail', 'address', 'multi_checkbox' ) ? $address : '';

		$zipcode = get_post_meta( $listing_id, 'zipcode', true );
		$zipcode = $zipcode && Functions::get_option_item( 'rtcl_moderation_settings', 'display_options_detail', 'zipcode', 'multi_checkbox' ) ? $zipcode : '';

		$locations = array();
		if ( count( $listing_locations ) ) {
			foreach ( $listing_locations as $location ) {
				$locations[] = $location->name;
			}
			$locations = array_reverse( $locations );
			$loc = implode( ', ', $locations );
		}

		if ( $address ) {
			$render .= sprintf( '<div>%s</div>' , $address );
		}

		if ( $address && $loc && $zipcode ) {
			$render .= sprintf( '<div>%s, %s</div>' , $loc, $zipcode );
		}
		elseif ( $address && $loc ) {
			$render .= sprintf( '<div>%s</div>' , $loc );
		}
		elseif ( $zipcode ) {
			$render .= sprintf( '<div>%s</div>' , $zipcode );
		}

		return $render;
	}

	public static function the_phone( $phone = '', $whatsapp_number = '' ) {
        if (!$phone) {
            return;
        }
        $mobileClass = wp_is_mobile() ? " rtcl-mobile" : null;
        $phone_options = [
            'safe_phone' => mb_substr($phone, 0, mb_strlen($phone) - 3) . apply_filters('rtcl_phone_number_placeholder', 'XXX'),
            'phone_hidden' => mb_substr($phone, -3)
        ];
        if ($whatsapp_number && !Functions::is_field_disabled('whatsapp_number')) {
            $phone_options['safe_whatsapp_number'] = mb_substr($whatsapp_number, 0, mb_strlen($whatsapp_number) - 3) . apply_filters('rtcl_phone_number_placeholder', 'XXX');
            $phone_options['whatsapp_hidden'] = mb_substr($whatsapp_number, -3);
        }
        $phone_options = apply_filters('rtcl_phone_number_options', $phone_options, ['phone' => $phone, 'whatsapp_number' => $whatsapp_number])
        ?>
        <div class="rtcl-contact-reveal-wrapper reveal-phone<?php echo esc_attr($mobileClass); ?>"
             data-options="<?php echo htmlspecialchars(wp_json_encode($phone_options)); ?>">
            <div class='numbers'><?php echo esc_html($phone_options['safe_phone']); ?></div>
            <small class='text-muted'><?php esc_html_e('Click to reveal phone number', 'classima'); ?></small>
        </div>
        <?php
	}

	public static function get_listing_type( $listing ){

		$listing_types = Functions::get_listing_types();
		$listing_types = empty( $listing_types ) ? array() : $listing_types;

		$type = $listing->get_type();

		if ( $type && !empty( $listing_types[$type] ) ) {
			$result = array(
				'label' => $listing_types[$type],
				'icon'  => 'fa-tags'				
			);
		}
		else {
			$result = false;
		}

		return $result;
	}

	public static function store_query() {
		global $post;
		
		$args = array(
			'post_type'      => rtcl()->post_type,
			'post_status'    => 'publish',
			'posts_per_page' => Functions::get_option_item( 'rtcl_general_settings', 'listings_per_page', 20 ),
			'author'         => get_post_meta( $post->ID, 'store_owner_id', true ),
			'paged'          => Pagination::get_page_number(),
		);

		$general_settings = Functions::get_option('rtcl_general_settings');
		$atts = array(
			'orderby' => !empty($general_settings['orderby']) ? $general_settings['orderby'] : 'date',
			'order'   => !empty($general_settings['order']) ? $general_settings['order'] : 'DESC',
		);

		$current_order = Pagination::get_listings_current_order($atts['orderby'] . '-' . $atts['order']);
		switch ($current_order) {
			case 'title-asc' :
			$args['orderby'] = 'title';
			$args['order'] = 'ASC';
			break;
			case 'title-desc' :
			$args['orderby'] = 'title';
			$args['order'] = 'DESC';
			break;
			case 'date-asc' :
			$args['orderby'] = 'date';
			$args['order'] = 'ASC';
			break;
			case 'date-desc' :
			$args['orderby'] = 'date';
			$args['order'] = 'DESC';
			break;
			case 'price-asc' :
			$args['meta_key'] = 'price';
			$args['orderby'] = 'meta_value_num';
			$args['order'] = 'ASC';
			break;
			case 'price-desc' :
			$args['meta_key'] = 'price';
			$args['orderby'] = 'meta_value_num';
			$args['order'] = 'DESC';
			break;
			case 'views-asc' :
			$args['meta_key'] = '_views';
			$args['orderby'] = 'meta_value_num';
			$args['order'] = 'ASC';
			break;
			case 'views-desc' :
			$args['meta_key'] = '_views';
			$args['orderby'] = 'meta_value_num';
			$args['order'] = 'DESC';
			break;
			case 'rand' :
			$args['orderby'] = 'rand';
			break;
		}

		return new \WP_Query( $args );
	}

}

Listing_Functions::instance();