<?php
/**
 * File Changes Monitor.
 *
 * @package wfcm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * File Changes Monitor Class.
 *
 * This class is responsible for monitoring
 * the file changes on the server.
 */
class WFCM_Monitor {

	/**
	 * Sensor Instance.
	 *
	 * @var WFCM_Monitor
	 */
	protected static $instance = null;

	/**
	 * WP Root Path.
	 *
	 * @var string
	 */
	private $root_path = '';

	/**
	 * Paths to exclude during scan.
	 *
	 * @var array
	 */
	private $excludes = array();

	/**
	 * View settings.
	 *
	 * @var array
	 */
	public $scan_settings = array();

	/**
	 * Frequency daily hour.
	 *
	 * For testing change hour here [01 to 23]
	 *
	 * @var array
	 */
	private static $daily_hour = array( '04' );

	/**
	 * Frequency weekly date.
	 *
	 * For testing change date here [1 (for Monday) through 7 (for Sunday)]
	 *
	 * @var string
	 */
	private static $weekly_day = '1';

	/**
	 * Frequency montly date.
	 *
	 * For testing change date here [01 to 31]
	 *
	 * @var string
	 */
	private static $monthly_day = '01';

	/**
	 * Schedule hook name.
	 *
	 * @var string
	 */
	public static $schedule_hook = 'wfcm_monitor_file_changes';

	/**
	 * Scan files counter during a scan.
	 *
	 * @var int
	 */
	private $scan_file_count = 0;

	/**
	 * Scan files limit reached.
	 *
	 * @var bool
	 */
	private $scan_limit_file = false;

	/**
	 * Stored files to exclude.
	 *
	 * @var array
	 */
	private $files_to_exclude = array();

	/**
	 * WP uploads directory.
	 *
	 * @var array
	 */
	private $uploads_dir = array();

	/**
	 * Scan changes count.
	 *
	 * @var array
	 */
	private $scan_changes_count = array();

	/**
	 * Flag to track if scan has completed incase we need to run recursively.
	 *
	 * @var bool
	 */
	private $scan_completed = false;

	/**
	 * Keep track of this scan run time so we can break early before a timeout.
	 *
	 * @var int
	 */
	private $scan_start_time = 0;

	/**
	 * Used to hold the max length we are willing to run a scan part for in
	 * seconds.
	 *
	 * This will be set to 4 minutes is there is no time saved in database.
	 *
	 * @var int
	 */
	private $scan_max_execution_time;

	/**
	 * Class constants.
	 */
	const SCAN_HOURLY       = 'hourly';
	const SCAN_DAILY        = 'daily';
	const SCAN_WEEKLY       = 'weekly';
	const SCAN_MONTHLY      = 'monthly';
	const SCAN_FILE_LIMIT   = 200000;
	const HASHING_ALGORITHM = 'sha256';

	/**
	 * Return WFCM_Monitor Instance.
	 *
	 * Ensures only one instance of monitor is loaded or can be loaded.
	 *
	 * @return WFCM_Monitor
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->root_path = trailingslashit( ABSPATH );
		$this->register_hooks();
		$this->load_settings();
		$this->schedule_file_changes_monitor();

		// try get a max scan length from database otherwise default to 4 mins.
		// NOTE: this code could be adjusted to allow user configuration.
		$this->scan_max_execution_time = (int) get_option( 'wfcm_max_scan_time', 4 * MINUTE_IN_SECONDS );
	}

	/**
	 * Register Hooks.
	 */
	public function register_hooks() {
		add_filter( 'cron_schedules', array( $this, 'add_recurring_schedules' ) ); // phpcs:ignore
		add_filter( 'wfcm_file_scan_stored_files', array( $this, 'filter_scan_files' ), 10, 2 );
		add_filter( 'wfcm_file_scan_scanned_files', array( $this, 'filter_scan_files' ), 10, 2 );
		add_action( 'wfcm_after_file_scan', array( $this, 'empty_skip_file_alerts' ), 10, 1 );
		add_action( 'wfcm_last_scanned_directory', array( $this, 'reset_core_updates_flag' ), 10, 1 );
	}

	/**
	 * Load File Change Monitor Settings.
	 */
	public function load_settings() {
		$this->scan_settings = wfcm_get_monitor_settings();

		// Set the scan hours.
		if ( ! empty( $this->scan_settings['hour'] ) ) {
			$saved_hour = (int) $this->scan_settings['hour'];
			$next_hour  = $saved_hour + 1;
			$hours      = array( $saved_hour, $next_hour );
			foreach ( $hours as $hour ) {
				$daily_hour[] = str_pad( $hour, 2, '0', STR_PAD_LEFT );
			}
			self::$daily_hour = $daily_hour;
		}

		// Set weekly day.
		if ( ! empty( $this->scan_settings['day'] ) ) {
			self::$weekly_day = $this->scan_settings['day'];
		}

		// Set monthly date.
		if ( ! empty( $this->scan_settings['date'] ) ) {
			self::$monthly_day = $this->scan_settings['date'];
		}
	}

	/**
	 * Schedule file changes monitor cron.
	 */
	public function schedule_file_changes_monitor() {
		// Schedule file changes if the feature is enabled.
		if ( is_multisite() && ! is_main_site() ) {
			// Clear the scheduled hook if feature is disabled.
			wp_clear_scheduled_hook( self::$schedule_hook );
		} elseif ( 'yes' === $this->scan_settings['enabled'] ) {
			// Hook scheduled method.
			add_action( self::$schedule_hook, array( $this, 'scan_file_changes' ) );
			// Schedule event if there isn't any already.
			if ( ! wp_next_scheduled( self::$schedule_hook ) ) {
				$frequency_option = wfcm_get_setting( 'scan-frequency', 'daily' );
				// figure out the NEXT schedule time to recur from.
				$time = $this->get_next_cron_schedule_time( $frequency_option );
				wp_schedule_event(
					$time,               // Timestamp.
					$frequency_option,   // Frequency.
					self::$schedule_hook // Scheduled event.
				);
			}
		} else {
			// Clear the scheduled hook if feature is disabled.
			wp_clear_scheduled_hook( self::$schedule_hook );
		}
	}

	/**
	 * Given a frequency formulates the next time that occurs and returns a
	 * timestamp for that time to use when scheduling initial crons.
	 *
	 * @method get_next_cron_schedule_time
	 * @since  1.5.0
	 * @param  string $frequency_option an option of hourly/daily/weekly/monthly.
	 * @return int
	 */
	private function get_next_cron_schedule_time( $frequency_option ) {
		$time = current_time( 'timestamp' );

		// Allow for local timezones.
		if ( ! empty( get_option( 'timezone_string' ) ) ) {
			$local_timezone = get_option( 'timezone_string' );
		} else {
			$local_timezone = get_option( 'gmt_offset' );

			// Turn 0 into something strtotime can work with.
			if ( '0' === $local_timezone ) {
				$local_timezone = '+0';
			}
		}

		switch ( $frequency_option ) {
			case self::SCAN_HOURLY:
				// hourly scans start at the beginning of the next hour.
				$date = new DateTime();

				// Adjust for timezone.
				if ( ! empty( get_option( 'timezone_string' ) ) ) {
					$timezone = new DateTimeZone( get_option( 'timezone_string' ) );
					$date->setTimezone( $timezone );
				} elseif ( ! empty( get_option( 'timezone_string' ) ) ) {
					$timezone = new DateTimeZone( get_option( 'gmt_offset' ) );
					$date->setTimezone( $timezone );
				}

				$minutes = $date->format( 'i' );

				$date->modify( '+1 hour' );
				// if we had any minutes then remove them.
				if ( $minutes > 0 ) {
					$date->modify( '-' . $minutes . ' minutes' );
				}

				$time = $date->getTimestamp();
				break;
			case self::SCAN_DAILY:
				// daily starts on a given hour of the first day it occurs.
				$hour      = (int) wfcm_get_setting( 'scan-hour' );
				$next_time = strtotime( 'today ' . $hour . ':00 ' . $local_timezone );

				// if already passed today then add 1 day to timestamp.
				if ( $next_time < $time ) {
					$next_time = strtotime( '+1 day', $next_time );
				}

				$time = $next_time;
				break;
			case self::SCAN_WEEKLY:
				// weekly runs on a given day each week at a given hour.
				$hour      = (int) wfcm_get_setting( 'scan-hour' );
				$day_num   = (int) wfcm_get_setting( 'scan-day' );
				$day       = $this->convert_to_day_string( $day_num );

				$next_time = strtotime( $day . ' ' . $hour . ':00 ' . ' ' . $local_timezone );
				// if that day has passed this week already then add 1 week.
				if ( $next_time < $time ) {
					$next_time = strtotime( '+1 week', $next_time );
				}

				$time = $next_time;
				break;
			case self::SCAN_MONTHLY:
				// monthly starts on a given hour of a given day and then it
				// uses a recurrence schedule of every 30 days.
				$hour      = (int) wfcm_get_setting( 'scan-hour' );
				$date      = (int) wfcm_get_setting( 'scan-date' );
				$month     = date( 'F' ); // Month as a string.
				$next_time = strtotime( $hour . ':00 ' . $date . ' ' . $month . ' ' . $local_timezone );
				// if that date has passed this month add 1 month to timestamp.
				if ( $next_time < $time ) {
					$next_time = strtotime( '+1 month', $next_time );
				}

				$time = $next_time;
				break;
		}
		return ( false === $time ) ? time() : $time;
	}

	/**
	 * Converts a number reporesenting a day of the week into a string for it.
	 *
	 * NOTE: 1 = Monday, 7 = Sunday but is zero corrected by subtracting 1.
	 *
	 * @method convert_to_day_string
	 * @since  1.5.0
	 * @param  int $day_num a day number.
	 * @return string
	 */
	private function convert_to_day_string( $day_num ) {
		// Scan days option.
		$day_key   = (int) $day_num - 1;
		$scan_days = array(
			'Monday',
			'Tuesday',
			'Wednesday',
			'Thursday',
			'Friday',
			'Saturday',
			'Sunday',
		);
		// Return a day string - uses day 1 = Monday by default.
		return ( isset( $scan_days[ $day_key ] ) ) ? $scan_days[ $day_key ] : $scan_days[1];
	}

	/**
	 * Add time intervals for scheduling.
	 *
	 * @param  array $schedules - Array of schedules.
	 * @return array
	 */
	public function add_recurring_schedules( $schedules ) {
		$schedules['tenminutes'] = array(
			'interval' => 600,
			'display'  => __( 'Every 10 minutes', 'website-file-changes-monitor' ),
		);
		$schedules['weekly']     = array(
			'interval' => 7 * DAY_IN_SECONDS,
			'display'  => __( 'Once a week', 'website-file-changes-monitor' ),
		);
		$schedules['monthly']    = array(
			'interval' => 30 * DAY_IN_SECONDS,
			'display'  => __( 'Once a month', 'website-file-changes-monitor' ),
		);
		return $schedules;
	}

	/**
	 * Scan File Changes.
	 */
	public function scan_file_changes() {

		// has done the migration to sha256?
		if ( 'no' === wfcm_get_setting( 'is-initial-scan-0', 'yes' ) && ! wfcm_get_setting( 'sha256-hashing', false ) ) {
			// user not migrated to sha256 yet.

			// We want to use sha256 for hashing but it may not be available on all
			// systems - some php 5.6 may not have it.
			$admin_notices = wfcm_get_setting( 'admin-notices', array() ); // Get admin notices.

			// Add a notice telling user they need to upgrade hashing for their
			// files.
			if ( ! isset( $admin_notices ) || ! is_array( $admin_notices ) ) {
				$admin_notices = array();
			}
			$admin_notices['hashing-upgrade']['upgrade-needed'] = true;
			// save notice.
			wfcm_save_setting( 'admin-notices', $admin_notices );
			// Returns early so we DO NOT hash with old hashes.
			return;
		}

		// We want to use sha256 for hashing but it may not be available on all
		// systems - some php 5.6 may not have it.
		$admin_notices = wfcm_get_setting( 'admin-notices', array() ); // Get admin notices.
		if ( ! in_array( self::HASHING_ALGORITHM, hash_algos(), true ) ) {
			// Add a notice informing user they do not have the necessary hash
			// algorithm on their site.
			if ( ! isset( $admin_notices ) || ! is_array( $admin_notices ) ) {
				$admin_notices = array();
			}
			$admin_notices['hashing-algorith']['sha256-unavailable'] = true;
			// save notice and return early.
			wfcm_save_setting( 'admin-notices', $admin_notices );
			// DO NOT hash with old algorithm.
			return;
		} elseif ( isset( $admin_notices['hashing-algorith'] ) ) {
			unset( $admin_notices['hashing-algortith'] );
			wfcm_save_setting( 'admin-notices', $admin_notices );
		}

		// Check if a scan is already in progress. Bail early if it is - there
		// should never be 2 scans running at the same time.
		if ( wfcm_get_setting( 'scan-in-progress', false ) ) {
			return;
		}

		// check if the previous scan left any 'last-scanned' around.
		$last_scanned_option = wfcm_get_setting( 'last-scanned', false );
		if ( false !== $last_scanned_option && ! empty( $last_scanned_option ) ) {
			// previous scan never completed for some reason...
			$admin_notices = wfcm_get_setting( 'admin-notices', array() );

			if ( ! isset( $admin_notices ) || ! is_array( $admin_notices ) ) {
				$admin_notices = array();
			}

			if ( ! isset( $admin_notices['previous-scan-fail-generic'] ) ) {
				$admin_notices['previous-scan-fail-generic'] = true;
				wfcm_save_setting( 'admin-notices', $admin_notices );

				// send user an email about this.
				wfcm_send_scan_fail_email( true, 'generic' );
			}
		}

		// Get directories to be scanned.
		$directories = $this->scan_settings['directories'];
		// Server directories.
		$stored_server_dirs = wfcm_get_server_directories();

		// Always start assuming we are at part 0/false.
		$last_scanned_item = false;
		$break_while       = false;

		// Set the scan in progress to true because the scan is about to start.
		wfcm_save_setting( 'scan-in-progress', true );

		// Trigger WSAL Event 6033.
		do_action( 'wfcm_wsal_file_scan_started' );

		// Start tracking total runtime and increase php max_execution_time.
		$this->start_tracking_php_runtime();

		/**
		 * Loop through all the parts of the scan directories one at a time
		 * till we reach the last one ( number 6 ) - or till $break_while
		 * becomes true.
		 *
		 * NOTE $break_while is used with the early halt setup that tries stop
		 * a scan before it hits any timeout period.
		 */
		while ( false === $break_while && 6 !== $last_scanned_item ) {

			// Set the directory/part number to scan this itteration.
			if ( false === $last_scanned_item || $last_scanned_item > 5 ) {
				$next_to_scan_num = 0;
			} elseif ( 'root' === $last_scanned_item ) {
				$next_to_scan_num = 1;
			} else {
				$next_to_scan_num = $last_scanned_item + 1;
			}

			// Get directory path to scan.
			$path_to_scan = $stored_server_dirs[ $next_to_scan_num ];

			// Set the options name for file list.
			$file_list_name = "local-files-$next_to_scan_num";

			// Log the scan start time and part.
			if ( $this->scan_settings['debug-logging'] ) {
				$msg  = wfcm_get_log_timestamp() . ' ';
				$msg .= __( 'WFCM started scanning:', 'website-file-changes-monitor' ) . ' ';
				$msg .= $path_to_scan ? $path_to_scan : 'root';
				$msg .= "\n";
				wfcm_write_to_log( $msg );
			}

			// If we are on 'root' or a valid path from $directories to scan...
			if ( ( empty( $path_to_scan ) && in_array( 'root', $directories, true ) ) || ( $path_to_scan && in_array( $path_to_scan, $directories, true ) ) ) {
				// Exclude everything else not in this path from current scan
				// part.
				$exclude_server_dirs = $stored_server_dirs;
				unset( $exclude_server_dirs[ $next_to_scan_num ] );
				$this->excludes = $exclude_server_dirs;

				// Try get list of files that were already scanned from DB.
				$stored_files = wfcm_get_setting( $file_list_name, array() );

				// Set up the initial scan changes count values.
				$this->scan_changes_count();

				/**
				 * `Filter`: Stored files filter.
				 *
				 * @param array  $stored_files – Files array already saved in DB from last scan.
				 * @param string $path_to_scan – Path currently being scanned.
				 */
				$filtered_stored_files = apply_filters( 'wfcm_file_scan_stored_files', $stored_files, $path_to_scan );

				// Get array of already directories scanned from DB.
				$scanned_dirs = wfcm_get_setting( 'scanned-dirs', array() );

				// If already scanned directories don't exist then it marks the start of a scan.
				if ( empty( $scanned_dirs ) ) {
					wfcm_save_setting( 'last-scan-start', time() );
				}

				/**
				 * Before file scan action hook.
				 *
				 * @param string $path_to_scan - Directory path to scan.
				 */
				do_action( 'wfcm_before_file_scan', $path_to_scan );

				// Reset scan counter.
				$this->reset_scan_counter();

				// Scan the path.
				$scanned_files = $this->scan_path( $path_to_scan );

				/**
				 * `Filter`: Scanned files filter.
				 *
				 * @param array  $scanned_files – Files array already saved in DB from last scan.
				 * @param string $path_to_scan  – Path currently being scanned.
				 */
				$filtered_scanned_files = apply_filters( 'wfcm_file_scan_scanned_files', $scanned_files, $path_to_scan );

				// Add the currently scanned path to scanned directories.
				$scanned_dirs[] = $path_to_scan;

				/**
				 * After file scan action hook.
				 *
				 * @param string $path_to_scan - Directory path to scan.
				 */
				do_action( 'wfcm_after_file_scan', $path_to_scan );

				// Get initial scan setting.
				$initial_scan = wfcm_get_setting( "is-initial-scan-$next_to_scan_num", 'yes' );

				// If the scan is not initial then.
				if ( 'yes' !== $initial_scan ) {

					// generates the list of added/removed/modified files and
					// creates events for those items.
					$this->compute_differences_and_create_change_events( $filtered_stored_files, $filtered_scanned_files );

					// Check for files limit alert.
					if ( $this->scan_limit_file ) {
						$admin_notices = wfcm_get_setting( 'admin-notices', array() );

						if ( ! isset( $admin_notices['files-limit'] ) || ! is_array( $admin_notices['files-limit'] ) ) {
							$admin_notices['files-limit'] = array();
						}

						if ( ! in_array( $path_to_scan, $admin_notices['files-limit'], true ) ) {
							array_push( $admin_notices['files-limit'], $path_to_scan );
						}

						wfcm_save_setting( 'admin-notices', $admin_notices );

						// Trigger WSAL Event 6032.
						do_action( 'wfcm_wsal_file_limit_exceeded', $path_to_scan );
					}

					$this->scan_changes_count( 'save' );

					/**
					 * `Action`: Last scanned directory.
					 *
					 * @param int $next_to_scan_num – Last scanned directory.
					 */
					do_action( 'wfcm_last_scanned_directory', $next_to_scan_num );
				} else {
					wfcm_save_setting( "is-initial-scan-$next_to_scan_num", 'no' ); // Initial scan check set to false.
					wfcm_save_setting( 'sha256-hashing', true ); // done this with sha256.
				}

				// Store scanned files list.
				wfcm_save_setting( $file_list_name, $scanned_files );
				wfcm_save_setting( 'scanned-dirs', $scanned_dirs );

			}

			/**
			 * Update last scanned directory.
			 *
			 * IMPORTANT: This option is saved outside start scan check
			 * so that if the scan is skipped, then the increment of
			 * next to scan is not disturbed.
			 */
			if ( 0 === $next_to_scan_num ) {
				wfcm_save_setting( 'last-scanned', 'root' );
				$last_scanned_item = 'root';

				do_action( 'wfcm_files_monitoring_started' );
			} elseif ( 6 === $next_to_scan_num ) {
				// save this to db as 'false' === empty ready for next run but
				// keep the numbered version in the runtime.
				wfcm_save_setting( 'last-scanned', false );
				$last_scanned_item = $next_to_scan_num;

				$this->scan_completed = true;
				do_action( 'wfcm_files_monitoring_ended' );
			} else {
				wfcm_save_setting( 'last-scanned', $next_to_scan_num );
				$last_scanned_item = $next_to_scan_num;
			}

			// Log the scan part end time.
			if ( $this->scan_settings['debug-logging'] ) {
				$msg  = wfcm_get_log_timestamp() . ' ';
				$msg .= __( 'WFCM finished scanning:', 'website-file-changes-monitor' ) . ' ';
				$msg .= $path_to_scan ? $path_to_scan : 'root';
				$msg .= "\n";
				wfcm_write_to_log( $msg );
			}

			/**
			 * We are trying to ensure a full scan completes on every run by
			 * increasing php time limits. If the scan still does not complete
			 * in that time limit then
			 *
			 *  1: setup to break from the scan early
			 *  2: email user about the timeout
			 *  3: set a notice to display on the next page request
			 */
			if ( ! $this->scan_completed ) {
				$scan_timeout = ( ( $this->scan_max_execution_time - ( 60 + 20 ) ) > 0 ) ? $this->scan_max_execution_time - ( 60 + 20 ) : $this->scan_max_execution_time;
				if ( time() > ( $this->scan_start_time + $scan_timeout ) ) {
					$break_while = true;
					// setting notice for scan timeouts.
					$admin_notices = wfcm_get_setting( 'admin-notices', array() );
					if ( ! isset( $admin_notices ) || ! is_array( $admin_notices ) ) {
						$admin_notices = array();
					}

					$admin_notices['previous-scan-fail-timeout'] = array(
						'failed' => true,
						'time'   => time(),
					);
					wfcm_save_setting( 'admin-notices', $admin_notices );
					// send user an email about this.
					wfcm_send_scan_fail_email( true, 'timeout' );
					// If mail should have been sent log the time when WFCM sends the email.
					if ( $this->scan_settings['debug-logging'] ) {
						$msg = wfcm_get_log_timestamp() . ' ' . __( 'WFCM sent an email', 'website-file-changes-monitor' ) . " \n";
						wfcm_write_to_log( $msg );
					}

					// log a debug message that we were nearing timeout.
					if ( $this->scan_settings['debug-logging'] ) {
						$msg  = wfcm_get_log_timestamp() . ' ';
						$msg .= __( 'Scan time halted as it was nearing max execution time.', 'website-file-changes-manager' );
						$msg .= "\n";
						$msg .= wfcm_get_log_timestamp() . ' ';
						$msg .= sprintf(
							/* Translators: a integer of seconds */
							__( 'Total time run: %1$d seconds', 'website-file-changes-manager' ),
							time() - $this->scan_start_time
						);
						$msg .= "\n";
						wfcm_write_to_log( $msg );
					}
				}
			}
		}

		// Set the scan in progress to false because scan is complete.
		wfcm_save_setting( 'scan-in-progress', false );
		// save the last scan timestamp to display frontend.
		wfcm_save_setting( 'last-scan-timestamp', time() );

		// Trigger WSAL Event 6033.
		do_action( 'wfcm_wsal_file_scan_stopped' );

		// Send email notification.
		$changes = wfcm_send_changes_email( $this->scan_changes_count );

		// If mail should have been sent log the time when WFCM sends the scan email.
		if ( $changes && $this->scan_settings['debug-logging'] ) {
			$msg = wfcm_get_log_timestamp() . ' ' . __( 'WFCM sent an email', 'website-file-changes-monitor' ) . " \n";
			wfcm_write_to_log( $msg );
		}

		// Delete changes count for this scan.
		$this->scan_changes_count( 'delete' );

		// Get admin notices.
		$admin_notices = wfcm_get_setting( 'admin-notices', array() );

		if ( ! $changes ) {
			$admin_notices['empty-scan'] = true; // Set scan empty notice to true because there are no file changes in the latest scan.
		} else {
			$admin_notices['empty-scan'] = false; // Set scan empty notice to false because there are file changes in the latest scan.
		}

		// Save admin notices.
		wfcm_save_setting( 'admin-notices', $admin_notices );

	}

	/**
	 * Given lists of files this arranges them into different arrays and cretes
	 * the posts for each event of the given types.
	 *
	 * @method compute_differences_and_create_change_events
	 * @since  1.5.0
	 * @param  array $filtered_stored_files [description]
	 * @param  array $filtered_scanned_files [description]
	 */
	private function compute_differences_and_create_change_events( $filtered_stored_files, $filtered_scanned_files ) {
		// Compare the results to find out about file added and removed.
		$files_added   = array_diff_key( $filtered_scanned_files, $filtered_stored_files );
		$files_removed = array_diff_key( $filtered_stored_files, $filtered_scanned_files );

		/**
		 * File changes.
		 *
		 * To scan the files with changes, we need to
		 *
		 *  1. Remove the newly added files from scanned files – no need to add them to changed files array.
		 *  2. Remove the deleted files from already logged files – no need to compare them since they are removed.
		 *  3. Then start scanning for differences – check the difference in hash.
		 */
		$scanned_files_minus_added  = array_diff_key( $filtered_scanned_files, $files_added );
		$stored_files_minus_deleted = array_diff_key( $filtered_stored_files, $files_removed );

		// Changed files array.
		$files_changed = array();

		// Go through each newly scanned file.
		foreach ( $scanned_files_minus_added as $file => $file_hash ) {
			// Check if it exists in already stored array of files, ignore if the key does not exists.
			if ( array_key_exists( $file, $stored_files_minus_deleted ) ) {
				// If key exists, then check if the file hash is set and compare it to already stored hash.
				if (
					! empty( $file_hash ) && ! empty( $stored_files_minus_deleted[ $file ] )
					&& 0 !== strcmp( $file_hash, $stored_files_minus_deleted[ $file ] )
				) {
					// If the file hashes don't match then store the file in changed files array.
					$files_changed[ $file ] = $file_hash;
				}
			}
		}

		// Files added alert.
		if ( in_array( 'added', $this->scan_settings['type'], true ) && count( $files_added ) > 0 ) {
			// Get excluded site content.
			$site_content = wfcm_get_setting( WFCM_Settings::$site_content );

			// Add the file count.
			$this->scan_changes_count['files_added'] += count( $files_added );

			// Log the alert.
			foreach ( $files_added as $file => $file_hash ) {
				// Get directory name.
				$directory_name = dirname( $file );

				// Check if the directory is in excluded directories list.
				if ( ! empty( $site_content->skip_dirs ) && in_array( $directory_name, $site_content->skip_dirs, true ) ) {
					continue; // If true, then skip the loop.
				}

				// Get filename from file path.
				$filename = basename( $file );

				// Check if the filename is in excluded files list.
				if ( ! empty( $site_content->skip_files ) && in_array( $filename, $site_content->skip_files, true ) ) {
					continue; // If true, then skip the loop.
				}

				// Check for allowed extensions.
				if ( ! empty( $site_content->skip_exts ) && in_array( pathinfo( $filename, PATHINFO_EXTENSION ), $site_content->skip_exts, true ) ) {
					continue; // If true, then skip the loop.
				}

				// Created file event.
				wfcm_create_event( 'added', $file, $file_hash );

				// Log the added files.
				if ( $this->scan_settings['debug-logging'] ) {
					$msg  = wfcm_get_log_timestamp() . ' ';
					$msg .= __( 'Added file:', 'website-file-changes-monitor' );
					$msg .= " {$file}\n";
					wfcm_write_to_log( $msg );
				}
			}
		}

		// Files removed alert.
		if ( in_array( 'deleted', $this->scan_settings['type'], true ) && count( $files_removed ) > 0 ) {
			// Add the file count.
			$this->scan_changes_count['files_deleted'] += count( $files_removed );

			// Log the alert.
			foreach ( $files_removed as $file => $file_hash ) {
				// Get directory name.
				$directory_name = dirname( $file );

				// Check if directory is in excluded directories list.
				if ( in_array( $directory_name, $this->scan_settings['exclude-dirs'], true ) ) {
					continue; // If true, then skip the loop.
				}

				// Get filename from file path.
				$filename = basename( $file );

				// Check if the filename is in excluded files list.
				if ( in_array( $filename, $this->scan_settings['exclude-files'], true ) ) {
					continue; // If true, then skip the loop.
				}

				// Check for allowed extensions.
				if ( in_array( pathinfo( $filename, PATHINFO_EXTENSION ), $this->scan_settings['exclude-exts'], true ) ) {
					continue; // If true, then skip the loop.
				}

				// Removed file event.
				wfcm_create_event( 'deleted', $file, $file_hash );

				// Log the removed files.
				if ( $this->scan_settings['debug-logging'] ) {
					$msg  = wfcm_get_log_timestamp() . ' ';
					$msg .= __( 'Deleted file:', 'website-file-changes-monitor' );
					$msg .= " {$file}\n";
					wfcm_write_to_log( $msg );
				}
			}
		}

		// Files edited alert.
		if ( in_array( 'modified', $this->scan_settings['type'], true ) && count( $files_changed ) > 0 ) {
			// Add the file count.
			$this->scan_changes_count['files_modified'] += count( $files_changed );

			foreach ( $files_changed as $file => $file_hash ) {
				// Create event for each changed file.
				wfcm_create_event( 'modified', $file, $file_hash );

				// Log the modified files.
				if ( $this->scan_settings['debug-logging'] ) {
					$msg  = wfcm_get_log_timestamp() . ' Modified file: ' . $file;
					$msg .= "\n";
					wfcm_write_to_log( $msg );
				}
			}
		}
	}

	/**
	 * Starts the counter for our runtime for breaking early and increase php
	 * max_execution_time as well to account for long scans.
	 *
	 * @method start_tracking_php_runtime
	 * @since  1.5.0
	 */
	private function start_tracking_php_runtime() {

		// hold the scan start time so we can bail before max execution time.
		$this->scan_start_time = ( 0 !== $this->scan_start_time ) ? $this->scan_start_time : time();

		/**
		 * Try increase the php max execution time.
		 */
		set_time_limit( $this->scan_max_execution_time );
		$current_max = ini_get( 'max_execution_time' );
		if ( (int) $current_max !== (int) $this->scan_max_execution_time ) {
			// note: when xDebug is watching max_execution_time from ini_get is always string "0" causing this to always fire in develop.
			if ( $this->scan_settings['debug-logging'] ) {
				$msg  = wfcm_get_log_timestamp() . ' ';
				$msg .= __( 'Unable to increase max excution time, PHP safe_mode may be enabled.', 'website-file-changes-monitor' );
				$msg .= "\n";
				wfcm_write_to_log( $msg );
			}
		}

	}

	/**
	 * Check scan frequency.
	 *
	 * Scan start checks:
	 *   1. Check frequency is not empty.
	 *   2. Check if there is any directory left to scan.
	 *     2a. If there is a directory left, then proceed to check frequency.
	 *     2b. Else check if 24 hrs limit is passed or not.
	 *   3. Check frequency of the scan set by user and decide to start the scan or not.
	 *
	 * @param string $frequency - Frequency of the scan.
	 * @return bool True if scan is a go, false if not.
	 */
	public function check_start_scan( $frequency ) {
		// If empty then return false.
		if ( empty( $frequency ) ) {
			return false;
		}

		/**
		 * When there are no directories left to scan then:
		 *
		 * 1. Get the last scan start time.
		 * 2. Check for 24 hrs limit.
		 * 3a. If the limit has passed then remove options related to last scan.
		 * 3b. Else return false.
		 */
		if ( ! $this->dir_left_to_scan( $this->scan_settings['directories'] ) ) {
			// Get last scan time.
			$last_scan_start = wfcm_get_setting( 'last-scan-start', false );

			if ( ! empty( $last_scan_start ) ) {
				// Check for minimum 24 hours.
				$scan_hrs = $this->hours_since_last_scan( $last_scan_start );

				// If scan hours difference has passed 24 hrs limit then remove the options.
				if ( $scan_hrs > 23 ) {
					wfcm_delete_setting( 'scanned-dirs' ); // Delete already scanned directories option.
					wfcm_delete_setting( 'last-scan-start' ); // Delete last scan complete timestamp option.
				} else {
					// Else if they have not passed their limit, then return false.
					return false;
				}
			}
		}

		// Scan check.
		$scan = false;

		// Frequency set by user on the settings page.
		switch ( $frequency ) {
			case self::SCAN_DAILY: // Daily scan.
				if ( in_array( $this->calculate_daily_hour(), self::$daily_hour, true ) ) {
					$scan = true;
				}
				break;
			case self::SCAN_WEEKLY: // Weekly scan.
				$weekly_day = $this->calculate_weekly_day();
				$scan       = ( self::$weekly_day === $weekly_day ) ? true : false;
				break;
			case self::SCAN_MONTHLY: // Monthly scan.
				$str_date = $this->calculate_monthly_day();
				if ( ! empty( $str_date ) ) {
					$scan = ( date( 'Y-m-d' ) == $str_date ) ? true : false;
				}
				break;
		}
		return $scan;
	}

	/**
	 * Check to determine if there is any directory left to scan.
	 *
	 * @param array $scan_directories - Array of directories to scan set by user.
	 * @return bool
	 */
	public function dir_left_to_scan( $scan_directories ) {
		// False if $scan_directories is empty.
		if ( empty( $scan_directories ) ) {
			return false;
		}

		// If multisite then remove all the subsites uploads of multisite from scan directories.
		if ( is_multisite() ) {
			$uploads_dir         = wfcm_get_server_directory( $this->get_uploads_dir_path() );
			$mu_uploads_site_dir = $uploads_dir . '/sites'; // Multsite uploads directory.

			foreach ( $scan_directories as $index => $dir ) {
				if ( false !== strpos( $dir, $mu_uploads_site_dir ) ) {
					unset( $scan_directories[ $index ] );
				}
			}
		}

		// Get array of already directories scanned from DB.
		$already_scanned_dirs = wfcm_get_setting( 'scanned-dirs', array() );

		// Check if already scanned directories has `root` directory.
		if ( in_array( '', $already_scanned_dirs, true ) ) {
			// If found then search for `root` in the directories to be scanned.
			$key = array_search( 'root', $scan_directories, true );
			if ( false !== $key ) {
				// If key is found then remove it from directories to be scanned array.
				unset( $scan_directories[ $key ] );
			}
		}

		// Check the difference in directories.
		$diff = array_diff( $scan_directories, $already_scanned_dirs );

		// If the diff array has 1 or more value then scan needs to run.
		if ( is_array( $diff ) && count( $diff ) > 0 ) {
			return true;
		} elseif ( empty( $diff ) ) {
			return false;
		}
		return false;
	}

	/**
	 * Get number of hours since last file changes scan.
	 *
	 * @param float $created_on - Timestamp of last scan.
	 * @return bool|int         - False if $created_on is empty | Number of hours otherwise.
	 */
	public function hours_since_last_scan( $created_on ) {
		// If $created_on is empty, then return.
		if ( ! $created_on ) {
			return false;
		}

		// Last alert date.
		$created_date = new DateTime( date( 'Y-m-d H:i:s', $created_on ) );

		// Current date.
		$current_date = new DateTime( 'NOW' );

		// Calculate time difference.
		$time_diff = $current_date->diff( $created_date );
		$diff_days = $time_diff->d; // Difference in number of days.
		$diff_hrs  = $time_diff->h; // Difference in number of hours.
		$total_hrs = ( $diff_days * 24 ) + $diff_hrs; // Total number of hours.

		// Return difference in hours.
		return $total_hrs;
	}

	/**
	 * Calculate and return hour of the day based on WordPress timezone.
	 *
	 * @return string - Hour of the day.
	 */
	private function calculate_daily_hour() {
		return date( 'H', time() + ( get_option( 'gmt_offset' ) * ( 60 * 60 ) ) );
	}

	/**
	 * Calculate and return day of the week based on WordPress timezone.
	 *
	 * @return string|bool - Day of the week or false.
	 */
	private function calculate_weekly_day() {
		if ( in_array( $this->calculate_daily_hour(), self::$daily_hour, true ) ) {
			return date( 'w' );
		}
		return false;
	}

	/**
	 * Calculate and return day of the month based on WordPress timezone.
	 *
	 * @return string|bool - Day of the week or false.
	 */
	private function calculate_monthly_day() {
		if ( in_array( $this->calculate_daily_hour(), self::$daily_hour, true ) ) {
			return date( 'Y-m-' ) . self::$monthly_day;
		}
		return false;
	}

	/**
	 * Reset file and directory counter for scan.
	 */
	public function reset_scan_counter() {
		$this->scan_file_count = 0;
		$this->scan_limit_file = false;
	}

	/**
	 * Scan path for files.
	 *
	 * @param string $path - Directory path to scan.
	 * @return array       - Array of files present in $path.
	 */
	private function scan_path( $path = '' ) {
		// Check excluded paths.
		if ( in_array( $path, $this->excludes ) ) {
			return array();
		}

		// Set the directory path.
		$dir_path = $this->root_path . $path;
		$files    = array(); // Array of files to return.

		// Open directory.
		$dir_handle = @opendir( $dir_path );

		if ( false === $dir_handle ) {
			return $files; // Return if directory fails to open.
		}

		$is_multisite     = is_multisite();                               // Multsite checks.
		$directories      = $this->scan_settings['directories'];          // Get directories to be scanned.
		$file_size_limit  = $this->scan_settings['file-size'];            // Get file size limit.
		$file_size_limit  = $file_size_limit * 1048576;                   // Calculate file size limit in bytes; 1MB = 1024 KB = 1024 * 1024 bytes = 1048576 bytes.
		$files_over_limit = array();                                      // Array of files which are over their file size limit.
		$admin_notices    = wfcm_get_setting( 'admin-notices', array() ); // Get admin notices.

		$uploads_dir         = wfcm_get_server_directory( $this->get_uploads_dir_path() );
		$mu_uploads_site_dir = $uploads_dir . '/sites'; // Multsite uploads directory.
		// A list of development folders we may want to skip.
		$dev_folders = (array) apply_filters( 'wfcm_excluded_dev_folders', array( '.git', '.github', '.svn', 'node_modules' ) );

		// Scan the directory for files.
		while ( false !== ( $item = @readdir( $dir_handle ) ) ) {
			// Ignore `.` and `..` from directory.
			if ( '.' === $item || '..' === $item ) {
				continue;
			}

			// Filter scannable filenames, some special characters are allowed.
			if ( preg_match( '/[^A-Za-z0-9 _.@-]/', $item ) > 0 ) {
				// File name contains a special character of some type...
				if ( preg_match( '/[\|\&\$\^]/', $item ) > 0 ) {
					if ( $this->scan_settings['debug-logging'] ) {
						// log this unusual file discovered.
						$location = sanitize_text_field( $dir_path . '/' . (string) $item );
						$message  = esc_html__( 'Encountered unsual filename at: ', 'website-file-changes-monitor' ) . $location;
						wfcm_write_to_log( $message );
					}
					// before version 1.5 we would skip over this file here
					// now we hash it as well.
				}
			}

			// Check if the option to scan dev folders is NOT enabled.
			// By default we don't scan them.
			if ( ! $this->scan_settings['scan-dev-folders'] ) {
				foreach ( $dev_folders as $dev_folder ) {
					// If the current item is a folder which is set to skip...
					if ( false !== strpos( $item, $dev_folder ) ) {
						// Skip this item and continue to next.
						continue 2;
					}
				}
			}

			// Set item paths.
			if ( ! empty( $path ) ) {
				$relative_name = $path . '/' . $item;     // Relative file path w.r.t. the location in 7 major folders.
				$absolute_name = $dir_path . '/' . $item; // Complete file path w.r.t. ABSPATH.
			} else {
				// If path is empty then it is root.
				$relative_name = $path . $item;     // Relative file path w.r.t. the location in 7 major folders.
				$absolute_name = $dir_path . $item; // Complete file path w.r.t. ABSPATH.
			}

			// If we're on root then ignore `wp-admin`, `wp-content` & `wp-includes`.
			if ( empty( $path ) && ( false !== strpos( $absolute_name, 'wp-admin' ) || false !== strpos( $absolute_name, WP_CONTENT_DIR ) || false !== strpos( $absolute_name, WPINC ) ) ) {
				continue;
			}

			// Check for directory.
			if ( is_dir( $absolute_name ) ) {
				/**
				 * `Filter`: Directory name filter before opening it for scan.
				 *
				 * @param string $item - Directory name.
				 */
				$item = apply_filters( 'wcfm_directory_before_file_scan', $item );
				if ( ! $item ) {
					continue;
				}

				// Check if the directory is in excluded directories list.
				if ( in_array( $absolute_name, $this->scan_settings['exclude-dirs'], true ) ) {
					continue; // Skip the directory.
				}

				// If not multisite then simply scan.
				if ( ! $is_multisite ) {
					$files = array_merge( $files, $this->scan_path( $relative_name ) );
				} else {
					/**
					 * Check if `wp-content/uploads/sites` is present in the
					 * relative name of the directory & it is allowed to scan.
					 */
					if ( false !== strpos( $relative_name, $mu_uploads_site_dir ) && in_array( $mu_uploads_site_dir, $directories, true ) ) {
						$files = array_merge( $files, $this->scan_path( $relative_name ) );
					} elseif ( false !== strpos( $relative_name, $mu_uploads_site_dir ) && ! in_array( $mu_uploads_site_dir, $directories, true ) ) {
						// If `wp-content/uploads/sites` is not allowed to scan then skip the loop.
						continue;
					} else {
						$files = array_merge( $files, $this->scan_path( $relative_name ) );
					}
				}
			} else {
				/**
				 * `Filter`: File name filter before scan.
				 *
				 * @param string $item – File name.
				 */
				$item = apply_filters( 'wfcm_filename_before_file_scan', $item );
				if ( ! $item ) {
					continue;
				}

				// Check if the item is in excluded files list.
				if ( in_array( $item, $this->scan_settings['exclude-files'], true ) ) {
					continue; // If true, then skip the loop.
				}

				// Check for allowed extensions.
				if ( in_array( pathinfo( $item, PATHINFO_EXTENSION ), $this->scan_settings['exclude-exts'], true ) ) {
					continue; // If true, then skip the loop.
				}

				// Check files count.
				if ( $this->scan_file_count > self::SCAN_FILE_LIMIT ) { // If file limit is reached.
					$this->scan_limit_file = true; // Then set the limit flag.
					break; // And break the loop.
				}

				// Check file size limit.
				if ( ! is_link( $absolute_name ) && filesize( $absolute_name ) < $file_size_limit ) {
					$this->scan_file_count++;

					// File data.
					$files[ $absolute_name ] = @hash_file( self::HASHING_ALGORITHM, $absolute_name ); // File hash.
				} elseif ( is_link( $absolute_name ) ) {
					$files[ $absolute_name ] = '';
				} else {
					if ( ! isset( $admin_notices['filesize-limit'] ) || ! in_array( $absolute_name, $admin_notices['filesize-limit'], true ) ) {
						// File size is more than the limit.
						array_push( $files_over_limit, $absolute_name );
					}

					// File data.
					$files[ $absolute_name ] = '';
				}
			}
		}

		// Close the directory.
		@closedir( $dir_handle );

		if ( ! empty( $files_over_limit ) ) {
			if ( ! isset( $admin_notices['filesize-limit'] ) || ! is_array( $admin_notices['filesize-limit'] ) ) {
				$admin_notices['filesize-limit'] = array();
			}

			$admin_notices['filesize-limit'] = array_merge( $admin_notices['filesize-limit'], $files_over_limit );

			wfcm_save_setting( 'admin-notices', $admin_notices );

			// Trigger WSAL Event 6031.
			do_action( 'wfcm_wsal_file_size_exceeded', $files_over_limit );
		}

		// Return files data.
		return $files;
	}

	/**
	 * Filter scan files before file changes comparison. This
	 * function filters both stored & scanned files.
	 *
	 * Filters:
	 *     1. wp-content/plugins (Plugins).
	 *     2. wp-content/themes (Themes).
	 *     3. wp-admin (WP Core).
	 *     4. wp-includes (WP Core).
	 *
	 * Hooks using this function:
	 *     1. wfcm_file_scan_stored_files.
	 *     2. wfcm_file_scan_scanned_files.
	 *
	 * @param array  $scan_files   - Scan files array.
	 * @param string $path_to_scan - Path currently being scanned.
	 * @return array
	 */
	public function filter_scan_files( $scan_files, $path_to_scan ) {
		// If the path to scan is of plugins.
		if ( false !== strpos( $path_to_scan, wfcm_get_server_directory( WP_PLUGIN_DIR ) ) ) {
			// Filter plugin files.
			$scan_files = $this->filter_excluded_scan_files( $scan_files, 'plugins' );
		} elseif ( false !== strpos( $path_to_scan, wfcm_get_server_directory( get_theme_root() ) ) ) { // And if the path to scan is of themes then.
			// Filter theme files.
			$scan_files = $this->filter_excluded_scan_files( $scan_files, 'themes' );
		} elseif (
			empty( $path_to_scan )                           // Root path.
			|| false !== strpos( $path_to_scan, 'wp-admin' ) // WP Admin.
			|| false !== strpos( $path_to_scan, WPINC )      // WP Includes.
		) {
			// Get `site_content` option.
			$site_content = wfcm_get_setting( WFCM_Settings::$site_content );

			// If the `skip_core` is set and its value is equal to true then.
			if ( isset( $site_content->skip_core ) && true === $site_content->skip_core ) {
				// Check the create events for wp-core file updates.
				$this->filter_excluded_scan_files( $scan_files, $path_to_scan );

				// Empty the scan files.
				$scan_files = array();
			}
		}

		// Return the filtered scan files.
		return $scan_files;
	}

	/**
	 * Filter different types of content from scan files.
	 *
	 * Excluded types:
	 *  1. Plugins.
	 *  2. Themes.
	 *
	 * @param array  $scan_files    - Array of scan files.
	 * @param string $excluded_type - Type to be excluded.
	 * @return array
	 */
	private function filter_excluded_scan_files( $scan_files, $excluded_type ) {
		if ( empty( $scan_files ) ) {
			return $scan_files;
		}

		// Get list of excluded plugins/themes.
		$excluded_contents = wfcm_get_setting( WFCM_Settings::$site_content );

		// If excluded files exists then.
		if ( ! empty( $excluded_contents ) ) {
			// Get the array of scan files.
			$files = array_keys( $scan_files );

			// An array of files to exclude from scan files array.
			$files_to_exclude = array();

			// Type of content to skip.
			$skip_type = 'skip_' . $excluded_type; // Possitble values: `plugins` or `themes`.

			// Get current filter.
			$current_filter = current_filter();

			if (
				in_array( $excluded_type, array( 'plugins', 'themes' ), true ) // Only two skip types are allowed.
				&& isset( $excluded_contents->$skip_type )                     // Skip type array exists.
				&& is_array( $excluded_contents->$skip_type )                  // Skip type is array.
				&& ! empty( $excluded_contents->$skip_type )                   // And is not empty.
			) {
				// Go through each plugin to be skipped.
				foreach ( $excluded_contents->$skip_type as $content => $context ) {
					// Path of plugin to search in stored files.
					$search_path = '/' . $excluded_type . '/' . $content;

					// An array of content to be stored as meta for event.
					$event_content = array();

					// Get array of files to exclude of plugins from scan files array.
					foreach ( $files as $file ) {
						if ( false !== strpos( $file, $search_path ) ) {
							$files_to_exclude[] = $file;

							$event_content[ $file ] = (object) array(
								'file' => $file,
								'hash' => isset( $scan_files[ $file ] ) ? $scan_files[ $file ] : false,
							);
						}
					}

					if ( 'update' === $context ) {
						if ( 'wfcm_file_scan_stored_files' === $current_filter ) {
							$this->files_to_exclude[ $search_path ] = $event_content;
						} elseif ( 'wfcm_file_scan_scanned_files' === $current_filter ) {
							$this->check_directory_for_updates( $event_content, $search_path );
						}
					}

					if ( ! empty( $event_content ) ) {
						$dir_path = untrailingslashit( WP_CONTENT_DIR ) . $search_path;

						if ( in_array( 'added', $this->scan_settings['type'], true ) && 'wfcm_file_scan_scanned_files' === $current_filter && 'install' === $context ) {
							$event_context = '';
							if ( 'plugins' === $excluded_type ) {
								// Set context.
								$event_context = __( 'Plugin Install', 'website-file-changes-monitor' );

								// Set the count.
								$this->scan_changes_count['plugin_installs'] += 1;

								// Log the installed plugin files.
								if ( $this->scan_settings['debug-logging'] ) {
									$msg  = wfcm_get_log_timestamp() . ' ';
									$msg .= __( 'Installed plugin:', 'website-file-changes-monitor' ) . " {$dir_path}\n";
									$msg .= __( 'Added files:', 'website-file-changes-monitor' ) . "\n";
									$msg .= implode( "\n", array_keys( $event_content ) );
									$msg .= "\n";
									wfcm_write_to_log( $msg );
								}
							} elseif ( 'themes' === $excluded_type ) {
								// Set context.
								$event_context = __( 'Theme Install', 'website-file-changes-monitor' );

								// Set the count.
								$this->scan_changes_count['theme_installs'] += 1;

								// Log the installed theme files.
								if ( $this->scan_settings['debug-logging'] ) {
									$msg  = wfcm_get_log_timestamp() . ' ';
									$msg .= __( 'Installed theme:', 'website-file-changes-monitor' ) . " {$dir_path}\n";
									$msg .= __( 'Added files:', 'website-file-changes-monitor' ) . "\n";
									$msg .= implode( "\n", array_keys( $event_content ) );
									$msg .= "\n";
									wfcm_write_to_log( $msg );
								}
							}

							wfcm_create_directory_event( 'added', $dir_path, array_values( $event_content ), $event_context );
						} elseif ( in_array( 'deleted', $this->scan_settings['type'], true ) && 'wfcm_file_scan_stored_files' === $current_filter && 'uninstall' === $context ) {
							$event_context = '';
							if ( 'plugins' === $excluded_type ) {
								// Set context.
								$event_context = __( 'Plugin Uninstall', 'website-file-changes-monitor' );

								// Set the count.
								$this->scan_changes_count['plugin_uninstalls'] += 1;

								// Log the uninstalled plugin files.
								if ( $this->scan_settings['debug-logging'] ) {
									$msg  = wfcm_get_log_timestamp() . ' ';
									$msg .= __( 'Uninstalled plugin:', 'website-file-changes-monitor' ) . " {$dir_path}\n";
									$msg .= __( 'Deleted files:', 'website-file-changes-monitor' ) . "\n";
									$msg .= implode( "\n", array_keys( $event_content ) );
									$msg .= "\n";
									wfcm_write_to_log( $msg );
								}
							} elseif ( 'themes' === $excluded_type ) {
								// Set context.
								$event_context = __( 'Theme Uninstall', 'website-file-changes-monitor' );

								// Set the count.
								$this->scan_changes_count['theme_uninstalls'] += 1;

								// Log the uninstalled theme files.
								if ( $this->scan_settings['debug-logging'] ) {
									$msg  = wfcm_get_log_timestamp() . ' ';
									$msg .= __( 'Uninstalled theme:', 'website-file-changes-monitor' ) . " {$dir_path}\n";
									$msg .= __( 'Deleted files:', 'website-file-changes-monitor' ) . "\n";
									$msg .= implode( "\n", array_keys( $event_content ) );
									$msg .= "\n";
									wfcm_write_to_log( $msg );
								}
							}

							wfcm_create_directory_event( 'deleted', $dir_path, array_values( $event_content ), $event_context );
						}
					}
				}
			} elseif ( ! $excluded_type || in_array( $excluded_type, array( 'wp-admin', WPINC ), true ) ) {
				// An array of content to be stored as meta for event.
				$event_content = array();

				$directory = trailingslashit( ABSPATH ) . $excluded_type;

				foreach ( $scan_files as $file => $file_hash ) {
					$event_content[ $file ] = (object) array(
						'file' => $file,
						'hash' => $file_hash,
					);
				}

				if ( ! empty( $event_content ) ) {
					if ( 'wfcm_file_scan_stored_files' === $current_filter ) {
						$this->files_to_exclude[ $directory ] = $event_content;
					} elseif ( 'wfcm_file_scan_scanned_files' === $current_filter ) {
						$this->check_directory_for_updates( $event_content, $directory );
					}
				}
			}

			// If there are files to be excluded then.
			if ( ! empty( $files_to_exclude ) ) {
				// Go through each file to be excluded and unset it from scan files array.
				foreach ( $files_to_exclude as $file_to_exclude ) {
					if ( array_key_exists( $file_to_exclude, $scan_files ) ) {
						unset( $scan_files[ $file_to_exclude ] );
					}
				}
			}
		}

		return $scan_files;
	}

	/**
	 * Empty skip file alerts array after scanning the path.
	 *
	 * @param string $path_to_scan - Path currently being scanned.
	 * @return void
	 */
	public function empty_skip_file_alerts( $path_to_scan ) {
		// Check path to scan is not empty.
		if ( empty( $path_to_scan ) ) {
			return;
		}

		// If path to scan is of plugins then empty the skip plugins array.
		if ( false !== strpos( $path_to_scan, wfcm_get_server_directory( WP_PLUGIN_DIR ) ) ) {
			// Get contents list.
			$site_content = wfcm_get_setting( WFCM_Settings::$site_content, false );

			// if we don't have an object make this one.
			if ( ! $site_content ) {
				$site_content = new stdClass();
			}
			// Empty skip plugins array.
			$site_content->skip_plugins = array();

			// Save it.
			wfcm_save_setting( WFCM_Settings::$site_content, $site_content );

			// If path to scan is of themes then empty the skip themes array.
		} elseif ( false !== strpos( $path_to_scan, wfcm_get_server_directory( get_theme_root() ) ) ) {
			// Get contents list.
			$site_content = wfcm_get_setting( WFCM_Settings::$site_content, false );

			// if we don't have an object make this one.
			if ( ! $site_content ) {
				$site_content = new stdClass();
			}
			// Empty skip themes array.
			$site_content->skip_themes = array();

			// Save it.
			wfcm_save_setting( WFCM_Settings::$site_content, $site_content );
		}
	}

	/**
	 * Reset core file changes flag.
	 *
	 * @param int $last_scanned_dir - Last scanned directory.
	 */
	public function reset_core_updates_flag( $last_scanned_dir ) {
		// Check if last scanned directory exists and it is at last directory.
		if ( ! empty( $last_scanned_dir ) && 6 === $last_scanned_dir ) {
			// Get `site_content` option.
			$site_content = wfcm_get_setting( WFCM_Settings::$site_content, false );

			// Check WP core update.
			if ( isset( $site_content->skip_core ) && $site_content->skip_core ) {
				$this->scan_changes_count['wp_core_update'] = 1;
			}

			// Check if the option is instance of stdClass.
			if ( false !== $site_content && $site_content instanceof stdClass ) {
				$site_content->skip_core  = false;   // Reset skip core after the scan is complete.
				$site_content->skip_files = array(); // Empty the skip files at the end of the scan.
				$site_content->skip_exts  = array(); // Empty the skip extensions at the end of the scan.
				$site_content->skip_dirs  = array(); // Empty the skip directories at the end of the scan.
				wfcm_save_setting( WFCM_Settings::$site_content, $site_content ); // Save the option.
			}
		}
	}

	/**
	 * Check directory for file change events after updates.
	 *
	 * @param array  $scanned_files - Array of excluded scanned files.
	 * @param string $directory     - Name of the directory.
	 */
	public function check_directory_for_updates( $scanned_files, $directory ) {
		// Get the files previously stored in the directory.
		$stored_files = $this->files_to_exclude[ $directory ];

		// Compare the results to find out about file added and removed.
		$files_added   = array_diff_key( $scanned_files, $stored_files );
		$files_removed = array_diff_key( $stored_files, $scanned_files );

		/**
		 * File changes.
		 *
		 * To scan the files with changes, we need to
		 *
		 *  1. Remove the newly added files from scanned files – no need to add them to changed files array.
		 *  2. Remove the deleted files from already logged files – no need to compare them since they are removed.
		 *  3. Then start scanning for differences – check the difference in hash.
		 */
		$scanned_files_minus_added  = array_diff_key( $scanned_files, $files_added );
		$stored_files_minus_deleted = array_diff_key( $stored_files, $files_removed );

		// Changed files array.
		$files_changed = array();

		// Go through each newly scanned file.
		foreach ( $scanned_files_minus_added as $file => $file_obj ) {
			// Check if it exists in already stored array of files, ignore if the key does not exists.
			if ( array_key_exists( $file, $stored_files_minus_deleted ) ) {
				// If key exists, then check if the file hash is set and compare it to already stored hash.
				if (
					! empty( $file_obj->hash ) && ! empty( $stored_files_minus_deleted[ $file ] )
					&& 0 !== strcmp( $file_obj->hash, $stored_files_minus_deleted[ $file ]->hash )
				) {
					// If the file hashes don't match then store the file in changed files array.
					$files_changed[ $file ] = $file_obj;
				}
			}
		}

		$dirname       = ABSPATH !== $directory ? dirname( $directory ) : $directory;
		$dir_path      = '';
		$event_context = '';
		$log_type      = '';

		if ( '/plugins' === $dirname ) {
			$dir_path      = untrailingslashit( WP_CONTENT_DIR ) . $directory;
			$event_context = __( 'Plugin Update', 'website-file-changes-monitor' );
			$log_type      = 'plugin';

			// Set the count.
			$this->scan_changes_count['plugin_updates'] += 1;
		} elseif ( '/themes' === $dirname ) {
			$dir_path      = untrailingslashit( WP_CONTENT_DIR ) . $directory;
			$event_context = __( 'Theme Update', 'website-file-changes-monitor' );
			$log_type      = 'theme';

			// Set the count.
			$this->scan_changes_count['theme_updates'] += 1;
		} elseif ( ABSPATH === $directory || false !== strpos( $directory, 'wp-admin' ) || false !== strpos( $directory, WPINC ) ) {
			$dir_path      = $directory;
			$event_context = __( 'Core Update', 'website-file-changes-monitor' );
			$log_type      = 'core';
		}

		if ( in_array( 'added', $this->scan_settings['type'], true ) && count( $files_added ) > 0 ) {
			wfcm_create_directory_event( 'added', $dir_path, array_values( $files_added ), $event_context );

			// Log the added update files.
			if ( $this->scan_settings['debug-logging'] ) {
				$msg  = wfcm_get_log_timestamp() . ' ';
				$msg .= __( 'Updated', 'website-file-changes-monitor' ) . " {$log_type}: " . $dir_path . "\n";
				$msg .= __( 'Added files:', 'website-file-changes-monitor' ) . "\n";
				$msg .= implode( "\n", array_keys( $files_added ) );
				$msg .= "\n";
				wfcm_write_to_log( $msg );
			}
		}

		if ( in_array( 'deleted', $this->scan_settings['type'], true ) && count( $files_removed ) > 0 ) {
			wfcm_create_directory_event( 'deleted', $dir_path, array_values( $files_removed ), $event_context );

			// Log the deleted update files.
			if ( $this->scan_settings['debug-logging'] ) {
				$msg  = wfcm_get_log_timestamp() . ' ';
				$msg .= __( 'Updated', 'website-file-changes-monitor' ) . " {$log_type}: " . $dir_path . "\n";
				$msg .= __( 'Deleted files:', 'website-file-changes-monitor' ) . "\n";
				$msg .= implode( "\n", array_keys( $files_removed ) );
				$msg .= "\n";
				wfcm_write_to_log( $msg );
			}
		}

		if ( in_array( 'modified', $this->scan_settings['type'], true ) && count( $files_changed ) > 0 ) {
			wfcm_create_directory_event( 'modified', $dir_path, array_values( $files_changed ), $event_context );

			// Log the modified update files.
			if ( $this->scan_settings['debug-logging'] ) {
				$msg  = wfcm_get_log_timestamp() . ' ';
				$msg .= __( 'Updated', 'website-file-changes-monitor' ) . " {$log_type}: " . $dir_path . "\n";
				$msg .= __( 'Modified files:', 'website-file-changes-monitor' ) . "\n";
				$msg .= implode( "\n", array_keys( $files_changed ) );
				$msg .= "\n";
				wfcm_write_to_log( $msg );
			}
		}
	}

	/**
	 * Returns the path of WP uploads directory.
	 *
	 * @return string
	 */
	private function get_uploads_dir_path() {
		if ( empty( $this->uploads_dir ) ) {
			$this->uploads_dir = wp_upload_dir(); // Get WP uploads directory.
		}
		return $this->uploads_dir['basedir'];
	}

	/**
	 * Scan changes count; get, save, or delete.
	 *
	 * @param string $action - Count action; get, save, or delete.
	 */
	private function scan_changes_count( $action = 'get' ) {
		if ( 'get' === $action ) {
			$this->scan_changes_count = get_transient( 'wfcm-scan-changes-count' );

			if ( false === $this->scan_changes_count ) {
				$this->scan_changes_count = array(
					'files_added'       => 0,
					'files_deleted'     => 0,
					'files_modified'    => 0,
					'plugin_installs'   => 0,
					'plugin_updates'    => 0,
					'plugin_uninstalls' => 0,
					'theme_installs'    => 0,
					'theme_updates'     => 0,
					'theme_uninstalls'  => 0,
					'wp_core_update'    => 0,
				);
			}
		} elseif ( 'save' === $action ) {
			set_transient( 'wfcm-scan-changes-count', $this->scan_changes_count, DAY_IN_SECONDS );
		} elseif ( 'delete' === $action ) {
			delete_transient( 'wfcm-scan-changes-count' );
		}
	}
}

wfcm_get_monitor();
