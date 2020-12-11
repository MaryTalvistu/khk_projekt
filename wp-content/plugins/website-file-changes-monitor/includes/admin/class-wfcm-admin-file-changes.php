<?php
/**
 * Admin File Changes View.
 *
 * @package wfcm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Admin file changes view class.
 */
class WFCM_Admin_File_Changes {

	/**
	 * Admin messages.
	 *
	 * @var array
	 */
	private static $messages = array();

	/**
	 * Allowed HTML.
	 *
	 * @var array
	 */
	private static $allowed_html = array(
		'a'      => array(
			'href'       => array(),
			'target'     => array(),
			'data-nonce' => array(),
		),
		'strong' => array(),
		'ul'     => array(),
		'li'     => array(),
		'p'      => array(),
	);

	/**
	 * Page tabs.
	 *
	 * @var array
	 */
	private static $tabs = array();

	/**
	 * Add admin message.
	 *
	 * @param string $key         - Message key.
	 * @param string $type        - Type of message.
	 * @param string $message     - Admin message.
	 * @param bool   $dismissible - Notice is dismissible or not.
	 */
	public static function add_message( $key, $type, $message, $dismissible = true ) {
		self::$messages[ $key ] = array(
			'type'        => $type,
			'message'     => $message,
			'dismissible' => $dismissible,
		);
	}

	/**
	 * Add specific page messages.
	 */
	public static function add_messages() {
		// Get file limits message setting.
		$admin_notices = wfcm_get_setting( 'admin-notices', array() );

		if ( ! empty( $admin_notices ) ) {

			// clear scan in progress so user can re-scan.
			wfcm_save_setting( 'scan-in-progress', false );

			if ( ( isset( $admin_notices['previous-scan-fail-generic'] ) && ! empty( $admin_notices['previous-scan-fail-generic'] ) ) ) {
				$msg = '<p>' . sprintf(
					/* Translators: 1 - WP White Security support hyperlink. 2 - support link closer */
					__( 'We detected that a previous file integrity scan failed due to an unknown reason. Contact us at %1$ssupport@wpwhitesecurity.com%2$s to help you with this issue.', 'website-file-changes-monitor' ),
					'<a href="mailto:support@wpwhitesecurity.com" target="_blank">',
					'</a>'
				) . '</p>';
				self::add_message( 'previous-scan-fail-generic', 'error', $msg );
			}
			if ( isset( $admin_notices['previous-scan-fail-timeout'] ) && ! empty( $admin_notices['previous-scan-fail-timeout'] ) ) {
				if ( isset( $admin_notices['previous-scan-fail-timeout']['time'] ) ) {
					$datetime_format          = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
					$timestamp                = $admin_notices['previous-scan-fail-timeout']['time'] + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS );
					$date_and_time_of_failure = date( $datetime_format, $timestamp ) . ' ';
				} else {
					$date_and_time_of_failure = '';
				}
				$msg = '<p>' . sprintf(
					/* Translators: 1 - WP White Security support hyperlink. 2 - support link closer */
					__( 'The last file integrity scan was halted %3$sbecause it took more than maximum scan time (3 minutes) to complete. Contact us at %1$ssupport@wpwhitesecurity.com%2$s to help you with this issue.', 'website-file-changes-monitor' ),
					'<a href="mailto:support@wpwhitesecurity.com" target="_blank">',
					'</a>',
					$date_and_time_of_failure
				) . '</p>';
				self::add_message( 'previous-scan-fail-timeout', 'error', $msg );

			}

			if ( isset( $admin_notices['hashing-upgrade']['upgrade-needed'] ) && $admin_notices['hashing-upgrade']['upgrade-needed'] ) {
				if ( wfcm_get_setting( 'sha256-hashing', false ) ) {
					unset( $admin_notices['hashing-upgrade'] );
					wfcm_save_setting( 'admin-notices', $admin_notices );
				} else {
					$msg = '<p>' . sprintf(
						/* Translators: link fragments. */
						__( 'The plugin changed to use SHA-256 for hashing. You need to delete the old file fingerprints and run a scan to generate new ones with the new hashing algorithm. You can %1$scontact us%2$s for assistance.', 'website-file-changes-monitor' ),
						'<a href="https://www.wpwhitesecurity.com/support/submit-ticket/" target="_blank">',
						'</a>'
					) . '</p>';
					self::add_message( 'hashing-upgrade', 'error', $msg );
				}
			}

			// display the hashing algo missing notice if it's there and still
			// not finding the hash match in available list.
			if ( isset( $admin_notices['hashing-algorith']['sha256-unavailable'] ) && $admin_notices['hashing-algorith']['sha256-unavailable'] ) {
				if ( ! in_array( WFCM_Monitor::HASHING_ALGORITHM, hash_algos(), true ) ) {
					$msg = '<p>' . sprintf(
						/* Translators: link fragments. */
						__( 'The plugin uses SHA-256 for hashing. It seems that this hashing method is not enabled on your website. Please %1$scontact us%2$s for assistance.', 'website-file-changes-monitor' ),
						'<a href="https://www.wpwhitesecurity.com/support/submit-ticket/" target="_blank">',
						'</a>'
					) . '</p>';
					self::add_message( 'hashing-algorith', 'error', $msg );
				}
			}

			if ( isset( $admin_notices['files-limit'] ) && ! empty( $admin_notices['files-limit'] ) ) {
				// Append strong tag to each directory name.
				$dirs = array_reduce(
					$admin_notices['files-limit'],
					function( $dirs, $dir ) {
						array_push( $dirs, "<li><strong>$dir</strong></li>" );
						return $dirs;
					},
					array()
				);

				$msg = '<p>' . sprintf(
					/* Translators: %s: WP White Security support hyperlink. */
					__( 'The plugin stopped scanning the below directories because they have more than 200,000 files. Please contact %s for assistance.', 'website-file-changes-monitor' ),
					'<a href="mailto:support@wpwhitesecurity.com" target="_blank">' . __( 'our support', 'website-file-changes-monitor' ) . '</a>'
				) . '</p>';
				$msg .= '<ul>' . implode( '', $dirs ) . '</ul>';

				self::add_message( 'files-limit', 'warning', $msg );
			}

			if ( isset( $admin_notices['filesize-limit'] ) && ! empty( $admin_notices['filesize-limit'] ) ) {
				// Append strong tag to each directory name.
				$files = array_reduce(
					$admin_notices['filesize-limit'],
					function( $files, $file ) {
						// Create nonce for excluding the file.
						$exclude_nonce = wp_create_nonce( 'wfcm-exclude-file-nonce' );
						array_push( $files, "<li><strong>$file</strong> <a href='#wfcm_exclude_large_file' data-nonce='". esc_attr( $exclude_nonce ) ."'>". __( 'Exclude file', 'website-file-changes-monitor' ) ."</a></li>" );
						return $files;
					},
					array()
				);

				$max_file_size = (int) wfcm_get_setting( 'scan-file-size' );

				$msg = '<p>' . sprintf(
					/* Translators: %s: Plugin settings hyperlink. */
					__( 'These files are bigger than %sMB and have not been scanned. To scan them increase the file size scan limit from the %s.', 'website-file-changes-monitor' ),
					$max_file_size,
					'<a href="' . add_query_arg( 'page', 'wfcm-settings', admin_url( 'admin.php' ) ) . '">' . __( 'plugin settings', 'website-file-changes-monitor' ) . '</a>'
				) . '</p>';

				$msg .= '<ul>' . implode( '', $files ) . '</ul>';

				self::add_message( 'filesize-limit', 'warning', $msg );
			}

			if ( isset( $admin_notices['empty-scan'] ) && $admin_notices['empty-scan'] ) {
				// Get last scan timestamp.
				$last_scan = wfcm_get_setting( 'last-scan-timestamp', false );

				if ( $last_scan ) {
					$datetime_format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
					$last_scan       = $last_scan + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS );
					$last_scan       = date( $datetime_format, $last_scan );

					/* Translators: Date and time */
					$msg = '<p>' . sprintf( __( 'There were no file changes detected during the last file scan, which ran on %s.', 'website-file-changes-monitor' ), $last_scan ) . '</p>';
					self::add_message( 'empty-scan', 'info', $msg );
				}
			}
		}

		// Add permalink structure notice.
		$permalink_structure = get_option( 'permalink_structure', false );

		if ( ! $permalink_structure ) {
			$msg = '<p>' . sprintf(
				/* Translators: %s: Website permalink settings hyperlink. */
				__( 'It seems that your permalinks are not configured. Please %s for the plugin to display the file changes.', 'website-file-changes-monitor' ),
				'<a href="' . admin_url( 'options-permalink.php' ) . '">' . __( 'configure them', 'website-file-changes-monitor' ) . '</a>'
			) . '</p>';
			self::add_message( 'permalink-notice', 'error', $msg, false );
		}
	}

	/**
	 * Show admin message.
	 */
	public static function show_messages() {
		if ( ! empty( self::$messages ) ) {
			$messages = apply_filters( 'wfcm_admin_file_changes_messages', self::$messages );

			foreach ( $messages as $key => $notice ) :
				$classes  = 'notice notice-' . $notice['type'] . ' wfcm-admin-notice';
				$classes .= $notice['dismissible'] ? ' is-dismissible' : '';
				?>
				<div id="wfcm-admin-notice-<?php echo esc_attr( $key ); ?>" class="<?php echo esc_attr( $classes ); ?>">
					<?php echo wp_kses( $notice['message'], self::$allowed_html ); ?>
				</div>
				<?php
			endforeach;
		}
	}

	/**
	 * Set tabs of the page.
	 */
	private static function set_tabs() {
		self::$tabs = apply_filters(
			'wfcm_admin_file_changes_page_tabs',
			array(
				'added-files'    => array(
					'title' => __( 'Added Files', 'website-file-changes-monitor' ),
					'link'  => self::get_page_url(),
				),
				'modified-files' => array(
					'title' => __( 'Modified Files', 'website-file-changes-monitor' ),
					'link'  => add_query_arg( 'tab', 'modified-files', self::get_page_url() ),
				),
				'deleted-files'  => array(
					'title' => __( 'Deleted Files', 'website-file-changes-monitor' ),
					'link'  => add_query_arg( 'tab', 'deleted-files', self::get_page_url() ),
				),
			)
		);
	}

	/**
	 * Get active tab.
	 *
	 * @return string
	 */
	private static function get_active_tab() {
		return isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'added-files'; // phpcs:ignore
	}

	/**
	 * Return page url.
	 *
	 * @return string
	 */
	public static function get_page_url() {
		$admin_url = is_multisite() ? network_admin_url( 'admin.php' ) : admin_url( 'admin.php' );
		return add_query_arg( 'page', 'wfcm-file-changes', $admin_url );
	}

	/**
	 * Page View.
	 */
	public static function output() {
		self::add_messages(); // Add notifications to the view.
		// setup a filter to add counts to the nav tabs at generation time.
		add_filter( 'wfcm_admin_file_changes_page_tabs', 'WFCM_Admin_File_Changes::append_count_for_tabs' );
		self::set_tabs();

		$wp_version        = get_bloginfo( 'version' );
		$suffix            = ( defined( 'WP_DEBUG' ) && true === WP_DEBUG ) ? '' : '.min'; // Check for debug mode.
		$wfcm_dependencies = array();
		$datetime_format   = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
		$last_scan_time    = wfcm_get_setting( 'last-scan-timestamp', false );
		$last_scan_time    = $last_scan_time + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS );
		$last_scan_time    = date( $datetime_format, $last_scan_time );

		wp_enqueue_style(
			'wfcm-file-changes-styles',
			WFCM_BASE_URL . 'assets/css/dist/build.file-changes' . $suffix . '.css',
			array(),
			( defined( 'WP_DEBUG' ) && true === WP_DEBUG ) ? filemtime( WFCM_BASE_DIR . 'assets/css/dist/build.file-changes.css' ) : WFCM_VERSION
		);

		// For WordPress versions earlier than 5.0, enqueue react and react-dom from the vendors directory.
		if ( version_compare( $wp_version, '5.0', '<' ) ) {
			wp_enqueue_script(
				'wfcm-react',
				WFCM_BASE_URL . 'assets/js/dist/vendors/react.min.js',
				array(),
				'16.6.3',
				true
			);

			wp_enqueue_script(
				'wfcm-react-dom',
				WFCM_BASE_URL . 'assets/js/dist/vendors/react-dom.min.js',
				array(),
				'16.6.3',
				true
			);

			$wfcm_dependencies = array( 'wfcm-react', 'wfcm-react-dom' );
		} else {
			// Otherwise enqueue WordPress' react library.
			$wfcm_dependencies = array( 'wp-element' );
		}

		wp_register_script(
			'wfcm-file-changes',
			WFCM_BASE_URL . 'assets/js/dist/file-changes' . $suffix . '.js',
			$wfcm_dependencies,
			( defined( 'WP_DEBUG' ) && true === WP_DEBUG ) ? filemtime( WFCM_BASE_DIR . 'assets/js/dist/file-changes.js' ) : WFCM_VERSION,
			true
		);
		$migrated = wfcm_get_setting( 'sha256-hashing', false );
		if ( ! $migrated ) {
			if ( 'no' === wfcm_get_setting( 'is-initial-scan-0', 'yes' ) ) {
				$sha256_migrated = false;
			} else {
				$sha256_migrated = true;
			}
		} else {
			$sha256_migrated = true;
		}

		wp_localize_script(
			'wfcm-file-changes',
			'wfcmFileChanges',
			array(
				'security'       => wp_create_nonce( 'wp_rest' ),
				'fileEvents'     => array(
					'get'           => esc_url_raw( rest_url( WFCM_REST_NAMESPACE . WFCM_REST_API::$events_base ) ),
					'delete'        => esc_url_raw( rest_url( WFCM_REST_NAMESPACE . WFCM_REST_API::$events_base ) ),
					'mark_all_read' => esc_url_raw( rest_url( WFCM_REST_NAMESPACE . WFCM_REST_API::$mark_all_read_base ) ),
				),
				'pageHead'       => __( 'Website File Changes Monitor', 'website-file-changes-monitor' ),
				'pagination'     => array(
					'fileChanges'  => __( 'file changes', 'website-file-changes-monitor' ),
					'firstPage'    => __( 'First page', 'website-file-changes-monitor' ),
					'previousPage' => __( 'Previous page', 'website-file-changes-monitor' ),
					'currentPage'  => __( 'Current page', 'website-file-changes-monitor' ),
					'nextPage'     => __( 'Next page', 'website-file-changes-monitor' ),
					'lastPage'     => __( 'Last page', 'website-file-changes-monitor' ),
				),
				'labels'         => array(
					'addedFiles'    => __( 'Added files', 'website-file-changes-monitor' ),
					'deletedFiles'  => __( 'Deleted files', 'website-file-changes-monitor' ),
					'modifiedFiles' => __( 'Modified files', 'website-file-changes-monitor' ),
				),
				'bulkActions'    => array(
					'screenReader' => __( 'Select bulk action', 'website-file-changes-monitor' ),
					'bulkActions'  => __( 'Bulk Actions', 'website-file-changes-monitor' ),
					'markAsRead'   => __( 'Mark as read', 'website-file-changes-monitor' ),
					'exclude'      => __( 'Exclude', 'website-file-changes-monitor' ),
					'apply'        => __( 'Apply', 'website-file-changes-monitor' ),
				),
				'showItems'      => array(
					'added'    => (int) wfcm_get_setting( 'added-per-page', false ),
					'modified' => (int) wfcm_get_setting( 'modified-per-page', false ),
					'deleted'  => (int) wfcm_get_setting( 'deleted-per-page', false ),
				),
				'table'          => array(
					'path'        => __( 'Path', 'website-file-changes-monitor' ),
					'name'        => __( 'Name', 'website-file-changes-monitor' ),
					'type'        => __( 'Type', 'website-file-changes-monitor' ),
					'markAsRead'  => __( 'Mark as read', 'website-file-changes-monitor' ),
					'exclude'     => __( 'Exclude from scans', 'website-file-changes-monitor' ),
					'dateTime'    => __( 'Date', 'website-file-changes-monitor' ),
					'noEvents'    => __( 'No file changes detected!', 'website-file-changes-monitor' ),
					'excludeFile' => __( 'Exclude file', 'website-file-changes-monitor' ),
					'excludeDir'  => __( 'Exclude directory', 'website-file-changes-monitor' ),
				),
				'monitor'        => array(
					'start' => esc_url_raw( rest_url( WFCM_REST_NAMESPACE . WFCM_REST_API::$monitor_base . '/start' ) ),
					'stop'  => esc_url_raw( rest_url( WFCM_REST_NAMESPACE . WFCM_REST_API::$monitor_base . '/stop' ) ),
				),
				'scanModal'      => array(
					'logoSrc'         => WFCM_BASE_URL . 'assets/img/wfcm-logo.svg',
					'dismiss'         => wfcm_get_setting( 'dismiss-instant-scan-modal', false ),
					'adminAjax'       => admin_url( 'admin-ajax.php' ),
					'headingComplete' => __( 'Instant file scan complete!', 'website-file-changes-monitor' ),
					'scanNow'         => __( 'Launch instant file scan', 'website-file-changes-monitor' ),
					'scanDismiss'     => __( 'Wait for scheduled scan', 'website-file-changes-monitor' ),
					'scanning'        => __( 'Scanning...', 'website-file-changes-monitor' ),
					'scanComplete'    => __( 'Scan complete!', 'website-file-changes-monitor' ),
					'scanFailed'      => __( 'Scan failed!', 'website-file-changes-monitor' ),
					'ok'              => __( 'OK', 'website-file-changes-monitor' ),
					'initialMsg'      => __( 'The plugin will scan for file changes at 2:00AM every day. You can either wait for the first scan or launch an instant scan.', 'website-file-changes-monitor' ),
					'scheduleHelpTxt' => sprintf(
						/* Translators: 1 - <strong> tag, 2 - a closing </strong> tag. */
						__( '%1$sTip:%2$s You can change the scan schedule and frequency from the plugin settings.', 'website-file-changes-monitor' ),
						'<strong>',
						'</strong>'
					),
					'afterScanMsg'    => __( 'The first file scan is complete. Now the plugin has the file fingerprints and it will alert you via email when it detect changes.', 'website-file-changes-monitor' ),
					'bgScanMsg'       => __( 'The first file scan will run now in the background. Once the initial scan is completed it will alert you via email when it detect changes. <br><br>You can continue with the setup as it runs.', 'website-file-changes-monitor' ),
					'sendTestMail'    => __( 'Send a test email', 'website-file-changes-monitor' ),
					'emailSending'    => __( 'Sending...', 'website-file-changes-monitor ' ),
					'sendingFailed'   => __( 'Failed to send', 'website-file-changes-monitor ' ),
					'emailSent'       => __( 'Email sent', 'website-file-changes-monitor ' ),
					'emailMsg'        => __( 'The plugin sends an email when it identifies file changes during a scan. Use the <i>Send test email</i> button below to test and confirm the plugin can send emails.', 'website-file-changes-monitor ' ),
					'emailSuccessMsg' => __( 'Success', 'website-file-changes-monitor' ),
					'exitButton'      => __( 'Exit', 'website-file-changes-monitor' ),
					'emailSentLine1'  => __( 'If you received the test email everything is setup correctly. You will be notified when the plugin detects file changes.', 'website-file-changes-monitor' ),
					'emailSentLine2'  => __( 'If you have not received an email, please <a href="https://www.wpwhitesecurity.com/support/submit-ticket/" target="_blank">contact us</a> so we can help you troubleshoot the issue.', 'website-file-changes-monitor' ),
				),
				'migrationModal' => array(
					'migrated'        => $sha256_migrated,
					'migrating'       => __( 'Migrating...', 'website-file-changes-monitor' ),
					'modalLine1'      => __( 'In this update we have changed the hashing algorithm from MD5 to sha256. We have changed it because SHA256 is not prone to hash collisions, making it more secure.', 'website-file-changes-monitor' ),
					'modalLine2'      => __( 'Because of this change the plugin needs to rebuild the files signatures. Click Launch Scan to rebuild the file signatures now.', 'webiste-file-changes-monitor' ),
					'oldClearedLine1' => __( 'The upgrade of file signatures was successful.', 'website-file-changes-monitor' ),
					'upgradeButton'   => __( 'Launch Now', 'website-file-changes-monitor' ),
				),
				'instantScan'    => array(
					'scanNow'      => __( 'Scan now', 'website-file-changes-monitor' ),
					'scanning'     => __( 'Scanning...', 'website-file-changes-monitor' ),
					'scanFailed'   => __( 'Scan failed', 'website-file-changes-monitor' ),
					'lastScan'     => __( 'Last scan', 'website-file-changes-monitor' ),
					'lastScanTime' => $last_scan_time,
				),
				'markAllRead'    => array(
					'markNow'               => __( 'Mark all as read', 'website-file-changes-monitor' ),
					'running'               => __( 'Marking as read...', 'website-file-changes-monitor' ),
					'markingAllReadFailed'  => __( 'Marking failed', 'website-file-changes-monitor' ),
					'markingAllReadSuccess' => __( 'Marking complete', 'website-file-changes-monitor' ),
					'markReadButtonMain'    => __( 'Only the {{$type}} file changes notifications', 'website-file-changes-monitor' ),
					'markReadButtonAll'     => __( 'All file changes', 'website-file-changes-monitor' ),
					'markReadModalTitle'    => __( 'Mark all {{$type}} file changes notifications as read', 'website-file-changes-monitor' ),
					'markReadModalMsg'      => __( 'Do you want to mark the {{$type}} file changes notifications as read, or all of the file changes notifications reported at this moment?', 'website-file-changes-monitor' ),
				),
				'monitor'        => array(
					'start' => esc_url_raw( rest_url( WFCM_REST_NAMESPACE . WFCM_REST_API::$monitor_base . '/start' ) ),
					'stop'  => esc_url_raw( rest_url( WFCM_REST_NAMESPACE . WFCM_REST_API::$monitor_base . '/stop' ) ),
				),
				'dateTimeFormat' => $datetime_format,
				'scanErrorModal' => array(
					'heading' => __( 'Instant scan failed', 'website-file-changes-monitor' ),
					/* Translators: Contact us hyperlink */
					'body'    => sprintf( __( 'Oops! Something went wrong with the scan. Please %s for assistance.', 'website-file-changes-monitor' ), '<a href="https://www.wpwhitesecurity.com/support/?utm_source=plugin&utm_medium=referral&utm_campaign=WFCM&utm_content=help+page" target="_blank">' . __( 'contact us', 'website-file-changes-monitor' ) . '</a>' ),
					'dismiss' => __( 'Ok', 'website-file-changes-monitor' ),
				),
			)
		);

		wp_enqueue_script( 'wfcm-file-changes' );

		// Display notifications of the view.
		self::show_messages();

		require_once trailingslashit( dirname( __FILE__ ) ) . 'views/html-admin-file-changes.php';
	}

	/**
	 * Run on a filter to gets counts for the event types of each tab item.
	 *
	 * This runs through a transient cache because the query might be expansive.
	 * We are counting all rows...
	 *
	 * @method append_count_for_tabs
	 * @since  1.4.0
	 * @param  array $tabs An array of tab links for the nav items.
	 * @return array
	 */
	public static function append_count_for_tabs( $tabs ) {
		foreach ( $tabs as $key => $tab ) {
			$count = get_transient( 'wfcm_event_type_tabs_count_' . rtrim( $key, '-files' ) );
			if ( false !== $count ) {
				$tabs[ $key ]['unread_count'] = $count;
			} else {
				/**
				 * This is a meta query so we shuold not use -1 to get all the
				 * posts since query will already be expansive. Ask for just 1
				 * and allow it to get the found rows counting all posts.
				 */
				$args = array(
					'post_type'     => 'wfcm_file_event',
					'post_per_page' => 1, // we only want 1 post, really we do this to count the found rows.
					'meta_query'    => array(
						'relation' => 'AND',
						// get unread items.
						array(
							'key'   => 'status',
							'value' => 'unread',
						),
						// for only the current tab item.
						array(
							'key'   => 'event_type',
							'value' => rtrim( $key, '-files' ),
						),
					),
				);
				$query = new \WP_Query( $args );
				$count = $query->found_posts;
				// allow zero values, sometimes all items are read.
				if ( $count || 0 === $count ) {
					$tabs[ $key ]['unread_count'] = $count;
					// cache this value so we don't need to count this every refresh.
					set_transient( 'wfcm_event_type_tabs_count_' . rtrim( $key, '-files' ), $count, DAY_IN_SECONDS );
				}
			}
		}
		return $tabs;
	}
}
