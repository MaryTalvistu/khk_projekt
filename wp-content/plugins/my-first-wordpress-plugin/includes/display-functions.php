
<?php

/*our display functions for outputting information*/

function mfwp_add_content($content) {

	global $mfwp_options;

	if(is_singular() && $mfwp_options["enable"] == true ) {
		$extra_content = sprintf('<p class="twitter-message %s">%s <a href="%s" target="_blank">%s</a> %s<br>%s</p>', $mfwp_options["theme"], __('Follow', 'my-first-wordpress-plugin'), esc_url($mfwp_options["twitter_url"]), esc_html($mfwp_options["twitter_name"]), __('on Twitter:', 'my-first-wordpress-plugin'), wp_kses_post($mfwp_options["twitter_bio"]) );
		$content .= $extra_content;
	}
	return $content;
}
add_filter('the_content', 'mfwp_add_content');