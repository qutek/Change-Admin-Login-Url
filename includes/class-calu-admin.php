<?php
/**
 * CALU_Admin Class.
 *
 * @class       CALU_Admin
 * @version		1.0.0
 * @author lafif <hello@lafif.me>
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * CALU_Admin class.
 */
class CALU_Admin {

    /**
     * Singleton method
     *
     * @return self
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new CALU_Admin();
        }

        return $instance;
    }

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->includes();

		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		add_action( 'network_admin_notices', array( $this, 'admin_notices' ) );

		add_filter( 'plugin_action_links_' . WPST_CWPL_PLUGIN_BASENAME, array( $this, 'plugin_action_links' ) );

		if ( is_multisite() && is_plugin_active_for_network( WPST_CWPL_PLUGIN_BASENAME ) ) {
			add_filter( 'network_admin_plugin_action_links_' . WPST_CWPL_PLUGIN_BASENAME, array( $this, 'plugin_action_links' ) );

			add_action( 'wpmu_options', array( $this, 'wpmu_options' ) );
			add_action( 'update_wpmu_options', array( $this, 'update_wpmu_options' ) );
		}
		
	}

	public function admin_init() {
		global $pagenow;

		if($pagenow !== 'options-permalink.php')
			return;

		add_settings_section( 'calu-permalink', __( 'Change Admin Login URL', 'calu' ), array( $this, 'settings' ), 'permalink' );	

		if ( isset( $_POST['calu_url'] ) ) {
			if (
				( $login_url = sanitize_title_with_dashes( $_POST['calu_url'] ) ) &&
				strpos( $login_url, 'wp-login' ) === false &&
				! in_array( $login_url, $this->forbidden_slugs() )
			) {
				if ( is_multisite() && $login_url === get_site_option( 'calu_url', 'login' ) ) {
					delete_option( 'calu_url' );
				} else {
					update_option( 'calu_url', $login_url );
				}
			}
		}
		
	}

	public function admin_notices() {
		global $pagenow;

		if ( ! is_network_admin() && $pagenow === 'options-permalink.php' && isset( $_GET['settings-updated'] ) ) {
			echo '<div class="updated"><p>' . sprintf( __( 'Your login page is now here: %s. Bookmark this page!', 'calu' ), '<strong><a href="' . calu_login_url() . '">' . calu_login_url() . '</a></strong>' ) . '</p></div>';
		}
	}

	public function settings(){
		?>
		<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Hic cupiditate eos libero maiores repudiandae fugit dignissimos, non quasi iste incidunt similique obcaecati omnis corrupti praesentium quam, suscipit, sapiente vel provident!</p>
		
		<?php  
		if ( is_multisite() && is_super_admin() && is_plugin_active_for_network( WPST_CWPL_PLUGIN_BASENAME ) ) {
			echo '<p>' . sprintf( __( 'To set a networkwide default, go to %s.', 'calu' ), '<a href="' . network_admin_url( 'settings.php#rwl-page-input' ) . '">' . __( 'Network Settings', 'calu' ) . '</a>') . '</p>';
		}
		?>

		<table id="calu-permalink" class="form-table">
			<tbody>
				<tr>
					<th><label for="calu-permalink"><?php _e('Custom Login Permalink', 'calu'); ?></label></th>
					<td> 
						<code style="padding: 7px 5px 6px 5px; margin-right: -4px;"><?php echo get_option( 'permalink_structure' ) ? trailingslashit( home_url() ) : home_url( '?' ); ?></code><input name="calu_url" id="calu-permalink" type="text" value="<?php echo calu_login_slug(); ?>" class="regular-text code">
					</td>
				</tr>
			</tbody>
		</table>
		<?php
	}

	public function plugin_action_links( $links ) {
		if ( is_network_admin() && is_plugin_active_for_network( WPST_CWPL_PLUGIN_BASENAME ) ) {
			array_unshift( $links, '<a href="' . network_admin_url( 'settings.php#calu-permalink' ) . '">' . __( 'Settings', 'calu' ) . '</a>' );
		} elseif ( ! is_network_admin() ) {
			array_unshift( $links, '<a href="' . admin_url( 'options-permalink.php#calu-permalink' ) . '">' . __( 'Settings', 'calu' ) . '</a>' );
		}

		return $links;
	}

	public function wpmu_options() {
		$out = '';

		$out .= '<h3>' . __( 'Change Admin Login URL', 'calu' ) . '</h3>';
		$out .= '<p>' . __( 'This option allows you to set a networkwide default, which can be overridden by individual sites. Simply go to to the site’s permalink settings to change the url.', 'calu' ) . '</p>';
		$out .= '<table class="form-table">';
			$out .= '<tr valign="top">';
				$out .= '<th scope="row">' . __( 'Networkwide default', 'calu' ) . '</th>';
				$out .= '<td><input type="text" name="calu_url" value="' . get_site_option( 'calu_url', 'login' )  . '"></td>';
			$out .= '</tr>';
		$out .= '</table>';

		echo $out;
	}

	public function update_wpmu_options() {
		if (
			( $login_url = sanitize_title_with_dashes( $_POST['calu_url'] ) ) &&
			strpos( $login_url, 'wp-login' ) === false &&
			! in_array( $login_url, $this->forbidden_slugs() )
		) {
			update_site_option( 'calu_url', $login_url );
		}
	}


	public function forbidden_slugs() {
		$wp = new WP();
		return array_merge( $wp->public_query_vars, $wp->private_query_vars );
	}

	public function includes(){
	
	}

}

CALU_Admin::init();