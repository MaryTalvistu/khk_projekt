<?php

/**
* Hook into WSAL's action that runs before sensors get loaded.
*/
add_action( 'wsal_before_sensor_load', 'wfcm_load_custom_sensors_and_events_dirs' );

/**
 * Used to hook into the `wsal_before_sensor_load` action to add some filters
 * for including custom sensor and event directories.
 */
function wfcm_load_custom_sensors_and_events_dirs( $sensor ) {
	add_filter( 'wsal_custom_sensors_classes_dirs', 'wfcm_wsal_custom_sensors_path' );
	add_filter( 'wsal_custom_alerts_dirs', 'wfcm_wsal_add_custom_events_path' );
	return $sensor;
}

/**
 * Adds a new path to the sensors directory array which is checked for when the
 * plugin loads the sensors.
 */
function wfcm_wsal_custom_sensors_path( $paths = array() ) {
  $paths   = ( is_array( $paths ) ) ? $paths : array();
	$paths[] = trailingslashit( trailingslashit( dirname( __FILE__ ) ) . 'sensor' );
	return $paths;
}

/**
 * Adds a new path to the custom events directory array which is checked for
 * when the plugin loads all of the events.
 */
function wfcm_wsal_add_custom_events_path( $paths ) {
  $paths   = ( is_array( $paths ) ) ? $paths : array();
	$paths[] = trailingslashit( trailingslashit( dirname( __FILE__ ) ) . 'alerts' );
	return $paths;
}

/**
 * Adds new meta formatting for our plugion
 *
 * @method wsal_wpforms_add_custom_meta_format
 * @since  1.0.0
 */
function wfcm_wsal_add_custom_meta_format( $value, $name ) {
	$wcfm_modified_page = '';
	$redirect_args      = array(
		'page' => 'wfcm-file-changes',
		'tab'  => 'modified-files',
	);
	if ( ! is_multisite() ) {
		$wcfm_modified_page = add_query_arg( $redirect_args, admin_url( 'admin.php' ) );
	} else {
		$wcfm_modified_page = add_query_arg( $redirect_args, network_admin_url( 'admin.php' ) );
	}
	if ( '%ReviewChangesLink%' === $name ) {
			return '<a target="_blank" href="' . $wcfm_modified_page . '">' . __( 'Review changes', 'website-file-changes-monitor' ) . '</a>';
	}

	$wcfm_modified_page = '';
	$redirect_args      = array(
		'page' => 'wfcm-file-changes',
		'tab'  => 'deleted-files',
	);
	if ( ! is_multisite() ) {
		$wcfm_modified_page = add_query_arg( $redirect_args, admin_url( 'admin.php' ) );
	} else {
		$wcfm_modified_page = add_query_arg( $redirect_args, network_admin_url( 'admin.php' ) );
	}
	if ( '%ReviewDeletionsLink%' === $name ) {
			return '<a target="_blank" href="' . $wcfm_modified_page . '">' . __( 'Review Changes', 'website-file-changes-monitor' ) . '</a>';
	}

	$wcfm_modified_page = '';
	$redirect_args      = array(
		'page' => 'wfcm-file-changes',
	);
	if ( ! is_multisite() ) {
		$wcfm_modified_page = add_query_arg( $redirect_args, admin_url( 'admin.php' ) );
	} else {
		$wcfm_modified_page = add_query_arg( $redirect_args, network_admin_url( 'admin.php' ) );
	}
	if ( '%ReviewAdditionsLink%' === $name ) {
			return '<a target="_blank" href="' . $wcfm_modified_page . '">' . __( 'Review Changes', 'website-file-changes-monitor' ) . '</a>';
	}
	return $value;
}

add_filter( 'wsal_meta_formatter_custom_formatter', 'wfcm_wsal_add_custom_meta_format', 10, 2 );

/**
 * Adds new ignored CPT for our plugin
 */
function wfcm_wsal_add_custom_ignored_cpt( $post_types ) {
	$new_post_types = array(
		'wfcm_file_event',
	);

	// combine the two arrays.
	$post_types = array_merge( $post_types, $new_post_types );
	return $post_types;
}

add_filter( 'wsal_ignored_custom_post_types', 'wfcm_wsal_add_custom_ignored_cpt' );
