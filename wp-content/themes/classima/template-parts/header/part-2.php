<?php
/**
 * @author  RadiusTheme
 * @since   1.0
 * @version 1.4.1
 */

namespace radiustheme\Classima;

use Rtcl\Helpers\Link;

$nav_menu_args = Helper::nav_menu_args();

$light_logo = empty( RDTheme::$options['logo_light']['url'] ) ? Helper::get_img( 'logo-light.png' ) : RDTheme::$options['logo_light']['url'];
$dark_logo = empty( RDTheme::$options['logo']['url'] ) ? Helper::get_img( 'logo-dark.png' ) : RDTheme::$options['logo']['url'];

$has_header_icons = RDTheme::$options['header_icon'] || ( RDTheme::$options['header_btn_txt'] && RDTheme::$options['header_btn_url'] ) ? true: false;
$login_icon_title = is_user_logged_in() ? esc_html__( 'My Account', 'classima' ) : esc_html__( 'Login/Register', 'classima' );
?>
<div class="main-header-inner">
	<div class="site-branding">
		<a class="dark-logo" href="<?php echo esc_url( home_url( '/' ) );?>"><img src="<?php echo esc_url( $dark_logo );?>" alt="<?php esc_attr( bloginfo( 'name' ) ) ;?>"></a>
		<a class="light-logo" href="<?php echo esc_url( home_url( '/' ) );?>"><img src="<?php echo esc_url( $light_logo );?>" alt="<?php esc_attr( bloginfo( 'name' ) ) ;?>"></a>
	</div>

	<div class="main-navigation-area">
		<div id="main-navigation" class="main-navigation"><?php wp_nav_menu( $nav_menu_args );?></div>
	</div>

	<?php if ( $has_header_icons ): ?>

		<div class="header-icon-area">

			<?php if ( Helper::is_chat_enabled() ): ?>
				<a class="header-chat-icon rtcl-chat-unread-count" title="<?php esc_html_e( 'Chat','classima' );?>" href="<?php echo esc_url( Link::get_my_account_page_link( 'chat' ) ); ?>"><i class="fa fa-comments-o" aria-hidden="true"></i></a>
			<?php endif; ?>

			<?php if ( RDTheme::$options['header_icon'] && class_exists( 'Rtcl' ) ): ?>
				<a class="header-login-icon" data-toggle="tooltip" title="<?php echo esc_attr( $login_icon_title );?>" href="<?php echo esc_url( Link::get_my_account_page_link() ); ?>"><i class="fa fa-user-o" aria-hidden="true"></i></a>
			<?php endif; ?>

			<?php if ( RDTheme::$options['header_btn_txt'] && RDTheme::$options['header_btn_url'] ): ?>
				<div class="header-btn-area">
					<a class="header-btn" href="<?php echo esc_url( RDTheme::$options['header_btn_url'] );?>"><i class="fa fa-plus" aria-hidden="true"></i><?php echo esc_html( RDTheme::$options['header_btn_txt'] );?></a>
				</div>
			<?php endif; ?>
		</div>
		
	<?php endif; ?>
</div>