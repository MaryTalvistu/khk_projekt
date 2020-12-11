<?php
/**
 * Admin Menus.
 *
 * @package wfcm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Plugin Admin Menus Class.
 */
class WFCM_Admin_Menus {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$menu_action = is_multisite() ? 'network_admin_menu' : 'admin_menu';

		add_action( $menu_action, array( $this, 'add_admin_menu' ), 10 );
		add_action( $menu_action, array( $this, 'settings_menu' ), 20 );
		add_action( $menu_action, array( $this, 'about_menu' ), 30 );
		add_action( $menu_action, array( $this, 'add_events_count' ), 40 );

		add_action( 'admin_print_styles', array( $this, 'admin_styles' ) );
		add_filter( 'plugin_action_links_' . WFCM_BASE_NAME, array( $this, 'shortcut_links' ), 10, 1 );
		add_action( 'wp_ajax_wfcm_dismiss_instant_scan_modal', array( $this, 'dismiss_instant_scan_modal' ) );
		add_action( 'wp_ajax_wfcm_sha256_upgrade_flush', array( $this, 'sha256_upgrade_flush' ) );
		add_action( 'wp_ajax_wfcm_send_test_email', array( $this, 'send_test_email' ) );
		add_action( 'wp_ajax_wfcm_exclude_file_from_notice', array( $this, 'exclude_file_from_notice' ) );
	}

	/**
	 * Add Plugin Admin Menu.
	 *
	 * Admin menu pages and sub-pages:
	 *
	 * 1. Files Monitor.
	 * 2. Settings.
	 * 3. Help & About.
	 */
	public function add_admin_menu() {
		add_menu_page(
			__( 'Website File Changes Monitor', 'website-file-changes-monitor' ),
			__( 'Files Monitor', 'website-file-changes-monitor' ),
			'manage_options',
			'wfcm-file-changes',
			null,
			WFCM_BASE_URL . 'assets/img/wfcm-menu-icon.svg',
			'75'
		);

		add_submenu_page( 'wfcm-file-changes', __( 'Website File Changes Monitor', 'website-file-changes-monitor' ), __( 'Files Monitor', 'website-file-changes-monitor' ), 'manage_options', 'wfcm-file-changes', array( $this, 'file_changes_page' ) );
	}

	/**
	 * Add Settings Menu.
	 */
	public function settings_menu() {
		$settings_page = add_submenu_page( 'wfcm-file-changes', __( 'Settings', 'website-file-changes-monitor' ), __( 'Settings', 'website-file-changes-monitor' ), 'manage_options', 'wfcm-settings', array( $this, 'settings_page' ) );
		add_action( "load-$settings_page", array( $this, 'settings_page_init' ) );
	}

	/**
	 * Add About Menu.
	 */
	public function about_menu() {
		add_submenu_page( 'wfcm-file-changes', __( 'Help & About', 'website-file-changes-monitor' ), __( 'Help & About', 'website-file-changes-monitor' ), 'manage_options', 'wfcm-about', array( $this, 'about_page' ) );
	}

	/**
	 * Files Monitor Page.
	 */
	public function file_changes_page() {
		WFCM_Admin_File_Changes::output();
	}

	/**
	 * Settings Page.
	 */
	public function settings_page() {
		WFCM_Admin_Settings::output();
	}

	/**
	 * Settings Page Initialized.
	 */
	public function settings_page_init() {
		if ( ! empty( $_POST['submit'] ) ) { // @codingStandardsIgnoreLine
			WFCM_Admin_Settings::save();
		}
	}

	/**
	 * About Page.
	 */
	public function about_page() {
		WFCM_Admin_About::output();
	}

	/**
	 * Add events count to menu.
	 */
	public function add_events_count() {
		global $menu;

		$events_count = wp_count_posts( 'wfcm_file_event' );

		if ( isset( $events_count->private ) && $events_count->private ) {
			$count_html = '<span class="update-plugins"><span class="events-count">' . $events_count->private . '</span></span>';

			foreach ( $menu as $key => $value ) {
				if ( 'wfcm-file-changes' === $menu[ $key ][2] ) {
					$menu[ $key ][0] .= ' ' . $count_html; // phpcs:ignore
					break;
				}
			}
		}
	}

	/**
	 * Print admin styles.
	 */
	public function admin_styles() {
		?>
		<style>#adminmenu .toplevel_page_wfcm-file-changes .wp-menu-image img { padding: 5px 0 0 0; }</style>
		<?php
	}

	/**
	 * Add shortcut links to plugins page.
	 *
	 * @param array $old_links - Array of old links.
	 * @return array
	 */
	public function shortcut_links( $old_links ) {
		$new_links[] = '<a href="' . add_query_arg( 'page', 'wfcm-file-changes', admin_url( 'admin.php' ) ) . '">' . __( 'See File Changes', 'website-file-changes-monitor' ) . '</a>';
		$new_links[] = '<a href="' . add_query_arg( 'page', 'wfcm-settings', admin_url( 'admin.php' ) ) . '">' . __( 'Settings', 'website-file-changes-monitor' ) . '</a>';
		$new_links[] = '<a href="' . add_query_arg( 'page', 'wfcm-about', admin_url( 'admin.php' ) ) . '">' . __( 'Support', 'website-file-changes-monitor' ) . '</a>';
		return array_merge( $new_links, $old_links );
	}

	/**
	 * Ajax handler to dismiss instant scan modal.
	 */
	public function dismiss_instant_scan_modal() {
		check_admin_referer( 'wp_rest', 'security' );
		wfcm_save_setting( 'dismiss-instant-scan-modal', true );
		die();
	}

	/**
	 * Ajax handler to flush out old file lists so that new fingerprints can be
	 * generated on the next scan.
	 *
	 * @method sha256_upgrade_flush
	 * @since  1.5.0
	 */
	public function sha256_upgrade_flush() {
		check_admin_referer( 'wp_rest', 'security' );
		// only modify options if user has manage_options cap.
		if ( current_user_can( 'manage_options' ) ) {
			// loop through all 7 file list groups and delete them.
			for ( $x = 0; $x <= 6; $x++ ) {
				wfcm_delete_setting( 'is-initial-scan-' . $x );
				wfcm_delete_setting( 'local-files-' . $x );
			}
			wfcm_save_setting( 'sha256-hashing', true );
		}
		die();
	}

	/**
	 * Sends a test email to the configured account.
	 *
	 * @method dismiss_instant_scan_modal
	 * @since  1.5
	 */
	public function send_test_email() {
		check_admin_referer( 'wp_rest', 'security' );

		// get the settings.
		$email_notice_type = wfcm_get_setting( WFCM_Settings::NOTIFY_TYPE, 'admin' );
		$email_custom_list = wfcm_get_setting( WFCM_Settings::NOTIFY_ADDRESSES, array() );
		// convert TO an array from a string.
		$email_custom_list = ( ! is_array( $email_custom_list ) ) ? explode( ',', $email_custom_list ) : $email_custom_list;
		// Set up a subject and body and empty array for results.
		$email_subject = __( 'WFCM Test Mail', 'website-file-changes-monitor' );
		$email_body    = __( 'This is a test email from Website File Changes Monitor running on your site.', 'website-file-changes-monitor' );
		$sent_results  = array();

		/*
		 * Decide where to send email notifications. This uses a custom list of
		 * 1 or more addresses and falls back to admin address if a custom list
		 * is not used.
		 */
		if ( 'custom' === $email_notice_type && ! empty( $email_custom_list ) ) {
			// we have a custom list to use.
			foreach ( $email_custom_list as $email_address ) {
				if ( filter_var( $email_address, FILTER_VALIDATE_EMAIL ) ) {
					$result = WFCM_Email::send( $email_address, $email_subject, $email_body );
					if ( $result ) {
						$sent_results[] = $result;
					}
				}
			}
		} else {
			// sending to admin address.
			$result = WFCM_Email::send( get_bloginfo( 'admin_email' ), esc_html( $email_subject ), esc_html( $email_body ) );
			if ( $result ) {
				$sent_results[] = $result;
			}
		}

		if ( ! empty( $sent_results ) ) {
			wp_send_json_success( $sent_results );
		} else {
			wp_send_json_error(
				array(
					esc_html( 'Error sending test email', 'website-file-changes-monitor' ),
				)
			);
		}
		// should never reach this die.
		die();
	}

	public function exclude_file_from_notice() {
		check_ajax_referer( 'wfcm-exclude-file-nonce' );

		if ( isset( $_POST['file'] ) ) {
			$file = $_POST['file'];
		} else {
			exit;
		}

		$currently_excluded_files = wfcm_get_setting( 'scan-exclude-files' );
		$current_notices          = wfcm_get_setting( 'admin-notices' );
		$current_notices          = $current_notices['filesize-limit'];
		$new_files_to_exclude     = array( basename( $file ) );
		$file                     = str_replace('\\\\', '\\', $file );

		foreach ( $current_notices as $key => $file_path ) {
			if ( $file_path == $file ) {
				unset( $current_notices[$key] );
			}
		}

		$new_notice_files['filesize-limit'] = $current_notices;
		$excluded_files                     = array_unique( array_merge( $currently_excluded_files, $new_files_to_exclude ) );

		// Update settings.
		wfcm_save_setting( 'scan-exclude-files', $excluded_files );
		wfcm_save_setting( 'admin-notices', $new_notice_files );

		// Send response to AJAX call.
		wp_send_json_success(
			array(
				'message' => esc_html__( 'File excluded', 'website-file-changes-montitor' ),
			)
		);
	}

}

new WFCM_Admin_Menus();
