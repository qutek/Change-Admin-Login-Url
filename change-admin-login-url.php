<?php
/**
 * Plugin Name: Change Admin Login Url
 * Description: Change and display 404 for default WordPress login url
 * Author: Lafif Astahdziq
 * Author URI: https://lafif.me
 * Author Email: hello@lafif.me
 * Version: 1.0.0
 * Text Domain: calu
 * Domain Path: /languages/ 
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Change_Admin_Login_Url' ) ) :

/**
 * Main Change_Admin_Login_Url Class
 *
 * @class Change_Admin_Login_Url
 * @version	1.0.0
 */
final class Change_Admin_Login_Url {

	/**
	 * @var string
	 */
	public $version = '1.0.0';

	public $capability = 'manage_options';

	public $notices;

	/**
	 * @var Change_Admin_Login_Url The single instance of the class
	 * @since 1.0.0
	 */
	protected static $_instance = null;

	/**
	 * Main Change_Admin_Login_Url Instance
	 *
	 * Ensures only one instance of Change_Admin_Login_Url is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @return Change_Admin_Login_Url - Main instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Change_Admin_Login_Url Constructor.
	 */
	public function __construct() {
		$this->define_constants();
		$this->includes();
		$this->init_hooks();

		do_action( 'calu_loaded' );
	}

	/**
	 * Hook into actions and filters
	 * @since  1.0.0
	 */
	private function init_hooks() {

		register_activation_hook( __FILE__, array( $this, 'install' ) );

		add_action( 'init', array( $this, 'init' ), 0 );

		register_uninstall_hook( __FILE__, 'uninstall' );
	}

	/**
	 * All install stuff
	 * @return [type] [description]
	 */
	public function install() {
		
		// we did something on install
		do_action( 'on_calu_install' );
	}

	/**
	 * All uninstall stuff
	 * @return [type] [description]
	 */
	public function uninstall() {

		// we remove what we did 
		do_action( 'on_calu_uninstall' );
	}

	/**
	 * Init Change_Admin_Login_Url when WordPress Initialises.
	 */
	public function init() {

		// register all scripts
		$this->register_scripts();
	}

	/**
	 * Register all scripts to used on our pages
	 * @return [type] [description]
	 */
	private function register_scripts(){

		// wp_register_style( 'calu', plugins_url( '/assets/css/calu.css', __FILE__ ) );
		// wp_register_script( 'asPieProgress', plugins_url( '/assets/js/jquery-asPieProgress.js', __FILE__ ), array('jquery'), '', true );
 	
		do_action( 'calu_register_script' );
 	}

	/**
	 * Define Change_Admin_Login_Url Constants
	 */
	private function define_constants() {

		$this->define( 'WPST_CWPL_PLUGIN_FILE', __FILE__ );
		$this->define( 'WPST_CWPL_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
		$this->define( 'WPST_CWPL_VERSION', $this->version );
	}

	/**
	 * Define constant if not already set
	 * @param  string $name
	 * @param  string|bool $value
	 */
	private function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	/**
	 * What type of request is this?
	 * string $type ajax, frontend or admin
	 * @return bool
	 */
	public function is_request( $type ) {
		switch ( $type ) {
			case 'admin' :
				return is_admin();
			case 'ajax' :
				return defined( 'DOING_AJAX' );
			case 'cron' :
				return defined( 'DOING_CRON' );
			case 'frontend' :
				return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
		}
	}

	/**
	 * Include required core files used in admin and on the frontend.
	 */
	public function includes() {
		// all public includes
		include_once( 'includes/functions-calu.php' );
		include_once( 'includes/class-calu-login.php' );

		if ( $this->is_request( 'admin' ) ) {
			include_once( 'includes/class-calu-admin.php' );
		}

		if ( $this->is_request( 'ajax' ) ) {

		}

		if ( $this->is_request( 'frontend' ) ) {
		}
	}

	/**
	 * Get the plugin url.
	 * @return string
	 */
	public function plugin_url() {
		return untrailingslashit( plugins_url( '/', __FILE__ ) );
	}

	/**
	 * Get the plugin path.
	 * @return string
	 */
	public function plugin_path() {
		return untrailingslashit( plugin_dir_path( __FILE__ ) );
	}

	/**
	 * Get Ajax URL.
	 * @return string
	 */
	public function ajax_url() {
		return admin_url( 'admin-ajax.php', 'relative' );
	}

}

endif;

function incompatible_notice(){
	?>
    <div class="error">
        <p><?php _e('We need the latest version of WordPress', 'calu'); ?></p>
    </div>
    <?php
}

/**
 * Returns the main instance of Change_Admin_Login_Url to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return Change_Admin_Login_Url
 */
function CALU() {
	global $wp_version;

	if ( version_compare( $wp_version, '4.0-RC1-src', '<' ) ) {
		add_action( 'admin_notices', 'incompatible_notice' );
		add_action( 'network_admin_notices', 'incompatible_notice' );

		return;
	}

	return Change_Admin_Login_Url::instance();
}

CALU();