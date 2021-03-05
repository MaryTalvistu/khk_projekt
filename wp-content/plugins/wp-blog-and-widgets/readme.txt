=== WP Blog and Widget  ===
Contributors: wponlinesupport, anoopranawat, pratik-jain
Tags: blog design, blog layout, wordpress blog , custom blog template, wordpress blog widget, blog layout design, custom blog layout, Free WordPress blog, blog custom post type, blog tab, blog menu, blog page with custom post type, blog, latest blog, custom post type, CPT, widget, wponlinesupport
Requires at least: 4.0
Tested up to: 5.6
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A quick, easy way to add a Blog custom post type, Blog widget to WordPress. Also, work with the Gutenberg shortcode block.

== Description ==

WP Blog and Widget plugin add a Blog custom post type,  blog widget to your WordPress site. WP Blog adds a Blog tab to your admin menu, which allows you to enter Blog posts just as you would regular posts.

> **Note:** WP Blog and Widget plugin is created with custom post type. If you are looking for a plugin that works with your WordPress existing  Post, please use our plugin [Blog Designer – Post and Widget](https://wordpress.org/plugins/blog-designer-for-post-and-widget/).

WP Blog and widgets, manage and display blog, date archives, widget on your website. You can display the latest blog post on your homepage/frontpage as well as in the inner page. 

Check [Demo and Features](https://demo.wponlinesupport.com/blog-demo/) for additional information.

View [Masonry Add-on](https://www.wponlinesupport.com/wordpress-plugin-addon/masonry-addon-wp-blog-widgets/) with 12 designs and 7 effects.

Also, work with the Gutenberg shortcode block.

**WordPress blog plugin contains two shortcodes**

<code>
1) Display Blog in a page (list view and grid view)
[blog limit="10"] 

2) Display Latest blog on Homepage/Frontpage in list view and grid view
[recent_blog_post limit="3" grid="3"]
</code>

= Important Note For How to Install =

* Install the Blog plugin and activate it.
* Plugin add BLOG tab in the WordPress admin menu side. Add some posts.
* Create a Blog page and add shortcode:
 
<code>[blog]</code>

* Also you can Display the blog post with category wise :

<code> Sports news [blog category="category_id"] </code>

* Display blog with Grid:

<code>[blog grid="2"] </code>

* **Complete shortcode example:**

<code>[blog limit="10" category="category_id" grid="2" show_author="false" show_content="true" show_full_content="true" show_category_name="true" show_date="false" content_words_limit="30" ]</code>

* Comments for the blog.

* Added Widget Options like Show Blog date, Show Blog Categories, Select Blog Categories.

* If you want to use Latest blog post on the home page then use the following shortcode 

<code>[recent_blog_post limit="3" grid="3"]</code>

* Template code : <code><?php echo do_shortcode('[blog]'); ?> and <?php echo do_shortcode('[recent_blog_post]'); ?> </code>

= Use Following parameters with shortcode =

<code>[blog]</code>

* **limit :** [blog limit="10"] (Display latest 10 blog post and then pagination).
* **category :**  [blog category="category_id"] (Display Blog post categories wise).
* **grid :** [blog grid="2"] (Display blog in Grid formats. To display blog in list view please do not use 'grid' parameter. ).
* **show_date :** [blog show_date="false"] (Display Blog date OR not. By default value is "True". Options are "ture OR false")
* **show_category_name :** [blog show_category_name="true" ] (Display blog post category name OR not. By default value is "True". Options are "ture OR false").
* **show_author :** [blog show_author="false"] (Display Blog author OR not. By default value is "True". Options are "ture OR false")
* **show_content :** [blog show_content="true" ] (Display blog post Short content OR not. By default value is "True". Options are "ture OR false").
* **show_full_content :** [blog show_full_content="true"] (Display Full Blog post content on main page if you do not want word limit. By default value is "false". Note : This parameter will not work with [recent_blog_post] shortcode.)
* **content_words_limit :** [blog content_words_limit="30" ] (Control blog post short content Words limit. By default limit is 20 words).
* **order:** [blog order="DESC"] (Blog order ie DESC or ASC).
* **orderby**: [blog orderby="date"] (Order by Blog ie date, ID, author, title, modified, rand and menu_order etc).
* **pagination_type:** [blog pagination_type="numeric"] (Select the pagination type for News ie "numeric" OR "next-prev". Note : This parameter will not work with [recent_blog_post] shortcode. ).
* **extra_class:** [blog extra_class=""]

<code>[recent_blog_post]</code>

* **limit :** [blog limit="10"] (Display latest 10 blog post and then pagination).
* **category :**  [blog category="category_id"] (Display Blog post categories wise).
* **grid :** [blog grid="2"] (Display blog in Grid formats. To display blog in list view please do not use 'grid' parameter. ).
* **show_date :** [blog show_date="false"] (Display Blog date OR not. By default value is "True". Options are "ture OR false")
* **show_author :** [blog show_author="false"] (Display Blog author OR not. By default value is "True". Options are "ture OR false")
* **show_content :** [blog show_content="true" ] (Display blog post Short content OR not. By default value is "True". Options are "ture OR false").
* **show_full_content :** [blog show_full_content="true"] (Display Full Blog post content on main page if you do not want word limit. By default value is "false". Note : This parameter will not work with [recent_blog_post] shortcode.)
* **show_category_name :** [blog show_category_name="true" ] (Display blog post category name OR not. By default value is "True". Options are "ture OR false").
* **content_words_limit :** [blog content_words_limit="30" ] (Control blog post short content Words limit. By default limit is 20 words).
* **order:** [blog order="DESC"] (Blog order ie DESC or ASC).
* **orderby**: [blog orderby="date"] (Order by Blog ie date, ID, author, title, modified, rand and menu_order etc).
* **extra_class:** [blog extra_class=""]

If you are getting any kind of problem with Blog page means you are not able to see all blog posts then please remodify your permalinks Structure for example 
first, select "Default" and save then again select "Custom Structure "  and save. 

Finally, the plugin adds a Recent Blog Post widget  which can be placed on any sidebar available in your theme. You can set the title of this list and the number of blog posts to show.

= Language Support =
* Added Added language for German, French (France) (Beta)

= Added New Features : =
* Added Added language for German, French (France) (Beta)
* Added new shortcode parameters show_date
* Added new shortcode parameters show_author.
* Added Image option in the widget

= PRO Features Include : =
> <strong>Premium Version</strong><br>
> Added 6 shortcodes
>
> <code>[blog], [recent_blog_post], [recent_blog_post_slider]</code>	
>
> * Recent Blog Slider.
> * Recent Blog Carousel.
> * Recent Blog in Grid view.
> * 50 Designs for Grid Layout
> * 45 Designs for Slider/Carousel
> * 8 Designs for List View
> * 13 Designs for Grid Box
> * 8 Designs for Blog Grid Box Slider
> * Gutenberg Block Supports
> * WPBakery Page Builder Supports
> * Elementor, Bevear and SiteOrigin Page Builder Support (New).
> * Divi Page Builder Native Support (New).
> * 6 Shortcodes
> * 6 Widgets (slider,list and category etc
> * Popular grid slider feature.
> * Custom Read More link for Blog Post.
> * Blog display with categories.
> * Drag & Drop feature to display Blog post in your desired order and other 6 types of order parameter.
> * 'Publicize' support with Jetpack to publish your Blog post on your social network.
> * 100% Multilanguage.
>
> View [PRO DEMO and Features](https://wponlinesupport.com/wp-plugin/wp-blog-and-widgets/) for additional information.
>
> View [Masonry Add-on](https://wponlinesupport.com/wordpress-plugin-addon/masonry-addon-wp-blog-widgets/) with 12 designs and 7 effects.
>

= Features include: =
* Added Widget Options like Show Blog date, Show Blog Categories, Select Blog Categories.
* Just create a Blog page with any name and add the shortcode  <code> [blog] </code>
* Easy to configure.
* Smoothly integrates into any theme.
* Yearly, Monthly and Daily archives.
* Blog Categories.
* Blog Tags.
* Recent Blog Post widget. 
* CSS file for customization.

= Privacy & Policy =
* We have also opt-in e-mail selection, once you download the plugin so that we can inform you and nurture you about products and its features.
 
== Installation ==

1. Upload the 'wp-blog-and-widgets' folder to the '/wp-content/plugins/' directory.
2. Activate the WP Blog and widgets plugin through the 'Plugins' menu in WordPress.
3. Add and manage Blog Post on your site by clicking on the  'Blog' tab that appears in your admin menu.
4. Create a page with the any name and paste this shortcode  <code> [blog] </code>.

= How to install: =
[youtube https://www.youtube.com/watch?v=hxamkKf-80U]  

== Frequently Asked Questions ==

= How to enable/disable Gutenberg editor support for Blog Posts? =

Just add this code in your theme function.php file to enable/disable Gutenberg editor support for  Blog Posts :

<code>
function prefix_gutenberg_editor_support($wpbaw_blog_args){
 $wpbaw_blog_args['show_in_rest'] = false; 
  return $wpbaw_blog_args;	
}
add_filter( 'wpbaw_blog_registered_post_type_args', 'prefix_gutenberg_editor_support' );
</code>

== Screenshots ==
1. Display Blog Posts with a grid-1 view.
2. Display Recent Blog Posts.
3. Display Blog Posts with List view.
4. Display Blog Posts with grid-3 view
5. Display Blog Posts with grid-2 view
6. Blog admin view

== Changelog ==

= 2.0 (23, Oct 2020) =
* [*] Update - Regular plugin maintenance. Updated readme file.
* [+] New - Click to copy the shortcode from the getting started page.
* [*] Added - Added our other Popular Plugins under blog and widget --> Install Popular Plugins From WPOS. This will help you to save your time during creating a website.

= 1.9 (28, August 2020) =
* [+] New - Added Gutenberg block support. Now use plugin easily with Gutenberg!
* [+] New - Added 'align' and 'extra_class' parameter for all shortcode. Now both slider shortcode is support twenty-nineteen and twenty-twenty theme Gutenberg block align and additional class feature.
* [+] New - Added 'show_full_content' parameter for recent post shortcode.
* [*] Tweak - Code optimization and performance improvements.
* [*] Template File - Main design file has been updated. If you have an override template file then verify with the latest copy.

= 1.8.2 (14, July 2020) =
* [*] Follow WordPress Detailed Plugin Guidelines for Offload Media and Analytics Code.

= 1.8.1 (28, Dec 2019) =
* [+] Added : Added 2 new parameter - order and orderby
* [*] Replaced wp_reset_query(); with wp_reset_postdata(); 
* [*] Improve some code in the shortcode file.

= 1.7 (05, June 2019) =
* [+] Added author support for the blog post type. After updating the plugin, if you are not able to see the author on the front-end side, please click on the Blog menu and assign the author for all blog posts by clicking on the Quick Edit link.

= 1.6.1 (09, Feb 2019) =
* [*] Minor change in Opt-in flow.

= 1.6 (20, Dec 2018) =
* [*] Fixed the issue where custom taxonomies were not showing after giving Gutenberg support for a custom blog post. Thanks, @sabkor for showing us this issue.

= 1.5.1 (18, Dec 2018) =
* [*] Update Opt-in flow.

= 1.5 (13-12-2018) =
* [+] Added `apply_filters` to update the blog custom post type arguments.
* [+] If you are using WordPress 5.0 OR WordPress 5.0 plus classic editor plugin, we have added `show_in_rest=> true` to enable the Gutenberg Editor support for Blog Posts or Not.

= 1.4 (08-12-2018) =
* [*] Tested with WordPress 5.0 and Gutenberg.

= 1.3.2 (05, June 2018) =
* [*] Follow some WordPress Detailed Plugin Guidelines.

= 1.3.1 (20/07/2017) =
* [+] Fixed grid issue showing 15px gap in left and right

= 1.3 (15/02/2017) =
* [+] Added new shortcode parameter "pagination_type" (Select the pagination type for blog ie "numeric" OR "next-prev" ).

= 1.2.8 =
* [+] Added "How it Works" tab under "Blog"
* [-] Removed "Pro Design" tab
* [+] Added some design to Read More button and Blog Title
* Fixed widget image display issue.

= 1.2.7 =
* Fixed Image display issue.
* Fixed CSS issue.
* Fixed widget image display issue.

= 1.2.6 =
* Added excerpt functionality in the post description.
* Resolved display post content issue.

= 1.2.5 =
* Updated PRO plugin design page.
* Added German (Switzerland), Spanish (Spain), French (Canada), Italian, and Dutch Beta translation.

= 1.2.4 =
* Fixed some CSS issues.

= 1.2.3 =
* Added Added language for German, French (France) (Beta)
* Added 2 new designs for the pro version
* Fixed some bug 

= 1.2.2 =
* Fixed some bug and added an option for pro designs

= 1.2.1 =
* Added new shortcode parameters show_author.
* Added Image option in the widget
* Fixed some bugs.

= 1.2 =
* Added new shortcode parameters show_date.
* Fixed some bugs.

= 1.1 =

* Added Widget Options like Show Blog date, Show Blog Categories, Select Blog Categories.
* Fixed some bugs

= 1.0 =
* Initial release.