<?php

include 'mo_oauth_log.php';

class Mo_Oauth_Widget extends WP_Widget {

	public function __construct() {
		update_option( 'host_name', 'https://login.xecurify.com' );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_plugin_styles' ) );
		add_action( 'init', array( $this, 'mo_oauth_start_session' ) );
		add_action( 'wp_logout', array( $this, 'mo_oauth_end_session' ) );
		add_action( 'login_form', array( $this, 'mo_oauth_wplogin_form_button' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'mo_oauth_wplogin_form_style' ) );
		parent::__construct( 'mo_oauth_widget', MO_OAUTH_ADMIN_MENU, array( 'description' => __( 'Login to Apps with OAuth', 'flw' ), ) );

	 }

	 function mo_oauth_wplogin_form_style(){

		wp_enqueue_style( 'mo_oauth_fontawesome', plugins_url( 'css/font-awesome.css', __FILE__ ) );
		wp_enqueue_style( 'mo_oauth_wploginform', plugins_url( 'css/login-page.css', __FILE__ ) );
	}

	function mo_oauth_wplogin_form_button() {
		$appslist = get_option('mo_oauth_apps_list');
		if(is_array($appslist) && sizeof($appslist) > 0){
			$this->mo_oauth_load_login_script();
			foreach($appslist as $key => $app){

				if(isset($app['show_on_login_page']) && $app['show_on_login_page'] === 1){

					$this->mo_oauth_wplogin_form_style();

					echo '<br>';
					echo '<h4>Connect with :</h4><br>';
					echo '<div class="row">';

					$logo_class = $this->mo_oauth_client_login_button_logo($app['appId']);
					
					echo '<a style="text-decoration:none" href="javascript:void(0)" onClick="moOAuthLoginNew(\''.$key.'\');"><div class="mo_oauth_login_button"><i class="'.$logo_class.' mo_oauth_login_button_icon"></i><h3 class="mo_oauth_login_button_text">Login with '.ucwords($key).'</h3></div></a>';	
					echo '</div><br><br>';
				}
			}
		}
	}

	function mo_oauth_client_login_button_logo($currentAppId) {
		$currentapp = mo_oauth_client_get_app($currentAppId);
		$logo_class = $currentapp->logo_class;
		return $logo_class;
	}

	function mo_oauth_start_session() {
		if( ! session_id() && ! mo_oauth_client_is_ajax_request() && ! mo_oauth_client_is_rest_api_call() ) {
			session_start();
		}

		if(isset($_REQUEST['option']) and $_REQUEST['option'] == 'testattrmappingconfig'){
			$mo_oauth_app_name = $_REQUEST['app'];
			wp_redirect(site_url().'?option=oauthredirect&app_name='. urlencode($mo_oauth_app_name)."&test=true");
			exit();
		}

	}

	function mo_oauth_end_session() {
		if( ! session_id() )
		{ 	session_start();
        }
		session_destroy();
	}

	public function widget( $args, $instance ) {
		extract( $args );

		echo $args['before_widget'];
		if ( ! empty( $wid_title ) ) {
			echo $args['before_title'] . $wid_title . $args['after_title'];
		}
		echo $this->mo_oauth_login_form();
		echo $args['after_widget'];
	}

	public function update( $new_instance, $old_instance ) {
		$instance = array();
		if(isset($new_instance['wid_title']))
			$instance['wid_title'] = strip_tags( $new_instance['wid_title'] );
			
		return $instance;
	}

	public function mo_oauth_login_form() {
		global $post;
		$this->error_message();
		$temp = '';

		$appslist = get_option('mo_oauth_apps_list');
		if($appslist && sizeof($appslist)>0)
			$appsConfigured = true;

		if( ! is_user_logged_in() ) {
			
			if( isset($appsConfigured) && $appsConfigured ) {

				$this->mo_oauth_load_login_script();

				$style = get_option('mo_oauth_icon_width') ? "width:".get_option('mo_oauth_icon_width').";" : "";
				$style .= get_option('mo_oauth_icon_height') ? "height:".get_option('mo_oauth_icon_height').";" : "";
				$style .= get_option('mo_oauth_icon_margin') ? "margin:".get_option('mo_oauth_icon_margin').";" : "";
				$custom_css = get_option('mo_oauth_icon_configure_css');
				if(empty($custom_css))
					$temp .= '<style>.oauthloginbutton{background: #7272dc;height:40px;padding:8px;text-align:center;color:#fff;}</style>';
				else
					$temp .= '<style>'.$custom_css.'</style>';
				
				if (is_array($appslist)) {
					foreach($appslist as $key=>$app){
						$logo_class = $this->mo_oauth_client_login_button_logo($app['appId']);

						$temp .= '<a style="text-decoration:none" href="javascript:void(0)" onClick="moOAuthLoginNew(\''.$key.'\');"><div class="mo_oauth_login_button_widget"><i class="'.$logo_class.' mo_oauth_login_button_icon_widget"></i><h3 class="mo_oauth_login_button_text_widget">Login with '.ucwords($key).'</h3></div></a>';			
					}	
				}
			} else {
				$temp .= '<div>No apps configured.</div>';
			}
		} else {
			$current_user = wp_get_current_user();
			$link_with_username = __('Howdy, ', 'flw') . $current_user->display_name;
			$temp .= "<div id=\"logged_in_user\" class=\"login_wid\">
			<li>".$link_with_username." | <a href=\"".wp_logout_url( site_url() )."\" >Logout</a></li>
		</div>";
			
		}
		return $temp;
	}

	private function mo_oauth_load_login_script() {
	?>
	<script type="text/javascript">

		function HandlePopupResult(result) {
			window.location.href = result;
		}

		function moOAuthLogin(app_name) {
			window.location.href = '<?php echo site_url() ?>' + '/?option=generateDynmicUrl&app_name=' + app_name;
		}
		function moOAuthLoginNew(app_name) {
			window.location.href = '<?php echo site_url() ?>' + '/?option=oauthredirect&app_name=' + app_name;
		}
	</script>
	<?php
	}



	public function error_message() {
		if( isset( $_SESSION['msg'] ) and $_SESSION['msg'] ) {
			echo '<div class="' . $_SESSION['msg_class'] . '">' . $_SESSION['msg'] . '</div>';
			unset( $_SESSION['msg'] );
			unset( $_SESSION['msg_class'] );
		}
	}

	public function register_plugin_styles() {
		wp_enqueue_style( 'style_login_widget', plugins_url( 'css/style_login_widget.css', __FILE__ ) );
	}


}

function mo_oauth_update_email_to_username_attr($currentappname){
	$appslist = get_option('mo_oauth_apps_list');
	$appslist[$currentappname]['username_attr'] = $appslist[$currentappname]['email_attr'];
	update_option('mo_oauth_apps_list',$appslist);
}

	function mo_oauth_login_validate(){

		/* Handle Eve Online old flow */
		if( isset( $_REQUEST['option'] ) and strpos( $_REQUEST['option'], 'oauthredirect' ) !== false ) {
			$appname = $_REQUEST['app_name'];
			$appslist = get_option('mo_oauth_apps_list');
			if(isset($_REQUEST['redirect_url'])){
				update_option('mo_oauth_redirect_url',$_REQUEST['redirect_url']);
			}

			if(isset($_REQUEST['test']))
				setcookie("mo_oauth_test", true);
			else
				setcookie("mo_oauth_test", false);

			if($appslist == false){
				MO_Oauth_Debug::mo_oauth_log('Looks like you have not configured OAuth provider, please try to configure OAuth provider first');
				exit("Looks like you have not configured OAuth provider, please try to configure OAuth provider first");
			}
				
			foreach($appslist as $key => $app){

				if($appname==$key && (isset($app['send_state'])!==true || $app['send_state'])){

					if($app['appId']=="twitter")
							{
								  include "twitter.php";
								  setcookie('tappname',$appname);
                				  Mo_twitter::mo_openid_get_app_code($_COOKIE['tappname']);
                				  exit();
							}

					$state = base64_encode($appname);
					$authorizationUrl = $app['authorizeurl'];
				
					if(strpos($authorizationUrl, '?' ) !== false)
					$authorizationUrl = $authorizationUrl."&client_id=".$app['clientid']."&scope=".$app['scope']."&redirect_uri=".$app['redirecturi']."&response_type=code&state=".$state;
				    else
					$authorizationUrl = $authorizationUrl."?client_id=".$app['clientid']."&scope=".$app['scope']."&redirect_uri=".$app['redirecturi']."&response_type=code&state=".$state;

					if ( strpos( $authorizationUrl, 'apple' ) !== false ) {
						$authorizationUrl = str_replace( "response_type=code", "response_type=code+id_token", $authorizationUrl );
						$authorizationUrl = $authorizationUrl . "&response_mode=form_post";
					}

					if(session_id() == '' || !isset($_SESSION))
						session_start();
					$_SESSION['oauth2state'] = $state;
					$_SESSION['appname'] = $appname;

					header('Location: ' . $authorizationUrl);
					exit;
				}
				else{
					$state=null;
					$authorizationUrl = $app['authorizeurl'];
				
					if(strpos($authorizationUrl, '?' ) !== false)
					$authorizationUrl = $authorizationUrl."&client_id=".$app['clientid']."&scope=".$app['scope']."&redirect_uri=".$app['redirecturi']."&response_type=code";
				    else
					$authorizationUrl = $authorizationUrl."?client_id=".$app['clientid']."&scope=".$app['scope']."&redirect_uri=".$app['redirecturi']."&response_type=code";

					if(session_id() == '' || !isset($_SESSION))
						session_start();
					$_SESSION['oauth2state'] = $state;
					$_SESSION['appname'] = $appname;

					header('Location: ' . $authorizationUrl);
					exit;
				}
			}
			}
		
		else if( strpos( $_SERVER['REQUEST_URI'], "openidcallback") !== false ||((strpos( $_SERVER['REQUEST_URI'], "oauth_token")!== false)&&(strpos( $_SERVER['REQUEST_URI'], "oauth_verifier") ))) {
        			
					$appslist = get_option('mo_oauth_apps_list');
					$username_attr = "";
					$currentapp = false;
					foreach($appslist as $key => $app){
						if($key == $_COOKIE['tappname']){
							include "twitter.php";
							$currentapp = $app;
							if(isset($app['username_attr'])){
								$username_attr = $app['username_attr'];
							}else if(isset($app['email_attr'])){
									mo_oauth_update_email_to_username_attr($_COOKIE['tappname']);
									$username_attr = $app['email_attr'];	
							}
						}
					}

     	   			$resourceOwner=Mo_twitter::mo_openid_get_access_token($_COOKIE['tappname']);

     	   			$username = "";
					update_option('mo_oauth_attr_name_list', $resourceOwner);
					//TEST Configuration
					if(isset($_COOKIE['mo_oauth_test']) && $_COOKIE['mo_oauth_test']){
						echo '<div style="font-family:Calibri;padding:0 3%;">';
						echo '<style>table{border-collapse:collapse;}th {background-color: #eee; text-align: center; padding: 8px; border-width:1px; border-style:solid; border-color:#212121;}tr:nth-child(odd) {background-color: #f2f2f2;} td{padding:8px;border-width:1px; border-style:solid; border-color:#212121;}</style>';
						echo "<h2>Test Configuration</h2><table><tr><th>Attribute Name</th><th>Attribute Value</th></tr>";
						mo_oauth_client_testattrmappingconfig("",$resourceOwner);
						echo "</table>";
						echo '<div style="padding: 10px;"></div><input style="padding:1%;width:100px;background: #0091CD none repeat scroll 0% 0%;cursor: pointer;font-size:15px;border-width: 1px;border-style: solid;border-radius: 3px;white-space: nowrap;box-sizing: border-box;border-color: #0073AA;box-shadow: 0px 1px 0px rgba(120, 200, 230, 0.6) inset;color: #FFF;"type="button" value="Done" onClick="self.close();">&emsp;<a href="#" onclick="window.opener.proceedToAttributeMapping();self.close();">Proceed To Attribute/Role Mapping</a></div>';
						exit();
					}
					//$username_attr='first_name';
					if(!empty($username_attr))
						$username = mo_oauth_client_getnestedattribute($resourceOwner, $username_attr); //$resourceOwner[$email_attr];
					
					if(empty($username) || "" === $username){
						MO_Oauth_Debug::mo_oauth_log('Username not received. Check your Attribute Mapping configuration.');
						exit('Username not received. Check your <b>Attribute Mapping</b> configuration.');
					}
					
					if ( ! is_string( $username ) ) {
						MO_Oauth_Debug::mo_oauth_log('Username is not a string. It is ' . mo_oauth_client_get_proper_prefix( gettype( $username ) ));
						wp_die( 'Username is not a string. It is ' . mo_oauth_client_get_proper_prefix( gettype( $username ) ) );
					}
			
					$user = get_user_by("login",$username);
					// if(!$user)
					// 	$user = get_user_by( 'email', $username);

					if($user){
						$user_id = $user->ID;
					} else {
						$user_id = 0;
						if(mo_oauth_hbca_xyake()) {
							$user = mo_oauth_jhuyn_jgsukaj($username);
						} else {
							$user = mo_oauth_hjsguh_kiishuyauh878gs($username);
						}
						
					}
					if($user){
						wp_set_current_user($user->ID);
						wp_set_auth_cookie($user->ID);
						$user  = get_user_by( 'ID',$user->ID );
						do_action( 'wp_login', $user->user_login, $user );
						$redirect_to = get_option('mo_oauth_redirect_url');

						if($redirect_to == false){
							$redirect_to = home_url();
						}

						wp_redirect($redirect_to);						
						exit;
					}


    								}

		else if(strpos($_SERVER['REQUEST_URI'], "/oauthcallback") !== false || isset($_REQUEST['code'])) {

			if(session_id() == '' || !isset($_SESSION))
				session_start();

			// OAuth state security check
			/*
			if (empty($_GET['state']) || (isset($_SESSION['oauth2state']) && $_GET['state'] !== $_SESSION['oauth2state'])) {
				if (isset($_SESSION['oauth2state'])) {
					unset($_SESSION['oauth2state']);
				}
				exit('Invalid state');
			} */

			if (!isset($_REQUEST['code'])){
				if(isset($_REQUEST['error_description'])){
					MO_Oauth_Debug::mo_oauth_log($_REQUEST['error_description']);
					exit($_REQUEST['error_description']);
				}
				else if(isset($_REQUEST['error']))
				{
					MO_Oauth_Debug::mo_oauth_log($_REQUEST['error']);
					exit($_REQUEST['error']);
				}
				MO_Oauth_Debug::mo_oauth_log('Invalid response');
				exit('Invalid response');
			} else {

				try {

					$currentappname = "";

					if (isset($_SESSION['appname']) && !empty($_SESSION['appname']))
						$currentappname = $_SESSION['appname'];
					else if (isset($_REQUEST['state']) && !empty($_REQUEST['state'])){
						$currentappname = base64_decode($_REQUEST['state']);
					}

					if (empty($currentappname)) {
						MO_Oauth_Debug::mo_oauth_log('No request found for this application.');
						exit('No request found for this application.');
					}

					$appslist = get_option('mo_oauth_apps_list');
					$username_attr = "";
					$currentapp = false;
					foreach($appslist as $key => $app){
						if($key == $currentappname){
							$currentapp = $app;
							if(isset($app['username_attr'])){
								$username_attr = $app['username_attr'];
							}else if(isset($app['email_attr'])){
									mo_oauth_update_email_to_username_attr($currentappname);
									$username_attr = $app['email_attr'];	
							}
						}
					}

					if (!$currentapp){
						MO_Oauth_Debug::mo_oauth_log('Application not configured.');
						exit('Application not configured.');
					}

					$mo_oauth_handler = new Mo_OAuth_Hanlder();
					if(isset($currentapp['apptype']) && $currentapp['apptype']=='openidconnect') {
						// OpenId connect
						if( isset( $_REQUEST['id_token'] ) ) {
							$idToken = $_REQUEST['id_token'];
						} else {
							if(!isset($currentapp['send_headers']))
								$currentapp['send_headers'] = false;
							if(!isset($currentapp['send_body']))
								$currentapp['send_body'] = false;
							$tokenResponse = $mo_oauth_handler->getIdToken($currentapp['accesstokenurl'], 'authorization_code',
									$currentapp['clientid'], $currentapp['clientsecret'], $_GET['code'], $currentapp['redirecturi'], $currentapp['send_headers'], $currentapp['send_body']);
	
							$idToken = isset($tokenResponse["id_token"]) ? $tokenResponse["id_token"] : $tokenResponse["access_token"];

						}	
		
						if(!$idToken){
							MO_Oauth_Debug::mo_oauth_log('Invalid token received.');
							exit('Invalid token received.');
						}
						else
							$resourceOwner = $mo_oauth_handler->getResourceOwnerFromIdToken($idToken);

					} else {
						// echo "OAuth";
						$accessTokenUrl = $currentapp['accesstokenurl'];
						
						if(!isset($currentapp['send_headers']))
							$currentapp['send_headers'] = false;
						if(!isset($currentapp['send_body']))
							$currentapp['send_body'] = false;

                        if(strpos($currentapp['authorizeurl'], 'clever.com/oauth') != false || 
                    		$currentapp['appId'] == 'bitrix24') {
                            $accessToken = $mo_oauth_handler->getAccessTokenCurl($accessTokenUrl, 'authorization_code', $currentapp['clientid'], $currentapp['clientsecret'], $_GET['code'], $currentapp['redirecturi'], $currentapp['send_headers'], $currentapp['send_body']);
                        } else {
                            $accessToken = $mo_oauth_handler->getAccessToken($accessTokenUrl, 'authorization_code', $currentapp['clientid'], $currentapp['clientsecret'], $_GET['code'], $currentapp['redirecturi'], $currentapp['send_headers'], $currentapp['send_body']);
                        }
						if(!$accessToken){
							MO_Oauth_Debug::mo_oauth_log('Invalid token received.');
							exit('Invalid token received.');
						}

						$resourceownerdetailsurl = $currentapp['resourceownerdetailsurl'];
						if (substr($resourceownerdetailsurl, -1) == "=") {
							$resourceownerdetailsurl .= $accessToken;
						}

						$resourceOwner = $mo_oauth_handler->getResourceOwner($resourceownerdetailsurl, $accessToken);
					}

					$username = "";
					update_option('mo_oauth_attr_name_list', $resourceOwner);
					//TEST Configuration
					if(isset($_COOKIE['mo_oauth_test']) && $_COOKIE['mo_oauth_test']){
						echo '<div style="font-family:Calibri;padding:0 3%;">';
						echo '<style>table{border-collapse:collapse;}th {background-color: #eee; text-align: center; padding: 8px; border-width:1px; border-style:solid; border-color:#212121;}tr:nth-child(odd) {background-color: #f2f2f2;} td{padding:8px;border-width:1px; border-style:solid; border-color:#212121;}</style>';
						echo "<h2>Test Configuration</h2><table><tr><th>Attribute Name</th><th>Attribute Value</th></tr>";
						mo_oauth_client_testattrmappingconfig("",$resourceOwner);
						echo "</table>";
						echo '<div style="padding: 10px;"></div><input style="padding:1%;width:100px;background: #0091CD none repeat scroll 0% 0%;cursor: pointer;font-size:15px;border-width: 1px;border-style: solid;border-radius: 3px;white-space: nowrap;box-sizing: border-box;border-color: #0073AA;box-shadow: 0px 1px 0px rgba(120, 200, 230, 0.6) inset;color: #FFF;"type="button" value="Done" onClick="self.close();">&emsp;<a href="#" onclick="window.opener.proceedToAttributeMapping();self.close();">Proceed To Attribute/Role Mapping</a></div>';
						exit();
					}

					if(!empty($username_attr))
						$username = mo_oauth_client_getnestedattribute($resourceOwner, $username_attr); //$resourceOwner[$email_attr];

					if(empty($username) || "" === $username){
						MO_Oauth_Debug::mo_oauth_log('Username not received. Check your Attribute Mapping configuration.');
						exit('Username not received. Check your <b>Attribute Mapping</b> configuration.');
					}
					
					if ( ! is_string( $username ) ) {
						MO_Oauth_Debug::mo_oauth_log('Username is not a string. It is ' . mo_oauth_client_get_proper_prefix( gettype( $username ) ));
						wp_die( 'Username is not a string. It is ' . mo_oauth_client_get_proper_prefix( gettype( $username ) ) );
					}

					$user = get_user_by("login",$username);
					// if(!$user)
					// 	$user = get_user_by( 'email', $username);

					if($user){
						$user_id = $user->ID;
					} else {
						$user_id = 0;
						if(mo_oauth_hbca_xyake()) {
							$user = mo_oauth_jhuyn_jgsukaj($username);
						} else {
							$user = mo_oauth_hjsguh_kiishuyauh878gs($username);
						}
					}
					if($user){
						wp_set_current_user($user->ID);
						wp_set_auth_cookie($user->ID);
						$user  = get_user_by( 'ID',$user->ID );
						do_action( 'wp_login', $user->user_login, $user );
						$redirect_to = get_option('mo_oauth_redirect_url');

						if($redirect_to == false){
							$redirect_to = home_url();
						}

						wp_redirect($redirect_to);						
						exit;
					}


				} catch (Exception $e) {

					// Failed to get the access token or user details.
					//print_r($e);
					MO_Oauth_Debug::mo_oauth_log($e->getMessage());
					exit($e->getMessage());

				}

			}

		} else if( isset( $_REQUEST['option'] ) and strpos( $_REQUEST['option'], 'generateDynmicUrl' ) !== false ) {
			$client_id = get_option('mo_oauth_' . $_REQUEST['app_name'] . '_client_id');
			$timestamp = round( microtime(true) * 1000 );
			$api_key = get_option('mo_oauth_client_admin_api_key');
			$token = $client_id . ':' . number_format($timestamp, 0, '', '') . ':' . $api_key;

			$customer_token = get_option('mo_oauth_client_customer_token');
			$method = 'AES-128-ECB';
			$ivSize = openssl_cipher_iv_length($method);
			$iv     = openssl_random_pseudo_bytes($ivSize);
			$token_params_encrypt = openssl_encrypt ($token, $method, $customer_token,OPENSSL_RAW_DATA||OPENSSL_ZERO_PADDING, $iv);
			$token_params_encode = base64_encode( $iv.$token_params_encrypt );
			$token_params = urlencode( $token_params_encode );

			$return_url = urlencode( site_url() . '/?option=mooauth' );
			$url = get_option('host_name') . '/moas/oauth/client/authorize?token=' . $token_params . '&id=' . get_option('mo_oauth_client_admin_customer_key') . '&encrypted=true&app=' . $_REQUEST['app_name'] . '_oauth&returnurl=' . $return_url;
			wp_redirect( $url );
			exit;
		} else if( isset( $_REQUEST['option'] ) and strpos( $_REQUEST['option'], 'mooauth' ) !== false ){

			//do stuff after returning from oAuth processing
			$access_token 	= stripslashes( $_POST['access_token'] );
			$token_type	 	= stripslashes( $_POST['token_type'] );
			$user_email = '';
			if(array_key_exists('email', $_POST))
				$user_email 	= sanitize_email( $_POST['email'] );


			if( $user_email ) {
				if( email_exists( $user_email ) ) { // user is a member
					  $user 	= get_user_by('email', $user_email );
					  $user_id 	= $user->ID;
					  wp_set_auth_cookie( $user_id, true );
				} else { // this user is a guest
					  $random_password 	= wp_generate_password( 10, false );
					  $user_id 			= wp_create_user( $user_email, $random_password, $user_email );
					  wp_set_auth_cookie( $user_id, true );
				}
			} 
			wp_redirect( home_url() );
			exit;
		}
		/* End of old flow */
	}

	function mo_oauth_hjsguh_kiishuyauh878gs($username)
	{
		$random_password = wp_generate_password( 10, false );
		// if(is_email($email))
		// 	$user_id = wp_create_user( $email, $random_password, $email );
		// else
		// 	$user_id = wp_create_user( $email, $random_password);	
		$user_id = 	wp_create_user( $username, $random_password);
		$user = get_user_by( 'login', $username);			
		wp_update_user( array( 'ID' => $user_id ) );
		return $user;
	}

	//here entity is corporation, alliance or character name. The administrator compares these when user logs in
	function mo_oauth_check_validity_of_entity($entityValue, $entitySessionValue, $entityName) {

		$entityString = $entityValue ? $entityValue : false;
		$valid_entity = false;
		if( $entityString ) {			//checks if entityString is defined
			if ( strpos( $entityString, ',' ) !== false ) {			//checks if there are more than 1 entity defined
				$entity_list = array_map( 'trim', explode( ",", $entityString ) );
				foreach( $entity_list as $entity ) {			//checks for each entity to exist
					if( $entity == $entitySessionValue ) {
						$valid_entity = true;
						break;
					}
				}
			} else {		//only one entity is defined
				if( $entityString == $entitySessionValue ) {
					$valid_entity = true;
				}
			}
		} else {			//entity is not defined
			$valid_entity = false;
		}
		return $valid_entity;
	}

	function mo_oauth_jhuyn_jgsukaj($temp_var)
	{
		return mo_oauth_jkhuiysuayhbw($temp_var);
	}

	function mo_oauth_client_testattrmappingconfig($nestedprefix, $resourceOwnerDetails, $tr_class_prefix = ''){
		$tr = '<tr class="' . $tr_class_prefix . 'tr">';
		$td = '<td class="' . $tr_class_prefix . 'td">';
		foreach($resourceOwnerDetails as $key => $resource){
			if(is_array($resource) || is_object($resource)){
				if(!empty($nestedprefix))
					$nestedprefix .= ".";
				mo_oauth_client_testattrmappingconfig($nestedprefix.$key,$resource, $tr_class_prefix);
				$nestedprefix = rtrim($nestedprefix,".");
			} else {
				echo $tr . $td;
				if(!empty($nestedprefix))
					echo $nestedprefix.".";
				echo $key."</td>".$td.$resource."</td></tr>";
			}
		}
	}

	function mo_oauth_client_getnestedattribute($resource, $key){
		//echo $key." : ";print_r($resource); echo "<br>";
		if($key==="")
			return "";

		$keys = explode(".",$key);
		if(sizeof($keys)>1){
			$current_key = $keys[0];
			if(isset($resource[$current_key]))
				return mo_oauth_client_getnestedattribute($resource[$current_key], str_replace($current_key.".","",$key));
		} else {
			$current_key = $keys[0];
			if(isset($resource[$current_key])) {
				return $resource[$current_key];
			}
		}
	}

	function mo_oauth_jkhuiysuayhbw($ejhi)
	{
		$user = mo_oauth_hjsguh_kiishuyauh878gs($ejhi);
		/*$option = 0; $flag = false;	
		$mo_oauth_authorizations = get_option('mo_oauth_authorizations');
		if(!empty($mo_oauth_authorizations))
			$option = get_option( 'mo_oauth_authorizations' );
		
		if($user);								
			++$option;							
		update_option( 'mo_oauth_authorizations', $option);
		if($option >= 10)
		{
			$mo_oauth_set_val = base64_decode('bW9fb2F1dGhfZmxhZw==');
		    update_option($mo_oauth_set_val, true);
		}*/
		return $user;
	}

	function mo_oauth_client_get_proper_prefix( $type ) {
		$letter = substr( $type, 0, 1 );
		$vowels = [ 'a', 'e', 'i', 'o', 'u' ];
		return ( in_array( $letter, $vowels ) ) ? ' an ' . $type : ' a ' . $type;
	}

	function register_mo_oauth_widget() {
		register_widget('mo_oauth_widget');
	}

	function mo_oauth_client_is_ajax_request() {
		return defined('DOING_AJAX') && DOING_AJAX;
	}

	function mo_oauth_client_is_rest_api_call() {
		return strpos( $_SERVER['REQUEST_URI'], '/wp-json' ) == false;
	}

	add_action('widgets_init', 'register_mo_oauth_widget');
	add_action( 'init', 'mo_oauth_login_validate' );
?>
