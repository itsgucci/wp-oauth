<?php

class Mo_OAuth_Client {

	protected $loader;

	protected $plugin_name;

	protected $version;

	public function __construct() {
		$this->plugin_name = 'miniOrange '.MO_OAUTH_PLUGIN_NAME;
		$this->version = '1.0.1';
		$this->load_dependencies();
		$this->define_admin_hooks();
		//$this->define_public_hooks();
	}

	
	private function load_dependencies() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-mo-oauth-client-loader.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-mo-oauth-client-admin.php';
		//require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-test-public.php';
		$this->loader = new Mo_OAuth_Client_Loader();
	}


	private function define_admin_hooks() {
		$plugin_admin = new Mo_OAuth_Client_Admin( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'admin_menu' );
		$this->loader->add_action( 'admin_enqueue_scripts', '', 'mo_oauth_client_plugin_settings_style' );
		$this->loader->add_action( 'admin_enqueue_scripts', '', 'mo_oauth_client_plugin_settings_script' );
	}


	/*private function define_public_hooks() {
		$plugin_public = new Test_Public( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}*/

	public function run() {
		$this->loader->run();
	}

	public function get_plugin_name() {
		return $this->plugin_name;
	}

	public function get_loader() {
		return $this->loader;
	}

	public function get_version() {
		return $this->version;
	}

}
