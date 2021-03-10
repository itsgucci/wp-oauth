<?php


require('partials/sign-in-settings.php');
require('partials/customization.php');
require('partials/addapp.php');
require('partials/updateapp.php');
require('partials/app-list.php');
require('partials/attr-role-mapping.php');

class Mo_OAuth_Client_Admin_Apps {
	
	public static function sign_in_settings() {
		mo_oauth_client_sign_in_settings_ui();
	}
	
	public static function customization() {
		mo_oauth_client_customization_ui();
	}
	
	public static function applist() {
		mo_oauth_client_applist_page();
	}
	
	public static function add_app() {
		mo_oauth_client_add_app_page();
	}
	
	public static function update_app($appname) {
		mo_oauth_client_update_app_page($appname);
	}

	public static function attribute_role_mapping() {
		mo_oauth_client_attribite_role_mapping_ui();
	}
}

?>