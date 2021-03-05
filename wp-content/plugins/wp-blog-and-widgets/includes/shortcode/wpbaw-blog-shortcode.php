<?php
/**
 * 'blog' Shortcode
 * 
 * @package WP Blog and Widgets
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function wpbaw_get_blog_post( $atts, $content = null ){

	// Shortcode Parameter
	extract(shortcode_atts(array(
		'limit'                 => 10,
		'category'              => '',
		'grid'                  => 0,
		'show_date'             => 'true',
		'show_category_name'    => 'true',
		'show_author'           => 'true',
		'show_content'          => 'true',
		'show_full_content'     => 'false',
		'content_words_limit'   => 20,
		'pagination_type'       => 'numeric',
		'order'					=> 'DESC',
		'orderby'				=> 'date',
		'extra_class'			=> '',
		'className'				=> '',
		'align'					=> '',
	), $atts));

	// Define variables
	$limit 					= !empty( $limit ) 						? $limit 						: 10;
	$category 				= !empty($category) 					? explode(',', $category) 		: '';
	$grid 					= !empty( $grid ) 						? $grid 						: 0;
	$show_date 				= ( $show_date == 'true' ) 				? 1 							: 0;
	$show_category_name 	= ( $show_category_name == 'true' )		? 1 							: 0;
	$show_content 			= ( $show_content == 'true' ) 			? 1 							: 0;
	$show_author   			= ( $show_author == 'true' ) 			? 1 							: 0;
	$show_full_content   	= ( $show_full_content == 'true' ) 		? 1 							: 0;
	$content_words_limit 	= !empty( $content_words_limit ) 		? $content_words_limit 			: 20;
	$pagination_type   		= ( $pagination_type == 'numeric' ) 	? 'numeric' 					: 'next-prev';
	$order 					= ( strtolower( $order ) == 'asc' ) 	? 'ASC' 						: 'DESC';
	$orderby 				= !empty( $orderby )					? $orderby 						: 'date';
	$align					= ! empty( $align )						? 'align'.$align				: '';
	$extra_class			= $extra_class .' '. $align .' '. $className;
	$extra_class			= wpbaw_get_sanitize_html_classes( $extra_class );

	ob_start();

	// Taking some globals
	global $post;

	// Pagination Variable
	$paged = 1;
	if ( get_query_var('paged') ) {
		$paged = get_query_var('paged');
	} else if ( get_query_var('page') ) {
		$paged = get_query_var('page');
	}

	// Taking some variables
	$count = 0;

	// Query Parameters
	$args = array (
		'post_type'      => WPBAW_POST_TYPE,
		'orderby'        => $orderby, 
		'order'          => $order,
		'posts_per_page' => $limit,
		'paged'          => $paged,
	);

	// Category Parameter
	if($category != ""){
		$args['tax_query'] = array(
			array( 
				'taxonomy' 	=> WPBAW_CAT,
				'field' 	=> 'term_id',
				'terms' 	=> $category
			)
		 );
	}

	// WP Query
	$query = new WP_Query($args);

	// Taking template variables
	$post_count = $query->post_count; ?>

	<div class="blogfree-plugin blog-clearfix <?php echo $extra_class; ?>">
	<?php  
		// If post is there  
		if ( $query->have_posts() ) : while ( $query->have_posts() ) : $query->the_post();

		$count++;

		// Category Name
		$terms = get_the_terms( $post->ID, WPBAW_CAT );
		$news_links = array();

		if($terms){
			foreach ( $terms as $term ) {
				$term_link = get_term_link( $term );
				$news_links[] = '<a href="' . esc_url( $term_link ) . '">'.$term->name.'</a>';
			}
		}

		$cate_name = join( ", ", $news_links );

		$feat_image = wpbaw_get_post_featured_image( $post->ID, 'large' );
		$post_title = get_the_title();

		// CSS first and last class
		$css_class = 'wpbaw-blog-class';

		if ( ( is_numeric( $grid ) && ( $grid > 0 ) && ( 0 == ($count - 1) % $grid ) ) || 1 == $count ) { $css_class .= ' wpbaw-first'; }
		if ( ( is_numeric( $grid ) && ( $grid > 0 ) && ( 0 == $count % $grid ) ) || $post_count == $count ) { $css_class .= ' wpbaw-last'; }

		if($show_date){ $date_class = "has-date";}else{$date_class = "has-no-date";} ?>

		<div id="post-<?php the_ID(); ?>" class="blog type-blog blog-clearfix <?php echo ($feat_image) ? "has-thumb" : "no-thumb";?> blog-col-<?php echo $grid.' '.$css_class.' '.$date_class; ?>">
			<div class="blog-inner-wrap-view blog-clearfix">
				<?php if ( $feat_image ) { ?>
					<div class="blog-thumb">
						<div class="grid-blog-thumb">
							<a href="<?php the_permalink(); ?>"><img class="wpbaw-blog-img" src="<?php echo esc_url($feat_image); ?>" alt="<?php echo $post_title; ?>"/></a>
						</div>
					</div>
				<?php } ?>
				<div class="blog-content">
					<?php if($grid == '1') {
						if($show_date){?>
							<div class="date-post">
								<h2><span><?php echo get_the_date('j'); ?></span></h2>
								<p><?php echo get_the_date('M y'); ?></p>
							</div>
						 <?php } ?>
					<?php } else { ?>
						<div class="grid-category-post">
							<?php echo ($show_category_name && $cate_name != '') ? $cate_name : ""?>
						</div>
						<?php if($show_author || $show_date){ ?>
							<div class="blog-author">
								<?php if($show_author) {?>
									<span>
										<?php esc_html_e( 'By', 'wp-blog-and-widgets' ); ?> <a href="<?php echo esc_url( get_author_posts_url( $post->post_author ) ); ?>" target="_self"><?php echo get_the_author(); ?></a>
									</span>
								<?php }?>
								<?php echo ($show_author && $show_date) ? '/' : '' ?>
								<?php echo ($show_date)? get_the_date() : "" ;?>
							</div>
						<?php } 
					}?>
					<div class="post-content-text">
						<?php if($grid == '1'){ ?>
							<div class="grid-1-date">
								<?php echo ($show_date)? get_the_date() : "" ;?>
							</div>
						<?php } ?>
						<?php if($post_title) { ?>
							<h3 class="blog-title"><a href="<?php the_permalink(); ?>" rel="bookmark"><?php echo $post_title; ?></a></h3>
						<?php } ?>
						<?php if($grid == '1'){ ?>
							<div class="blog-cat">
							  <?php if($show_author) { ?> <span class="grid-1-author"><?php _e( 'By', 'wp-blog-and-widgets' ); ?> <a href="<?php echo esc_url( get_author_posts_url( $post->post_author ) ); ?>" target="_self"><?php echo get_the_author(); ?></a></span>  <?php } echo ($show_author && $show_category_name) ? '/' : '' ?> <?php if($show_category_name) { echo $cate_name; } ?>
							</div>
						<?php }?>
						<?php if($show_content){ ?>
							<div class="blog-content-excerpt">
								<?php  if( !$show_full_content ) { ?>
									<p class="blog-short-content"><?php echo wpbaw_get_post_excerpt( $post->ID, get_the_content(), $content_words_limit ); ?></p>
									<a href="<?php the_permalink(); ?>" class="blog-more-link"> <?php _e( 'Read More', 'wp-blog-and-widgets' ); ?></a>
								<?php } else {
									echo get_the_content();
								} ?>
							</div>
						<?php }?>
					</div>
				</div>
			</div>
		</div>
		<?php endwhile; 
		endif; ?>
		<div class="blog_pagination blog-clearfix">
			<?php if($pagination_type == 'numeric'){
				echo wpbaw_pagination( array( 'paged' => $paged , 'total' => $query->max_num_pages ) ); } else { ?>
				<div class="button-blog-p"><?php next_posts_link( ' Next >>', $query->max_num_pages ); ?></div>
				<div class="button-blog-n"><?php previous_posts_link( '<< Previous' ); ?> </div>
			<?php } ?>
		</div>
	</div>
	<?php
	wp_reset_postdata(); 
	return ob_get_clean();
}

// Blog Shortcode
add_shortcode('blog','wpbaw_get_blog_post');