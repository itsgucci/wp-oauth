<?php


require('partials/register.php');
require('partials/verify-password.php');
require('partials/verify-otp.php');

class Mo_OAuth_Client_Admin_Account {
	
	public static function register() {
		if(!mo_oauth_is_customer_registered()){
			mo_oauth_client_register_ui();
		} else {
			mo_oauth_client_show_customer_info();
		}
	}
	
	public static function verify_password() {
		mo_oauth_client_verify_password_ui();
	}
	
	public static function otp_verification() {
		mo_oauth_client_otp_verification_ui();
	}

}

?>