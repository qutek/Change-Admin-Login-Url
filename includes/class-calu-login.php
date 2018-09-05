<?php
/**
 * CALU_Login Class.
 *
 * @class       CALU_Login
 * @version		1.0
 * @author lafif <hello@lafif.me>
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * CALU_Login class.
 */
class CALU_Login {

	private $wp_login_php;

    /**
     * Singleton method
     *
     * @return self
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new CALU_Login();
        }

        return $instance;
    }

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->includes();

		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ), 1 );
		add_action( 'wp_loaded', array( $this, 'wp_loaded' ) );

		add_filter( 'site_url', array( $this, 'site_url' ), 10, 4 );
		add_filter( 'network_site_url', array( $this, 'network_site_url' ), 10, 3 );
		add_filter( 'wp_redirect', array( $this, 'wp_redirect' ), 10, 2 );

		add_filter( 'site_option_welcome_email', array( $this, 'welcome_email' ) );

		remove_action( 'template_redirect', 'wp_redirect_admin_locations', 1000 );

	}

	public function plugins_loaded() {
		global $pagenow;

		load_plugin_textdomain( 'calu' );

		if (
			! is_multisite() && (
				strpos( $_SERVER['REQUEST_URI'], 'wp-signup' ) !== false ||
				strpos( $_SERVER['REQUEST_URI'], 'wp-activate' ) !== false
			)
		) {
			wp_die( __( 'This feature is not enabled.', 'calu' ) );
		}

		$request = parse_url( $_SERVER['REQUEST_URI'] );

		if ( (
				strpos( $_SERVER['REQUEST_URI'], 'wp-login.php' ) !== false ||
				untrailingslashit( $request['path'] ) === site_url( 'wp-login', 'relative' )
			) &&
			! is_admin()
		) {
			$this->wp_login_php = true;
			$_SERVER['REQUEST_URI'] = calu_trailingslashit( '/' . str_repeat( '-/', 10 ) );
			$pagenow = 'index.php';
		} elseif (
			untrailingslashit( $request['path'] ) === home_url( calu_login_slug(), 'relative' ) || (
				! get_option( 'permalink_structure' ) &&
				isset( $_GET[calu_login_slug()] ) &&
				empty( $_GET[calu_login_slug()] )
		) ) {
			$pagenow = 'wp-login.php';
		}
	}

	public function wp_loaded() {
		global $pagenow;

		if ( is_admin() && ! is_user_logged_in() && ! defined( 'DOING_AJAX' ) ) {

			$function = apply_filters( 'calu_wp_admin_handler', 'calu_default_wp_admin_handler' );

			if( is_callable( $function ) ){
				call_user_func( $function );
			}
		}

		$request = parse_url( $_SERVER['REQUEST_URI'] );

		if (
			$pagenow === 'wp-login.php' &&
			$request['path'] !== calu_trailingslashit( $request['path'] ) &&
			get_option( 'permalink_structure' )
		) {
			wp_safe_redirect( calu_trailingslashit( calu_login_url() ) . ( ! empty( $_SERVER['QUERY_STRING'] ) ? '?' . $_SERVER['QUERY_STRING'] : '' ) );
			die;
		} elseif ( $this->wp_login_php ) {
			if (
				( $referer = wp_get_referer() ) &&
				strpos( $referer, 'wp-activate.php' ) !== false &&
				( $referer = parse_url( $referer ) ) &&
				! empty( $referer['query'] )
			) {
				parse_str( $referer['query'], $referer );

				if (
					! empty( $referer['key'] ) &&
					( $result = wpmu_activate_signup( $referer['key'] ) ) &&
					is_wp_error( $result ) && (
						$result->get_error_code() === 'already_active' ||
						$result->get_error_code() === 'blog_taken'
				) ) {
					wp_safe_redirect( calu_login_url() . ( ! empty( $_SERVER['QUERY_STRING'] ) ? '?' . $_SERVER['QUERY_STRING'] : '' ) );
					die;
				}
			}

			$this->wp_template_loader();
		} elseif ( $pagenow === 'wp-login.php' ) {
			global $error, $interim_login, $action, $user_login;

			@require_once ABSPATH . 'wp-login.php';

			die;
		}
	}

	private function wp_template_loader() {
		global $pagenow;

		$pagenow = 'index.php';

		if ( ! defined( 'WP_USE_THEMES' ) ) {
			define( 'WP_USE_THEMES', true );
		}

		wp();

		if ( $_SERVER['REQUEST_URI'] === calu_trailingslashit( str_repeat( '-/', 10 ) ) ) {
			$_SERVER['REQUEST_URI'] = calu_trailingslashit( '/login-404/' );
		}

		require_once( ABSPATH . WPINC . '/template-loader.php' );

		die();
	}

	public function site_url( $url, $path, $scheme, $blog_id ) {
		return calu_filter_wp_login_php( $url, $scheme );
	}

	public function network_site_url( $url, $path, $scheme ) {
		return calu_filter_wp_login_php( $url, $scheme );
	}

	public function wp_redirect( $location, $status ) {
		return calu_filter_wp_login_php( $location );
	}

	public function welcome_email( $value ) {
		return $value = str_replace( 'wp-login.php', trailingslashit( calu_login_slug() ), $value );
	}

	public function includes(){
	
	}

}

CALU_Login::init();