<?php
/**
 * Plugin Name: WP Blog and Widget
 * Plugin URL: https://www.wponlinesupport.com/plugins/
 * Text Domain: wp-blog-and-widgets
 * Domain Path: /languages/
 * Description: Display Blog on your website with list and in grid view. Also work with Gutenberg shortcode block.
 * Version: 2.0
 * Author: WP OnlineSupport
 * Author URI: https://www.wponlinesupport.com/
 * Contributors: WP OnlineSupport
*/

if( !defined( 'WPBAW_VERSION' ) ) {
	define( 'WPBAW_VERSION', '2.0' ); // Version of plugin
}
if( !defined( 'WPBAW_DIR' ) ) {
	define( 'WPBAW_DIR', dirname( __FILE__ ) ); // Plugin dir
}
if( !defined( 'WPBAW_URL' ) ) {
	define( 'WPBAW_URL', plugin_dir_url( __FILE__ ) ); // Plugin url
}
if( !defined( 'WPBAW_PLUGIN_BASENAME' ) ) {
	define( 'WPBAW_PLUGIN_BASENAME', plugin_basename( __FILE__ ) ); // Plugin base name
}
if( !defined( 'WPBAW_POST_TYPE' ) ) {
	define( 'WPBAW_POST_TYPE', 'blog_post' ); // Plugin post type
}
if( !defined( 'WPBAW_CAT' ) ) {
	define( 'WPBAW_CAT', 'blog-category' ); // Plugin category name
}
if(!defined( 'WPBAW_PLUGIN_LINK' ) ) {
	define( 'WPBAW_PLUGIN_LINK', 'https://www.wponlinesupport.com/wp-plugin/wp-blog-and-widgets/?utm_source=WP&utm_medium=WP-Plugins&utm_campaign=Features-PRO#fndtn-lifetime' ); // Plugin link
}

/**
 * Load Text Domain and do stuff once all plugin is loaded
 * This gets the plugin ready for translation
 * 
 * @package WP Blog and Widgets
 * @since 1.0.0
 */
function wpbaw_blog_load_textdomain() {

	global $wp_version;

	// Set filter for plugin's languages directory
	$wpbaw_pro_lang_dir = dirname( plugin_basename( __FILE__ ) ) . '/languages/';
	$wpbaw_pro_lang_dir = apply_filters( 'wpbaw_languages_directory', $wpbaw_pro_lang_dir );

	// Traditional WordPress plugin locale filter.
	$get_locale = get_locale();

	if ( $wp_version >= 4.7 ) {
		$get_locale = get_user_locale();
	}

	// Traditional WordPress plugin locale filter
	$locale = apply_filters( 'plugin_locale',  $get_locale, 'wp-blog-and-widgets' );
	$mofile = sprintf( '%1$s-%2$s.mo', 'wp-blog-and-widgets', $locale );

	// Setup paths to current locale file
	$mofile_global  = WP_LANG_DIR . '/plugins/' . basename( WPBAW_DIR ) . '/' . $mofile;

	if ( file_exists( $mofile_global ) ) { // Look in global /wp-content/languages/plugin-name folder
		load_textdomain( 'wp-blog-and-widgets', $mofile_global );
	} else { // Load the default language files
		load_plugin_textdomain( 'wp-blog-and-widgets', false, $wpbaw_pro_lang_dir );
	}
}
add_action('plugins_loaded', 'wpbaw_blog_load_textdomain');

/**
 * Activation Hook
 * 
 * Register plugin activation hook.
 * 
 * @package WP Blog and Widgets
 * @since 1.0.0
 */
register_activation_hook( __FILE__, 'wpbaw_blog_install' );

/**
 * Deactivation Hook
 * 
 * Register plugin deactivation hook.
 * 
 * @package WP Blog and Widgets
 * @since 1.0.0
 */
register_deactivation_hook( __FILE__, 'wpbaw_blog_uninstall');

/**
 * Plugin Setup (On Activation)
 * 
 * Does the initial setup,
 * stest default values for the plugin options.
 * 
 * @package WP Blog and Widgets
 * @since 1.0.0
 */
function wpbaw_blog_install(){

	wpbaw_register_post_type();
	wpbaw_register_taxonomies();

	// IMP need to flush rules for custom registered post type
	flush_rewrite_rules();

	if( is_plugin_active('wp-blog-and-widgets-pro/wp-blog-and-widgets.php') ){
	 	add_action('update_option_active_plugins', 'wpbaw_blog_deactivate_version');
	}
}

/**
 * Plugin Setup (On Deactivation)
 * 
 * Delete  plugin options.
 * 
 * @package WP Blog and Widgets
 * @since 1.0.0
 */
function wpbaw_blog_uninstall() {

	// IMP need to flush rules for custom registered post type
	flush_rewrite_rules();
}

/**
 * Deactivate free plugin
 * 
 * @package WP Blog and Widgets
 * @since 1.0.0
 */
function wpbaw_blog_deactivate_version(){
   deactivate_plugins('wp-blog-and-widgets-pro/wp-blog-and-widgets.php',true);
}

/**
 * Function to display admin notice of activated plugin.
 * 
 * @package WP Blog and Widgets
 * @since 1.0.0
 */
function wpbaw_blog_admin_notice() {

	global $pagenow;

	$dir = ABSPATH . 'wp-content/plugins/wp-blog-and-widgets-pro/wp-blog-and-widgets.php';
	$notice_link        = add_query_arg( array('message' => 'wpbawh-plugin-notice'), admin_url('plugins.php') );
	$notice_transient   = get_transient( 'wpbawh_install_notice' );

	if( $notice_transient == false && $pagenow == 'plugins.php' && file_exists( $dir ) && current_user_can( 'install_plugins' ) ) {        
		echo '<div class="updated notice" style="position:relative;">
			<p>
				<strong>'.sprintf( __('Thank you for activating %s', 'wp-blog-and-widgets'), 'WP Blog and Widget').'</strong>.<br/>
				'.sprintf( __('It looks like you had PRO version %s of this plugin activated. To avoid conflicts the extra version has been deactivated and we recommend you delete it.', 'wp-blog-and-widgets'), '<strong>(<em>WP Blog and Widget Pro</em>)</strong>' ).'
			</p>
			<a href="'.esc_url( $notice_link ).'" class="notice-dismiss" style="text-decoration:none;"></a>
		</div>';
	}
}

// Action to display notice
add_action( 'admin_notices', 'wpbaw_blog_admin_notice');

//Blog Admin Class File
require_once( WPBAW_DIR . '/includes/admin/class-wpbaw-admin.php' );

//Custom Post Type File
require_once( WPBAW_DIR . '/includes/wpbaw-post-types.php' );

//Function File
require_once( WPBAW_DIR . '/includes/class-wpbaw-script.php' );

//Function File
require_once( WPBAW_DIR . '/includes/wpbaw-functions.php' );

//Blog Shortcode File
require_once( WPBAW_DIR . '/includes/shortcode/wpbaw-blog-shortcode.php' );
require_once( WPBAW_DIR . '/includes/shortcode/wpbaw-recent-blog-shortcode.php' );

//Blog Widget File
require_once( WPBAW_DIR . '/includes/widgets/class-wpbaw-blog.php' );

// Gutenberg Block Initializer
if ( function_exists( 'register_block_type' ) ) {
	require_once( WPBAW_DIR . '/includes/admin/supports/gutenberg-block.php' );
}

/* Recommended Plugins Starts */
if ( is_admin() ) {
	require_once( WPBAW_DIR . '/wpos-plugins/wpos-recommendation.php' );

	wpos_espbw_init_module( array(
							'prefix'	=> 'wpbaw',
							'menu'		=> 'edit.php?post_type='.WPBAW_POST_TYPE,
							'position'	=> 5,
						));
}
/* Recommended Plugins Ends */

/* Plugin Wpos Analytics Data Starts */
function wpos_analytics_anl22_load() {

	require_once dirname( __FILE__ ) . '/wpos-analytics/wpos-analytics.php';

	$wpos_analytics = wpos_anylc_init_module( array(
							'id'			=> 22,
							'file'			=> plugin_basename( __FILE__ ),
							'name'			=> 'WP Blog and Widget',
							'slug'			=> 'wp-blog-and-widget',
							'type'			=> 'plugin',
							'menu'			=> 'edit.php?post_type=blog_post',
							'text_domain'	=> 'wp-blog-and-widgets',
						));

	return $wpos_analytics;
}

// Init Analytics
wpos_analytics_anl22_load();
/* Plugin Wpos Analytics Data Ends */