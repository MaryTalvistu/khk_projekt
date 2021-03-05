<?php
/**
 * Admin Class
 *
 * Handles the admin functionality of plugin
 *
 * @package WP Blog and Widget
 * @since 1.3.2
 */

if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Wpbaw_Admin {

	function __construct() {

		// Action to add admin menu
		add_action( 'admin_menu', array($this, 'wpbaw_register_menu'), 12 );

		// Init Processes
		add_action( 'admin_init', array($this, 'wpbaw_admin_init_process') );

		// Get Posts Filter
		add_filter( 'pre_get_posts', array($this, 'wpbaw_blog_display_tags') );

		// Manage Category Shortcode Columns
		add_filter("manage_edit-".WPBAW_CAT."_columns", array($this, 'blog_category_manage_columns')); 
		add_filter("manage_".WPBAW_CAT."_custom_column", array($this, 'blog_category_columns'), 10, 3);
	}

	/**
	 * Function to add menu
	 * 
	 * @package WP Blog and Widget
	 * @since 1.3.2
	 */
	function wpbaw_register_menu() {

		// How it work page
		add_submenu_page( 'edit.php?post_type='.WPBAW_POST_TYPE, __('How it works, our plugins and offers', 'wp-blog-and-widgets'), __('How It Works', 'wp-blog-and-widgets'), 'manage_options', 'wpbawh-designs', array($this, 'wpbawh_designs_page') );

		// Plugin features menu
		add_submenu_page( 'edit.php?post_type='.WPBAW_POST_TYPE, __('Upgrade to PRO - WP Blog and Widget', 'wp-blog-and-widgets'), '<span style="color:#2ECC71">'.__('Upgrade to PRO', 'wp-blog-and-widgets').'</span>', 'edit_posts', 'wpbawh-premium', array($this, 'wpbaw_premium_page') );
	}

	/**
	 * Function to display plugin design HTML
	 * 
	 * @package WP Blog and Widget
	 * @since 2.0
	 */
	function wpbawh_designs_page() {
		include_once( WPBAW_DIR . '/includes/admin/wpbaw-how-it-work.php' );
	}

	/**
	 * Getting Started Page Html
	 * 
	 * @package WP Blog and Widget
	 * @since 1.3.2
	 */
	function wpbaw_premium_page() {
		include_once( WPBAW_DIR . '/includes/admin/settings/premium.php' );
	}

	/**
	 * Function to notification transient
	 * 
	 * @package WP Blog and Widget
	 * @since 1.3.2
	 */
	function wpbaw_admin_init_process() {
		// If plugin notice is dismissed
	    if( isset($_GET['message']) && $_GET['message'] == 'wpbawh-plugin-notice' ) {
	    	set_transient( 'wpbawh_install_notice', true, 604800 );
	    }
	}

	/**
	 * Function to blog display tags
	 * 
	 * @package WP Blog and Widget
	 * @since 1.3.2
	 */
	function wpbaw_blog_display_tags( $query ) {

		if( is_tag() && $query->is_main_query() ) {       
		   $post_types = array( 'post', 'blog_post' );
			$query->set( 'post_type', $post_types );
		}
	}

	/**
	 * Function to add category manage column
	 * 
	 * @package WP Blog and Widgets
	 * @since 1.0
	 */
	function blog_category_manage_columns($theme_columns) {
		$new_columns = array(
				'cb' => '<input type="checkbox" />',
				'name' => __('Name'),
				'blog_shortcode' => __( 'Blog Category Shortcode', 'wp-blog-and-widgets' ),
				'slug' => __('Slug'),
				'posts' => __('Posts')
				);
		return $new_columns;
	}

	/**
	 * Function to add category column
	 * 
	 * @package WP Blog and Widgets
	 * @since 1.0
	 */
	function blog_category_columns($out, $column_name, $theme_id) {
		$theme = get_term($theme_id, 'blog-category');
		switch ($column_name) {      

			case 'title':
				echo get_the_title();
			break;
			case 'blog_shortcode':

				 echo '[blog category="' . $theme_id. '"]';
				  echo '[recent_blog_post category="' . $theme_id. '"]';
			break;

			default:
				break;
		}
		return $out;
	}
}

$wpbaw_Admin = new Wpbaw_Admin();