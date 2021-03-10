<?php
/** miniOrange enables user to log in through OAuth to apps such as Cognito, Azure, Google, EVE Online etc.
    Copyright (C) 2015  miniOrange

	Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

	The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

	THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
		
	* @package 		miniOrange OAuth
	* @license		https://docs.miniorange.com/mit-license MIT/Expat
*/

/**
	This library is miniOrange Authentication Service. 
	Contains Request Calls to Customer service.

**/

class Mo_OAuth_Client_Customer {
	
	public $email;
	public $phone;
	
	private $defaultCustomerKey = "16555";
	private $defaultApiKey = "fFd2XcvTGDemZvbw1bcUesNJWEqKbbUq";

	function create_customer(){
		$url = get_option('host_name') . '/moas/rest/customer/add';
		$this->email 		= get_option('mo_oauth_admin_email');
		$this->phone 		= get_option('mo_oauth_client_admin_phone');
		$password 			= get_option('password');
		$firstName    		= get_option('mo_oauth_admin_fname');
		$lastName     		= get_option('mo_oauth_admin_lname');
		$company      		= get_option('mo_oauth_admin_company');
		
		$fields = array(
			'companyName' => $company,
			'areaOfInterest' => MO_OAUTH_AREA_OF_INTEREST,
			'firstname'	=> $firstName,
			'lastname'	=> $lastName,
			'email'		=> $this->email,
			'phone'		=> $this->phone,
			'password'	=> $password
		);
		$field_string = json_encode($fields);
		$headers = array( 'Content-Type' => 'application/json', 'charset' => 'UTF - 8', 'Authorization' => 'Basic' );
		$args = array(
			'method' =>'POST',
			'body' => $field_string,
			'timeout' => '5',
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
		
		return wp_remote_retrieve_body($response);
	}
	
	function get_customer_key() {
		$url 	= get_option('host_name') . "/moas/rest/customer/key";
		$ch 	= curl_init( $url );
		$email 	= get_option("mo_oauth_admin_email");
		
		$password 			= get_option("password");
		
		$fields = array(
			'email' 	=> $email,
			'password' 	=> $password
		);
		$field_string = json_encode( $fields );
		
		$headers = array( 'Content-Type' => 'application/json', 'charset' => 'UTF - 8', 'Authorization' => 'Basic' );
		$args = array(
			'method' =>'POST',
			'body' => $field_string,
			'timeout' => '5',
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
		
		return wp_remote_retrieve_body($response);
	}
	
	function add_oauth_application( $name, $app_name ) {
		$url = get_option('host_name') . '/moas/rest/application/addoauth';
		$customerKey = get_option('mo_oauth_client_admin_customer_key');
		$scope = get_option('mo_oauth_' . $name . '_scope');
		$client_id = get_option('mo_oauth_' . $name . '_client_id');
		$client_secret = get_option('mo_oauth_' . $name . '_client_secret');
		if($scope != false) {
			$fields = array(
				'applicationName'	=> $app_name,
				'scope'				=> $scope,
				'customerId' 		=> $customerKey,
				'clientId' 			=> $client_id,
				'clientSecret' 		=> $client_secret
			);
		} else {
			$fields = array(
				'applicationName'	=> $app_name,
				'customerId' 		=> $customerKey,
				'clientId' 			=> $client_id,
				'clientSecret' 		=> $client_secret
			);
		}
		$field_string = json_encode( $fields );
		
		$headers = array( 'Content-Type' => 'application/json', 'charset' => 'UTF - 8', 'Authorization' => 'Basic' );
		$args = array(
			'method' =>'POST',
			'body' => $field_string,
			'timeout' => '5',
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
		
		return wp_remote_retrieve_body($response);
	}

	private static function createAuthHeader($customerKey, $apiKey)
    {
        $currentTimestampInMillis = self::getTimestamp();
        if(MoUtility::isBlank($currentTimestampInMillis))
        {
            $currentTimestampInMillis = round(microtime(true) * 1000);
            $currentTimestampInMillis = number_format($currentTimestampInMillis, 0, '', '');
        }
        $stringToHash = $customerKey . $currentTimestampInMillis . $apiKey;
        $authHeader = hash("sha512", $stringToHash);

        $header = array (
            "Content-Type: application/json",
            "Customer-Key: $customerKey",
            "Timestamp: $currentTimestampInMillis",
            "Authorization: $authHeader"
        );
        return $header;
    }	

    function submit_contact_us( $email, $phone, $query, $send_config = true ) {
		global $current_user;
		wp_get_current_user();
		
		$mo_oauth = new mo_oauth();
		$plugin_config          = $mo_oauth->export_plugin_config( true );
		$config_to_send         = json_encode( $plugin_config, JSON_UNESCAPED_SLASHES );
		$plugin_version         = get_plugin_data( __DIR__ . DIRECTORY_SEPARATOR . 'mo_oauth_settings.php' )['Version'];

		$query = '[WP ' . MO_OAUTH_PLUGIN_NAME . ' ' . $plugin_version . '] ' . $query;
		if( $send_config ) {
			$query .= "<br><br>Config String:<br><pre style=\"border:1px solid #444;padding:10px;\"><code>" . $config_to_send . "</code></pre>";
		}
		$fields = array(
			'firstName'			=> $current_user->user_firstname,
			'lastName'	 		=> $current_user->user_lastname,
			'company' 			=> $_SERVER['SERVER_NAME'],
			'email' 			=> $email,
			'ccEmail' 		    => 'oauthsupport@xecurify.com',
			'phone'				=> $phone,
			'query'				=> $query
		);
		$field_string = json_encode( $fields, JSON_UNESCAPED_SLASHES );
		
		$url = get_option('host_name') . '/moas/rest/customer/contact-us';

		$headers = array( 'Content-Type' => 'application/json', 'charset' => 'UTF - 8', 'Authorization' => 'Basic' );
		$args = array(
			'method' =>'POST',
			'body' => $field_string,
			'timeout' => '15',
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
		
		return true;
	}

	function submit_setup_call( $email, $issue, $desc, $call_date, $call_time_zone, $call_time, $ist_date, $ist_time ) {
		if(!$this->check_internet_connection())
			return;
		$url = get_option( 'host_name' ) . '/moas/api/notify/send';
		
		$plugin_version     = get_plugin_data( __DIR__ . DIRECTORY_SEPARATOR . 'mo_oauth_settings.php' )['Version'];

		$customerKey = $this->defaultCustomerKey;
		$apiKey =  $this->defaultApiKey;

		$currentTimeInMillis = self::get_timestamp();
		$stringToHash 		= $customerKey .  $currentTimeInMillis . $apiKey;
		$hashValue 			= hash("sha512", $stringToHash);
		$fromEmail 			= $email;
		$subject            = 'Call Request: WordPress '.MO_OAUTH_PLUGIN_NAME.' '.$plugin_version;
		$site_url=site_url();

		global $user;
		$user         = wp_get_current_user();

		$content='<div>Hello,<br><br>First Name : '.$user->user_firstname.'<br><br>Last Name : '.$user->user_lastname.'<br><br>Company : <a href="'.$_SERVER['SERVER_NAME'].'" target="_blank" >'.$_SERVER['SERVER_NAME'].'</a><br><br>Email : <a href="mailto:'.$fromEmail.'" target="_blank">'.$fromEmail.'</a><br><br>Issue : '.$issue.'<br><br>Description : '.$desc.'<br><br>Preferred time ('.$call_time_zone.') : '.$call_time.', '.$call_date.'<br><br>IST time : '.$ist_time.', '.$ist_date.'</div>';

		$fields = array(
			'customerKey'	=> $customerKey,
			'sendEmail' 	=> true,
			'email' 		=> array(
				'customerKey' 	=> $customerKey,
				'fromEmail' 	=> $fromEmail,
				'bccEmail' 		=> 'oauthsupport@xecurify.com',
				'fromName' 		=> 'miniOrange',
				'toEmail' 		=> 'oauthsupport@xecurify.com',
				'toName' 		=> 'oauthsupport@xecurify.com',
				'subject' 		=> $subject,
				'content' 		=> $content
			),
		);
		$field_string = json_encode($fields);
		$headers = array( 'Content-Type' => 'application/json');
		$headers['Customer-Key'] = $customerKey;
		$headers['Timestamp'] = $currentTimeInMillis;
		$headers['Authorization'] = $hashValue;
		$args = array(
			'method' =>'POST',
			'body' => $field_string,
			'timeout' => '5',
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
	}
	
	function send_otp_token($email, $phone, $sendToEmail = TRUE, $sendToPhone = FALSE){
			$url = get_option('host_name') . '/moas/api/auth/challenge';
			
			$customerKey =  $this->defaultCustomerKey;
			$apiKey =  $this->defaultApiKey;

			$username = get_option('mo_oauth_admin_email');
			$phone=get_option('mo_oauth_client_admin_phone');
			/* Current time in milliseconds since midnight, January 1, 1970 UTC. */
			$currentTimeInMillis = self::get_timestamp();

			/* Creating the Hash using SHA-512 algorithm */
			$stringToHash = $customerKey . $currentTimeInMillis . $apiKey;
			$hashValue = hash("sha512", $stringToHash);

			$customerKeyHeader = "Customer-Key: " . $customerKey;
			$timestampHeader = "Timestamp: " . $currentTimeInMillis;
			$authorizationHeader = "Authorization: " . $hashValue;

			if($sendToEmail){
				$fields = array(
					'customerKey' => $customerKey,
					'email' => $username,
					'authType' => 'EMAIL',
					);}
			else{
					$fields=array(
					'customerKey'=>$customerKey,
					'phone' => $phone,
					'authType' => 'SMS');
			}
			$field_string = json_encode($fields);
			
			$headers = array( 'Content-Type' => 'application/json');
			$headers['Customer-Key'] = $customerKey;
			$headers['Timestamp'] = $currentTimeInMillis;
			$headers['Authorization'] = $hashValue;
			$args = array(
				'method' =>'POST',
				'body' => $field_string,
				'timeout' => '5',
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
			
			return wp_remote_retrieve_body($response);
		}

		public function get_timestamp() {
		    $url = get_option ( 'host_name' ) . '/moas/rest/mobile/get-timestamp';
		    $headers = array( 'Content-Type' => 'application/json', 'charset' => 'UTF - 8', 'Authorization' => 'Basic' );
			$args = array(
				'method' =>'POST',
				'body' => array(),
				'timeout' => '5',
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
			
			return wp_remote_retrieve_body($response);
		}

		function validate_otp_token($transactionId,$otpToken){
			$url = get_option('host_name') . '/moas/api/auth/validate';
			

			$customerKey =  $this->defaultCustomerKey;
			$apiKey =  $this->defaultApiKey;

			$username = get_option('mo_oauth_admin_email');

			/* Current time in milliseconds since midnight, January 1, 1970 UTC. */
			$currentTimeInMillis = self::get_timestamp();

			/* Creating the Hash using SHA-512 algorithm */
			$stringToHash = $customerKey . $currentTimeInMillis . $apiKey;
			$hashValue = hash("sha512", $stringToHash);

			$customerKeyHeader = "Customer-Key: " . $customerKey;
			$timestampHeader = "Timestamp: " . $currentTimeInMillis;
			$authorizationHeader = "Authorization: " . $hashValue;

			$fields = '';

				//*check for otp over sms/email
				$fields = array(
					'txId' => $transactionId,
					'token' => $otpToken,
				);

			$field_string = json_encode($fields);

			$headers = array( 'Content-Type' => 'application/json');
			$headers['Customer-Key'] = $customerKey;
			$headers['Timestamp'] = $currentTimeInMillis;
			$headers['Authorization'] = $hashValue;
			$args = array(
				'method' =>'POST',
				'body' => $field_string,
				'timeout' => '5',
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
			
			return wp_remote_retrieve_body($response);
	}
	
	function check_customer() {
			$url 	= get_option('host_name') . "/moas/rest/customer/check-if-exists";
			$ch 	= curl_init( $url );
			$email 	= get_option("mo_oauth_admin_email");

			$fields = array(
				'email' 	=> $email,
			);
			$field_string = json_encode( $fields );
			$headers = array( 'Content-Type' => 'application/json', 'charset' => 'UTF - 8', 'Authorization' => 'Basic' );
			$args = array(
				'method' =>'POST',
				'body' => $field_string,
				'timeout' => '5',
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
			
			return wp_remote_retrieve_body($response);
	}
	
	function mo_oauth_send_email_alert($email,$reply,$message,$subject){

		if(!$this->check_internet_connection())
			return;
		$url = get_option( 'host_name' ) . '/moas/api/notify/send';
		
		$plugin_version     = get_plugin_data( __DIR__ . DIRECTORY_SEPARATOR . 'mo_oauth_settings.php' )['Version'];

		$customerKey = $this->defaultCustomerKey;
		$apiKey =  $this->defaultApiKey;

		$currentTimeInMillis = self::get_timestamp();
		$stringToHash 		= $customerKey .  $currentTimeInMillis . $apiKey;
		$hashValue 			= hash("sha512", $stringToHash);
		$customerKeyHeader 	= "Customer-Key: " . $customerKey;
		$timestampHeader 	= "Timestamp: " .  $currentTimeInMillis;
		$authorizationHeader= "Authorization: " . $hashValue;
		$fromEmail 			= $email;
		$subject            = $subject.' '.$plugin_version;
		$site_url=site_url();

		global $user;
		$user         = wp_get_current_user();
		$query        = '[WP ' . MO_OAUTH_PLUGIN_NAME . ' '.$plugin_version.'] : ' . $message;

		$content='<div >Hello, <br><br>First Name :'.$user->user_firstname.'<br><br>Last  Name :'.$user->user_lastname.'   <br><br>Company :<a href="'.$_SERVER['SERVER_NAME'].'" target="_blank" >'.$_SERVER['SERVER_NAME'].'</a><br><br>Email :<a href="mailto:'.$fromEmail.'" target="_blank">'.$fromEmail.'</a><br><br>'.$reply.'<br><br>Query :'.$query.'</div>';

		$fields = array(
			'customerKey'	=> $customerKey,
			'sendEmail' 	=> true,
			'email' 		=> array(
				'customerKey' 	=> $customerKey,
				'fromEmail' 	=> $fromEmail,
				'bccEmail' 		=> 'oauthsupport@xecurify.com',
				'fromName' 		=> 'miniOrange',
				'toEmail' 		=> 'oauthsupport@xecurify.com',
				'toName' 		=> 'oauthsupport@xecurify.com',
				'subject' 		=> $subject,
				'content' 		=> $content
			),
		);
		$field_string = json_encode($fields);
		$headers = array( 'Content-Type' => 'application/json');
		$headers['Customer-Key'] = $customerKey;
		$headers['Timestamp'] = $currentTimeInMillis;
		$headers['Authorization'] = $hashValue;
		$args = array(
			'method' =>'POST',
			'body' => $field_string,
			'timeout' => '5',
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
	}

	function mo_oauth_send_demo_alert($email,$demo_plan,$message,$subject) {

		if(!$this->check_internet_connection())
			return;
		$url = get_option( 'host_name' ) . '/moas/api/notify/send';
		
		$customerKey = $this->defaultCustomerKey;
		$apiKey =  $this->defaultApiKey;

		$currentTimeInMillis = self::get_timestamp();
		$stringToHash 		= $customerKey .  $currentTimeInMillis . $apiKey;
		$hashValue 			= hash("sha512", $stringToHash);
		$customerKeyHeader 	= "Customer-Key: " . $customerKey;
		$timestampHeader 	= "Timestamp: " .  $currentTimeInMillis;
		$authorizationHeader= "Authorization: " . $hashValue;
		$fromEmail 			= $email;
		$site_url=site_url();

		global $user;
		$user         = wp_get_current_user();

		$content='<div >Hello, </a><br><br>Email :<a href="mailto:'. $fromEmail.'" target="_blank">'.$fromEmail.'</a><br><br>Requested Demo for     : ' . $demo_plan . '<br><br>Requirements (User usecase)           : ' . $message.'</div>';

		$fields = array(
			'customerKey'	=> $customerKey,
			'sendEmail' 	=> true,
			'email' 		=> array(
				'customerKey' 	=> $customerKey,
				'fromEmail' 	=> $fromEmail,
				'bccEmail' 		=> 'oauthsupport@xecurify.com',
				'fromName' 		=> 'miniOrange',
				'toEmail' 		=> 'oauthsupport@xecurify.com',
				'toName' 		=> 'oauthsupport@xecurify.com',
				'subject' 		=> $subject,
				'content' 		=> $content
			),
		);
		$field_string = json_encode($fields);
		$headers = array( 'Content-Type' => 'application/json');
		$headers['Customer-Key'] = $customerKey;
		$headers['Timestamp'] = $currentTimeInMillis;
		$headers['Authorization'] = $hashValue;
		$args = array(
			'method' =>'POST',
			'body' => $field_string,
			'timeout' => '5',
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
	}
	
	function mo_oauth_forgot_password($email) {
		$url = get_option ( 'host_name' ) . '/moas/rest/customer/password-reset';
		/* The customer Key provided to you */
		$customerKey = get_option ( 'mo_oauth_client_admin_customer_key' );
		
		/* The customer API Key provided to you */
		$apiKey = get_option ( 'mo_oauth_client_admin_api_key' );
		
		/* Current time in milliseconds since midnight, January 1, 1970 UTC. */
		$currentTimeInMillis = self::get_timestamp();
		
		/* Creating the Hash using SHA-512 algorithm */
		$stringToHash = $customerKey . $currentTimeInMillis . $apiKey;
		$hashValue = hash ( "sha512", $stringToHash );
		
		$customerKeyHeader = "Customer-Key: " . $customerKey;
		$timestampHeader = "Timestamp: " . number_format ( $currentTimeInMillis, 0, '', '' );
		$authorizationHeader = "Authorization: " . $hashValue;
		
		$fields = '';
		
		// *check for otp over sms/email
		$fields = array (
				'email' => $email 
		);
		
		$field_string = json_encode ( $fields );
		
		$headers = array( 'Content-Type' => 'application/json');
		$headers['Customer-Key'] = $customerKey;
		$headers['Timestamp'] = $currentTimeInMillis;
		$headers['Authorization'] = $hashValue;
		$args = array(
			'method' =>'POST',
			'body' => $field_string,
			'timeout' => '5',
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
		
		return wp_remote_retrieve_body($response);
	}
	
	function check_internet_connection() {
		return (bool) @fsockopen('login.xecurify.com', 443, $iErrno, $sErrStr, 5);
	}
	

}?>