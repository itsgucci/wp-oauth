<?php
/**
 * Plugin Name: OAuth Single Sign On - SSO (OAuth Client)
 * Plugin URI: miniorange-login-with-eve-online-google-facebook
 * Description: This WordPress Single Sign-On plugin allows login into WordPress with your Azure AD B2C, AWS Cognito, Centrify, Salesforce, Discord, WordPress or other custom OAuth 2.0 / OpenID Connect providers. WordPress OAuth Client plugin works with any Identity provider that conforms to the OAuth 2.0 and OpenID Connect (OIDC) 1.0 standard.
 * Version: 6.19.5
 * Author: miniOrange
 * Author URI: https://www.miniorange.com
 * License: MIT/Expat
* License URI: https://docs.miniorange.com/mit-license
*/

require('handler/oauth_handler.php');
include_once dirname( __FILE__ ) . '/class-mo-oauth-widget.php';
require('class-customer.php');
require plugin_dir_path( __FILE__ ) . 'includes/class-mo-oauth-client.php';
require('views/feedback_form.php');
//require_once 'views/VisualTour/class-mocvisualtour.php';
require('constants.php');

class mo_oauth {

	function __construct() {

		add_action( 'admin_init',  array( $this, 'miniorange_oauth_save_settings' ) );
		add_action( 'plugins_loaded',  array( $this, 'mo_login_widget_text_domain' ) );
		register_deactivation_hook(__FILE__, array( $this, 'mo_oauth_deactivate'));
		//add_action( 'admin_init', array( $this, 'tutorial' ) );
		register_activation_hook(__FILE__, array($this,'mo_oauth_set_cron_job'));
		add_shortcode('mo_oauth_login', array( $this,'mo_oauth_shortcode_login'));
		add_action( 'admin_footer', array( $this, 'mo_oauth_client_feedback_request' ) );
		add_action( 'check_if_wp_rest_apis_are_open', array( $this, 'mo_oauth_scheduled_task' ) );

	}

	/*function tutorial($page) {
		if ( class_exists( 'MOCVisualTour' ) ) {
			$tour = new MOCVisualTour();
		}
	}*/

	function mo_oauth_success_message() {
		$class = "error";
		$message = get_option('message');
		echo "<div class='" . $class . "'> <p>" . $message . "</p></div>";
	}

	function mo_oauth_client_feedback_request() {
		mo_oauth_client_display_feedback_form();
	}

	function mo_oauth_error_message() {
		$class = "updated";
		$message = get_option('message');
		echo "<div class='" . $class . "'><p>" . $message . "</p></div>";
	}
	/*
		*   Custom Intervals
		*	Name             dispname                Interval
		*   three_minutes    Every Three minutes	 3  * MINUTE_IN_SECONDS (3 * 60)
		*   five_minutes     Every Five minutes	     5  * MINUTE_IN_SECONDS (5 * 60)
		*   ten_minutes      Every Ten minutes	     10 * MINUTE_IN_SECONDS (10 * 60)
		*   three_days     	 Every Three days	     3  * 24 * 60 * MINUTE_IN_SECONDS
		*   five_days      	 Every Five days	     5  * 24 * 60 * MINUTE_IN_SECONDS
		*
		*
		*   Default Intervals
		*   Name         dispname        Interval (in sec)
		*   hourly       Once Hourly	 3600 (1 hour)
		*   twicedaily   Twice Daily	 43200 (12 hours)
		*   daily        Once Daily	     86400 (1 day) 
		*   weekly       Once Weekly	 604800 (1 week) 
	*/

	public function mo_oauth_set_cron_job()
	{
		
		//add_filter( 'cron_schedules', array($this,'add_cron_interval'));// uncomment this for custom intervals
		
		if (!wp_next_scheduled('check_if_wp_rest_apis_are_open')) {
			
			//$custom_interval=apply_filters('cron_schedules',array('three_minutes'));//uncomment this for custom interval		
      		wp_schedule_event( time()+604800, 'weekly', 'check_if_wp_rest_apis_are_open' );// update timestamp and name according to interval
 		}

	}
	public function mo_oauth_deactivate() {
		delete_option('host_name');
		delete_option('mo_oauth_client_new_registration');
		delete_option('mo_oauth_client_admin_phone');
		delete_option('mo_oauth_client_verify_customer');
		delete_option('mo_oauth_client_admin_customer_key');
		delete_option('mo_oauth_client_admin_api_key');
		delete_option('mo_oauth_client_new_customer');
		delete_option('mo_oauth_client_customer_token');
		delete_option('message');
		delete_option('mo_oauth_client_registration_status');
		delete_option('mo_oauth_client_show_mo_server_message');
		delete_option('mo_oauth_log');
		delete_option('mo_oauth_debug');
		wp_clear_scheduled_hook( 'check_if_wp_rest_apis_are_open' );
	}


		function add_cron_interval( $schedules ) { 
		
		if(isset($schedules['three_minutes']))
		{
    		$schedules['three_minutes'] = array(
        	'interval' => 3 * MINUTE_IN_SECONDS,
        	'display'  => esc_html__( 'Every Three minutes' ), );
		}else if(isset($schedules['five_minutes']))
		{
    		$schedules['five_minutes'] = array(
        	'interval' => 5 * MINUTE_IN_SECONDS,
        	'display'  => esc_html__( 'Every Five minutes' ), );
		}else if(isset($schedules['ten_minutes']))
		{
    		$schedules['ten_minutes'] = array(
        	'interval' => 10 * MINUTE_IN_SECONDS,
        	'display'  => esc_html__( 'Every Ten minutes' ), );
		}else if(isset($schedules['three_days']))
		{
    		$schedules['three_days'] = array(
        	'interval' => 3 * 24 * 60 * MINUTE_IN_SECONDS,
        	'display'  => esc_html__( 'Every Three days' ), );
		}else if(isset($schedules['five_days']))
		{
    		$schedules['five_days'] = array(
        	'interval' => 5 * 24 * 60 * MINUTE_IN_SECONDS,
        	'display'  => esc_html__( 'Every Five days' ), );
		}
		
    return $schedules;
}

	function mo_oauth_scheduled_task() {    
    	//error_log("seems to get here on ".date('m/d/Y H:i:s', time()));
    	$url=site_url()."/wp-json/wp/v2/posts";
    	$response = wp_remote_get($url, array(
			'method' => 'GET',
			'timeout' => 45,
			'redirection' => 5,
			'httpversion' => 1.0,
			'blocking' => true,
			'headers' => array(),
			'cookies' => array(),
			'sslverify' => false,
		));
    	
    	//error_log(print_r($response,TRUE));
    	if(is_wp_error( $response ))
    	{
    		if(is_object($response))
    			error_log(print_r($response->errors,TRUE));
    		return;
    	}
    	$code=wp_remote_retrieve_response_code($response);
    	//error_log($code);
    	if(isset($code) && $code=='200')
    	{
    		//error_log($response['body']);
    		
    		if(isset($response))
    		{
    			update_option( 'mo_oauth_client_show_rest_api_message', true);	
    			//error_log("option set mo_oauth_client_show_rest_api_message=".get_option("mo_oauth_client_show_rest_api_message"));			
    		}
    		
    	}		
    	
    	
  }


	function mo_login_widget_text_domain(){
		load_plugin_textdomain( 'flw', FALSE, basename( dirname( __FILE__ ) ) . '/languages' );
	}

	private function mo_oauth_show_success_message() {
		remove_action( 'admin_notices', array( $this, 'mo_oauth_success_message') );
		add_action( 'admin_notices', array( $this, 'mo_oauth_error_message') );
	}

	private function mo_oauth_show_error_message() {
		remove_action( 'admin_notices', array( $this, 'mo_oauth_error_message') );
		add_action( 'admin_notices', array( $this, 'mo_oauth_success_message') );
	}

	public function mo_oauth_check_empty_or_null( $value ) {
		if( ! isset( $value ) || empty( $value ) ) {
			return true;
		}
		return false;
	}

	function miniorange_oauth_save_settings(){

		if ( isset( $_POST['option'] ) and sanitize_text_field( wp_unslash( $_POST['option'] ) ) == "mo_oauth_client_mo_server_message" && isset( $_REQUEST['mo_oauth_mo_server_message_form_field'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['mo_oauth_mo_server_message_form_field'] ) ), 'mo_oauth_mo_server_message_form' )) {
			update_option( 'mo_oauth_client_show_mo_server_message', 1 );
			return;
		}
		if ( isset( $_POST['option'] ) and sanitize_text_field( wp_unslash( $_POST['option'] ) ) == "mo_oauth_client_rest_api_message" && isset( $_REQUEST['mo_oauth_client_rest_api_form_field'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['mo_oauth_client_rest_api_form_field'] ) ), 'mo_oauth_client_rest_api_form' )) {
			
			delete_option('mo_oauth_client_show_rest_api_message');
			wp_clear_scheduled_hook( 'check_if_wp_rest_apis_are_open' );
			return;
		}

		if ( isset( $_POST['option'] ) and sanitize_text_field( wp_unslash( $_POST['option'] ) ) == "clear_pointers" && isset( $_REQUEST['mo_oauth_clear_pointers_form_field'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['mo_oauth_clear_pointers_form_field'] ) ), 'mo_oauth_clear_pointers_form' )) {
			update_user_meta(get_current_user_id(),'dismissed_wp_pointers','');
			return;
		}

		if ( isset( $_POST['option'] ) and sanitize_text_field( wp_unslash( $_POST['option'] ) ) == "change_miniorange" && isset( $_REQUEST['mo_oauth_goto_login_form_field'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['mo_oauth_goto_login_form_field'] ) ), 'mo_oauth_goto_login_form' )) {
			if( current_user_can( 'administrator' ) ) {
				$this->mo_oauth_deactivate();
				return;
			}
		}

		if( isset( $_POST['option'] ) and sanitize_text_field( wp_unslash( $_POST['option'] ) ) == "mo_oauth_register_customer" && isset( $_REQUEST['mo_oauth_register_form_field'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['mo_oauth_register_form_field'] ) ), 'mo_oauth_register_form' )) {
			if( current_user_can( 'administrator' ) ) {
				$email = '';
				$phone = '';
				$password = '';
				$confirmPassword = '';
				$fname = '';
				$lname = '';
				$company = '';
				if( $this->mo_oauth_check_empty_or_null( $_POST['email'] ) || $this->mo_oauth_check_empty_or_null( $_POST['password'] ) || $this->mo_oauth_check_empty_or_null( $_POST['confirmPassword'] ) ) {
					update_option( 'message', 'All the fields are required. Please enter valid entries.');
					$this->mo_oauth_show_error_message();
					return;
				} else if( strlen( $_POST['password'] ) < 8 || strlen( $_POST['confirmPassword'] ) < 8){
					update_option( 'message', 'Choose a password with minimum length 8.');
					$this->mo_oauth_show_error_message();
					return;
				} else{
					$email = sanitize_email( $_POST['email'] );
					$phone = stripslashes( $_POST['phone'] );
					$password = stripslashes( $_POST['password'] );
					$confirmPassword = stripslashes( $_POST['confirmPassword'] );
					$fname = stripslashes( $_POST['fname'] );
					$lname = stripslashes( $_POST['lname'] );
					$company = stripslashes( $_POST['company'] );
				}

				update_option( 'mo_oauth_admin_email', $email );
				update_option( 'mo_oauth_client_admin_phone', $phone );
				update_option( 'mo_oauth_admin_fname', $fname );
				update_option( 'mo_oauth_admin_lname', $lname );
				update_option( 'mo_oauth_admin_company', $company );

				if( mo_oauth_is_curl_installed() == 0 ) {
					return $this->mo_oauth_show_curl_error();
				}

				if( strcmp( $password, $confirmPassword) == 0 ) {
					update_option( 'password', $password );
					$customer = new Mo_OAuth_Client_Customer();
					$email=get_option('mo_oauth_admin_email');
					$content = json_decode($customer->check_customer(), true);
					if( strcasecmp( $content['status'], 'CUSTOMER_NOT_FOUND') == 0 ){
						$response = json_decode($customer->create_customer(), true);
						if(strcasecmp($response['status'], 'SUCCESS') == 0) {
							$this->mo_oauth_get_current_customer();
							wp_redirect( admin_url( '/admin.php?page=mo_oauth_settings&tab=licensing' ), 301 );
							exit;
						} if( strcasecmp($response['status'], 'FAILED') == 0 && strcasecmp($response['message'], 'Email is not enterprise email.') == 0 ) {
                            update_option( 'message', 'Please use your Enterprise email for registration.');
                        } else {
							update_option( 'message', 'Failed to create customer. Try again.');
						}
						$this->mo_oauth_show_success_message();
					} else {
						$this->mo_oauth_get_current_customer();
					}
				} else {
					update_option( 'message', 'Passwords do not match.');
					delete_option('mo_oauth_client_verify_customer');
					$this->mo_oauth_show_error_message();
				}
			}
		}

		if( isset( $_POST['option'] ) and sanitize_text_field( wp_unslash( $_POST['option'] ) ) == "mo_oauth_client_goto_login" && isset( $_REQUEST['mo_oauth_goto_login_form_field'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['mo_oauth_goto_login_form_field'] ) ), 'mo_oauth_goto_login_form' )) {
			delete_option( 'mo_oauth_client_new_registration' );
			update_option( 'mo_oauth_client_verify_customer', 'true' );
		}

		 if(isset($_POST['option']) and sanitize_text_field( wp_unslash( $_POST['option'] ) ) == "mo_oauth_validate_otp" && isset( $_REQUEST['mo_oauth_verify_otp_form_field'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['mo_oauth_verify_otp_form_field'] ) ), 'mo_oauth_verify_otp_form' )){
		 	if ( current_user_can( 'administrator' ) ) {
		 		if( mo_oauth_is_curl_installed() == 0 ) {
					return $this->mo_oauth_show_curl_error();
				}
				//validation and sanitization
				$otp_token = '';
				if( $this->mo_oauth_check_empty_or_null( $_POST['mo_oauth_otp_token'] ) ) {
					update_option( 'message', 'Please enter a value in OTP field.');
					update_option('mo_oauth_client_registration_status','MO_OTP_VALIDATION_FAILURE');
					$this->mo_oauth_show_error_message();
					return;
				} else{
					$otp_token = stripslashes( $_POST['mo_oauth_otp_token'] );
				}

				$customer = new Mo_OAuth_Client_Customer();
				$content = json_decode($customer->validate_otp_token($_SESSION['mo_oauth_transactionId'], $otp_token ),true);
				if(strcasecmp($content['status'], 'SUCCESS') == 0) {
					$this->create_customer();
				}else{
					update_option( 'message','Invalid one time passcode. Please enter a valid OTP.');
					update_option('mo_oauth_client_registration_status','MO_OTP_VALIDATION_FAILURE');
					$this->mo_oauth_show_error_message();
				}
		 	}
		}

		if( isset( $_POST['option'] ) and sanitize_text_field( wp_unslash( $_POST['option'] ) ) == "mo_oauth_verify_customer" && isset( $_REQUEST['mo_oauth_verify_password_form_field'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['mo_oauth_verify_password_form_field'] ) ), 'mo_oauth_verify_password_form' )) {	//register the admin to miniOrange
			if( current_user_can( 'administrator' ) ) {
				if( mo_oauth_is_curl_installed() == 0 ) {
					return $this->mo_oauth_show_curl_error();
				}
				//validation and sanitization
				$email = '';
				$password = '';
				if( $this->mo_oauth_check_empty_or_null( $_POST['email'] ) || $this->mo_oauth_check_empty_or_null( $_POST['password'] ) ) {
					update_option( 'message', 'All the fields are required. Please enter valid entries.');
					$this->mo_oauth_show_error_message();
					return;
				} else{
					$email = sanitize_email( $_POST['email'] );
					$password = stripslashes( $_POST['password'] );
				}

				update_option( 'mo_oauth_admin_email', $email );
				update_option( 'password', $password );
				$customer = new Mo_OAuth_Client_Customer();
				$content = $customer->get_customer_key();
				$customerKey = json_decode( $content, true );
				if( json_last_error() == JSON_ERROR_NONE ) {
					update_option( 'mo_oauth_client_admin_customer_key', $customerKey['id'] );
					update_option( 'mo_oauth_client_admin_api_key', $customerKey['apiKey'] );
					update_option( 'mo_oauth_client_customer_token', $customerKey['token'] );
					if( isset( $customerKey['phone'] ) )
						update_option( 'mo_oauth_client_admin_phone', $customerKey['phone'] );
					delete_option('password');
					update_option( 'message', 'Customer retrieved successfully');
					delete_option('mo_oauth_client_verify_customer');
					delete_option('mo_oauth_client_new_registration');
					$this->mo_oauth_show_success_message();
				} else {
					update_option( 'message', 'Invalid username or password. Please try again.');
					$this->mo_oauth_show_error_message();
				}
			}
		} 
		else if( isset( $_POST['option'] ) and sanitize_text_field( wp_unslash( $_POST['option'] ) ) == "mo_oauth_add_app" && isset( $_REQUEST['mo_oauth_add_app_form_field'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['mo_oauth_add_app_form_field'] ) ), 'mo_oauth_add_app_form' )) {
			if( current_user_can( 'administrator' ) ) {
				$scope = '';
				$clientid = '';
				$clientsecret = '';
				if($this->mo_oauth_check_empty_or_null($_POST['mo_oauth_client_id']) || $this->mo_oauth_check_empty_or_null($_POST['mo_oauth_client_secret'])) {
					update_option( 'message', 'Please enter valid Client ID and Client Secret.');
					$this->mo_oauth_show_error_message();
					return;
				} else {
                    // $callback_url = stripslashes($_POST['mo_oauth_callback_url']);
                    $callback_url=site_url();
                    $scope = isset($_POST['mo_oauth_scope']) ? stripslashes($_POST['mo_oauth_scope']) : "";
                    $clientid = stripslashes(trim($_POST['mo_oauth_client_id']));
                    $clientsecret = stripslashes(trim($_POST['mo_oauth_client_secret']));
                    $appname = rtrim(stripslashes( $_POST['mo_oauth_custom_app_name'] ), " ");
                    $ssoprotocol = stripslashes($_POST['mo_oauth_app_type']);
                    $selectedapp = stripslashes($_POST['mo_oauth_app_name']);
                    $send_headers = isset($_POST['mo_oauth_authorization_header']) ? sanitize_post($_POST['mo_oauth_authorization_header']) : "0";
                    $send_body = isset($_POST['mo_oauth_body']) ? sanitize_post($_POST['mo_oauth_body']) : "0";
                    $send_state=isset($_POST['mo_oauth_state']) ? (int)filter_var($_POST['mo_oauth_state'], FILTER_SANITIZE_NUMBER_INT) : 0;
                    $show_on_login_page = isset($_POST['mo_oauth_show_on_login_page']) ? (int)filter_var($_POST['mo_oauth_show_on_login_page'], FILTER_SANITIZE_NUMBER_INT) : 0;

                    if ($selectedapp == 'wso2') {
                        update_option('mo_oauth_client_custom_token_endpoint_no_csecret', true);
                    }

                    if (get_option('mo_oauth_apps_list'))
                        $appslist = get_option('mo_oauth_apps_list');
                    else
                        $appslist = array();

					$email_attr = "";
					$name_attr = "";
                    $newapp = array();

                    $isupdate = false;
                    foreach ($appslist as $key => $currentapp) {
                        if ($appname == $key) {
                            $newapp = $currentapp;
                            $isupdate = true;
                            break;
                        }
                    }

                    if (!$isupdate && sizeof($appslist) > 0) {
                        update_option('message', 'You can only add 1 application with free version. Upgrade to enterprise version if you want to add more applications.');
                        $this->mo_oauth_show_error_message();
                        return;
                    }


					$newapp['clientid'] = $clientid;
					$newapp['clientsecret'] = $clientsecret;
					$newapp['scope'] = $scope;
					$newapp['redirecturi'] = $callback_url;
					$newapp['ssoprotocol'] = $ssoprotocol;
                    $newapp['send_headers'] = $send_headers;
                    $newapp['send_body'] = $send_body;
                    $newapp['send_state']=$send_state;
                    $newapp['show_on_login_page'] = $show_on_login_page;
                    if (isset($_POST['mo_oauth_app_type'])) {
                        $newapp['apptype'] = stripslashes($_POST['mo_oauth_app_type']);
                    } else {
                        $newapp['apptype'] = stripslashes('oauth');
                    }

                    if(isset($_POST['mo_oauth_app_name'])) {
                        $newapp['appId'] = stripslashes( $_POST['mo_oauth_app_name'] );
                    }

                    if (isset($_POST['mo_oauth_discovery']) && $_POST['mo_oauth_discovery'] != "") {
                        add_option('mo_existing_app_flow', true);
                        $discovery_endpoint = $_POST['mo_oauth_discovery'];
                        if(isset($_POST['mo_oauth_provider_domain'])) {
                            $domain = stripslashes(rtrim($_POST['mo_oauth_provider_domain'],"/"));
                            $discovery_endpoint = str_replace("domain", $domain, $discovery_endpoint);
                            $newapp['domain'] = $domain;
                        } elseif(isset($_POST['mo_oauth_provider_tenant'])) {
                            $tenant = stripslashes(trim($_POST['mo_oauth_provider_tenant']));
                            $discovery_endpoint = str_replace("tenant", $tenant, $discovery_endpoint);
                            $newapp['tenant'] = $tenant;
                        }

                        if(isset($_POST['mo_oauth_provider_policy'])) {
                            $policy = stripslashes(trim($_POST['mo_oauth_provider_policy']));
                            $discovery_endpoint = str_replace("policy", $policy, $discovery_endpoint);
                            $newapp['policy'] = $policy;
                        } elseif(isset($_POST['mo_oauth_provider_realm'])) {
                            $realm = stripslashes(trim($_POST['mo_oauth_provider_realm']));
                            $discovery_endpoint = str_replace("realmname", $realm, $discovery_endpoint);
                            $newapp['realm'] = $realm;
                        }
                        error_log("OAuth Client Endpoint: ");
                        error_log($discovery_endpoint);
                        $provider_se = null;

                        if((filter_var($discovery_endpoint, FILTER_VALIDATE_URL))){
                            update_option('mo_oc_valid_discovery_ep', true);
                            $arrContextOptions=array( 
                        		"ssl"=>array(
                        			"verify_peer"=>false,
                        			"verify_peer_name"=>false,
                        		),
                        	);  
                        	$content=@file_get_contents($discovery_endpoint,false, stream_context_create($arrContextOptions));
                            $provider_se = array();
                            if($content)
                            	$provider_se=json_decode($content);
                            $scope1 = isset($provider_se->scopes_supported[0])?$provider_se->scopes_supported[0] : "";
                            $scope2 = isset($provider_se->scopes_supported[1])?$provider_se->scopes_supported[1] : "";
                            $pscope = stripslashes($scope1)." ".stripslashes($scope2);
                            $newapp['scope'] = (isset($scope) && $scope != "" ) ? $scope : $pscope;
                            $newapp['authorizeurl'] = isset($provider_se->authorization_endpoint) ? stripslashes($provider_se->authorization_endpoint) : "";
                            $newapp['accesstokenurl'] = isset($provider_se->token_endpoint) ? stripslashes($provider_se->token_endpoint ) : "";
                            $newapp['resourceownerdetailsurl'] = isset($provider_se->userinfo_endpoint) ? stripslashes($provider_se->userinfo_endpoint) : "";
                            $newapp['discovery'] = $discovery_endpoint;
                        }
                    } else {
                        update_option('mo_oc_valid_discovery_ep', true);
                        $newapp['authorizeurl'] = isset($_POST['mo_oauth_authorizeurl']) ? stripslashes($_POST['mo_oauth_authorizeurl']) : "";
                        $newapp['accesstokenurl'] = isset($_POST['mo_oauth_accesstokenurl']) ? stripslashes($_POST['mo_oauth_accesstokenurl']) : "";
                        $newapp['resourceownerdetailsurl'] = isset($_POST['mo_oauth_resourceownerdetailsurl']) ? stripslashes($_POST['mo_oauth_resourceownerdetailsurl']) : "";
                    }

					$appslist[$appname] = $newapp;
					update_option('mo_oauth_apps_list', $appslist);

					if( isset($_POST['mo_oauth_discovery']) && !$provider_se)
                    {
                        update_option( 'message', '<strong>Error: </strong> Incorrect Domain/Tenant/Policy/Realm. Please configure with correct values and try again.' );
                        update_option( 'mo_discovery_validation', 'invalid');
                        $this->mo_oauth_show_error_message();
                    } else {
                        update_option('message', 'Your settings are saved successfully.');
                        update_option('mo_discovery_validation', 'valid');
                        $this->mo_oauth_show_success_message();
//                    }
                        if (!isset($newapp['username_attr']) || empty($newapp['username_attr']) && get_option('mo_oauth_apps_list') ) {
                            $notices = get_option('mo_oauth_client_notice_messages');
                            $notices['attr_mapp_notice'] = 'Please map the attributes by going to the <a href="' . admin_url('admin.php?page=mo_oauth_settings&tab=attributemapping') . '">Attribute/Role Mapping</a> Tab.';
                            update_option('mo_oauth_client_notice_messages', $notices);
                        }
                    }
				}
			}
		}
		else if( isset( $_POST['option'] ) and sanitize_text_field( wp_unslash( $_POST['option'] ) ) == "mo_oauth_app_customization" && isset( $_REQUEST['mo_oauth_app_customization_form_field'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['mo_oauth_app_customization_form_field'] ) ), 'mo_oauth_app_customization_form' )) {

			if( current_user_can( 'administrator' ) ) {
				update_option( 'mo_oauth_icon_width', stripslashes($_POST['mo_oauth_icon_width']));
				update_option( 'mo_oauth_icon_height', stripslashes($_POST['mo_oauth_icon_height']));
				update_option( 'mo_oauth_icon_margin', stripslashes($_POST['mo_oauth_icon_margin']));
				update_option('mo_oauth_icon_configure_css', stripcslashes(stripslashes($_POST['mo_oauth_icon_configure_css'])));
				update_option( 'message', 'Your settings were saved' );
				$this->mo_oauth_show_success_message();
			}
		}
		else if( isset( $_POST['option'] ) and sanitize_text_field( wp_unslash( $_POST['option'] ) ) == "mo_oauth_attribute_mapping"  && isset( $_REQUEST['mo_oauth_attr_role_mapping_form_field'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['mo_oauth_attr_role_mapping_form_field'] ) ), 'mo_oauth_attr_role_mapping_form' )) {

			if( current_user_can( 'administrator' ) ) {
				$appname = isset($_POST['mo_oauth_app_name']) ? stripslashes( $_POST['mo_oauth_app_name'] ) : '';
				$username_attr = isset($_POST['mo_oauth_username_attr']) ? stripslashes( $_POST['mo_oauth_username_attr'] ) : '';
				$attr_option = isset($_POST['mo_attr_option']) ? stripslashes( $_POST['mo_attr_option'] ) : '';
				if ( empty( $appname ) ) {
					update_option( 'message', 'You MUST configure an application before you map attributes.' );
					$this->mo_oauth_show_error_message();
					return;
				}
				$appslist = get_option('mo_oauth_apps_list');
				foreach($appslist as $key => $currentapp){
					if($appname == $key){
						$currentapp['username_attr'] = $username_attr;
						$appslist[$key] = $currentapp;
						break;
					}
				}

				update_option('mo_oauth_apps_list', $appslist);
				update_option( 'message', 'Your settings are saved successfully.' );
				update_option('mo_attr_option', $attr_option);
				$this->mo_oauth_show_success_message();
				$notices = get_option( 'mo_oauth_client_notice_messages' );
				if( isset( $notices['attr_mapp_notice'] ) ) {
					unset( $notices['attr_mapp_notice'] );
					update_option( 'mo_oauth_client_notice_messages', $notices );
				}
			}
		}
		
		elseif( isset( $_POST['option'] ) and sanitize_text_field( wp_unslash( $_POST['option'] ) ) == "mo_oauth_contact_us_query_option" && isset( $_REQUEST['mo_oauth_support_form_field'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['mo_oauth_support_form_field'] ) ), 'mo_oauth_support_form' )) {

			if( current_user_can( 'administrator' ) ) {
				if( mo_oauth_is_curl_installed() == 0 ) {
					return $this->mo_oauth_show_curl_error();
				}
				// Contact Us query
				$email = sanitize_email( $_POST['mo_oauth_contact_us_email'] );
				$phone = stripslashes( $_POST['mo_oauth_contact_us_phone'] );
				$query = stripslashes( $_POST['mo_oauth_contact_us_query'] );
				$send_config = isset( $_POST['mo_oauth_send_plugin_config'] );
				$customer = new Mo_OAuth_Client_Customer();
				if ( $this->mo_oauth_check_empty_or_null( $email ) || $this->mo_oauth_check_empty_or_null( $query ) ) {
					update_option('message', 'Please fill up Email and Query fields to submit your query.');
					$this->mo_oauth_show_error_message();
				} else {
					// $submited = json_decode( $customer->mo_oauth_send_email_alert( $email, $phone, $query, "Query for WP OAuth Single Sign On - ".$email ), true );
					// update_option('message', 'Thanks for getting in touch! We shall get back to you shortly.');
					// $this->mo_oauth_show_success_message();
					$submited = $customer->submit_contact_us( $email, $phone, $query, $send_config );
					if ( $submited == false ) {
						update_option('message', 'Your query could not be submitted. Please try again.');
						$this->mo_oauth_show_error_message();
					} else {
						update_option('message', 'Thanks for getting in touch! We shall get back to you shortly.');
						$this->mo_oauth_show_success_message();
					}
				}
			}	
		} 
		elseif( isset( $_POST['option'] ) and sanitize_text_field( wp_unslash( $_POST['option'] ) ) == "mo_oauth_setup_call_option" && isset( $_REQUEST['mo_oauth_setup_call_form_field'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['mo_oauth_setup_call_form_field'] ) ), 'mo_oauth_setup_call_form' )) {

			if( current_user_can( 'administrator' ) ) {
				if( mo_oauth_is_curl_installed() == 0 ) {
					return $this->mo_oauth_show_curl_error();
				}
				$email = sanitize_email( $_POST['mo_oauth_setup_call_email'] );
				$issue = stripslashes( $_POST['mo_oauth_setup_call_issue'] );
				$desc = stripslashes( $_POST['mo_oauth_setup_call_desc'] );
				$call_date = $_POST['mo_oauth_setup_call_date'];
				$time_diff = $_POST['mo_oauth_time_diff'];
				$call_time = $_POST['mo_oauth_setup_call_time'];
				$customer = new Mo_OAuth_Client_Customer();
				if ( $this->mo_oauth_check_empty_or_null( $email ) || $this->mo_oauth_check_empty_or_null( $issue ) || $this->mo_oauth_check_empty_or_null( $call_date ) || $this->mo_oauth_check_empty_or_null( $time_diff ) || $this->mo_oauth_check_empty_or_null( $call_time ) ) {
					update_option('message', 'Please fill up all the required fields.');
					$this->mo_oauth_show_error_message();
				} else {
					// Please modify the $time_diff to test for the different timezones.
					// Note - $time_diff for IST is -330
					// $time_diff = 240;
					$hrs = floor(abs($time_diff)/60);
					$mins = fmod(abs($time_diff),60);
					if($mins == 0) {
						$mins = '00';
					}
					$sign = '+';
					if($time_diff > 0) {
						$sign = '-';
					}
					$call_time_zone = 'UTC '.$sign.' '.$hrs.':'.$mins;
					$call_date = date("jS F",strtotime($call_date));
					
					//code to convert local time to IST
					$local_hrs = explode(':', $call_time)[0];
					$local_mins = explode(':', $call_time)[1];
					$call_time_mins = ($local_hrs * 60) + $local_mins;
					$ist_time = $call_time_mins + $time_diff + 330;
					$ist_date = $call_date;
					if($ist_time > 1440) {
						$ist_time = fmod($ist_time,1440);
						$ist_date = date("jS F", strtotime("1 day", strtotime($call_date)));
					}
					else if($ist_time < 0) {
						$ist_time = 1440 + $ist_time;
						$ist_date = date("jS F", strtotime("-1 day", strtotime($call_date)));
					}
					$ist_hrs = floor($ist_time/60);
					$ist_hrs = sprintf("%02d", $ist_hrs);

					$ist_mins = fmod($ist_time,60);
					$ist_mins = sprintf("%02d", $ist_mins);
					
					$ist_time = $ist_hrs.':'.$ist_mins;

					$customer->submit_setup_call( $email, $issue, $desc, $call_date, $call_time_zone, $call_time, $ist_date, $ist_time);
					update_option('message', 'Thanks for getting in touch! We shall get back to you shortly.');
					$this->mo_oauth_show_success_message();
				}
			}	
		} 
		elseif( isset( $_POST['option'] ) and sanitize_text_field( wp_unslash( $_POST['option'] ) ) == "mo_oauth_client_demo_request_form" && isset($_REQUEST['mo_oauth_client_demo_request_field']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_REQUEST['mo_oauth_client_demo_request_field'])), 'mo_oauth_client_demo_request_form') ) {

			if( current_user_can( 'administrator' ) ) {
				if( mo_oauth_is_curl_installed() == 0 ) {
					return $this->mo_oauth_show_curl_error();
				}
				// Demo Request
				$email = sanitize_email( $_POST['mo_auto_create_demosite_email'] );
				$demo_plan = stripslashes( $_POST['mo_auto_create_demosite_demo_plan'] );
				$query = stripslashes( $_POST['mo_auto_create_demosite_usecase'] );

				if ( $this->mo_oauth_check_empty_or_null( $email ) || $this->mo_oauth_check_empty_or_null( $demo_plan ) || $this->mo_oauth_check_empty_or_null($query) ) {
					update_option('message', 'Please fill up Usecase, Email field and Requested demo plan to submit your query.');
					$this->mo_oauth_show_error_message();
				} else {

					$demosite_status = (bool) @fsockopen('demo.miniorange.com', 443, $iErrno, $sErrStr, 5);

					if ( $demosite_status && "Not Sure" !==  $demo_plan ) {
						$url = 'http://demo.miniorange.com/wordpress-oauth/';
	
						$headers = array( 'Content-Type' => 'application/x-www-form-urlencoded', 'charset' => 'UTF - 8');
						$args = array(
							'method' =>'POST',
							'body' => array(
								'option' => 'mo_auto_create_demosite',
								'mo_auto_create_demosite_email' => $email,
								'mo_auto_create_demosite_usecase' => $query,
								'mo_auto_create_demosite_demo_plan' => $demo_plan,
								'mo_auto_create_demosite_plugin_name' => MO_OAUTH_PLUGIN_SLUG
							),
							'timeout' => '20',
							'redirection' => '5',
							'httpversion' => '1.0',
							'blocking' => true,
							'headers' => $headers,
	
						);
	
						$response = wp_remote_post( $url, $args );
	
						if ( is_wp_error( $response ) ) {
							$error_message = $response->get_error_message();
							echo "Something went wrong: $error_message";
							exit();
						}
						$output = wp_remote_retrieve_body($response);
						$output = json_decode($output);
	
						if(is_null($output)){
							$customer = new Mo_OAuth_Client_Customer();
							$customer->mo_oauth_send_demo_alert( $email, $demo_plan, $query, "WP OAuth Client On Demo Request - ".$email );
							update_option('message', "Thanks Thanks for getting in touch! We shall get back to you shortly.");
							$this->mo_oauth_show_success_message();
						} else {
							if($output->status == 'SUCCESS'){
								update_option('message', $output->message);
								$this->mo_oauth_show_success_message();
							}else{
								update_option('message', $output->message);
								$this->mo_oauth_show_error_message();
							}
						}
					} else {
						$customer = new Mo_OAuth_Client_Customer();
						$customer->mo_oauth_send_demo_alert( $email, $demo_plan, $query, "WP OAuth Client On Demo Request - ".$email );
						update_option('message', "Thanks Thanks for getting in touch! We shall get back to you shortly.");
						$this->mo_oauth_show_success_message();
					}
				}
			}
		}

		else if( isset( $_POST['option'] ) and sanitize_text_field( wp_unslash( $_POST['option'] ) ) == "mo_oauth_resend_otp_email" && isset( $_REQUEST['mo_oauth_resend_otp_form_field'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['mo_oauth_resend_otp_form_field'] ) ), 'mo_oauth_resend_otp_form' )) {
			if( mo_oauth_is_curl_installed() == 0 ) {
				return $this->mo_oauth_show_curl_error();
			}

			$customer = new Mo_OAuth_Client_Customer();
			$email=get_option('mo_oauth_admin_email');
			$content = json_decode($customer->send_otp_token($email, ''), true);
			if(strcasecmp($content['status'], 'SUCCESS') == 0) {
					update_option( 'message', ' A one time passcode is sent to ' . get_option('mo_oauth_admin_email') . ' again. Please check if you got the otp and enter it here.');
					$_SESSION['mo_oauth_transactionId'] = $content['txId'];
					update_option('mo_oauth_client_registration_status','MO_OTP_DELIVERED_SUCCESS');
					$this->mo_oauth_show_success_message();
			}else{
					update_option('message','There was an error in sending email. Please click on Resend OTP to try again.');
					update_option('mo_oauth_client_registration_status','MO_OTP_DELIVERED_FAILURE');
					$this->mo_oauth_show_error_message();
			}
		} else if (isset($_POST ['option']) and sanitize_text_field( wp_unslash( $_POST['option'] ) ) == "mo_oauth_resend_otp_phone" && isset( $_REQUEST['mo_oauth_resend_otp_form_field'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['mo_oauth_resend_otp_form_field'] ) ), 'mo_oauth_resend_otp_form' )) {

				if( mo_oauth_is_curl_installed() == 0 ) {
					return $this->mo_oauth_show_curl_error();
				}
				$phone = get_option('mo_oauth_client_admin_phone');
				$customer = new Mo_OAuth_Client_Customer();
				$content = json_decode($customer->send_otp_token('', $phone, FALSE, TRUE), true);
				if (strcasecmp($content ['status'], 'SUCCESS') == 0) {
					update_option('message', ' A one time passcode is sent to ' . $phone . ' again. Please check if you got the otp and enter it here.');
					update_option('mo_oauth_transactionId', $content ['txId']);
					update_option('mo_oauth_client_registration_status', 'MO_OTP_DELIVERED_SUCCESS_PHONE');
					$this->mo_oauth_show_success_message();
				} else {
					update_option('message', 'There was an error in sending email. Please click on Resend OTP to try again.');
					update_option('mo_oauth_client_registration_status', 'MO_OTP_DELIVERED_FAILURE_PHONE');
					$this->mo_oauth_show_error_message();
				}
			}else if (isset($_POST ['option']) && sanitize_text_field( wp_unslash( $_POST['option'] ) ) == 'mo_oauth_forgot_password_form_option' && isset( $_REQUEST['mo_oauth_forgotpassword_form_field'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['mo_oauth_forgotpassword_form_field'] ) ), 'mo_oauth_forgotpassword_form' )) {

				if( current_user_can( 'administrator' ) ) {
					if (! mo_oauth_is_curl_installed()) {
						update_option('mo_oauth_message', 'ERROR: <a href="http://php.net/manual/en/curl.installation.php" target="_blank">PHP cURL extension</a> is not installed or disabled. Resend OTP failed.');
						$this->mo_oauth_show_error_message();
						return;
					}

					$email = get_option('mo_oauth_admin_email');

					$customer = new Mo_OAuth_Client_Customer();
					$content = json_decode($customer->mo_oauth_forgot_password($email), true);

					if (strcasecmp($content ['status'], 'SUCCESS') == 0) {
						update_option('message', 'Your password has been reset successfully. Please enter the new password sent to ' . $email . '.');
						$this->mo_oauth_show_success_message();
					}
				}
		} else if( isset( $_POST['option'] ) and sanitize_text_field( wp_unslash( $_POST['option'] ) ) == "mo_oauth_change_email"  && isset( $_REQUEST['mo_oauth_change_email_form_field'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['mo_oauth_change_email_form_field'] ) ), 'mo_oauth_change_email_form')) {
			//Adding back button
			update_option('mo_oauth_client_verify_customer', '');
			update_option('mo_oauth_client_registration_status','');
			update_option('mo_oauth_client_new_registration','true');
		} else if( isset( $_POST['option'] ) and sanitize_text_field( wp_unslash( $_POST['option'] ) ) == "mo_oauth_register_with_phone_option" && isset( $_REQUEST['mo_oauth_register_with_phone_form_field'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['mo_oauth_register_with_phone_form_field'] ) ), 'mo_oauth_register_with_phone_form')) {

			if( current_user_can( 'administrator' ) ) {
				if(!mo_oauth_is_curl_installed()) {
					return $this->mo_oauth_show_curl_error();
				}
				$phone = stripslashes($_POST['phone']);
				$phone = str_replace(' ', '', $phone);
				$phone = str_replace('-', '', $phone);
				update_option('mo_oauth_client_admin_phone', $phone);
				$customer = new Mo_OAuth_Client_Customer();
				$content=json_decode( $customer->send_otp_token('', $phone, FALSE, TRUE),true);
				if($content) {
					update_option( 'message', ' A one time passcode is sent to ' . get_site_option('mo_oauth_client_admin_phone') . '. Please enter the otp here to verify your email.');
					$_SESSION['mo_oauth_transactionId'] = $content['txId'];
					update_option('mo_oauth_client_registration_status','MO_OTP_DELIVERED_SUCCESS_PHONE');
					$this->mo_oauth_show_success_message();
				}else{
					update_option('message','There was an error in sending SMS. Please click on Resend OTP to try again.');
					update_option('mo_oauth_client_registration_status','MO_OTP_DELIVERED_FAILURE_PHONE');
					$this->mo_oauth_show_error_message();
				}
			}
		}

		else if ( isset( $_POST['option'] ) and sanitize_text_field( wp_unslash( $_POST['option'] ) ) == 'mo_oauth_client_skip_feedback' && isset( $_REQUEST['mo_oauth_skip_feedback_form_field'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['mo_oauth_skip_feedback_form_field'] ) ), 'mo_oauth_skip_feedback_form')) {
			deactivate_plugins( __FILE__ );
			update_option( 'message', 'Plugin deactivated successfully' );
			$this->mo_oauth_show_success_message();
		}
		else if ( isset( $_POST['mo_oauth_client_feedback'] ) and sanitize_text_field( wp_unslash( $_POST['mo_oauth_client_feedback'] ) ) == 'true' && isset( $_REQUEST['mo_oauth_feedback_form_field'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['mo_oauth_feedback_form_field'] ) ), 'mo_oauth_feedback_form')) {
			
			if( current_user_can( 'administrator' ) ) {
				$user = wp_get_current_user();

				$message = 'Plugin Deactivated:';
				if(isset($_POST['deactivate_reason_select'])){
					$deactivate_reason = $_POST['deactivate_reason_select'];
				}
				
				$deactivate_reason_message = array_key_exists( 'query_feedback', $_POST ) ? sanitize_text_field( wp_unslash( $_POST['query_feedback'] ) ) : false;

				

				if ( $deactivate_reason ) {
					$message .= $deactivate_reason;
					if ( isset( $deactivate_reason_message ) ) {
						$message .= ': ' . $deactivate_reason_message;
					}

					if(isset($_POST['rate'])){
					$rate_value = htmlspecialchars($_POST['rate']);
					}

					$rating = "[Rating: ".$rate_value."]";

					$email = $_POST['query_mail'];
					if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
						$email = get_option("mo_oauth_admin_email");
						if(empty($email)){
							$email = $user->user_email;
						}
					}

					$reply_required = '';
					if(isset($_POST['get_reply']))
						$reply_required = htmlspecialchars($_POST['get_reply']);
						if(empty($reply_required)){
						$reply_required = "No";
						$reply ='[Reply :'.$reply_required.']';
					}else{
						$reply_required = "Yes";
						$reply ='[Reply :'.$reply_required.']';
					}

					$reply = $rating.' '.$reply;

					//only reason
					$feedback_reasons = new Mo_OAuth_Client_Customer();
					$submited = json_decode( $feedback_reasons->mo_oauth_send_email_alert( $email, $reply, $message, "Feedback: WordPress ".MO_OAUTH_PLUGIN_NAME ), true );
					deactivate_plugins( __FILE__ );
					update_option( 'message', 'Thank you for the feedback.' );
					$this->mo_oauth_show_success_message();
				} else {
					update_option( 'message', 'Please Select one of the reasons ,if your reason is not mentioned please select Other Reasons' );
					$this->mo_oauth_show_error_message();
				}
			}
		}


	}

	function mo_oauth_get_current_customer(){
		$customer = new Mo_OAuth_Client_Customer();
		$content = $customer->get_customer_key();
		$customerKey = json_decode( $content, true );
		if( json_last_error() == JSON_ERROR_NONE ) {
			update_option( 'mo_oauth_client_admin_customer_key', $customerKey['id'] );
			update_option( 'mo_oauth_client_admin_api_key', $customerKey['apiKey'] );
			update_option( 'mo_oauth_client_customer_token', $customerKey['token'] );
			update_option('password', '' );
			update_option( 'message', 'Customer retrieved successfully' );
			delete_option('mo_oauth_client_verify_customer');
			delete_option('mo_oauth_client_new_registration');
			$this->mo_oauth_show_success_message();
		} else {
			update_option( 'message', 'You already have an account with miniOrange. Please enter a valid password.');
			update_option('mo_oauth_client_verify_customer', 'true');
			$this->mo_oauth_show_error_message();

		}
	}

	function create_customer(){
		$customer = new Mo_OAuth_Client_Customer();
		$customerKey = json_decode( $customer->create_customer(), true );
		if( strcasecmp( $customerKey['status'], 'CUSTOMER_USERNAME_ALREADY_EXISTS') == 0 ) {
			$this->mo_oauth_get_current_customer();
			delete_option('mo_oauth_client_new_customer');
		} else if( strcasecmp( $customerKey['status'], 'SUCCESS' ) == 0 ) {
			update_option( 'mo_oauth_client_admin_customer_key', $customerKey['id'] );
			update_option( 'mo_oauth_client_admin_api_key', $customerKey['apiKey'] );
			update_option( 'mo_oauth_client_customer_token', $customerKey['token'] );
			update_option( 'password', '');
			update_option( 'message', 'Registered successfully.');
			update_option('mo_oauth_client_registration_status','MO_OAUTH_REGISTRATION_COMPLETE');
			update_option('mo_oauth_client_new_customer',1);
			delete_option('mo_oauth_client_verify_customer');
			delete_option('mo_oauth_client_new_registration');
			$this->mo_oauth_show_success_message();
		}
	}

	function mo_oauth_show_curl_error() {
		if( mo_oauth_is_curl_installed() == 0 ) {
			update_option( 'message', '<a href="http://php.net/manual/en/curl.installation.php" target="_blank">PHP CURL extension</a> is not installed or disabled. Please enable it to continue.');
			$this->mo_oauth_show_error_message();
			return;
		}
	}

	function mo_oauth_shortcode_login(){
		if(mo_oauth_hbca_xyake() || !mo_oauth_is_customer_registered()) {
			return '<div class="mo_oauth_premium_option_text" style="text-align: center;border: 1px solid;margin: 5px;padding-top: 25px;"><p>This feature is supported only in standard and higher versions.</p>
				<p><a href="'.get_site_url(null, '/wp-admin/').'admin.php?page=mo_oauth_settings&tab=licensing">Click Here</a> to see our full list of Features.</p></div>';
		}
		$mowidget = new Mo_Oauth_Widget;
		return $mowidget->mo_oauth_login_form();
	}

	function export_plugin_config( $share_with = false ) {
		$appslist = get_option('mo_oauth_apps_list');
		$currentapp_config = null;
		if ( is_array( $appslist ) ) {
			foreach( $appslist as $key => $value ) {
				$currentapp_config = $value;
				break;
			}
		}
		if ( $share_with ) {
			unset( $currentapp_config['clientid'] );
			unset( $currentapp_config['clientsecret'] );
		}
		return $currentapp_config;
	}

}

	function mo_oauth_is_customer_registered() {
		$email 			= get_option('mo_oauth_admin_email');
		$customerKey 	= get_option('mo_oauth_client_admin_customer_key');
		if( ! $email || ! $customerKey || ! is_numeric( trim( $customerKey ) ) ) {
			return 0;
		} else {
			return 1;
		}
	}

	function mo_oauth_is_curl_installed() {
		if  (in_array  ('curl', get_loaded_extensions())) {
			return 1;
		} else {
			return 0;
		}
	}

function is_url($url){
    $response = array();
    //Check if URL is empty
    if(!empty($url)) {
        $response = @get_headers($url) ? @get_headers($url) : array();
    }
    $array_op =  preg_grep('/(.*)200 OK/', $response );
    return (bool)(sizeof($array_op ) > 0);

}

new mo_oauth;
function run_mo_oauth_client() { $plugin = new Mo_OAuth_Client();$plugin->run();}
run_mo_oauth_client();
