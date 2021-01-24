<?php
/**
 * WFCM Event Post Type Data Store.
 *
 * @package wfcm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Event post type data store.
 */
class WFCM_Event_Data_Store {

	/**
	 * Event meta keys.
	 *
	 * @var array
	 */
	private $meta_keys = array(
		'event_type',
		'status',
		'origin'
	);

	public static function delete_events( $event_types ) {

		$common_query_args = array(
			'post_type'      => WFCM_Post_Types::EVENT_POST_TYPE_ID,
			'posts_per_page' => - 1,
			'paged'          => false,
			'fields'         => 'ids',
		);

		$events = new WP_Query( array_merge( $common_query_args, [
			'meta_query' => [
				'relation' => 'AND',
				[
					'key'     => 'event_type',
					'value'   => $event_types,
					'compare' => 'IN'
				]
			]
		] ) );

		$deleted_count = 0;
		$deleted_posts = [];

		// Delete all of the events and associated metadata.
		$ids = implode( ', ', $events->posts );
		if ( ! empty( $ids ) ) {
			global $wpdb;
			$deleted_count += $wpdb->query( "DELETE a,b,c,d FROM {$wpdb->prefix}posts a LEFT JOIN {$wpdb->prefix}term_relationships b ON ( a.ID = b.object_id ) LEFT JOIN {$wpdb->prefix}postmeta c ON ( a.ID = c.post_id ) LEFT JOIN {$wpdb->prefix}term_taxonomy d ON ( d.term_taxonomy_id = b.term_taxonomy_id ) WHERE a.ID IN ( {$ids} )" );
			$deleted_posts = $events->posts;
		}

		// if we have any deleted items then need to clear the transients.
		if ( $deleted_count > 0 ) {
			foreach ( $event_types as $type ) {
				delete_transient( "wfcm_event_type_tabs_count_{$type}" );
			}
		}

		return $deleted_posts;
	}

	/**
	 * Query events.
	 *
	 * @param array $query_args - Query arguments.
	 *
	 * @return array|object
	 */
	public function query( $query_args ) {
		$wp_query_args = $this->get_wp_query_vars( $query_args );

		$query = new WP_Query( $wp_query_args );

		$events = isset( $query->posts ) ? array_map( 'wfcm_get_event', $query->posts ) : array();

		if ( isset( $query_args['paginate'] ) && $query_args['paginate'] ) {
			return (object) array(
				'events'        => $events,
				'total'         => $query->found_posts,
				'max_num_pages' => $query->max_num_pages,
			);
		}

		return $events;
	}

	/**
	 * Returns WP_Query arguments.
	 *
	 * @param array $query_args - Query arguments.
	 *
	 * @return array
	 */
	private function get_wp_query_vars( $query_args ) {

		$wp_query_args = array(
			'meta_query' => array(), // phpcs:ignore
		);

		if ( ! array_key_exists( 'post_type', $query_args ) ) {
			$wp_query_args['post_type'] = 'wfcm_file_event';
		}

		foreach ( $query_args as $key => $value ) {
			if ( 'meta_query' === $key ) {
				continue;
			}

			if ( in_array( $key, $this->meta_keys, true ) ) {
				if ( ! $value ) {
					continue;
				}

				$wp_query_args['meta_query'][] = array(
					'key'     => $key,
					'value'   => $value,
					'compare' => is_array( $value ) ? 'IN' : '=',
				);
			} else {
				$wp_query_args[ $key ] = $value;
			}
		}

		//  add meta query using "AND" relationship if passed in the $query_args (we can add support for "OR" relationship in the future)
		if ( array_key_exists( 'meta_query', $query_args ) ) {
			if ( empty( $wp_query_args['meta_query'] ) ) {
				$wp_query_args['meta_query'] = $query_args['meta_query'];
			} else {
				$wp_query_args['meta_query'] = [
					'relation' => 'AND',
					$query_args['meta_query'],
					$wp_query_args['meta_query']
				];
			}
		}

		return $wp_query_args;
	}
}
