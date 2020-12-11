<?php
/**
 * WFCM REST API.
 *
 * @package wfcm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * WFCM REST API Class.
 *
 * This class registers and handles the REST API requests of the plugin.
 */
class WFCM_REST_API {

	/**
	 * Monitor events base.
	 *
	 * @var string
	 */
	public static $monitor_base = '/monitor';

	/**
	 * Events base.
	 *
	 * @var string
	 */
	public static $events_base = '/monitor-events';

	/**
	 * Base to use in REST requests for marking all as read.
	 *
	 * @var string
	 */
	public static $mark_all_read_base = '/mark-all-read';

	/**
	 * Admin notices base.
	 *
	 * @var string
	 */
	public static $admin_notices = '/admin-notices';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_monitor_rest_routes' ) );
		add_action( 'rest_api_init', array( $this, 'register_events_rest_routes' ) );
		add_action( 'rest_api_init', array( $this, 'register_admin_notices_rest_routes' ) );
	}

	/**
	 * Register Rest Route for Scanning.
	 */
	public function register_monitor_rest_routes() {
		// Start scan route.
		register_rest_route(
			WFCM_REST_NAMESPACE,
			self::$monitor_base . '/start',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'scan_start' ),
				'permission_callback' => function() {
					return current_user_can( 'manage_options' );
				},
			)
		);

		// Stop scan route.
		register_rest_route(
			WFCM_REST_NAMESPACE,
			self::$monitor_base . '/stop',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'scan_stop' ),
				'permission_callback' => function() {
					return current_user_can( 'manage_options' );
				},
			)
		);
	}

	/**
	 * Register Rest Route for Scanning.
	 */
	public function register_events_rest_routes() {
		// Register rest route for getting events.
		register_rest_route(
			WFCM_REST_NAMESPACE,
			self::$events_base . '/(?P<event_type>[\S]+)',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_events' ),
				'permission_callback' => function() {
					return current_user_can( 'manage_options' );
				},
				'args'                => [
					'event_type' => [
						'validate_callback' => function ( $param, $request, $key ) {
							return in_array( $param, WFCM_REST_API::get_event_types(), true );
						}
					]
				]
			)
		);

		// Register rest route for removing an event.
		register_rest_route(
			WFCM_REST_NAMESPACE,
			self::$events_base . '/(?P<event_id>[\d]+)',
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'delete_event' ),
				'permission_callback' => function() {
					return current_user_can( 'manage_options' );
				},
				'args'                => array(
					'event_id' => [
						'validate_callback'   => function ( $param, $request, $key ) {
							return is_numeric( $param );
						},
					],
					'exclude'     => array(
						'type'        => 'boolean',
						'default'     => false,
						'description' => __( 'Whether to exclude the content in future scans or not', 'website-file-changes-monitor' ),
					),
					'excludeType' => array(
						'type'        => 'string',
						'default'     => 'file',
						'description' => __( 'The type of exclusion, i.e., file or directory.', 'website-file-changes-monitor' ),
					),
				),
			)
		);

		// Register rest route for removing an event.
		register_rest_route(
			WFCM_REST_NAMESPACE,
			self::$mark_all_read_base . '/(?P<event_type>[\S]+)',
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'delete_events' ),
				'permission_callback' => function() {
					return current_user_can( 'manage_options' );
				},
				'args'                => [
					'event_type' => [
						'validate_callback' => function ( $param, $request, $key ) {
							return in_array( $param, array_merge( WFCM_REST_API::get_event_types(), array( 'all' ) ), true );
						}
					]
				]
			)
		);
	}

	/**
	 * Register rest route for admin notices.
	 */
	public function register_admin_notices_rest_routes() {
		// Register rest route dismissing admin notice.
		register_rest_route(
			WFCM_REST_NAMESPACE,
			self::$admin_notices . '/(?P<noticeKey>[\S]+)',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'dismiss_admin_notice' ),
				'permission_callback' => function() {
					return current_user_can( 'manage_options' );
				},
				'args' => [
					'noticeKey' => [
						'validate_callback'   => function ( $param ) {
							return filter_var( $param, FILTER_SANITIZE_STRING );
						}
					]
				]
			)
		);
	}

	/**
	 * REST API callback for start scan request.
	 *
	 * @return boolean
	 */
	public function scan_start() {

		// Run a manual scan of all directories.
		wfcm_get_monitor()->scan_file_changes();

		wfcm_delete_setting( 'scan-stop' );
		$datetime_format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
		$last_scan_time  = wfcm_get_setting( 'last-scan-timestamp', false );
		$last_scan_time  = $last_scan_time + ( get_option( 'gmt_offset' ) * 60 * 60 );
		$last_scan_time  = date( $datetime_format, $last_scan_time );
		return $last_scan_time;
	}

	/**
	 * REST API callback for stop scan request.
	 *
	 * @return boolean
	 */
	public function scan_stop() {
		wfcm_save_setting( 'scan-stop', true );

		return true;
	}

	/**
	 * Check if scan stop flag option is set.
	 *
	 * @return string|null
	 */
	private function check_scan_stop() {
		global $wpdb;
		$options_table = $wpdb->prefix . 'options';
		return $wpdb->get_var( "SELECT option_value FROM $options_table WHERE option_name = 'wfcm-scan-stop'" ); // phpcs:ignore
	}

	/**
	 * REST API callback for fetching created file events.
	 *
	 * @param WP_REST_Request $rest_request - REST request object.
	 * @return WP_Error|string - JSON string of events.
	 */
	public function get_events( $rest_request ) {
		// Get event params from request object.
		$event_type = $rest_request->get_param( 'event_type' );
		$paged      = $rest_request->get_param( 'paged' );
		$per_page   = $rest_request->get_param( 'per-page' );

		// Validate request variables.
		$paged    = is_int( $paged ) ? $paged : (int) $paged;
		$per_page = 'false' === $per_page ? false : (int) $per_page;

		if ( ! $event_type ) {
			return new WP_Error( 'wfcm_empty_event_type', __( 'No event type specified for the request.', 'website-file-changes-monitor' ), array( 'status' => 404 ) );
		}

		// Get event type stored per page option.
		$per_page_opt_name = $event_type . '-per-page';
		$stored_per_page   = wfcm_get_setting( $per_page_opt_name );

		if ( false === $per_page ) {
			if ( ! $stored_per_page ) {
				$per_page = 10;
			} else {
				$per_page = $stored_per_page;
			}
		} elseif ( $per_page !== $stored_per_page ) {
			wfcm_save_setting( $per_page_opt_name, $per_page );
		}

		// Set events query arguments.
		$event_args = array(
			'status'         => 'unread',
			'event_type'     => $event_type,
			'posts_per_page' => $per_page,
			'paginate'       => true,
			'paged'          => $paged,
		);

		// Query events.
		$events_query = wfcm_get_events( $event_args );

		// Convert events for JS response.
		$events_query->events = wfcm_get_events_for_js( $events_query->events );

		$response = new WP_REST_Response( $events_query );
		$response->set_status( 200 );

		return $response;
	}

	/**
	 * REST API callback for marking events as read.
	 *
	 * @param WP_REST_Request $rest_request - REST request object.
	 * @return WP_Error|WP_REST_Response
	 */
	public function delete_event( $rest_request ) {
		// Get event id from request.
		$event_id = $rest_request->get_param( 'event_id' );

		if ( ! $event_id ) {
			return new WP_Error( 'wfcm_empty_event_id', __( 'No event id specified for the request.', 'website-file-changes-monitor' ), array( 'status' => 404 ) );
		}

		// Get request body to check if event is excluded.
		$request_body  = $rest_request->get_body();
		$request_body  = json_decode( $request_body );
		$is_excluded   = isset( $request_body->exclude ) ? $request_body->exclude : false;
		$excluded_type = isset( $request_body->excludeType ) ? $request_body->excludeType : false;

		if ( $is_excluded ) {
			// Get event content type.
			$event        = wfcm_get_event( $event_id );
			$content_type = $event->get_content_type();

			if ( 'file' === $content_type ) {
				$excluded_setting = 'scan-exclude-files';

				if ( 'dir' === $excluded_type ) {
					$excluded_setting = 'scan-exclude-dirs';
				}

				$excluded_content = wfcm_get_setting( $excluded_setting, array() );

				if ( 'dir' === $excluded_type ) {
					$excluded_content[] = dirname( $event->get_event_title() );
				} else {
					$excluded_content[] = basename( $event->get_event_title() );
				}

				// Ensure no duplicated entries.
				$excluded_content = array_unique( $excluded_content );
				wfcm_save_setting( $excluded_setting, $excluded_content );
			} elseif ( 'directory' === $content_type ) {
				$excluded_content   = wfcm_get_setting( 'scan-exclude-dirs', array() );
				$excluded_content[] = $event->get_event_title();

				// Ensure no duplicated entries.
				$excluded_content = array_unique( $excluded_content );
				wfcm_save_setting( 'scan-exclude-dirs', $excluded_content );
			}
		}

		// Delete the event.
		$event_type = get_post_meta( $event_id, 'event_type', true );
		if ( wp_delete_post( $event_id, true ) ) {
			if ( $event_type ) {
				delete_transient( "wfcm_event_type_tabs_count_{$event_type}" );
			}
			$response = array( 'success' => true );
		} else {
			$response = array( 'success' => false );
		}

		$response = new WP_REST_Response( $response );
		$response->set_status( 200 );

		return $response;
	}

	/**
	 * Rest endpoint to delete all of a given type (or all types) of event.
	 *
	 * @method delete_events
	 * @since  1.5.0
	 * @param  WP_REST_Request $rest_request - REST request object.
	 * @return WP_Error|WP_REST_Response
	 */
	public function delete_events( $rest_request ) {
		// Get event id from request.
		$event_type = $rest_request->get_param( 'event_type' );
		$event_type = ( 'all' === $event_type ) ? 'all' : rtrim( $event_type, '-files' );

		if ( ! $event_type || ! in_array( $event_type, array_merge( $this->get_event_types(), array( 'all' ) ), true ) ) {
			return new WP_Error( 'wfcm_empty_event_type', __( 'No event type specified for the request.', 'website-file-changes-monitor' ), array( 'status' => 404 ) );
		}

		// Handle deleting the events of the passed type.
		$event_types = ( 'all' !== $event_type ) ? array( $event_type ) : $this->get_event_types();
		$events      = new WP_Query(
			array(
				'post_type'      => WFCM_Post_Types::EVENT_POST_TYPE_ID,
				'meta_key'       => 'event_type',
				'meta_value'     => $event_types,
				'meta_compare'   => 'IN',
				'posts_per_page' => -1,
				'paged'          => false,
				'fields'         => 'ids',
			)
		);

		$ids = implode( ', ', $events->posts );
		// Delete all of the events and assosiated metadata.
		if ( ! empty( $ids ) ) {
			global $wpdb;
			$deleted_items = $wpdb->query( "DELETE a,b,c,d FROM {$wpdb->prefix}posts a LEFT JOIN {$wpdb->prefix}term_relationships b ON ( a.ID = b.object_id ) LEFT JOIN {$wpdb->prefix}postmeta c ON ( a.ID = c.post_id ) LEFT JOIN {$wpdb->prefix}term_taxonomy d ON ( d.term_taxonomy_id = b.term_taxonomy_id ) WHERE a.ID IN ( {$ids} )" );
		}

		// if we have any deleted items then need to clear the transients.
		if ( isset( $deleted_items ) && ! empty( $deleted_items ) ) {
			foreach ( $event_types as $type ) {
				delete_transient( "wfcm_event_type_tabs_count_{$type}" );
			}
		}

		// return a successful response along with ids query was run with.
		$response = new WP_REST_Response(
			array(
				'success'        => true,
				'deleted_events' => ( ! empty( $events->posts ) ) ? $events->posts : array(),
			)
		);
		$response->set_status( 200 );
		return $response;
	}

	/**
	 * REST API callback for dismissing admin notice.
	 *
	 * @param WP_REST_Request $rest_request - REST request object.
	 * @return WP_Error|WP_REST_Response
	 */
	public function dismiss_admin_notice( $rest_request ) {
		// Get admin notice id.
		$notice_key = $rest_request->get_param( 'noticeKey' );

		if ( ! $notice_key ) {
			return new WP_Error( 'wfcm_empty_admin_notice_id', __( 'No admin notice key specified for the request.', 'website-file-changes-monitor' ), array( 'status' => 404 ) );
		}

		$admin_notices = wfcm_get_setting( 'admin-notices', array() );

		if ( isset( $admin_notices[ $notice_key ] ) ) {
			// Unset the notice.
			unset( $admin_notices[ $notice_key ] );

			// Save notice option.
			wfcm_save_setting( 'admin-notices', $admin_notices );

			// Prepare response.
			$response = array( 'success' => true );
		} else {
			$response = array( 'success' => false );
		}

		return new WP_REST_Response( $response, 200 );
	}

	/**
	 * Get a list of supported event types.
	 *
	 * @method get_event_types
	 * @since  1.5.0
	 * @return array
	 */
	private function get_event_types() {
		return array(
			'added',
			'modified',
			'deleted',
		);
	}
}

new WFCM_REST_API();
