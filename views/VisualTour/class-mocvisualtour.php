<?php
/**
 * Class for visual tour.
 *
 * @category   Free
 * @package    MoOauthClient
 * @author     miniOrange <info@miniorange.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @link       https://miniorange.com
 */

require_once 'vt-consts.php';

/**
 * Class for Configure OAuth Tab UI
 *
 * @category UI
 * @package  MoOauthClient
 * @author   miniOrange <info@miniorange.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @link     https://miniorange.com
 */
class MOCVisualTour {

	/**
	 * Housekeeping
	 *
	 * @var string
	 */
	protected $nonce;

	/**
	 * Housekeeping
	 *
	 * @var string
	 */
	protected $nonce_key;

	/**
	 * Housekeeping
	 *
	 * @var string
	 */
	protected $tour_ajax_action;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->nonce            = 'mo_admin_actions';
		$this->nonce_key        = 'security';
		$this->tour_ajax_action = 'miniorange-tour-taken';
		// add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_visual_tour_script' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_visual_tour_script' ] );
		add_action( "wp_ajax_{$this->tour_ajax_action}", [ $this, 'update_tour_taken' ] );
		add_action( "wp_ajax_nopriv_{$this->tour_ajax_action}", [ $this, 'update_tour_taken' ] );
	}

	/**
	 * Adds TourTaken variable in Options for the page that has tour completed
	 */
	public function update_tour_taken() {
		$this->validate_ajax_request();
		if ( isset( $_POST['overallDone'] ) ) {
			update_option( 'moc_tourTaken_overall', sanitize_text_field( wp_unslash( $_POST['overallDone'] ) ) );
			die();
		}
		if ( isset( $_POST['noShowRocket'] ) ) {
			update_option( 'moc_tourTaken_noShowRocket', sanitize_text_field( wp_unslash( $_POST['noShowRocket'] )));
			die();
		}
		if ( isset( $_POST['skipTour'] ) ) {
			$this->skipTourMovie();
		}
		update_option( 'tourTaken_' . sanitize_text_field( wp_unslash( $_POST['pageID'])), sanitize_text_field( wp_unslash( $_POST['doneTour'] )) );
		update_option( 'tourTaken_first_' . sanitize_text_field( wp_unslash( $_POST['pageID'])), sanitize_text_field( wp_unslash( $_POST['doneTour'])) );
		if ( isset( $_POST['tourNum'] ) && intval( $_POST['tourNum'] ) === 2 ) {
			update_option( 'tourTaken_second_' . sanitize_text_field( wp_unslash( $_POST['pageID'] ) ), sanitize_text_field( wp_unslash( $_POST['doneTour'] )) );
		}
		if ( isset( $_POST['tconfDone'] ) ) {
			update_option( 'tourTaken_tconf_shown', sanitize_text_field( wp_unslash( $_POST['tconfDone'] )) );
		}
		update_option( 'moc_tourTaken_first', true );
		die();
	}

	private function skipTourMovie() {
		$pages = [ 'config', 'attributemapping', 'signinsettings' ];
		foreach ( $pages as $pageID ) {
			update_option( 'tourTaken_' . $pageID, true );
			update_option( 'tourTaken_first_' . $pageID, true );
			update_option( 'tourTaken_second_' . $pageID, true );
		}
		update_option( 'moc_tourTaken_overall', true );
		update_option( 'moc_tourTaken_noShowRocket', true );
	}

	/**
	 * Checks if the request made is a valid ajax request or not.
	 * Only checks the none value for now.
	 */
	private function validate_ajax_request() {
		if ( ! check_ajax_referer( $this->nonce, $this->nonce_key ) ) {
			wp_send_json(
				[
					'message' => 'Invalid Operation. Please try again.',
					'result'  => 'error',
				]
			);
			exit;
		}
	}

	/**
	 * Function called by Enqueue Hook to register and localize the script and
	 * script variables.
	 */
	public function enqueue_visual_tour_script() {
		wp_register_script( 'tour_script', TOUR_RES_JS . 'visualTour.js', [ 'jquery' ], NULL, false );
		$currenttab = isset( $_REQUEST['tab'] ) && '' !== $_REQUEST['tab'] ? $_REQUEST['tab'] : '';
		wp_localize_script(
			'tour_script',
			'moTour',
			array(
				'siteURL'      => admin_url( 'admin-ajax.php' ),
				'tnonce'       => \wp_create_nonce( $this->nonce ),
				'pageID'       => $currenttab,
				'tourData'     => $this->get_tour_data( $currenttab ),
				'tourTaken'    => $this->check_tour_taken( $currenttab ),
				'ajaxAction'   => $this->tour_ajax_action,
				'nonceKey'     => \wp_create_nonce( $this->nonce_key ),
				'base_url'     => site_url(),
				'tbase_url'    => TOUR_RES_IMG,
				'tconfShown'   => boolval( get_option( 'tourTaken_tconf_shown' ) ),
				'overAllDone'  => boolval( get_option( 'moc_tourTaken_overall' ) ),
				'noShowRocket' => boolval( get_option( 'moc_tourTaken_noShowRocket' ) ),
			)
		);
		wp_enqueue_script( 'tour_script' );
		wp_enqueue_style( 'moc_visual_tour_style', TOUR_RES_CSS . 'visualTour.css', false, NULL, 'all' );
	}

	private function check_tour_taken( $currenttab ) {
		$final_check = get_option( 'tourTaken_' . $currenttab );
		if ( $currenttab === 'attributemapping' ) {
			$final_check = $final_check && get_option( 'tourTaken_first_' . $currenttab ) && get_option( 'tourTaken_second_' . $currenttab );
		}
		return $final_check;
	}
	/**
	 * Tour Data Template
	 *
	 * @param string $target_element jQuery Selector for the target element.
	 * @param string $point_to_side  the direction to point. place the card on the other side.
	 * @param string $title_html     Title of the card, can be string, HTML or empty.
	 * @param string $content_html   Description of the card, can be string, HTML or empty.
	 * @param string $button_text    text on the Next Button.
	 * @param string $img            image name.
	 * @param int    $size           size of the card, 0=small, 1=medium, 2=big.
	 * @return array    -   Tour card array
	 */
	public function tour_template( $target_element, $point_to_side, $title_html, $content_html, $button_text, $img, $size ) {
		$card_size = [ 'small', 'medium', 'big' ];
		return [
			'targetE'     => $target_element,
			'pointToSide' => $point_to_side,
			'titleHTML'   => $title_html,
			'contentHTML' => $content_html,
			'buttonText'  => $button_text,
			'img'         => $img ? TOUR_RES_IMG . $img : $img,
			'cardSize'    => $card_size[ $size ],
		];
	}

	/**
	 * This functions return the array containing the tour elements for the current page
	 *
	 * @param string $currenttab  current page/tab.
	 *
	 * @return array tour data for current tab
	 */
	private function get_tour_data( $currenttab = '' ) {
		$tour_data = [];
		$first_tour = boolval( get_option( 'tourTaken_first_' . $currenttab ) );
		$second_tour = boolval( get_option( 'tourTaken_second_' . $currenttab ) );
		if ( ! get_option( 'moc_tourTaken_first' ) && ! get_option( 'moc_tourTaken_noShowRocket' ) ) {
			$tour_data = [
				$this->tour_template(
					'',
					'',
					'<h1>Welcome!</h1>',
					'Fasten your seat belts for a quick ride.',
					'Let\'s Go!',
					'startTour.svg',
					2
				),
			];
			// $tour_data = array_merge( $tour_data, $this->get_tab_pointers() );
		}
		if ( 'config' === $currenttab ) {
			if ( isset( $_REQUEST['action'] ) && 'update' === $_REQUEST['action'] ) { // phpcs:ignore
				$tour_data = array_merge( $tour_data, $this->get_updateui_pointers() );
			}
			$appslist = get_option( 'mo_oauth_apps_list' ) ? get_option( 'mo_oauth_apps_list' ) : false;
			if ( $appslist && is_array( $appslist ) && 0 < count( $appslist ) && ! isset( $_REQUEST['appId'] ) && $first_tour ) {
				$tour_data = array_merge( $tour_data, $this->get_applist_pointers() );
			} elseif ( ! isset( $_REQUEST['appId'] ) && ! isset( $_REQUEST['action'] ) ) {
				$tour_data = array_merge( $tour_data, $this->get_defaultapps_pointers( $first_tour ) );
				if ( ! $first_tour ) {
					$tour_data = array_merge( $tour_data, $this->fake_config_movie() );
				}
			}
			if ( isset( $_REQUEST['appId'] ) && '' !== $_REQUEST['appId'] ) { // phpcs:ignore
				$tour_data = array_merge( $tour_data, $this->get_addapp_pointers( $second_tour ) );
				if ( ! $second_tour ) {
					$tour_data = array_merge( $tour_data, $this->get_app_config_pointers() );
				}
			}
		}
		if ( 'signinsettings' === $currenttab ) {
			$tour_data = array_merge( $tour_data, $this->get_signinsettings_pointers( $first_tour ) );
			if ( ! $first_tour ) {
				$tour_data = array_merge( $tour_data, $this->get_signinsettings_first_tour_pointers() );
			}
		}
		if ( 'attributemapping' === $currenttab ) {
			$tour_data = array_merge( $tour_data, $this->get_attrmap_pointers( $first_tour, $second_tour ) );
			if ( ! $first_tour ) {
				$tour_data = array_merge( $tour_data, $this->get_attrmap_first_tour_pointers() );
			}
		}

		return $tour_data;
	}

	/**
	 * Function to get all tab tour data.
	 *
	 * @return array
	 */
	private function get_tab_pointers() {
		return [
			$this->tour_template(
				'mo_support_layout',
				'right',
				'<h1>We are here!!</h1>',
				'Get in touch with us and we will help you setup the plugin in no time.',
				'Next',
				'help.svg',
				2
			),
			$this->tour_template(
				'tab-config',
				'up',
				'<h1>Configuration Tab</h1>',
				'You can choose and configure any OAuth/OpenID application.',
				'Next',
				'choose.svg',
				2
			),
			$this->tour_template(
				'tab-attrmapping',
				'up',
				'<h1>Attribute/Role Mapping Tab</h1>',
				'You can map the user roles as well as attributes in this tab.',
				'Next',
				'choose.svg',
				2
			),
			$this->tour_template(
				'tab-signinsettings',
				'up',
				'<h1>Sign In Settings</h1>',
				'You can find various SSO related configurations such as shortcodes and User Registration here!',
				'Next',
				'profile.svg',
				2
			),
			$this->tour_template(
				'tab-customization',
				'up',
				'<h1>Widget Customization Tab</h1>',
				'You can customize your login widget or shortcode widget to your liking with CSS here!',
				'Next',
				'choose.svg',
				2
			),
			$this->tour_template(
				'tab-requestdemo',
				'up',
				'<h1>Request For Demo</h1>',
				'Are you looking for premium features? Now, you can send a request to setup a demo of the premium version you are interested in and our team will set it up for you!',
				'Next',
				'preview.svg',
				2
			),
			$this->tour_template(
				'tab-acc-setup',
				'up',
				'<h1>I want to upgrade!</h1>',
				'You do not need to setup your account to use the plugin. If you want to upgrade, you will need a miniOrange account.',
				'Next',
				'popUp.svg',
				2
			),
			$this->tour_template(
				'tab-addons',
				'up',
				'<h1>I have special requirements!</h1>',
				'If you need additional features, you can always install these add-ons and extend the plugin functionality!',
				'Next',
				'addOns.svg',
				2
			),
			$this->tour_template(
				'license_upgrade',
				'up',
				'<h1>Licensing Plans</h1>',
				'You can check all the licensing plans and the features as well as options they offer, here.',
				'Next',
				'upgrade.svg',
				2
			),
			$this->tour_template(
				'faq_button_id',
				'up',
				'<h1>Facing a problem?</h1>',
				'You can check FAQs. Most questions can be solved by reading through the FAQs..',
				'Next',
				'faq.svg',
				2
			),
			$this->tour_template(
				'form_button_id',
				'up',
				'<h1>Have questions?</h1>',
				'You can check out all the questions that are already asked. If you feel that your question is not answered, you can always post new questions on our forums.',
				'Next',
				'maps-and-flags.svg',
				2
			),
			$this->tour_template(
				'restart_tour_button',
				'right',
				'<h1>Restart Tour</h1>',
				'If you need to revisit the tour, you can use this button to replay it for the current tab!',
				'Next',
				'replay.svg',
				2
			),
		];
	}

	/**
	 * Function to get update app page pointers
	 */
	private function get_updateui_pointers() {
		return [
			$this->tour_template(
				'mo_oauth_test_configuration',
				'up',
				'<h1>Test your configuration</h1>',
				'Click here to see the list of attributes provided by your OAuth Provider. If you are getting any error, please refer the FAQ tab.',
				'Next',
				'first_tour_testconfig',
				1
			),
			$this->tour_template(
				'mo_oauth_test_configuration',
				'up',
				'<h1>Checking Configuration</h1>',
				'You can check the configuration in the new window. We will use this list table to map our user attributes, so dont close the window yet!',
				'Next',
				'first_tour_goattrmap',
				1
			),
		];
	}

	private function get_attrmap_pointers($first_tour, $second_tour) {
		return [
			$this->tour_template(
				'attribute-mapping',
				'left',
				'<h1>Mapping Attributes</h1>',
				'Enter the appropriate values(attribute names) from the Test Configuration table.',
				'Next',
				'preview.svg',
				1
			),
			$this->tour_template(
				'role-mapping',
				'left',
				'<h1>Mapping Roles</h1>',
				'Enter the role values from your OAuth/OpenID provider and then select the WordPress Role that you need to assign that role.',
				($first_tour && $second_tour) ? 'false' : 'Next',
				'preview.svg',
				1
			),
		];
	}

	private function get_attrmap_first_tour_pointers() {
		return [
			$this->tour_template(
				'mo_oauth_email_attr_div',
				'up',
				'<h1>Mapping Attribute</h1>',
				'Enter the appropriate value(attribute name) from the Test Configuration table from the other window. We will enter "username" for this example.',
				'Next',
				'first_tour_mapuname',
				0
			),
			// $this->tour_template(
			// 	'mo_oauth_email_attr_div',
			// 	'up',
			// 	'<h1>Login Options</h1>',
			// 	'We provide multiple login options. You can choose to login with Widget, Shortcode or a Link. We also provide multiple options to modify the user experience while logging in.',
			// 	'Next',
			// 	'first_tour_gotosignin',
			// 	1
			// ),
		];
	}

	/**
	 * Function to get Sign in Settings pointers
	 */
	private function get_signinsettings_pointers( $tour_status ) {
		return [
			$this->tour_template(
				'wid-shortcode',
				'left',
				'<h1>Sign In Options</h1>',
				'You can display your login button using these methods.',
				'Next',
				'preview.svg',
				2
			),
			$this->tour_template(
				'advanced_settings_sso',
				'left',
				'<h1>Advanced Settings</h1>',
				'You can configure other <em>important</em> features that enhance the user experience. An incomplete list of these features is as follows: <div class="mo-tour-advsets"><ul><li>Forced Authentication</li><li>Account Linking</li><li>Custom Redirection after login/logout</li><li>Domain Restriction</li><li>Dynamic Callback URL</li></ul></div>',
				$tour_status ? 'false' : 'Next',
				'',
				2
			),
		];
	}

	private function get_signinsettings_first_tour_pointers() {
		return [
			$this->tour_template(
				'mo_support_layout',
				'right',
				'<h1>We are here!!</h1>',
				'If you face any issues or difficulties with the configuration, get in touch with us and we will help you setup the plugin in no time.',
				'Next',
				'first_tour_deleteapp',
				1
			),
		];
	}

	/**
	 * Function to get App List pointers
	 */
	private function get_applist_pointers() {
		return [
			// $this->tour_template(
			// 	'mo_oauth_app_list',
			// 	'left',
			// 	'<h1>App List</h1>',
			// 	'Click here to Update or Delete the application.',
			// 	'false',
			// 	'preview.svg',
			// 	2
			// ),
		];
	}

	private function fake_config_movie() {
		return [
			$this->tour_template(
				'vip-default-app',
				'left',
				'<h1>Lets See it in Action!</h1>',
				'We will configure AWS Cognito to begin with. You can, always, skip this step if you do not want to see the configuration.',
				'start_mov',
				'design.svg',
				2
			),
		];
	}
	/**
	 * Function to get Default Apps pointers
	 */
	private function get_defaultapps_pointers( $tour_status ) {
		return [
			$this->tour_template(
				'tab-config',
				'up',
				'<h1>Configuration Tab</h1>',
				'You can choose and configure any OAuth/OpenID application.',
				'Next',
				'choose.svg',
				2
			),
			$this->tour_template(
				'mo_oauth_client_default_apps',
				'left',
				'<h1>Select OAuth Provider</h1>',
				'Choose your OAuth Provider from the list of OAuth Providers',
				$tour_status ? 'false' : 'Next',
				'preview.svg',
				2
			),
			$this->tour_template(
				'mo_oauth_client_searchable_apps',
				'left',
				'<h1>Pre-Configured Providers</h1>',
				'Choose your OAuth Provider from the list of the Pre-Configured OAuth Providers, and we will configure the application for you!',
				$tour_status ? 'false' : 'Next',
				'preview.svg',
				2
			),
			$this->tour_template(
				'mo_oauth_client_custom_apps',
				'left',
				'<h1>Custom Providers</h1>',
				'Couldn\'t find your provider in the list? Do not worry. You can choose a custom application and configure it yourself. If you need any help, we would always be there!',
				$tour_status ? 'false' : 'Next',
				'preview.svg',
				2
			)
		];
	}

	/**
	 * Function to get Add Apps pointers
	 */
	private function get_addapp_pointers( $tour_status ) {
		return [
			$this->tour_template(
				'mo_oauth_config_guide',
				'left',
				'<h1>Configure Your App</h1>',
				'Need help with configuration? Click on How to Configure?',
				$tour_status ? 'false' : 'Next',
				'',
				1
			),
		];
	}

	private function get_app_config_pointers() {
		return [
			// $this->tour_template(
			// 	'mo_oauth_custom_app_name',
			// 	'left',
			// 	'<h1>Your Application Name</h1>',
			// 	'We will enter the name of the application. (No Special Characters)',
			// 	'Next',
			// 	'first_tour_appname',
			// 	1
			// ),
			$this->tour_template(
				'mo_oauth_client_creds',
				'left',
				'<h1>Your Application\'s Client ID</h1>',
				'We enter the client ID here',
				'Next',
				'first_tour_cid',
				1
			),
			// $this->tour_template(
			// 	'mo_oauth_client_secret',
			// 	'left',
			// 	'<h1>Your Application\'s Client Secret</h1>',
			// 	'We enter the client secret here',
			// 	'Next',
			// 	'first_tour_cs',
			// 	1
			// ),
			$this->tour_template(
				'mo_oauth_client_endpoints',
				'left',
				'<h1>Endpoints</h1>',
				'We enter appropriate endpoints here. If you have any questions regarding this step, please don\'t hesitate to contact us!',
				'Next',
				'first_tour_endpoints',
				1
			),
			$this->tour_template(
				'mo_save_app',
				'left',
				'<h1>All Set!</h1>',
				'After filling up the necessary details, we save.',
				'Next',
				'first_tour_mo_save_app',
				1
			),
		];
	}
}
