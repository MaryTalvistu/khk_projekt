<?php
/**
 * Plugin Admin Class File.
 *
 * @package wfcm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Admin Class.
 *
 * Handles the admin side of the plugin.
 */
class WFCM_Admin {

	/**
	 * Plugin Admin Notices.
	 *
	 * @var array
	 */
	private static $admin_notices = array();

	/**
	 * Allowed HTML.
	 *
	 * @var array
	 */
	private static $allowed_html = array(
		'a' => array(
			'href'   => array(),
			'target' => array(),
		),
	);

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'include_admin_files' ) );
		add_action( 'admin_notices', array( $this, 'show_admin_notices' ) );
		add_action( 'admin_footer', array( $this, 'admin_footer_scripts' ) );
		// Dequeue conflicting scripts.
		add_action( 'admin_print_scripts', array( $this, 'dequeue_conflicting_scripts' ) );
	}

	/**
	 * Include Admin Files.
	 */
	public function include_admin_files() {
		require_once trailingslashit( dirname( __FILE__ ) ) . 'class-wfcm-admin-menus.php';
		require_once trailingslashit( dirname( __FILE__ ) ) . 'class-wfcm-admin-plugins.php';
		require_once trailingslashit( dirname( __FILE__ ) ) . 'class-wfcm-admin-themes.php';
		require_once trailingslashit( dirname( __FILE__ ) ) . 'class-wfcm-admin-system.php';
	}

	/**
	 * Show plugin admin notices (if any).
	 */
	public function show_admin_notices() {
		if ( ! is_multisite() && ! current_user_can( 'manage_options' ) ) {
			return;
		} elseif ( is_multisite() && ! is_super_admin() ) {
			return;
		}

		// Get admin notices option.
		$admin_notices = wfcm_get_setting( 'admin-notices', array() );
	}

	/**
	 * Display notice.
	 *
	 * @param string $key - Notice key.
	 */
	private function display_notice( $key ) {
		$notice = self::$admin_notices[ $key ];
		?>
		<div id="wfcm-admin-notice-<?php echo esc_attr( $key ); ?>" class="notice notice-<?php echo esc_attr( $notice['type'] ); ?> wfcm-admin-notice is-dismissible">
			<p><?php echo wp_kses( $notice['message'], self::$allowed_html ); ?></p>
		</div>
		<?php
	}

	/**
	 * Render admin footer scripts (if needed).
	 */
	public function admin_footer_scripts() {
		// Check for debug mode.
		$suffix = ( defined( 'WP_DEBUG' ) && true === WP_DEBUG ) ? '' : '.min';

		wp_register_script(
			'wfcm-common',
			WFCM_BASE_URL . 'assets/js/dist/common' . $suffix . '.js',
			array(),
			( defined( 'WP_DEBUG' ) && true === WP_DEBUG ) ? filemtime( WFCM_BASE_DIR . 'assets/js/dist/common.js' ) : WFCM_VERSION,
			true
		);

		wp_localize_script(
			'wfcm-common',
			'wfcmData',
			array(
				'restNonce'         => wp_create_nonce( 'wp_rest' ),
				'restAdminEndpoint' => rest_url( WFCM_REST_NAMESPACE . WFCM_REST_API::$admin_notices ),
				'adminAjax'         => admin_url( 'admin-ajax.php' ),
			)
		);

		wp_enqueue_script( 'wfcm-common' );
	}

	public function dequeue_conflicting_scripts() {
		global $current_screen;
		// Only dequeue on our admin pages.
		if ( isset( $current_screen->base ) && strpos( $current_screen->base, 'wfcm-file-changes' ) !== false ) {
			wp_deregister_script( 'ph-multicarrier-admin-script' );
			wp_dequeue_script( 'ph-multicarrier-admin-script' );
		}
	}
}

new WFCM_Admin();
