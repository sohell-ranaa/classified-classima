<?php


namespace Rtcl\Traits;


use Rtcl\Helpers\Functions;

trait SettingsTrait {

	static function is_enable_chat() {
		return Functions::get_option_item( 'rtcl_chat_settings', 'enable', false, 'checkbox' );
	}

	static function is_enable_chat_unread_message_email() {
		return self::is_enable_chat() && Functions::get_option_item( 'rtcl_chat_settings', 'unread_message_email', false, 'checkbox' );
	}

	static function get_privacy_policy_page_id() {
		$page_id = Functions::get_option_item( 'rtcl_account_settings', 'page_for_privacy_policy', 0 );

		return apply_filters( 'rtcl_privacy_policy_page_id', 0 < $page_id ? absint( $page_id ) : 0 );
	}

	static function get_terms_and_conditions_page_id() {
		$page_id = Functions::get_option_item( 'rtcl_account_settings', 'page_for_terms_and_conditions', 0 );

		return apply_filters( 'rtcl_terms_and_conditions_page_id', 0 < $page_id ? absint( $page_id ) : 0 );
	}

}