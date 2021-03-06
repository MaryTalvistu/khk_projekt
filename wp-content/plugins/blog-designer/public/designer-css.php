<?php
/**
 * Dynamic css
 *
 * @version 1.0
 * @package Blog Designer
 */

$settings                       = get_option( 'wp_blog_designer_settings' ); //phpcs:disable
$background                     = ( isset( $settings['template_bgcolor'] ) && '' != $settings['template_bgcolor'] ) ? $settings['template_bgcolor'] : '';
$templatecolor                  = ( isset( $settings['template_color'] ) && '' != $settings['template_color'] ) ? $settings['template_color'] : '';
$grid_hoverback_color           = ( isset( $settings['grid_hoverback_color'] ) && '' != $settings['grid_hoverback_color'] ) ? $settings['grid_hoverback_color'] : '';
$color                          = ( isset( $settings['template_ftcolor'] ) && '' != $settings['template_ftcolor'] ) ? $settings['template_ftcolor'] : '';
$linkhover                      = ( isset( $settings['template_fthovercolor'] ) && '' != $settings['template_fthovercolor'] ) ? $settings['template_fthovercolor'] : '';
$titlecolor                     = ( isset( $settings['template_titlecolor'] ) && '' != $settings['template_titlecolor'] ) ? $settings['template_titlecolor'] : '';
$contentcolor                   = ( isset( $settings['template_contentcolor'] ) && '' != $settings['template_contentcolor'] ) ? $settings['template_contentcolor'] : '';
$readmorecolor                  = ( isset( $settings['template_readmorecolor'] ) && '' != $settings['template_readmorecolor'] ) ? $settings['template_readmorecolor'] : '';
$readmorebackcolor              = ( isset( $settings['template_readmorebackcolor'] ) && '' != $settings['template_readmorebackcolor'] ) ? $settings['template_readmorebackcolor'] : '';
$alterbackground                = ( isset( $settings['template_alterbgcolor'] ) && '' != $settings['template_alterbgcolor'] ) ? $settings['template_alterbgcolor'] : '';
$titlebackcolor                 = ( isset( $settings['template_titlebackcolor'] ) && '' != $settings['template_titlebackcolor'] ) ? $settings['template_titlebackcolor'] : '';
$social_icon_style              = get_option( 'social_icon_style' ); //phpcs:enable
$template_alternativebackground = get_option( 'template_alternativebackground' );
$template_titlefontsize         = get_option( 'template_titlefontsize' );
$content_fontsize               = get_option( 'content_fontsize' );
$custom_css                     = get_option( 'custom_css' );
?>
<style type="text/css">
	/**
	 * Table of Contents
	 *
	 * 1.0 - Pagination
	 * 2.0 - Social Media Icon
	 * 3.0 - Default Blog Template
	 * 4.0 - Classical Template
	 * 5.0 - Light Breeze Template
	 * 6.0 - Spektrum Template
	 * 7.0 - Evolution Template
	 * 8.0 - Timeline Template
	 * 9.0 - News Template
	 *
	 */
	/**
	 * 1.0 - Pagination
	 */
	.bd_pagination_box.wl_pagination_box .paging-navigation ul.page-numbers li a.page-numbers:hover,
	.bd_pagination_box.wl_pagination_box .paging-navigation ul.page-numbers li > span.current {
		<?php echo ( '' != $readmorebackcolor ) ? 'background-color: ' . esc_attr( $readmorebackcolor ) . ';' : ''; //phpcs:ignore ?>
		<?php echo ( '' != $readmorecolor ) ? 'color: ' . esc_attr( $readmorecolor ) . ';' : ''; //phpcs:ignore ?>
		<?php echo ( '' != $content_fontsize ) ? 'font-size:' . esc_attr( $content_fontsize ) . 'px;' : ''; //phpcs:ignore ?>
	}
	.bd_pagination_box.wl_pagination_box .paging-navigation ul.page-numbers li a.page-numbers {
		<?php echo ( '' != $readmorecolor ) ? 'background-color: ' . esc_attr( $readmorecolor ) . ';' : ''; //phpcs:ignore ?>
		<?php echo ( '' != $readmorebackcolor ) ? 'color: ' . esc_attr( $readmorebackcolor ) . ';' : ''; //phpcs:ignore ?>
		<?php echo ( '' != $content_fontsize ) ? 'font-size:' . esc_attr( $content_fontsize ) . 'px;' : ''; //phpcs:ignore ?>
	}
	.bd_pagination_box.wl_pagination_box .paging-navigation ul.page-numbers li a.page-numbers.dots {
		<?php echo ( '' != $content_fontsize ) ? 'font-size:' . esc_attr( $content_fontsize ) . 'px !important;' : ''; //phpcs:ignore ?>
	}
	/**
	 * 2.0 - Social Media Icon
	 */    
	.bdp_blog_template .social-component a {
		<?php
		if ( 0 == $social_icon_style ) { //phpcs:ignore
			echo 'border-radius: 100%;';
		}
		?>
	}
	/**
	 * 3.0 - Default Blog Template
	 */
	<?php if ( '' != $titlebackcolor ) { //phpcs:ignore ?>
		.bdp_blog_template.nicy .blog_header h2,
		.blog_template.crayon_slider .blog_header h2,
		.glossary .blog_header h2,
		.bdp_blog_template .bd-blog-header h2 {        
			<?php echo 'background: ' . esc_attr( $titlebackcolor ); ?>
		}
	<?php } ?>
	.blog_template .bd-more-tag-inline {
		<?php echo ( '' != $readmorecolor ) ? 'color: ' . esc_attr( $readmorecolor ) . ' !important;' : ''; //phpcs:ignore ?>
		<?php echo ( '' != $content_fontsize ) ? 'font-size:' . esc_attr( $content_fontsize ) . 'px;' : ''; //phpcs:ignore ?>
	}
	<?php if ( '' != $titlecolor || '' != $template_titlefontsize ) { //phpcs:ignore ?>
		.bdp_blog_template .blog_header h2,
		.bdp_blog_template .blog_header h2 a,
		.bdp_blog_template .bd-blog-header h2,
		.bdp_blog_template .bd-blog-header h2 a,
		.bdp_blog_template .bd_ad_container h4, 
		.blog_template .bd_ad_container h4 {
			<?php echo ( '' != $titlecolor ) ? 'color: ' . esc_attr( $titlecolor ) . ' !important;' : ''; //phpcs:ignore ?>
			<?php echo ( '' != $template_titlefontsize ) ? 'font-size: ' . esc_attr( $template_titlefontsize ) . 'px;' : ''; //phpcs:ignore ?>
		}
	<?php } ?>
	.post_content-inner p,
	.bd-post-content {
		<?php echo ( '' != $contentcolor ) ? 'color:' . esc_attr( $contentcolor ) . ';' : ''; //phpcs:ignore ?>
		<?php echo ( '' != $content_fontsize ) ? 'font-size:' . esc_attr( $content_fontsize ) . 'px;' : ''; //phpcs:ignore ?>
	}
	.bdp_blog_template .bd-metacats,
	.bd-tags,
	span.bd-category-link,
	.bdp_blog_template .author,
	.bdp_blog_template .date,
	.bdp_blog_template .bd-categories,
	.bdp_blog_template.evolution .bd-categories a,
	.bdp_blog_template .bd-categories a,
	.bd-meta-data-box .bd-metacats,
	.bd-meta-data-box .bd-metacomments,
	.bd-meta-data-box .bd-metacomments span,
	.bdp_blog_template .date i, .bdp_blog_template .author i, .bdp_blog_template .comment i,
	.bd-tags a,
	span.bd-category-link a,
	.bd-metadatabox p,
	span .bd-link-label,
	.bdp_blog_template .blog_footer span,
	.bd-metacomments i,
	.date_wrap i,
	.bdp_blog_template a,
	.bdp_blog_template .post_content,
	.bd-categories i,
	.bdp_blog_template.spektrum .post-bottom .post-by,
	.bdp_blog_template.spektrum .post-bottom .bd-tags,
	.bd-metadatabox,
	.bdp_blog_template.news .bd-blog-header .bd-metadatabox a,
	.bdp_blog_template.news .post-content-div .post_cat_tag > span i,
	.bdp_blog_template .bd-meta-data-box .bd-metadate, .bdp_blog_template .bd-meta-data-box .bd-metauser, .bdp_blog_template .bd-meta-data-box .bd-metacats,
	.bd-meta-data-box .bd-metacats a,
	.bd-meta-data-box .bd-metacomments a,
	.bdp_blog_template.box-template .bd-tags,
	.bdp_blog_template .datetime,
	.bdp_blog_template .link-lable,
	.bdp_blog_template .post-date
	.bdp_blog_template .bd_ad_container p, .blog_template .bd_ad_container {
		<?php echo ( '' != $content_fontsize ) ? 'font-size:' . esc_attr( $content_fontsize ) . 'px;' : ''; //phpcs:ignore ?>
	}
	.bdp_blog_template .date,
	.bdp_blog_template .comment,
	.bdp_blog_template .author .bd-icon-author,
	.bdp_blog_template .author,
	.bdp_blog_template.news .post-content-div .post_cat_tag > span i ,
	.bdp_blog_template .post-by,
	.bdp_blog_template .bd-categories i,
	.bdp_blog_template .bd-metacats i,
	.bdp_blog_template .category-link i,
	.bdp_blog_template .bd-tags i,
	.bdp_blog_template .bd-metacomments,
	.bdp_blog_template .date_wrap i,
	.bdp_blog_template .post-date,
	.bdp_blog_template .link-lable,
	.bdp_blog_template .datetime,
	.bdp_blog_template.glossary .post-author span,
	.bdp_blog_template .post_content-inner p,
	.bdp_blog_template .post_content,
	.bdp_blog_template .tags i {
		<?php echo ( '' != $contentcolor ) ? 'color:' . esc_attr( $contentcolor ) . ';' : ''; //phpcs:ignore ?>
	}
	.bdp_blog_template .bd-meta-data-box {
		<?php echo ( '' != $contentcolor ) ? 'color:' . esc_attr( $contentcolor ) . ';' : ''; //phpcs:ignore ?>
	}
	.bdp_blog_template .bd-meta-data-box i {
		<?php echo ( '' != $titlecolor ) ? 'color: ' . esc_attr( $titlecolor ) . ';' : ''; //phpcs:ignore ?>
	}
	<?php if ( '' != $contentcolor ) { //phpcs:ignore ?>
		.bd-metadatabox {
			<?php echo ( '' != $contentcolor ) ? 'color:' . esc_attr( $contentcolor ) . ';' : ''; //phpcs:ignore ?>
		}
	<?php } ?>
	.bd-link-label {
		<?php echo ( '' != $contentcolor ) ? 'color:' . esc_attr( $contentcolor ) . ';' : ''; //phpcs:ignore ?>
	}
	.bdp_blog_template a.bd-more-tag {
		<?php echo ( '' != $readmorebackcolor ) ? 'background-color: ' . esc_attr( $readmorebackcolor ) . '!important;' : ''; //phpcs:ignore ?>
		<?php echo ( '' != $readmorecolor ) ? 'color: ' . esc_attr( $readmorecolor ) . '!important;' : ''; //phpcs:ignore ?>
	}
	<?php if ( '' != $readmorebackcolor || '' != $readmorecolor ) { //phpcs:ignore ?>
		.bdp_blog_template a.bd-more-tag:hover {
			<?php echo ( '' != $readmorecolor ) ? 'background-color: ' . esc_attr( $readmorecolor ) . '!important;' : ''; //phpcs:ignore ?>
			<?php echo ( '' != $readmorebackcolor ) ? 'color: ' . esc_attr( $readmorebackcolor ) . '!important;' : ''; //phpcs:ignore ?>
		}
	<?php } ?>
	.bdp_blog_template i {
		font-style: normal !important;
	}
	<?php if ( '' != $color ) { //phpcs:ignore ?>
		.bd-tags,
		span.bd-category-link,
		.bdp_blog_template .bd-categories,
		.bd-meta-data-box .bd-metacats,
		.bd-meta-data-box .bd-metacats a,
		.bd-meta-data-box .bd-metacomments a,
		.bdp_blog_template .bd-categories a,
		.bd-post-content a,
		.bd-tags a,
		span.bd-category-link a,
		.bdp_blog_template a {
			color:<?php echo esc_attr( $color ); ?> !important;
			font-weight: normal !important;
		}
	<?php } ?>
	<?php if ( '' != $linkhover ) { //phpcs:ignore ?>
		.bd-meta-data-box .bd-metacats a:hover,
		.bd-meta-data-box .bd-metacomments a:hover,
		.bdp_blog_template .bd-categories a:hover,
		.spektrum .post-bottom .bd-categories a:hover,
		.bd-post-content a:hover,
		.bd-tags a:hover,
		span.bd-category-link a:hover,
		.bdp_blog_template a:hover,
		.bd-post-content a:hover {
			color:<?php echo esc_attr( $linkhover ); ?> !important;
		}
	<?php } ?>
	<?php if ( '' != $background ) { //phpcs:ignore ?>
		.bdp_blog_template.evolution,
		.bdp_blog_template.lightbreeze {
			background: <?php echo esc_attr( $background ); ?>;
		}
	<?php } ?>
	<?php
	if ( 0 == $template_alternativebackground ) { //phpcs:ignore
		if ( '' != $alterbackground ) { //phpcs:ignore
			?>
			.bdp_blog_template.evolution.alternative-back,
			.bdp_blog_template.lightbreeze.alternative-back {
				background: <?php echo esc_attr( $alterbackground ); ?>;
			}
		<?php } else { ?>
			.bdp_blog_template.evolution.alternative-back,
			.bdp_blog_template.lightbreeze.alternative-back {
				background: transparent;
			}
			<?php
		}
	}
	?>
	/**
	 * 4.0 - Classical Template
	 */
	.bdp_blog_template.classical .bd-blog-header .bd-tags {
		<?php echo ( '' != $color ) ? 'color: ' . esc_attr( $color ) . ';' : ''; //phpcs:ignore ?>
	}
	/**
	 * 5.0 - Light Breeze Template
	 */
	/**
	 * 6.0 - Spektrum Template
	 */
	.bdp_blog_template.spektrum .bd-blog-header {
		<?php echo ( '' != $titlebackcolor ) ? 'background:' . esc_attr( $titlebackcolor ) . ';' : ''; //phpcs:ignore ?>
	}
	<?php if ( '' != $titlecolor ) { //phpcs:ignore ?>
		.spektrum .date {
			background-color: <?php echo esc_attr( $titlecolor ); ?>;
		}
		<?php
	}
	if ( '' != $color ) { //phpcs:ignore
		?>
		.spektrum .post-bottom .bd-categories a {
			color: <?php echo esc_attr( $color ); ?>;
		}
		<?php
	}
	if ( '' != $readmorecolor ) { //phpcs:ignore
		?>
		.spektrum .details a {
			color :<?php echo esc_attr( $readmorecolor ); ?> !important;
			<?php echo ( '' != $content_fontsize ) ? 'font-size:' . esc_attr( $content_fontsize ) . 'px;' : ''; //phpcs:ignore ?>
		}
		<?php
	}
	if ( '' != $readmorebackcolor ) { //phpcs:ignore
		?>
		.spektrum .details a:hover {
			color :<?php echo esc_attr( $readmorebackcolor ); ?>;
		}
	<?php } ?>
	/**
	 * 7.0 - Evolution Template
	 */
	/**
	 * 8.0 - Timeline Template
	 */
	.timeline_bg_wrap:before {
		background: none repeat scroll 0 0 <?php echo esc_attr( $templatecolor ); ?>;
	}
	.bd-datetime {
		background: none repeat scroll 0 0 <?php echo esc_attr( $templatecolor ); ?>;
	}
	.bdp_blog_template.timeline .post_hentry > p > i {
		<?php echo ( '' != $templatecolor ) ? 'background: ' . esc_attr( $templatecolor ) . ';' : ''; //phpcs:ignore ?>
	}
	.bdp_blog_template.timeline:nth-child(2n+1) .post_content_wrap:before,
	.bdp_blog_template.timeline:nth-child(2n+1) .post_content_wrap:after {
		border-left: 8px solid <?php echo esc_attr( $templatecolor ); ?>;
	}
	.rtl .bdp_blog_template.timeline:nth-child(2n+1) .post_content_wrap:before,
	.rtl .bdp_blog_template.timeline:nth-child(2n+1) .post_content_wrap:after {
		border-right: 8px solid <?php echo esc_attr( $templatecolor ); ?>;
	}
	.bdp_blog_template.timeline:nth-child(2n) .post_content_wrap:before,
	.bdp_blog_template.timeline:nth-child(2n) .post_content_wrap:after {
		border-right: 8px solid <?php echo esc_attr( $templatecolor ); ?>;
	}
	.rtl .bdp_blog_template.timeline:nth-child(2n) .post_content_wrap:before,
	.rtl .bdp_blog_template.timeline:nth-child(2n) .post_content_wrap:after {
		border-left: 8px solid <?php echo esc_attr( $templatecolor ); ?>;
	}
	.post_content_wrap {
		border:1px solid <?php echo esc_attr( $templatecolor ); ?>;
	}
	.bdp_blog_template .post_content_wrap .blog_footer {
		border-top: 1px solid <?php echo esc_attr( $templatecolor ); ?> ;
	}
	.bdp_blog_template .post-icon {
		<?php echo ( '' != $titlebackcolor ) ? 'background:' . esc_attr( $titlebackcolor ) . ';' : ''; //phpcs:ignore ?>
	}
	.bdp_blog_template.timeline .desc h3 a{
		<?php echo ( '' != $titlecolor ) ? 'color: ' . esc_attr( $titlecolor ) . ' !important;' : ''; //phpcs:ignore ?>
		<?php echo ( '' != $titlebackcolor ) ? 'background:' . esc_attr( $titlebackcolor ) . ' !important;' : ''; //phpcs:ignore ?>
		<?php echo ( '' != $template_titlefontsize ) ? 'font-size: ' . esc_attr( $template_titlefontsize ) . 'px;' : ''; //phpcs:ignore ?>
	}
	/**
	 * 9.0 - News Template
	 */
	<?php if ( '' != $titlecolor || '' != $template_titlefontsize ) { //phpcs:ignore ?>
		.bdp_blog_template.news .bd-blog-header h2.title a{
			<?php echo ( '' != $titlecolor ) ? 'color: ' . esc_attr( $titlecolor ) . ';' : ''; //phpcs:ignore ?>
			<?php echo ( '' != $template_titlefontsize ) ? 'font-size: ' . esc_attr( $template_titlefontsize ) . 'px;' : ''; //phpcs:ignore ?>
		}
	<?php } ?>
	.blog_template.boxy-clean .blog_header h2,
	.bdp_blog_template.news .bd-blog-header h2.title{
		<?php
		if ( '' != $titlebackcolor ) { //phpcs:ignore
			echo 'background: ' . esc_attr( $titlebackcolor );
		}
		?>
	}
	.bdp_blog_template.news a.bd-more-tag{
		float: right !important;
	}
	.blog_template.boxy-clean ul li:hover,
	.blog_template.boxy-clean ul li:hover .blog_footer.blog_footer,
	.blog_template.boxy-clean ul li:hover .blog_div,
	.blog_template.boxy-clean ul li:hover .blog_header h2 {
		background: <?php echo esc_attr( $grid_hoverback_color ); ?>;
	}
	.glossary .post_content-inner {
		<?php echo ( '' != $templatecolor ) ? 'border-color: ' . esc_attr( $templatecolor ) . ';' : ''; //phpcs:ignore ?>
	}
	.blog_template.boxy-clean .blog_wrap.bdp_blog_template .author {
		<?php echo ( '' != $templatecolor ) ? 'background: ' . esc_attr( $templatecolor ) . ';' : ''; //phpcs:ignore ?>
	}
	<?php echo wp_unslash( $custom_css ); //phpcs:ignore ?>
</style>
