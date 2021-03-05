<?php
/**
 * Plugin generic functions file
 *
 * @package WP Blog and Widgets
 * @since 1.1.7
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Function to get post excerpt
 * 
 * @package WP Blog and Widgets
 * @since 1.1.7
 */
function wpbaw_get_post_excerpt( $post_id = null, $content = '', $word_length = '55', $more = '...' ) {
 
	$word_length 	= !empty($word_length) ? $word_length : '55';

	// If post id is passed
	if( !empty($post_id) ) {
		if (has_excerpt($post_id)) {
			$content = get_the_excerpt();
		} else {
			$content = !empty($content) ? $content : get_the_content();
		}
	}

	if( !empty($content) ) {
		$content = strip_shortcodes( $content ); // Strip shortcodes
		$content = wp_trim_words( $content, $word_length, $more );
	}

	return $content;
}

function wpbaw_pagination($args = array()){

	$big = 999999999; // need an unlikely integer

	$paging = apply_filters('wpbaw_blog_paging_args', array(
					'base'      => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
					'format'    => '?paged=%#%',
					'current'   => max( 1, $args['paged'] ),
					'total'     => $args['total'],
					'prev_next' => true,
					'prev_text' => __('« Previous', 'wp-blog-and-widgets'),
					'next_text' => __('Next »', 'wp-blog-and-widgets'),
				));
	
	echo paginate_links($paging);
}

/**
 * Function to get post featured image
 * 
 * @package WP Blog and Widgets
 * @since 1.1.7
 */
function wpbaw_get_post_featured_image( $post_id = '', $size = 'full' ) {
	$size 	= !empty($size) ? $size : 'full';
	$image = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), $size );

	if( !empty($image) ) {
		$image = isset($image[0]) ? $image[0] : '';
	}

	return $image;
}

/**
 * Sanitize Multiple HTML class
 * 
 * @package WP Blog and Widgets
 * @since 1.0.0
 */
function wpbaw_get_sanitize_html_classes($classes, $sep = " ") {
    $return = "";

    if( !is_array($classes) ) {
        $classes = explode($sep, $classes);
    }

    if( !empty($classes) ) {
        foreach($classes as $class){
            $return .= sanitize_html_class($class) . " ";
        }
        $return = trim( $return );
    }

    return $return;
}