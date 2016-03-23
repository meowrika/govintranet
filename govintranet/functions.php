<?php
/**
 * govintranet functions and definitions
 *
 * Sets up the theme and provides some helper functions. Some helper functions
 * are used in the theme as custom template tags. Others are attached to action and
 * filter hooks in WordPress to change core functionality.
 *
 * The first function, govintranet_setup(), sets up the theme by registering support
 * for various features in WordPress, such as post thumbnails, navigation menus, and the like.
 *
 * When using a child theme (see http://codex.wordpress.org/Theme_Development and
 * http://codex.wordpress.org/Child_Themes), you can override certain functions
 * (those wrapped in a function_exists() call) by defining them first in your child theme's
 * functions.php file. The child theme's functions.php file is included before the parent
 * theme's file, so the child theme functions would be used.
 *
 * Functions that are not pluggable (not wrapped in function_exists()) are instead attached
 * to a filter or action hook. The hook can be removed by using remove_action() or
 * remove_filter() and you can attach your own function to the hook.
 *
 * We can remove the parent theme's hook only after it is attached, which means we need to
 * wait until setting up the child theme:
 *
 * <code>
 * add_action( 'after_setup_theme', 'my_child_theme_setup' );
 * function my_child_theme_setup() {
 *     // We are providing our own filter for excerpt_length (or using the unfiltered value)
 *     remove_filter( 'excerpt_length', 'govintranet_excerpt_length' );
 *     ...
 * }
 * </code>
 *
 * For more information on hooks, actions, and filters, see http://codex.wordpress.org/Plugin_API.
 *
 * @package WordPress
 */

/**
 * Set the content width based on the theme's design and stylesheet.
 *
 * Used to set the width of images and content. Should be equal to the width the theme
 * is designed for, generally via the style.css stylesheet.
 */
if ( ! isset( $content_width ) )
	$content_width = 640;


/** Tell WordPress to run govintranet_setup() when the 'after_setup_theme' hook is run. */
add_action( 'after_setup_theme', 'govintranet_setup' );

if ( ! function_exists( 'govintranet_setup' ) ):
/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which runs
 * before the init hook. The init hook is too late for some features, such as indicating
 * support post thumbnails.
 *
 * To override govintranet_setup() in a child theme, add your own govintranet_setup to your child theme's
 * functions.php file.
 *
 * @uses add_theme_support() To add support for post thumbnails and automatic feed links.
 * @uses register_nav_menus() To add support for navigation menus.
 * @uses add_custom_background() To add support for a custom background.
 * @uses add_editor_style() To style the visual editor.
 * @uses load_theme_textdomain() For translation/localization support.
 * @uses add_custom_image_header() To add support for a custom header.
 * @uses register_default_headers() To register the default custom header images provided with the theme.
 * @uses set_post_thumbnail_size() To set a custom post thumbnail size.
 *
 * @since Twenty Ten 1.0
 */
function govintranet_setup() {

	// This theme styles the visual editor with editor-style.css to match the theme style.
	add_editor_style();

	// This theme uses post thumbnails
	add_theme_support( 'post-thumbnails' );
	
	// add post format support 
	add_theme_support( 'post-formats', array( 'status', 'link', 'gallery', 'image', 'video', 'audio' ) );
	add_post_type_support( 'news', 'post-formats' );
	add_post_type_support( 'task', 'post-formats' );
	
	// This remove this the issue of not being able to preview changes post types which support post-formats
	function post_format_parameter($url) {
		$url = remove_query_arg('post_format',$url);
		return $url;
	}
	add_filter('preview_post_link', 'post_format_parameter');

	// Add default posts and comments RSS feed links to head
	add_theme_support( 'automatic-feed-links' );

	// Make theme available for translation
	// Translations can be filed in the /languages/ directory
	load_theme_textdomain( 'govintranet');


	// This theme uses wp_nav_menu() in one location.
	register_nav_menus( array(
		'primary' => __( 'Primary Navigation', 'govintranet' ),
		'secondary' => __( 'Secondary Navigation', 'govintranet' ),
	) );

	// theme options functions:
	
	require_once ( get_stylesheet_directory() . '/theme-options.php'  );
	
	add_theme_support('custom-background');
	add_theme_support('custom-header');

}
endif;

/**
 * Makes some changes to the <title> tag, by filtering the output of wp_title().
 *
 * If we have a site description and we're viewing the home page or a blog posts
 * page (when using a static front page), then we will add the site description.
 *
 * If we're viewing a search result, then we're going to recreate the title entirely.
 * We're going to add page numbers to all titles as well, to the middle of a search
 * result title and the end of all other titles.
 *
 * The site title also gets added to all titles.
 *
 * @since Twenty Ten 1.0
 *
 * @param string $title Title generated by wp_title()
 * @param string $separator The separator passed to wp_title(). Twenty Ten uses a
 * 	vertical bar, "|", as a separator in header.php.
 * @return string The new title, ready for the <title> tag.
 */
function govintranet_filter_wp_title( $title, $separator ) {
	// Don't affect wp_title() calls in feeds.
	if ( is_feed() )
		return $title;

	// The $paged global variable contains the page number of a listing of posts.
	// The $page global variable contains the page number of a single post that is paged.
	// We'll display whichever one applies, if we're not looking at the first page.
	global $paged, $page, $post;

	if ( is_search() ) {
		// If we're a search, let's start over:
		$title = sprintf( __( 'Search results for %s', 'govintranet' ), '"' . get_search_query() . '"' );
		// Add a page number if we're on page 2 or more:
		if ( $paged >= 2 )
			$title .= " $separator " . sprintf( __( 'Page %s', 'govintranet' ), $paged );
		// Add the site name to the end:
		//$title .= " $separator " . get_bloginfo( 'name', 'display' );
		// We're done. Let's send the new title back to wp_title():
		return $title;
	}

	// Otherwise, let's start by adding the site name to the end:
	
if ( is_front_page() ){
		$title .= get_bloginfo( 'name', 'display' );
	}
	global $wp_query;
	$view = $wp_query->get_queried_object();
	if (isset($view) && isset($view->taxonomy) ) if ( $view->taxonomy == "a-to-z" ) {
		$title = _x("Letter","alphabet","govintranet") .  " " . $title ;
	}
	else if (isset($view) && $view->taxonomy == "category") {
		$title.= " " . __("tasks and guides category","govintranet");
	}
	else if (isset($view) && $view->taxonomy ) {
		return $title;
	}
	else if ($post->post_type == "task"  ) {

		$taskparent=$post->post_parent;
		$title_context='';
		if ($taskparent){
			$parent_guide_id = $taskparent; 		
			$taskparent = get_post($parent_guide_id);
			$title_context=" (".govintranetpress_custom_title($taskparent->post_title).")";
		}			
	
		$title .= $title_context. " - " . __('tasks and guides','govintranet')  ;
	}
	else if ($post->post_type == "project"  ) {
		$title .= " - " . __('project','govintranet') ;
	}
	else if ($post->post_type == "vacancy"  ) {
		$title .= " - " . __('job vacancies','govintranet') ;
	}
	else if ($post->post_type == "event"  ) {
		$title .= " - " . __('events','govintranet') ;
	}
	else if ($post->post_type == "jargon-buster"  ) {
		$title .= " - " . __('jargon buster','govintranet') ;
	}
	else if ($post->post_type == "forums"  ) {
		$title .= " - " . __('forums','govintranet') ;
	}
	else if ($post->post_type == "topics"  ) {
		$title .= " - " . __('forum topics','govintranet') ;
	}
	else if ($post->post_type == "replies"  ) {
		$title .= " - " . __('forum replies','govintranet') ;
	}
	else if ($post->post_type == "news"  ) {
		$title .= " - " . __('news','govintranet') ;
	}
	else if ($post->post_type == "news-update"  ) {
		$title .= " - " . __('news update','govintranet') ;
	}
	else if ($post->post_type == "blog"  ) {
		$title .= " - " . __('blog','govintranet') ;
	}
	else if (!$post->post_type  ) {
		global $post;
		$u = $post->post_title;
		$title .= $u." - " . __('staff profile','govintranet') ;
	}
	// If we have a site description and we're on the home/front page, add the description:
	$site_description = get_bloginfo( 'description', 'display' );
	if ( $site_description && ( is_home() || is_front_page() ) )
		$title .= " $separator " . $site_description;

	// Add a page number if necessary:
	if ( $paged >= 2 || $page >= 2 )
		$title .= " $separator " . sprintf( __( 'Page %s', 'govintranet' ), max( $paged, $page ) );

	// Return the new title to wp_title():
	return trim(preg_replace('/\[.*\]/i','',$title));

}
add_filter( 'wp_title', 'govintranet_filter_wp_title', 10, 2 );

/**
 * Get our wp_nav_menu() fallback, wp_page_menu(), to show a home link.
 *
 * To override this in a child theme, remove the filter and optionally add
 * your own function tied to the wp_page_menu_args filter hook.
 *
 * @since Twenty Ten 1.0
 */
function govintranet_page_menu_args( $args ) {
	$args['show_home'] = true;
	return $args;
}
add_filter( 'wp_page_menu_args', 'govintranet_page_menu_args' );

/**
 * Sets the post excerpt length to 40 characters.
 *
 * To override this length in a child theme, remove the filter and add your own
 * function tied to the excerpt_length filter hook.
 *
 * @since Twenty Ten 1.0
 * @return int
 */
function govintranet_excerpt_length( $length ) {
	return 30;
}
add_filter( 'excerpt_length', 'govintranet_excerpt_length' );

/**
 * Returns a "Continue Reading" link for excerpts
 *
 * @since Twenty Ten 1.0
 * @return string "Continue Reading" link
 */
function govintranet_continue_reading_link() {
	return ' <a href="'. get_permalink() . '">' . __( 'Continue reading <span class="meta-nav">&rarr;</span>', 'govintranet' ) . '</a>';
}

/**
 * Replaces "[...]" (appended to automatically generated excerpts) with an ellipsis and govintranet_continue_reading_link().
 *
 * To override this in a child theme, remove the filter and add your own
 * function tied to the excerpt_more filter hook.
 *
 * @since Twenty Ten 1.0
 * @return string An ellipsis
 */
function govintranet_auto_excerpt_more( $more ) {
	return ' &hellip;' . govintranet_continue_reading_link();
}
add_filter( 'excerpt_more', 'govintranet_auto_excerpt_more' );

/**
 * Adds a pretty "Continue Reading" link to custom post excerpts.
 *
 * To override this link in a child theme, remove the filter and add your own
 * function tied to the get_the_excerpt filter hook.
 *
 * @since Twenty Ten 1.0
 * @return string Excerpt with a pretty "Continue Reading" link
 */
function govintranet_custom_excerpt_more( $output ) {
	if ( has_excerpt() && ! is_attachment() ) {
		$output .= govintranet_continue_reading_link();
	}
	return $output;
}
add_filter( 'get_the_excerpt', 'govintranet_custom_excerpt_more' );

if ( ! function_exists( 'govintranet_comment' ) ) :
/**
 * Template for comments and pingbacks.
 *
 * To override this walker in a child theme without modifying the comments template
 * simply create your own govintranet_comment(), and that function will be used instead.
 *
 * Used as a callback by wp_list_comments() for displaying the comments.
 *
 * @since Twenty Ten 1.0
 */
function govintranet_comment( $comment, $args, $depth ) {
	$GLOBALS['comment'] = $comment;
	switch ( $comment->comment_type ) :
		case '' :
	?>
	<li <?php comment_class('well'); ?> id="li-comment-<?php comment_ID(); ?>">
		<div id="comment-<?php comment_ID(); ?>">
		<div class="comment-author vcard">
			<?php 
			$directory = 	get_option('options_forum_support');
			$staffdirectory = get_option('options_module_staff_directory');
			if ( $directory ):
				$directorystyle = get_option('options_staff_directory_style'); // 0 = squares, 1 = circles
				$avstyle="";
				if ( $directorystyle==1 ) $avstyle = " img-circle";
				$image_url = get_avatar($comment , 66);
				$image_url = str_replace(" photo", " photo alignleft".$avstyle, $image_url);
				$userurl = get_author_posts_url( $comment->user_id );  
				if ( $userurl == site_url("author/") ) $userurl = "";
				if (function_exists('bp_activity_screen_index')){ // if using BuddyPress - link to the members page
					$userurl=str_replace('/author', '/members', $userurl); }
				elseif (function_exists('bbp_get_displayed_user_field') && $staffdirectory ){ // if using bbPress - link to the staff page
					$userurl=str_replace('/author', '/staff', $userurl); }
				elseif (function_exists('bbp_get_displayed_user_field')  ){ // if using bbPress - link to the staff page
					$userurl=str_replace('/author', '/users', $userurl);
				} 
				$userdisplay = "";
				$user_object = get_userdata( $comment->user_id );
				if ( $user_object ) $userdisplay = $user_object->display_name;
				if ( $userurl ):
					echo "<a href='".$userurl."'>".$image_url."</a>";
					$userlink = "<a href='".$userurl."'>".$userdisplay."</a>";
				else:
					echo $image_url;
					$userlink = get_comment_author_link();
				endif;
			else:
				$userlink = get_comment_author_link();
			endif;
			?>
			<?php printf( __( '%s <span class="says">says:</span>', 'govintranet' ), sprintf( '<cite class="fn">%s</cite>', $userlink ) ); ?>
		</div><!-- .comment-author .vcard -->
		<?php if ( $comment->comment_approved == '0' ) : ?>
			<em><?php _e( 'Your comment is awaiting moderation.', 'govintranet' ); ?></em>
			<br />
		<?php endif; ?>

		<div class="comment-meta commentmetadata"><a href="<?php echo esc_url( get_comment_link( $comment->comment_ID ) ); ?>">
			<?php
				/* translators: 1: date, 2: time */
				printf( __( '<small>%1$s at %2$s</small>', 'govintranet' ), get_comment_date(),  get_comment_time() ); ?></a><?php edit_comment_link( __( '(Edit)', 'govintranet' ), ' ' );
			?>
		</div><!-- .comment-meta .commentmetadata -->

		<div class="comment-body"><?php comment_text(); ?></div>

		<div class="reply">
			<?php comment_reply_link( array_merge( $args, array( 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) ); ?>
		</div><!-- .reply -->
	</div><!-- #comment-##  -->

	<?php
			break;
		case 'pingback'  :
		case 'trackback' :
	?>
	<li class="post pingback">
		<p><?php _e( 'Pingback:', 'govintranet' ); ?> <?php comment_author_link(); ?><?php edit_comment_link( __('(Edit)', 'govintranet'), ' ' ); ?></p>
	<?php
			break;
	endswitch;
}
endif;



/**
 * Register widgetized areas, including two sidebars and four widget-ready columns in the footer.
 *
 * To override govintranet_widgets_init() in a child theme, remove the action hook and add your own
 * function tied to the init hook.
 *
 * @since Twenty Ten 1.0
 * @uses register_sidebar
 */
function govintranet_widgets_init() {

	register_sidebar( array(
		'name' => __( 'Homepage first column', 'govintranet' ),
		'id' => 'home-widget-area1',
		'description' => __( 'Homepage 1st column', 'govintranet' ),
		'before_widget' => '<div class="category-block">',
		'after_widget' => '</div>',
		'before_title' => '<h3>',
		'after_title' => '</h3>',
	) );

	register_sidebar( array(
		'name' => __( 'Homepage hero area', 'govintranet' ),
		'id' => 'home-widget-area-hero',
		'description' => __( 'Homepage hero area', 'govintranet' ),
		'before_widget' => '<div class="category-block">',
		'after_widget' => '</div>',
		'before_title' => '<h3>',
		'after_title' => '</h3>',
	) );
	register_sidebar( array(
		'name' => __( 'Homepage second column', 'govintranet' ),
		'id' => 'home-widget-area2',
		'description' => __( 'Homepage second column', 'govintranet' ),
		'before_widget' => '<div class="category-block">',
		'after_widget' => '</div>',
		'before_title' => '<h3>',
		'after_title' => '</h3>',
	) );
	register_sidebar( array(
		'name' => __( 'Homepage third column top', 'govintranet' ),
		'id' => 'home-widget-area3t',
		'description' => __( 'Homepage third column top', 'govintranet' ),
		'before_widget' => '<div class="category-block">',
		'after_widget' => '</div>',
		'before_title' => '<h3>',
		'after_title' => '</h3>',
	) );
	register_sidebar( array(
		'name' => __( 'Homepage third column bottom', 'govintranet' ),
		'id' => 'home-widget-area3b',
		'description' => __( 'Homepage third column bottom', 'govintranet' ),
		'before_widget' => '<div class="category-block">',
		'after_widget' => '</div>',
		'before_title' => '<h3>',
		'after_title' => '</h3>',
	) );
	register_sidebar( array(
		'name' => __( 'Utility widget box', 'govintranet' ),
		'id' => 'utility-widget-area',
		'description' => __( 'Utility widget area appears beneath the search box', 'govintranet' ),
		'before_title' => '<h3>',
		'after_title' => '</h3>',
	) );
	
	register_sidebar( array(
		'name' => __( 'Left footer', 'govintranet' ),
		'id' => 'first-footer-widget-area',
		'description' => __( 'Left footer widget area', 'govintranet' ),
		'before_widget' => '<div class="widget-box">',
		'after_widget' => '</div>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	) );

	register_sidebar( array(
		'name' => __( 'Right footer 1', 'govintranet' ),
		'id' => 'right1-footer-widget-area',
		'description' => __( 'The 1st right footer widget area', 'govintranet' ),
		'before_widget' => '<div class="widget-box">',
		'after_widget' => '</div>',
		'before_title' => '<h3>',
		'after_title' => '</h3>',
	) );

	register_sidebar( array(
		'name' => __( 'Right footer 2', 'govintranet' ),
		'id' => 'right2-footer-widget-area',
		'description' => __( 'The 2nd right footer widget area', 'govintranet' ),
		'before_widget' => '<div class="widget-box">',
		'after_widget' => '</div>',
		'before_title' => '<h3>',
		'after_title' => '</h3>',
	) );
	register_sidebar( array(
		'name' => __( 'Tasks sidebar', 'govintranet' ),
		'id' => 'task-widget-area',
		'description' => __( 'Tasks widget area, appears on individual tasks', 'govintranet' ),
		'before_widget' => '<div class="widget-box">',
		'after_widget' => '</div>',
		'before_title' => '<h3>',
		'after_title' => '</h3>',
	) );
	register_sidebar( array(
		'name' => __( 'News landing page', 'govintranet' ),
		'id' => 'newslanding-widget-area',
		'description' => __( 'The right-hand col on the news page', 'govintranet' ),
		'before_widget' => '<div class="widget-box">',
		'after_widget' => '</div>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	) );	
	register_sidebar( array(
		'name' => __( 'News sidebar', 'govintranet' ),
		'id' => 'news-widget-area',
		'description' => __( 'News widget area, appears on individual news and news updtes', 'govintranet' ),
		'before_widget' => '<div class="widget-box">',
		'after_widget' => '</div>',
		'before_title' => '<h3>',
		'after_title' => '</h3>',
	) );
	register_sidebar( array(
		'name' => __( 'Blog landing page', 'govintranet' ),
		'id' => 'bloglanding-widget-area',
		'description' => __( 'Blog landing page widget area', 'govintranet' ),
		'before_widget' => '<div class="widget-box">',
		'after_widget' => '</div>',
		'before_title' => '<h3>',
		'after_title' => '</h3>',
	) );
	register_sidebar( array(
		'name' => __( 'Blog sidebar', 'govintranet' ),
		'id' => 'blog-widget-area',
		'description' => __( 'Blog posts widget area, appears on individual blog posts', 'govintranet' ),
		'before_widget' => '<div class="widget-box">',
		'after_widget' => '</div>',
		'before_title' => '<h3>',
		'after_title' => '</h3>',
	) );
	register_sidebar( array(
		'name' => __( 'Events landing page', 'govintranet' ),
		'id' => 'eventslanding-widget-area',
		'description' => __( 'Events landing page widget area', 'govintranet' ),
		'before_widget' => '<div class="widget-box">',
		'after_widget' => '</div>',
		'before_title' => '<h3>',
		'after_title' => '</h3>',
	) );
	register_sidebar( array(
		'name' => __( 'Events sidebar', 'govintranet' ),
		'id' => 'events-widget-area',
		'description' => __( 'Events posts widget area, appears on individual events', 'govintranet' ),
		'before_widget' => '<div class="widget-box">',
		'after_widget' => '</div>',
		'before_title' => '<h3>',
		'after_title' => '</h3>',
	) );
	register_sidebar( array(
		'name' => __( 'Search results page', 'govintranet' ),
		'id' => 'serp-widget-area',
		'description' => __( 'Search results page widget area', 'govintranet' ),
		'before_widget' => '<div class="widget-box">',
		'after_widget' => '</div>',
		'before_title' => '<h3>',
		'after_title' => '</h3>',
	) );
	register_sidebar( array(
		'name' => __( 'Login area', 'govintranet' ),
		'id' => 'login-widget-area',
		'description' => __( 'Login widget area', 'govintranet' ),
		'before_widget' => '',
		'after_widget' => '',
		'before_title' => '',
		'after_title' => '',
	) );
	
}

/** Register sidebars by running govintranet_widgets_init() on the widgets_init hook. */
add_action( 'widgets_init', 'govintranet_widgets_init' );

function govintranetpress_custom_title( $output ) {
	if (!is_admin()) {
		return trim(preg_replace('/\[.*\]/i','',$output));
	} else {
		return $output;
	}
}
add_filter( 'the_title', 'govintranetpress_custom_title' );



/**
 * Removes the default styles that are packaged with the Recent Comments widget.
 *
 * To override this in a child theme, remove the filter and optionally add your own
 * function tied to the widgets_init action hook.
 *
 * @since Twenty Ten 1.0
 */
function govintranet_remove_recent_comments_style() {
	global $wp_widget_factory;
	remove_action( 'wp_head', array( $wp_widget_factory->widgets['WP_Widget_Recent_Comments'], 'recent_comments_style' ) );
}
add_action( 'widgets_init', 'govintranet_remove_recent_comments_style' );



// check jQuery is available

function enqueueThemeScripts() {
	 wp_enqueue_script( 'jquery' );
	 wp_enqueue_script( 'jquery-ui-core' );
	 wp_enqueue_script( 'jquery-effects-core' );
	 
	 wp_register_script( 'bootstrap_min', get_stylesheet_directory_uri() . "/js/bootstrap.min.js");
	 wp_enqueue_script( 'bootstrap_min' );

	 wp_register_script( 'ht-scripts', get_stylesheet_directory_uri() . "/js/ht-scripts.js");
	 wp_enqueue_script( 'ht-scripts' );

	 wp_enqueue_style( 'ht-font', "//fonts.googleapis.com/css?family=Open+Sans:300,400,700",'','screen');

	 wp_register_style( 'dashicons', includes_url("/css/dashicons.min.css"));
	 wp_enqueue_style( 'dashicons' );

}
add_action('wp_enqueue_scripts','enqueueThemeScripts');


function enqueueThemeStyles() {

	 wp_enqueue_style( 'ht-style', bloginfo( 'stylesheet_url' ),'','screen');

}
add_action('wp_enqueue_style','enqueueThemeStyles');


function govintranetpress_custom_excerpt_more( $output ) {
	return preg_replace('/<a[^>]+>Continue reading.*?<\/a>/i','',$output);
}
add_filter( 'get_the_excerpt', 'govintranetpress_custom_excerpt_more', 20 );

/***

Remove ability for editors and below to manage taxonomies
	
*/
function govintranetpress_setup_roles(){
	$author = get_role('author');
	$author->remove_cap('manage_categories');
	$editor = get_role('editor');
	$editor->remove_cap('manage_categories');
}
add_action('switch_theme', 'govintranetpress_setup_roles');

function add_mtc_post_types( $types )
{
    $types[] = 'news';
    $types[] = 'news-update';
    $types[] = 'blog';
    $types[] = 'task';
    $types[] = 'team';
    $types[] = 'project';
    $types[] = 'event';
    $types[] = 'vacancy';
    
    return $types;
}
add_filter( 'rd2_mtc_post_types', 'add_mtc_post_types' );

function get_post_thumbnail_caption() {
	if ( $thumb = get_post_thumbnail_id() )
		return get_post( $thumb )->post_excerpt;
}


// shorten cache lifetime for blog aggregators to keep it fresh
add_filter( 'wp_feed_cache_transient_lifetime', create_function( '$a', 'return 900;' ) ); // 15 mins

function renderLeftNav($outputcontent="TRUE") {
		global $post;
		wp_reset_postdata();
		$temppost = $post;
		$parent = $post->post_parent;
		$mainid = $post->ID;
		$navarray = array();
		$currentpost = get_post($mainid);
		$currenttitle = get_the_title();
		$subnavString = '';
					
		while (true){
			//iteratively get the post's parent until we reach the top of the hierarchy
			$post_parent = $currentpost->post_parent; 
			if ($post_parent!=0){	//if found a parent
				$navarray[] = $post_parent;
				$currentpost = get_post($post_parent);
				continue; //loop round again
			}
			break; //we're done with the parents
		};
		
		$navarray = array_reverse($navarray);
		
		foreach ($navarray as $nav){ //loop through nav array outputting menu options as appropriate (parent, current or child)
			$currentpost = get_post($nav);
			$subnavString .= "<li class='page_item menu-item-ancestor'>"; //parent page
			$subnavString .=  "<a href='".$currentpost->guid."'>".$currentpost->post_title."</a></li>";
		}
										
		if (!is_search() ) {
		
			$output = "
				<div id='sectionnav'>
				<ul>
				{$subnavString}";
			if (pageHasChildren($mainid)){
			$subpages = wp_list_pages("echo=0&title_li=&depth=3&child_of=". $mainid);
				$output .="	
					<li class='current_page_item'><a href='#'>{$currenttitle}</a></li>
					<ul class='submenu'>{$subpages}</ul>";
			} else {
			$subpages = wp_list_pages("echo=0&title_li=&depth=3&child_of=". $parent);
				$output .="	
					<ul class='submenu'>{$subpages}</ul>";
			}
			$output .="	
				</ul>
				</div>	
			";
	
			if ($outputcontent == "TRUE") { 
				echo $output; 
			} else {
				return true;
			}
			
		} else {
			$output = "
				<div id='spacernav'>
				</div>	
			";
	
			if ($outputcontent == "TRUE") { 
				echo $output; 
			} else {
				return false;
			}
			
		}			

}
function pageHasChildren($id="") {
	global $post;
	if ($id) {
		$children = get_pages('child_of='.$id);	
	} else {
		$children = get_pages('child_of='.$post->ID);
	}
	if (count($children) != 0) {
		return true;
	} else {
		return false;
	}
}

function postHasChildren($id,$type) {
	global $post;
	$children = get_posts('post_parent='.$id."&post_type=".$type);
	if (count($children) != 0) {
		return true;
	} else {
		return false;
	}
}


function my_custom_login_logo() {
	$hc = "options_login_logo";
	$hcitem = get_option($hc);
	$loginimage =  wp_get_attachment_image_src( $hcitem, 'large' );
	if ($hcitem){
    echo '<style type="text/css">
           h1 a { background-image:url('.$loginimage[0].') !important; 
           width: auto !important;
           background-size: auto !important;
           }
    </style>';
    } else {
    echo '<style type="text/css">
	h1 a { background-image:url('.get_stylesheet_directory_uri().'/images/loginbranding.png) !important; 
	       width: auto !important;
           background-size: auto !important;
           }
    </style>';
    }
}
add_action('login_head', 'my_custom_login_logo');


// *********** Bootstrap Walker for menu

require_once('wp_bootstrap_navwalker.php');

// add accesskeys to menus

class HT_Walker extends Walker_Nav_Menu {
	function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
		global $wp_query;
		$indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';

		$class_names = $value = '';

		$classes = empty( $item->classes ) ? array() : (array) $item->classes;
		$classes[] = 'menu-item-' . $item->ID;

		$class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args ) );
		$class_names = $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '';

		$id = apply_filters( 'nav_menu_item_id', 'menu-item-'. $item->ID, $item, $args );
		$id = $id ? ' id="' . esc_attr( $id ) . '"' : '';

		$output .= $indent . '<li' . $id . $value . $class_names .'>';

		$attributes  = ! empty( $item->attr_title ) ? ' title="'  . esc_attr( $item->attr_title ) .'"' : '';
		$attributes .= ! empty( $item->target )     ? ' target="' . esc_attr( $item->target     ) .'"' : '';
		$attributes .= ! strlen( $item->xfn ) == 0        ? ' accesskey="'    . esc_attr( $item->xfn        ) .'"' : '';
		$attributes .= ! empty( $item->url )        ? ' href="'   . esc_attr( $item->url        ) .'"' : '';

		$item_output = $args->before;
		$item_output .= '<a'. $attributes .'>';
		$item_output .= $args->link_before . apply_filters( 'the_title', $item->title, $item->ID ) . $args->link_after;
		$item_output .= '</a>';
		$item_output .= $args->after;

		$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
	}
}

// add custom styles from editor-style.css to TinyMCE menu

function add_my_editor_style() {
	add_editor_style();
}

add_action( 'admin_init', 'add_my_editor_style' );
// Customize the format dropdown items
if( !function_exists('base_custom_mce_format') ){
	function base_custom_mce_format($init) {
		// Add block format elements you want to show in dropdown
		$init['theme_advanced_blockformats'] = 'p,h2,h3,h4,h5,h6,pre,blockquote';
		//$init['extended_valid_elements'] = 'code[*]';
		return $init;
	}
	add_filter('tiny_mce_before_init', 'base_custom_mce_format' );
}

function my_colorful_tag_cloud( $cat_id, $tc_tax, $tc_post_type ) {
    $defaults = array(
        'smallest' => 12, 'largest' => 24, 'unit' => 'pt', 'number' => 45,
        'format' => 'flat', 'separator' => "\n", 'orderby' => 'name', 'order' => 'ASC',
        'exclude' => '', 'include' => '', 'link' => 'view', 'taxonomy' => 'post_tag', 'echo' => true
    );
	if (($post->post_type=='task')){
	    $defaults = array(
	        'smallest' => 12, 'largest' => 28, 'unit' => 'pt', 'number' => 90,
	        'format' => 'flat', 'separator' => "\n", 'orderby' => 'name', 'order' => 'ASC',
	        'exclude' => '', 'include' => '', 'link' => 'view', 'taxonomy' => 'post_tag', 'echo' => true
	    );
	}
    $args = wp_parse_args( $args, $defaults );
    global $wpdb;
    if ( $cat_id != "" ){
    	$tquery = $wpdb->prepare("SELECT DISTINCT terms2.term_id as term_id, terms2.name as name, terms2.slug as link, t2.count as count, t2.term_taxonomy_id as term_taxonomy_id, 0 as term_group, 'post_tag' as taxonomy FROM $wpdb->posts as p1 LEFT JOIN $wpdb->term_relationships as r1 ON p1.ID = r1.object_ID LEFT JOIN $wpdb->term_taxonomy as t1 ON r1.term_taxonomy_id = t1.term_taxonomy_id LEFT JOIN $wpdb->terms as terms1 ON t1.term_id = terms1.term_id, $wpdb->posts as p2 LEFT JOIN $wpdb->term_relationships as r2 ON p2.ID = r2.object_ID LEFT JOIN $wpdb->term_taxonomy as t2 ON r2.term_taxonomy_id = t2.term_taxonomy_id LEFT JOIN $wpdb->terms as terms2 ON t2.term_id = terms2.term_id WHERE ( t1.taxonomy = '%s' AND p1.post_status = 'publish' AND p1.post_type = '%s' AND terms1.term_id = '%s' AND t2.taxonomy = 'post_tag' AND p2.post_status = 'publish' AND p1.ID = p2.ID  ) ORDER BY t2.count desc limit 90",$tc_tax,$tc_post_type,$cat_id);

	} else {

		$tquery = $wpdb->prepare("SELECT DISTINCT terms2.term_id as term_id, terms2.name as name, terms2.slug as link, t2.count as count, t2.term_taxonomy_id as term_taxonomy_id, 0 as term_group, 'post_tag' as taxonomy FROM $wpdb->posts as p1 LEFT JOIN $wpdb->term_relationships as r1 ON p1.ID = r1.object_ID LEFT JOIN $wpdb->term_taxonomy as t1 ON r1.term_taxonomy_id = t1.term_taxonomy_id LEFT JOIN $wpdb->terms as terms1 ON t1.term_id = terms1.term_id, $wpdb->posts as p2 LEFT JOIN $wpdb->term_relationships as r2 ON p2.ID = r2.object_ID LEFT JOIN $wpdb->term_taxonomy as t2 ON r2.term_taxonomy_id = t2.term_taxonomy_id LEFT JOIN $wpdb->terms as terms2 ON t2.term_id = terms2.term_id WHERE ( t1.taxonomy = '%s' AND p1.post_status = 'publish' AND p1.post_type = '%s' AND t2.taxonomy = 'post_tag' AND p2.post_status = 'publish' AND p1.ID = p2.ID  ) ORDER BY t2.count desc limit 45",$tc_tax,$tc_post_type);

	}

	$tquery = "";

	if (post_type_exists( $tc_post_type ) ){
		$tquery="
		SELECT DISTINCT
		$wpdb->terms.term_id,
		$wpdb->terms.name,
		$wpdb->terms.slug,
		$wpdb->term_taxonomy.count,
		$wpdb->term_taxonomy.term_taxonomy_id,
		0 as term_group,
		'post_tag' as taxonomy
FROM				$wpdb->posts, $wpdb->term_taxonomy, $wpdb->term_relationships, $wpdb->terms
WHERE				$wpdb->posts.post_type = $tc_post_type AND
	$wpdb->posts.post_status = 'publish' AND
	$wpdb->posts.id = $wpdb->term_relationships.object_id AND
	$wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id AND
	$wpdb->term_taxonomy.taxonomy = 'post_tag' AND
	$wpdb->terms.term_id = $wpdb->term_taxonomy.term_id AND
	$wpdb->term_taxonomy.count > 0
	limit 45
	
		";
	}
	if ( $tquery ) $tags = $wpdb->get_results($tquery);			

    if ( empty( $tags ) || is_wp_error( $tags ) )
        return;

    foreach ( $tags as $key => $tag ) {
        $link = get_term_link( intval($tag->term_id), $tag->taxonomy );

        if ( is_wp_error( $link ) )
            return false;

        $tags[ $key ]->link = $link;
        $tags[ $key ]->id = $tag->term_id;
    }
    $defaults = array(
        'smallest' => 12, 'largest' => 24, 'unit' => 'pt', 'number' => 0,
        'format' => 'flat', 'separator' => "\n", 'orderby' => 'name', 'order' => 'ASC',
        'topic_count_text_callback' => 'default_topic_count_text',
        'topic_count_scale_callback' => 'default_topic_count_scale', 'filter' => 1,
    );

    if ( !isset( $args['topic_count_text_callback'] ) && isset( $args['single_text'] ) && isset( $args['multiple_text'] ) ) {
        $body = 'return sprintf (
            _n(' . var_export($args['single_text'], true) . ', ' . var_export($args['multiple_text'], true) . ', $count),
            number_format_i18n( $count ));';
        $args['topic_count_text_callback'] = create_function('$count', $body);
    }

    $args = wp_parse_args( $args, $defaults );
    extract( $args );

    if ( empty( $tags ) )
        return;

    $tags_sorted = apply_filters( 'tag_cloud_sort', $tags, $args );
    if ( $tags_sorted != $tags  ) { // the tags have been sorted by a plugin
        $tags = $tags_sorted;
        unset($tags_sorted);
    } else {
        if ( 'RAND' == $order ) {
            shuffle($tags);
        } else {
            // SQL cannot save you; this is a second (potentially different) sort on a subset of data.
            if ( 'name' == $orderby )
                uasort( $tags, '_wp_object_name_sort_cb' );
            else
                uasort( $tags, '_wp_object_count_sort_cb' );

            if ( 'DESC' == $order )
                $tags = array_reverse( $tags, true );
        }
    }

    if ( $number > 0 )
        $tags = array_slice($tags, 0, $number);

    $counts = array();
    $real_counts = array(); // For the alt tag
    foreach ( (array) $tags as $key => $tag ) {
        $real_counts[ $key ] = $tag->count;
        $counts[ $key ] = $topic_count_scale_callback($tag->count);
    }

    $min_count = min( $counts );
    $spread = max( $counts ) - $min_count;
    if ( $spread <= 0 )
        $spread = 1;
    $font_spread = $largest - $smallest;
    if ( $font_spread < 0 )
        $font_spread = 1;
    $font_step = $font_spread / $spread;

    $a = array();
    $colors = 6;

    foreach ( $tags as $key => $tag ) {
        $count = $counts[ $key ];
        $real_count = $real_counts[ $key ];
        $pstyp='';
        if ($post->post_type == 'project'){
        $pstyp='?posttype=project';
        }
        if ($post->post_type == 'vacancy'){
        $pstyp='?posttype=vacancy';
        }

        if ($post->post_type == 'task'){
        $pstyp='?posttype=task';
        }

        $tag_link = '#' != $tag->link ? esc_url( $tag->link ).$pstyp : '#';
        $tag_id = isset($tags[ $key ]->id) ? $tags[ $key ]->id : $key;
        $tag_name = $tags[ $key ]->name;
        $min_color = "#5679b9";
        $max_color ="#af1410";
        
        $color = round( ( $smallest + ( ( $count - $min_count ) * $font_step ) ) - ( $smallest - 1 ) ) ;
		$basecol=HTMLToRGB('#3a6f9e');
        
        $scolor = ChangeLuminosity($basecol, 60-($color*3.3));
        $scolor=RGBToHTML($scolor);
        $class = 'color-' . ( round( ( $smallest + ( ( $count - $min_count ) * $font_step ) ) - ( $smallest - 1 ) ) );
        $tag_link = explode("/", $tag_link);
        $tag_link=$tag_link[4];
        $a[] = "<a href='".site_url()."/tag/".$tag_link."/'  style='font-size: " .
            str_replace( ',', '.', ( $smallest + ( ( $count - $min_count ) * $font_step ) ) )
            . "$unit; color: ".$scolor.";'>$tag_name</a>";
    }

    $return = join( $separator, $a );

    return apply_filters( 'wp_generate_tag_cloud', $return, $tags, $args );
}


function add_pagination_to_author_page_query_string($query_string){
    if (isset($query_string['author_name']) && !is_admin()):
		if ( $_GET['show'] == "forum" ):
	    	$query_string['post_type'] = array('topic','reply');
	    else:
	    	$query_string['post_type'] = array('blog');
	    endif;
    endif;
    return $query_string;
}
add_filter('request', 'add_pagination_to_author_page_query_string');

function get_terms_by_post_type( $taxonomies, $post_types ) {
	if (!$taxonomies || !$post_types) {
		$results = "";
		return $results;
	}

    global $wpdb;

    $query = "SELECT t.*, COUNT(*) as total from $wpdb->terms AS t INNER JOIN $wpdb->term_taxonomy AS tt ON t.term_id = tt.term_id INNER JOIN $wpdb->term_relationships AS r ON r.term_taxonomy_id = tt.term_taxonomy_id INNER JOIN $wpdb->posts AS p ON p.ID = r.object_id WHERE p.post_status = 'publish' AND p.post_type IN('".join( "', '", $post_types )."') AND tt.parent = 0 AND tt.taxonomy IN('".join( "', '", $taxonomies )."') GROUP BY t.term_id order by t.slug";

    $results = $wpdb->get_results( $query );

    return $results;

}

function get_terms_by_media_type( $taxonomies, $post_types ) {
	if (!$taxonomies || !$post_types) {
		$results = "";
		return $results;
	}

    global $wpdb;

    $query = "SELECT t.*, COUNT(*) as total from $wpdb->terms AS t INNER JOIN $wpdb->term_taxonomy AS tt ON t.term_id = tt.term_id INNER JOIN $wpdb->term_relationships AS r ON r.term_taxonomy_id = tt.term_taxonomy_id INNER JOIN $wpdb->posts AS p ON p.ID = r.object_id WHERE p.post_status = 'inherit' AND p.post_type IN('".join( "', '", $post_types )."') AND tt.taxonomy IN('".join( "', '", $taxonomies )."') GROUP BY t.term_id order by t.slug";

    $results = $wpdb->get_results( $query );

    return $results;

}

if ( current_user_can('level_0') && !current_user_can('level_1') ){
	add_action( 'admin_menu', 'my_remove_menu_pages' );
	add_filter('show_admin_bar', '__return_false');
	function my_remove_menu_pages() {
	    remove_menu_page('edit.php?post_type=incsub_wiki');  
	    remove_menu_page('video-user-manuals/plugin.php');  
	    remove_menu_page('edit.php?post_type=task');  
	    remove_menu_page('edit.php?post_type=project');  
	    remove_menu_page('edit.php?post_type=news');  
		remove_menu_page('edit.php?post_type=news-update');  
	    remove_menu_page('edit.php?post_type=blog');  
	    remove_menu_page('edit.php?post_type=event');  
	    remove_menu_page('edit.php?post_type=vacancy');  
	    remove_menu_page('edit.php?post_type=intravert');  
	    remove_menu_page('edit.php?post_type=jargon-buster');  
		remove_menu_page('edit.php?post_type=team');  	    
	    remove_menu_page('index.php');  
	}
}

//remove settings for search to allow for things like T&S
/*
remove_filter('relevanssi_remove_punctuation', 'relevanssi_remove_punct');
add_filter('relevanssi_remove_punctuation', 'your_relevanssi_remove_punct');

function your_relevanssi_remove_punct($a) {
	$a = strip_tags($a);
	$a = stripslashes($a);
	$a = str_replace('&#8217;', '', $a);
	$a = str_replace("'", '', $a);
	$a = str_replace("´", '', $a);
	$a = str_replace("’", '', $a);
	$a = str_replace("‘", '', $a);
	$a = str_replace("„", '', $a);
	$a = str_replace("·", '', $a);
	$a = str_replace("”", '', $a);
	$a = str_replace("“", '', $a);
	$a = str_replace("…", '', $a);
	$a = str_replace("€", '', $a);
	$a = str_replace("&shy;", '', $a);
	$a = str_replace("—", ' ', $a);
	$a = str_replace("–", ' ', $a);
	$a = str_replace("×", ' ', $a);
    $a = preg_replace('/[[:space:]]+/', ' ', $a);	
	$a = trim($a);
        return $a;
}
*/
add_filter('relevanssi_hits_filter', 'ht_exclude_user_search');
function ht_exclude_user_search($hits){
	if ( ( !isset( $_GET['include'] ) || !$_GET['include']=='user' ) && isset( $_GET['post_type'] ) ): 
		//  just search post types, no users
		$hcount=-1;
		$recs=array();
		$newrecs = $hits;
		foreach ($hits[0] as $h){ 
			$hcount++;
			if ($h->post_type!='user'):
				array_push($recs, $h);
			endif;	
		}
		return array($recs);
	elseif ( isset( $_GET['include'] ) && $_GET['include']=='user' && !isset( $_GET['post_type'] ) ): 
		// just search users
		$hcount=-1;
		$recs=array();
		$newrecs = $hits;
		foreach ($hits[0] as $h){ 
			$hcount++;
			if ($h->post_type=='user'):
				array_push($recs, $h);
			endif;	
		}
		return array($recs);
	else:
		// include everything
		return $hits;
	endif;
}


$gis = "options_enable_search_stemmer";
$stemmer = get_option($gis);
if ($stemmer) {
	add_filter('relevanssi_stemmer', 'relevanssi_simple_english_stemmer');
}

add_filter('relevanssi_remove_punctuation', 'saveampersands_1', 9);
function saveampersands_1($a) {
    $a = str_replace('&', 'AMPERSAND', $a);
    return $a;
}
 
add_filter('relevanssi_remove_punctuation', 'saveampersands_2', 11);
function saveampersands_2($a) {
    $a = str_replace('AMPERSAND', '&', $a);
    return $a;
}

add_filter('relevanssi_get_words_query', 'fix_query');
function fix_query($query) {
    $query = $query . " HAVING c > 1";
    return $query;
}

// Added to extend allowed file types in Media upload 
add_filter('upload_mimes', 'custom_upload_mimes'); 
function custom_upload_mimes ( $existing_mimes=array() ) { 
	// Add *.RDP files to Media upload 
	$existing_mimes['rdp'] = 'application/rdp'; 
	$existing_mimes['eps'] = 'application/eps'; 
	return $existing_mimes; 
}

//remove title functionality in bbPress which interferes with our custom page titles
remove_action('wp_title', 'bbp_title');

 function HTMLToRGB($htmlCode)
  {
    if($htmlCode[0] == '#')
      $htmlCode = substr($htmlCode, 1);

    if (strlen($htmlCode) == 3)
    {
      $htmlCode = $htmlCode[0] . $htmlCode[0] . $htmlCode[1] . $htmlCode[1] . $htmlCode[2] . $htmlCode[2];
    }
    
    $r = hexdec($htmlCode[0] . $htmlCode[1]);
    $g = hexdec($htmlCode[2] . $htmlCode[3]);
    $b = hexdec($htmlCode[4] . $htmlCode[5]);

    return $b + ($g << 0x8) + ($r << 0x10);
  }

  function RGBToHTML($RGB)
  {
    $r = 0xFF & ($RGB >> 0x10);
    $g = 0xFF & ($RGB >> 0x8);
    $b = 0xFF & $RGB;

    $r = dechex($r);
    $g = dechex($g);
    $b = dechex($b);
    
    return "#" . str_pad($r, 2, "0", STR_PAD_LEFT) . str_pad($g, 2, "0", STR_PAD_LEFT) . str_pad($b, 2, "0", STR_PAD_LEFT);
  }
  
  function ChangeLuminosity($RGB, $LuminosityPercent)
  {
    $HSL = RGBToHSL($RGB);
    $NewHSL = (int)(((float)$LuminosityPercent / 100) * 255) + (0xFFFF00 & $HSL);
    return HSLToRGB($NewHSL);
  }

  function RGBToHSL($RGB)
  {
    $r = 0xFF & ($RGB >> 0x10);
    $g = 0xFF & ($RGB >> 0x8);
    $b = 0xFF & $RGB;

    $r = ((float)$r) / 255.0;
    $g = ((float)$g) / 255.0;
    $b = ((float)$b) / 255.0;

    $maxC = max($r, $g, $b);
    $minC = min($r, $g, $b);

    $l = ($maxC + $minC) / 2.0;

    if($maxC == $minC)
    {
      $s = 0;
      $h = 0;
    }
    else
    {
      if($l < .5)
      {
        $s = ($maxC - $minC) / ($maxC + $minC);
      }
      else
      {
        $s = ($maxC - $minC) / (2.0 - $maxC - $minC);
      }
      if($r == $maxC)
        $h = ($g - $b) / ($maxC - $minC);
      if($g == $maxC)
        $h = 2.0 + ($b - $r) / ($maxC - $minC);
      if($b == $maxC)
        $h = 4.0 + ($r - $g) / ($maxC - $minC);

      $h = $h / 6.0; 
    }

    $h = (int)round(255.0 * $h);
    $s = (int)round(255.0 * $s);
    $l = (int)round(255.0 * $l);

    $HSL = $l + ($s << 0x8) + ($h << 0x10);
    return $HSL;
  }

  function HSLToRGB($HSL)
  {
    $h = 0xFF & ($HSL >> 0x10);
    $s = 0xFF & ($HSL >> 0x8);
    $l = 0xFF & $HSL;

    $h = ((float)$h) / 255.0;
    $s = ((float)$s) / 255.0;
    $l = ((float)$l) / 255.0;

    if($s == 0)
    {
      $r = $l;
      $g = $l;
      $b = $l;
    }
    else
    {
      if($l < .5)
      {
        $t2 = $l * (1.0 + $s);
      }
      else
      {
        $t2 = ($l + $s) - ($l * $s);
      }
      $t1 = 2.0 * $l - $t2;

      $rt3 = $h + 1.0/3.0;
      $gt3 = $h;
      $bt3 = $h - 1.0/3.0;

      if($rt3 < 0) $rt3 += 1.0;
      if($rt3 > 1) $rt3 -= 1.0;
      if($gt3 < 0) $gt3 += 1.0;
      if($gt3 > 1) $gt3 -= 1.0;
      if($bt3 < 0) $bt3 += 1.0;
      if($bt3 > 1) $bt3 -= 1.0;

      if(6.0 * $rt3 < 1) $r = $t1 + ($t2 - $t1) * 6.0 * $rt3;
      elseif(2.0 * $rt3 < 1) $r = $t2;
      elseif(3.0 * $rt3 < 2) $r = $t1 + ($t2 - $t1) * ((2.0/3.0) - $rt3) * 6.0;
      else $r = $t1;

      if(6.0 * $gt3 < 1) $g = $t1 + ($t2 - $t1) * 6.0 * $gt3;
      elseif(2.0 * $gt3 < 1) $g = $t2;
      elseif(3.0 * $gt3 < 2) $g = $t1 + ($t2 - $t1) * ((2.0/3.0) - $gt3) * 6.0;
      else $g = $t1;

      if(6.0 * $bt3 < 1) $b = $t1 + ($t2 - $t1) * 6.0 * $bt3;
      elseif(2.0 * $bt3 < 1) $b = $t2;
      elseif(3.0 * $bt3 < 2) $b = $t1 + ($t2 - $t1) * ((2.0/3.0) - $bt3) * 6.0;
      else $b = $t1;
    }

    $r = (int)round(255.0 * $r);
    $g = (int)round(255.0 * $g);
    $b = (int)round(255.0 * $b);

    $RGB = $b + ($g << 0x8) + ($r << 0x10);
    return $RGB;
  }

// listing page thumbnail sizes, e.g. home page
;

add_image_size( "newshead", get_option('large_size_w'), get_option('large_size_h'), true );
add_image_size( "newsmedium", 650, 200, true );
add_image_size( "square32", 32, 32, true );
add_image_size( "square66", 66, 66, true );
add_image_size( "square150", 150, 150, true );

/**
 * Determines the difference between two timestamps.
 *
 * The difference is returned in a human readable format such as "1 hour",
 * "5 mins", "2 days".
 *
 * @since 1.5.0
 *
 * @param int $from Unix timestamp from which the difference begins.
 * @param int $to Optional. Unix timestamp to end the time difference. Default becomes time() if not set.
 * @return string Human readable time difference.
 * Taken from formatting.php to include months and years - Luke Oatham 
 */
function human_time_diff_plus( $from, $to = '' ) {
	$tzone = get_option('timezone_string');
	date_default_timezone_set($tzone);

	$MONTH_IN_SECONDS = DAY_IN_SECONDS * 30;
     if ( empty( $to ) )
          $to = time();
     $diff = (int) abs( $to - $from );
     if ( $diff <= HOUR_IN_SECONDS ) {
          $mins = round( $diff / MINUTE_IN_SECONDS );
          if ( $mins <= 1 ) {
               $mins = 0;
          }
          /* translators: min=minute */
          $since = sprintf( _n( '%s min', '%s mins', $mins ), $mins );
     } elseif ( ( $diff <= DAY_IN_SECONDS ) && ( $diff > HOUR_IN_SECONDS ) ) {
          $hours = round( $diff / HOUR_IN_SECONDS );
          if ( $hours <= 1 ) {
               $hours = 1;
          }
          $since = sprintf( _n( '%s hour', '%s hours', $hours ), $hours );
     } elseif ( $diff >= YEAR_IN_SECONDS ) {
          $years = round( $diff / YEAR_IN_SECONDS );
          if ( $years <= 1 ) {
               $years = 1;
          }
          $since = sprintf( _n( '%s year', '%s years', $years ), $years );
     } elseif ( ( $diff >= $MONTH_IN_SECONDS ) && ( $diff < YEAR_IN_SECONDS ) ) {
          $months = round( $diff / $MONTH_IN_SECONDS );
          if ( $months <= 1 ) {
               $months = 1;
          }
          $since = sprintf( _n( '%s month', '%s months', $months ), $months );
     } elseif ( $diff >= DAY_IN_SECONDS ) {
          $days = round( $diff / DAY_IN_SECONDS );
          if ( $days <= 1 ) {
               $days = 1;
          }
          $since = sprintf( _n( '%s day', '%s days', $days ), $days );
     }
     return $since;
}

//Embed Video Fix
function add_secure_video_options($html) {
   if (strpos($html, "<iframe" ) !== false  && is_ssl() ) {
        $search = array('src="http://www.youtu','src="http://youtu','http://eventbrite');
        $replace = array('src="https://www.youtu','src="https://youtu'.'https://eventbrite');
        $html = str_replace($search, $replace, $html);

        return $html;
   } else {
        return $html;
   }
}
add_filter('the_content', 'add_secure_video_options', 10);



/**
 * Register additional oEmbed providers
 * 
 * @author Syed Balkhi
 * @link http://goo.gl/tccJh
 */
function iweb_register_additional_oembed_providers() {
	wp_oembed_add_provider( 'http://www.mixcloud.com/*', 'http://www.mixcloud.com/oembed' );
}
add_action( 'init', 'iweb_register_additional_oembed_providers' );

/************************************************************************************

 			REGISTER CUSTOM POST TYPES, TAXONOMIES AND CUSTOM FIELDS

************************************************************************************/

if ( get_option( 'options_module_blog' ) ):
	add_action('init', 'cptui_register_my_cpt_blog');
	function cptui_register_my_cpt_blog() {
		register_post_type('blog', array(
		'label' => _x('Blog posts','post type name','govintranet'),
		'description' => '',
		'public' => true,
		'show_ui' => true,
		'show_in_menu' => true,
		'capability_type' => 'post',
		'map_meta_cap' => true,
		'hierarchical' => false,
		'rewrite' => array('slug' => 'blog', 'with_front' => true),
		'query_var' => true,
		'has_archive' => true,
		'menu_position' => '26',
		'menu_icon' => 'dashicons-welcome-write-blog',
		'supports' => array('title','editor','excerpt','comments','revisions','thumbnail','author','post-formats'),
		'taxonomies' => array('post_tag'),
		'labels' => array (
		  'name' => _x('Blog posts','post type name plural','govintranet'),
		  'singular_name' => _x('Blog post','post type name singular','govintranet'),
		  'menu_name' => _x('Blog posts','post type name','govintranet'),
		  'add_new' => __('Add Blog post','govintranet'),
		  'add_new_item' => __('Add New Blog post','govintranet'),
		  'edit' => __('Edit','govintranet'),
		  'edit_item' => __('Edit Blog post','govintranet'),
		  'new_item' => __('New Blog post','govintranet'),
		  'view' => __('View Blog post','govintranet'),
		  'view_item' => __('View Blog post','govintranet'),
		  'search_items' => __('Search Blog posts','govintranet'),
		  'not_found' => __('No Blog posts found','govintranet'),
		  'not_found_in_trash' => __('No Blog posts found in trash','govintranet'),
		  'parent' => __('Parent Blog post','govintranet'),
		  )
		) 
		); 
	}
	$labels = array(
		"name" => "Blog categories",
		"label" => "Blog categories",
		);

	$args = array(
		"labels" => $labels,
		"hierarchical" => true,
		"label" => "Blog categories",
		"show_ui" => true,
		"query_var" => true,
		"rewrite" => array( 'slug' => 'blog-category', 'with_front' => true ),
		"show_admin_column" => true,
	);
	register_taxonomy( "blog-category", array( "blog" ), $args );
endif;
	
if ( get_option( 'options_module_events' ) ) add_action('init', 'cptui_register_my_cpt_event');
function cptui_register_my_cpt_event() {
	register_post_type('event', array(
	'label' => __('Events','govintranet'),
	'description' => '',
	'public' => true,
	'show_ui' => true,
	'show_in_menu' => true,
	'capability_type' => 'post',
	'map_meta_cap' => true,
	'hierarchical' => false,
	'rewrite' => array('slug' => 'event', 'with_front' => true),
	'query_var' => true,
	'has_archive' => true,
	'menu_position' => '28',
	'menu_icon' => 'dashicons-calendar-alt',
	'supports' => array('title','editor','excerpt','comments','revisions','thumbnail','author'),
	'taxonomies' => array('post_tag','event-type'),
	'labels' => array (
	  'name' => __('Events','govintranet'),
	  'singular_name' => __('Event','govintranet'),
	  'menu_name' => __('Events','govintranet'),
	  'add_new' => __('Add Event','govintranet'),
	  'add_new_item' => __('Add New Event','govintranet'),
	  'edit' => __('Edit','govintranet'),
	  'edit_item' => __('Edit Event','govintranet'),
	  'new_item' => __('New Event','govintranet'),
	  'view' => __('View Event','govintranet'),
	  'view_item' => __('View Event','govintranet'),
	  'search_items' => __('Search Events','govintranet'),
	  'not_found' => __('No Events Found','govintranet'),
	  'not_found_in_trash' => __('No Events found in trash','govintranet'),
	  'parent' => __('Parent Event','govintranet'),
	  )
	) 
	); 
}

if ( get_option( 'options_module_jargon_buster' ) ) add_action('init', 'cptui_register_my_cpt_jargon_buster');
function cptui_register_my_cpt_jargon_buster() {
	register_post_type('jargon-buster', array(
	'label' => __('Jargon busters','govintranet'),
	'description' => '',
	'public' => true,
	'show_ui' => true,
	'show_in_menu' => true,
	'capability_type' => 'post',
	'map_meta_cap' => true,
	'hierarchical' => false,
	'rewrite' => array('slug' => 'jargon-buster', 'with_front' => true),
	'query_var' => true,
	'has_archive' => true,
	'menu_position' => '32',
	'menu_icon' => 'dashicons-info',
	'supports' => array('title','editor','excerpt','revisions','thumbnail','author'),
	'labels' => array (
	  'name' => __('Jargon busters','govintranet'),
	  'singular_name' => __('Jargon buster','govintranet'),
	  'menu_name' => __('Jargon busters','govintranet'),
	  'add_new' => __('Add Jargon buster','govintranet'),
	  'add_new_item' => __('Add New Jargon buster','govintranet'),
	  'edit' => __('Edit','govintranet'),
	  'edit_item' => __('Edit Jargon buster','govintranet'),
	  'new_item' => __('New Jargon buster','govintranet'),
	  'view' => __('View Jargon buster','govintranet'),
	  'view_item' => __('View Jargon buster','govintranet'),
	  'search_items' => __('Search Jargon busters','govintranet'),
	  'not_found' => __('No Jargon busters found','govintranet'),
	  'not_found_in_trash' => __('No Jargon busters found in trash','govintranet'),
	  'parent' => __('Parent Jargon buster','govintranet'),
	  )
	) 
	); 
}	

if ( get_option( 'options_module_news' ) ) add_action('init', 'cptui_register_my_cpt_news');
function cptui_register_my_cpt_news() {
	register_post_type('news', array(
	'label' => __('News','govintranet'),
	'description' => '',
	'public' => true,
	'show_ui' => true,
	'show_in_menu' => true,
	'capability_type' => 'post',
	'map_meta_cap' => true,
	'hierarchical' => false,
	'rewrite' => array('slug' => 'news', 'with_front' => true),
	'query_var' => true,
	'has_archive' => true,
	'menu_position' => '34',
	'menu_icon' => 'dashicons-format-status',
	'supports' => array('title','editor','excerpt','comments','revisions','thumbnail','author','post-formats'),
	'taxonomies' => array('post_tag'),
	'labels' => array (
	  'name' => _x('News','post type plural','govintranet'),
	  'singular_name' => _x('News','post type singular','govintranet'),
	  'menu_name' => __('News','govintranet'),
	  'add_new' => __('Add News','govintranet'),
	  'add_new_item' => __('Add New News','govintranet'),
	  'edit' => __('Edit','govintranet'),
	  'edit_item' => __('Edit News','govintranet'),
	  'new_item' => __('New News','govintranet'),
	  'view' => __('View News','govintranet'),
	  'view_item' => __('View News','govintranet'),
	  'search_items' => __('Search News','govintranet'),
	  'not_found' => __('No News found','govintranet'),
	  'not_found_in_trash' => __('No News found in trash','govintranet'),
	  'parent' => __('Parent News','govintranet'),
	  )
	) 
	); 
}

if ( get_option( 'options_module_news_updates' ) ) add_action('init', 'cptui_register_my_cpt_news_update');
function cptui_register_my_cpt_news_update() {
	$labels = array(
		"name" => __("News updates",'govintranet'),
		"singular_name" => __("News update",'govintranet'),
		'menu_name' => __('News updates','govintranet'),
		'add_new' => __('Add News update','govintranet'),
		'add_new_item' => __('Add News update','govintranet'),
		'edit' => __('Edit','govintranet'),
		'edit_item' => __('Edit news update','govintranet'),
		'new_item' => __('New news update','govintranet'),
		'not_found' => __('No news updates found','govintranet'),
		'not_found_in_trash' => __('No news updates found in trash','govintranet'),
		'parent' => __('Parent news update','govintranet'),
	);

	$args = array(
		"labels" => $labels,
		"description" => "",
		"public" => true,
		"show_ui" => true,
		"has_archive" => true,
		"show_in_menu" => true,
		"exclude_from_search" => false,
		"capability_type" => "post",
		"map_meta_cap" => true,
		"hierarchical" => false,
		"rewrite" => array( "slug" => "news-update", "with_front" => true ),
		"query_var" => true,
		"menu_position" => '35',		
		"menu_icon" => "dashicons-flag",		
		"supports" => array( "title", "editor", "excerpt", "comments", "revisions", "thumbnail", "author" ),			
		"taxonomies" => array( "post_tag" ),
	);
	register_post_type( "news-update", $args );
		
	if( function_exists('acf_add_local_field_group') ) acf_add_local_field_group(array (
		'key' => 'group_558c8b74375a2',
		'title' => __('Options','govintranet'),
		'fields' => array (
			array (
				'key' => 'field_558c8b8af3329',
				'label' => __('Icon','govintranet'),
				'name' => 'news_update_icon',
				'type' => 'text',
				'instructions' => __('See http://getbootstrap.com/components/#glyphicons','govintranet'),
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'default_value' => 'flag',
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
				'maxlength' => '',
				'readonly' => 0,
				'disabled' => 0,
			),
		),
		'location' => array (
			array (
				array (
					'param' => 'taxonomy',
					'operator' => '==',
					'value' => 'news-update-type',
				),
			),
		),
		'menu_order' => 0,
		'position' => 'normal',
		'style' => 'default',
		'label_placement' => 'top',
		'instruction_placement' => 'label',
		'hide_on_screen' => '',
	));

	if( function_exists('acf_add_local_field_group') ) {
		acf_add_local_field_group(array (
			'key' => 'group_558c8496b8b94',
			'title' => __('News update auto expiry','govintranet'),
			'fields' => array (
				array (
					'key' => 'field_558c8496c4f35',
					'label' => __('Enable auto-expiry','govintranet'),
					'name' => 'news_update_auto_expiry',
					'type' => 'true_false',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'message' => '',
					'default_value' => 0,
				),
				array (
					'key' => 'field_558c8496c9d3c',
					'label' => __('Expiry date','govintranet'),
					'name' => 'news_update_expiry_date',
					'type' => 'date_picker',
					'instructions' => '',
					'required' => 1,
					'conditional_logic' => array (
						array (
							array (
								'field' => 'field_558c8496c4f35',
								'operator' => '==',
								'value' => '1',
							),
						),
					),
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'display_format' => 'd/m/Y',
					'return_format' => 'Ymd',
					'first_day' => 1,
				),
				array (
					'key' => 'field_558c8496ceb61',
					'label' => __('Expiry time','govintranet'),
					'name' => 'news_update_expiry_time',
					'type' => 'text',
					'instructions' => '',
					'required' => 1,
					'conditional_logic' => array (
						array (
							array (
								'field' => 'field_558c8496c4f35',
								'operator' => '==',
								'value' => '1',
							),
						),
					),
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'placeholder' => '',
					'prepend' => '',
					'append' => '',
					'maxlength' => '',
					'readonly' => 0,
					'disabled' => 0,
				),
				array (
					'key' => 'field_558c8496d39b4',
					'label' => __('Expiry action','govintranet'),
					'name' => 'news_update_expiry_action',
					'type' => 'select',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => array (
						array (
							array (
								'field' => 'field_558c8496c4f35',
								'operator' => '==',
								'value' => '1',
							),
						),
					),
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'choices' => array (
						'Revert to draft status' => 'Revert to draft status',
						'Move to trash' => 'Move to trash',
					),
					'default_value' => array (
						'Revert to draft status' => 'Revert to draft status',
					),
					'allow_null' => 0,
					'multiple' => 0,
					'ui' => 0,
					'ajax' => 0,
					'placeholder' => '',
					'disabled' => 0,
					'readonly' => 0,
				),
			),
			'location' => array (
				array (
					array (
						'param' => 'post_type',
						'operator' => '==',
						'value' => 'news-update',
					),
				),
			),
			'menu_order' => 10,
			'position' => 'side',
			'style' => 'default',
			'label_placement' => 'top',
			'instruction_placement' => 'label',
			'hide_on_screen' => '',
		));
	}
}


if ( get_option( 'options_module_projects' ) ) add_action('init', 'cptui_register_my_cpt_project');
function cptui_register_my_cpt_project() {
	register_post_type('project', array(
	'label' => _x('Projects','noun','govintranet'),
	'description' => '',
	'public' => true,
	'show_ui' => true,
	'show_in_menu' => true,
	'capability_type' => 'post',
	'map_meta_cap' => true,
	'hierarchical' => true,
	'rewrite' => array('slug' => 'project', 'with_front' => true),
	'query_var' => true,
	'has_archive' => true,
	'menu_position' => '36',
	'menu_icon' => 'dashicons-chart-bar',
	'supports' => array('title','editor','excerpt','comments','revisions','thumbnail','author','page-attributes'),
	'taxonomies' => array('post_tag'),
	'labels' => array (
	  'name' => _x('Projects','noun','govintranet'),
	  'singular_name' => _x('Projects','noun','govintranet'),
	  'menu_name' => _x('Projects','noun','govintranet'),
	  'add_new' => __('Add Project','govintranet'),
	  'add_new_item' => __('Add New Project','govintranet'),
	  'edit' => __('Edit','govintranet'),
	  'edit_item' => __('Edit Project','govintranet'),
	  'new_item' => __('New Project','govintranet'),
	  'view' => __('View Project','govintranet'),
	  'view_item' => __('View Project','govintranet'),
	  'search_items' => __('Search Projects','govintranet'),
	  'not_found' => __('No Projects found','govintranet'),
	  'not_found_in_trash' => __('No Projects found in trash','govintranet'),
	  'parent' => __('Parent Project','govintranet'),
	  )
	) 
	); 
}

if ( get_option( 'options_module_tasks' ) ) add_action('init', 'cptui_register_my_cpt_task');
function cptui_register_my_cpt_task() {
	register_post_type('task', array(
	'label' => __('Tasks','govintranet'),
	'description' => '',
	'public' => true,
	'show_ui' => true,
	'show_in_menu' => true,
	'capability_type' => 'post',
	'map_meta_cap' => true,
	'hierarchical' => true,
	'rewrite' => array('slug' => 'task', 'with_front' => true),
	'query_var' => true,
	'has_archive' => true,
	'menu_position' => '38',
	'menu_icon' => 'dashicons-hammer',
	'supports' => array('title','editor','excerpt','comments','revisions','author','page-attributes'),
	'taxonomies' => array('category','post_tag'),
	'labels' => array (
	  'name' => __('Tasks','govintranet'),
	  'singular_name' => __('Task','govintranet'),
	  'menu_name' => __('Tasks','govintranet'),
	  'add_new' => __('Add Task','govintranet'),
	  'add_new_item' => __('Add New Task','govintranet'),
	  'edit' => __('Edit','govintranet'),
	  'edit_item' => __('Edit Task','govintranet'),
	  'new_item' => __('New Task','govintranet'),
	  'view' => __('View Task','govintranet'),
	  'view_item' => __('View Task','govintranet'),
	  'search_items' => __('Search Tasks','govintranet'),
	  'not_found' => __('No Tasks found','govintranet'),
	  'not_found_in_trash' => __('No Tasks found in trash','govintranet'),
	  'parent' => __('Parent Task','govintranet'),
	  )
	) 
	); 
	if ( get_option( 'options_module_tasks_manuals' ) ):
		acf_add_local_field_group(array (
			'key' => 'group_56a40dcab6d85',
			'title' => 'Manual',
			'fields' => array (
				array (
					'key' => 'field_56a40dec54af7',
					'label' => 'Treat as a manual',
					'name' => 'treat_as_a_manual',
					'type' => 'true_false',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'message' => '',
					'default_value' => 0,
				),
				array (
					'key' => 'field_56a40e2754af8',
					'label' => 'Manual chapters',
					'name' => 'manual_chapters',
					'type' => 'repeater',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => array (
						array (
							array (
								'field' => 'field_56a40dec54af7',
								'operator' => '==',
								'value' => '1',
							),
						),
					),
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'collapsed' => '',
					'min' => '',
					'max' => '',
					'layout' => 'row',
					'button_label' => 'Add a chapter',
					'sub_fields' => array (
						array (
							'key' => 'field_56a40e5854af9',
							'label' => 'Chapter title',
							'name' => 'manual_chapter_title',
							'type' => 'text',
							'instructions' => '',
							'required' => 1,
							'conditional_logic' => 0,
							'wrapper' => array (
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'default_value' => '',
							'placeholder' => '',
							'prepend' => '',
							'append' => '',
							'maxlength' => '',
							'readonly' => 0,
							'disabled' => 0,
						),
						array (
							'key' => 'field_56a40e7354afa',
							'label' => 'Content',
							'name' => 'manual_chapter_content',
							'type' => 'wysiwyg',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => 0,
							'wrapper' => array (
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'default_value' => '',
							'tabs' => 'all',
							'toolbar' => 'full',
							'media_upload' => 1,
						),
					),
				),
			),
			'location' => array (
				array (
					array (
						'param' => 'post_type',
						'operator' => '==',
						'value' => 'task',
					),
				),
			),
			'menu_order' => 0,
			'position' => 'normal',
			'style' => 'default',
			'label_placement' => 'top',
			'instruction_placement' => 'label',
			'hide_on_screen' => '',
			'active' => 1,
			'description' => '',
		));
	endif;
}

if ( get_option( 'options_module_teams' ) ) add_action('init', 'cptui_register_my_cpt_team');
function cptui_register_my_cpt_team() {
	register_post_type('team', array(
	'label' => __('Teams','govintranet'),
	'description' => '',
	'public' => true,
	'show_ui' => true,
	'show_in_menu' => true,
	'capability_type' => 'post',
	'map_meta_cap' => true,
	'hierarchical' => true,
	'rewrite' => array('slug' => 'team', 'with_front' => true),
	'query_var' => true,
	'has_archive' => true,
	'menu_position' => '40',
	'menu_icon' => 'dashicons-groups',
	'supports' => array('title','editor','excerpt','revisions','thumbnail','author','page-attributes'),
	'taxonomies' => array('post_tag'),
	'labels' => array (
	  'name' => __('Teams','govintranet'),
	  'singular_name' => __('Team','govintranet'),
	  'menu_name' => __('Teams','govintranet'),
	  'add_new' => __('Add Team','govintranet'),
	  'add_new_item' => __('Add New Team','govintranet'),
	  'edit' => __('Edit','govintranet'),
	  'edit_item' => __('Edit Team','govintranet'),
	  'new_item' => __('New Team','govintranet'),
	  'view' => __('View Team','govintranet'),
	  'view_item' => __('View Team','govintranet'),
	  'search_items' => __('Search Teams','govintranet'),
	  'not_found' => __('No Teams found','govintranet'),
	  'not_found_in_trash' =>__( 'No Teams found in trash','govintranet'),
	  'parent' => __('Parent Team','govintranet'),
	  )
	) 
	); 
}

if ( get_option( 'options_module_vacancies' ) ) add_action('init', 'cptui_register_my_cpt_vacancy');
function cptui_register_my_cpt_vacancy() {
	register_post_type('vacancy', array(
	'label' => __('Vacancies','govintranet'),
	'description' => '',
	'public' => true,
	'show_ui' => true,
	'show_in_menu' => true,
	'capability_type' => 'post',
	'map_meta_cap' => true,
	'hierarchical' => false,
	'rewrite' => array('slug' => 'vacancy', 'with_front' => true),
	'query_var' => true,
	'has_archive' => true,
	'menu_position' => '42',
	'menu_icon' => 'dashicons-randomize',
	'supports' => array('title','editor','excerpt','comments','revisions','thumbnail','author'),
	'taxonomies' => array('post_tag','grade'),
	'labels' => array (
	  'name' => __('Vacancies','govintranet'),
	  'singular_name' => __('Vacancy','govintranet'),
	  'menu_name' => __('Vacancies','govintranet'),
	  'add_new' => __('Add Vacancy','govintranet'),
	  'add_new_item' => __('Add New Vacancy','govintranet'),
	  'edit' => __('Edit','govintranet'),
	  'edit_item' => __('Edit Vacancy','govintranet'),
	  'new_item' => __('New Vacancy','govintranet'),
	  'view' => __('View Vacancy','govintranet'),
	  'view_item' => __('View Vacancy','govintranet'),
	  'search_items' => __('Search Vacancies','govintranet'),
	  'not_found' => __('No Vacancies found','govintranet'),
	  'not_found_in_trash' => __('No Vacancies found in trash','govintranet'),
	  'parent' => __('Parent Vacancy','govintranet'),
	  )
	) 
	); 
}

if ( get_option( 'options_module_news' ) ) add_action('init', 'cptui_register_my_taxes_news_type');
function cptui_register_my_taxes_news_type() {
	register_taxonomy( 'news-type',array (
	  0 => 'news',
	),
	array( 'hierarchical' => true,
		'label' => __('News types','govintranet'),
		'show_ui' => true,
		'query_var' => true,
		'show_admin_column' => true,
		'labels' => array (
	  'search_items' => __('News type','govintranet'),
	  'popular_items' => __('Popular types','govintranet'),
	  'all_items' => __('All types','govintranet'),
	  'parent_item' => __('Parent type','govintranet'),
	  'parent_item_colon' => '',
	  'edit_item' => __('Edit news type','govintranet'),
	  'update_item' => __('Update news type','govintranet'),
	  'add_new_item' => __('Add news type','govintranet'),
	  'new_item_name' => __('New type','govintranet'),
	  'separate_items_with_commas' => '',
	  'add_or_remove_items' => __('Add or remove a type','govintranet'),
	  'choose_from_most_used' => __('Most used','govintranet'),
	  ),
 		'update_count_callback' => 'ht_update_post_term_count' 
	) 
	); 
}

if ( get_option( 'options_module_news_updates' ) ) add_action('init', 'cptui_register_my_taxes_news_update_type');
function cptui_register_my_taxes_news_update_type() {
	$labels = array(
		"label" => __("News update type",'govintranet'),
			);

	$args = array(
		"labels" => $labels,
		"hierarchical" => true,
		"label" => __("News update types",'govintranet'),
		"show_ui" => true,
		"query_var" => true,
		"rewrite" => array( 'slug' => 'news-update-type', 'with_front' => true ),
		"show_admin_column" => true,
		'update_count_callback' => 'ht_update_post_term_count' 
	);
	register_taxonomy( "news-update-type", array( "news-update" ), $args );
}

if ( get_option( 'options_module_vacancies' ) || get_option ( 'options_module_staff_directory' ) ) add_action('init', 'cptui_register_my_taxes_grade');
function cptui_register_my_taxes_grade() {
	register_taxonomy( 'grade',array (
	  0 => 'post',
	  1 => 'vacancy',
	),
	array( 'hierarchical' => true,
		'label' => __('Grades','govintranet'),
		'show_ui' => true,
		'query_var' => true,
		'show_admin_column' => true,
		'labels' => array (
	  'search_items' => __('Grade','govintranet'),
	  'popular_items' => __('Popular grades','govintranet'),
	  'all_items' => __('All grades','govintranet'),
	  'parent_item' => __('Parent grade','govintranet'),
	  'parent_item_colon' => '',
	  'edit_item' => __('Edit grade','govintranet'),
	  'update_item' => __('Update grade','govintranet'),
	  'add_new_item' => __('Add new grade','govintranet'),
	  'new_item_name' => __('New grade','govintranet'),
	  'separate_items_with_commas' => '',
	  'add_or_remove_items' => __('Add or remove a grade','govintranet'),
	  'choose_from_most_used' => __('Most used','govintranet'),
	  ),
	'update_count_callback' => 'ht_update_post_term_count' 	  
	) 
	); 
}

if ( get_option( 'options_module_events' ) ) add_action('init', 'cptui_register_my_taxes_event_type');
function cptui_register_my_taxes_event_type() {
	register_taxonomy( 'event-type',array (
	  0 => 'event',
	),
	array( 'hierarchical' => true,
		'label' => __('Event types','govintranet'),
		'show_ui' => true,
		'query_var' => true,
		'show_admin_column' => true,
		'labels' => array (
	  'search_items' => __('Event type','govintranet'),
	  'popular_items' => __('Popular types','govintranet'),
	  'all_items' => __('All types','govintranet'),
	  'parent_item' => __('Parent event type','govintranet'),
	  'parent_item_colon' => '',
	  'edit_item' => __('Edit event type','govintranet'),
	  'update_item' => __('Update event type','govintranet'),
	  'add_new_item' => __('Add event type','govintranet'),
	  'new_item_name' => __('New event type','govintranet'),
	  'separate_items_with_commas' => '',
	  'add_or_remove_items' => __('Add or remove event types','govintranet'),
	  'choose_from_most_used' => __('Most used','govintranet'),
	  ),
	'update_count_callback' => 'ht_update_post_term_count' 	  
	) 
	); 
}

if ( get_option( 'options_module_a_to_z' ) ) add_action('init', 'cptui_register_my_taxes_a_to_z');
function cptui_register_my_taxes_a_to_z() {
	register_taxonomy( 'a-to-z',array (
	  0 => 'page',
	  1 => 'task',
	  2 => 'team',
	  3 => 'project',
	),
	array( 
		'hierarchical' => true,
		'label' => __('A to Z letters','govintranet'),
		'show_ui' => true,
		'query_var' => true,
		'show_admin_column' => true,
		'labels' => array (
		'search_items' => __('A to Z letter','govintranet'),
		'popular_items' => __('Popular letters','govintranet'),
		'all_items' => __('All letters','govintranet'),
		'parent_item' => __('Parent letter','govintranet'),
		'parent_item_colon' => '',
		'edit_item' => __('Edit letter','govintranet'),
		'update_item' => __('Update letter','govintranet'),
		'add_new_item' => __('Add new letter','govintranet'),
		'new_item_name' => _x('Letter','alphabet','govintranet'),
		'separate_items_with_commas' => '',
		'add_or_remove_items' => __('Add or remove letters','govintranet'),
		'choose_from_most_used' => __('Most used','govintranet'),
		),
		'update_count_callback' => 'ht_update_post_term_count' 
	) 
	); 
}

add_action('init', 'cptui_register_my_taxes_document_type');
function cptui_register_my_taxes_document_type() {
	register_taxonomy( 'document-type',array (
	  0 => 'post',
	),
	array( 'hierarchical' => true,
		'label' => __('Document types','govintranet'),
		'show_ui' => true,
		'query_var' => true,
		'show_admin_column' => false,
		'labels' => array (
	  'search_items' => __('Document type','govintranet'),
	  'popular_items' => __('Popular types','govintranet'),
	  'all_items' => __('All types','govintranet'),
	  'parent_item' => __('Parent document type','govintranet'),
	  'parent_item_colon' => '',
	  'edit_item' => __('Edit document type','govintranet'),
	  'update_item' => __('Update document type','govintranet'),
	  'add_new_item' => __('Add document type','govintranet'),
	  'new_item_name' => __('New document type','govintranet'),
	  'separate_items_with_commas' => '',
	  'add_or_remove_items' => __('Add or remove a document type','govintranet'),
	  'choose_from_most_used' => __('Most used','govintranet'),
	  )
	) 
	); 
}

if( function_exists('acf_add_local_field_group') ):

	acf_add_local_field_group(array (
		'key' => 'group_53bd5ee04bd4d',
		'title' => __('Category','govintranet'),
		'fields' => array (
			array (
				'key' => 'field_536ecbde02869',
				'label' => __('Text colour','govintranet'),
				'name' => 'cat_foreground_colour',
				'prefix' => '',
				'type' => 'color_picker',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'default_value' => '',
			),
			array (
				'key' => 'field_536ecbee0286a',
				'label' => __('Background colour','govintranet'),
				'name' => 'cat_background_colour',
				'prefix' => '',
				'type' => 'color_picker',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'default_value' => '',
			),
			array (
				'key' => 'field_536ecba302868',
				'label' => __('Long description','govintranet'),
				'name' => 'cat_long_description',
				'prefix' => '',
				'type' => 'wysiwyg',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'default_value' => '',
				'toolbar' => 'full',
				'media_upload' => 1,
			),
		),
		'location' => array (
			array (
				array (
					'param' => 'taxonomy',
					'operator' => '==',
					'value' => 'category',
				),
			),
		),
		'menu_order' => 0,
		'position' => 'normal',
		'style' => 'seamless',
		'label_placement' => 'top',
		'instruction_placement' => 'label',
		'hide_on_screen' => array (
		),
	));
	
	acf_add_local_field_group(array (
		'key' => 'group_54cd1e8380c49',
		'title' => __('Grades','govintranet'),
		'fields' => array (
			array (
				'key' => 'field_54cd1e8c7b238',
				'label' => __('Grade code','govintranet'),
				'name' => 'grade_code',
				'prefix' => '',
				'type' => 'text',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'default_value' => '',
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
				'maxlength' => '',
				'readonly' => 0,
				'disabled' => 0,
			),
		),
		'location' => array (
			array (
				array (
					'param' => 'taxonomy',
					'operator' => '==',
					'value' => 'grade',
				),
			),
		),
		'menu_order' => 0,
		'position' => 'normal',
		'style' => 'default',
		'label_placement' => 'top',
		'instruction_placement' => 'label',
		'hide_on_screen' => '',
	));
	
	acf_add_local_field_group(array (
		'key' => 'group_54cd25add8aaa',
		'title' => __('Teams','govintranet'),
		'fields' => array (
			array (
				'key' => 'field_54cd25b266b0f',
				'label' => __('Team lead','govintranet'),
				'name' => 'team_lead',
				'prefix' => '',
				'type' => 'user',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'role' => '',
				'allow_null' => 1,
				'multiple' => 1,
			),
		),
		'location' => array (
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'team',
				),
			),
		),
		'menu_order' => 0,
		'position' => 'normal',
		'style' => 'default',
		'label_placement' => 'top',
		'instruction_placement' => 'label',
		'hide_on_screen' => '',
	));
	
	acf_add_local_field_group(array (
		'key' => 'group_53bd5ee05808f',
		'title' => __('Events','govintranet'),
		'fields' => array (
			array (
				'key' => 'field_536ecdf48462e',
				'label' => __('Start date','govintranet'),
				'name' => 'event_start_date',
				'prefix' => '',
				'type' => 'date_picker',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'display_format' => 'd/m/Y',
				'return_format' => 'd/m/Y',
				'first_day' => 1,
			),
			array (
				'key' => 'field_536ece118462f',
				'label' => __('End date','govintranet'),
				'name' => 'event_end_date',
				'prefix' => '',
				'type' => 'date_picker',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'display_format' => 'd/m/Y',
				'return_format' => 'd/m/Y',
				'first_day' => 1,
			),
			array (
				'key' => 'field_53d256390bc51',
				'label' => __('Start time','govintranet'),
				'name' => 'event_start_time',
				'prefix' => '',
				'type' => 'text',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'default_value' => '',
				'placeholder' => __('Use 24 hour format 14:32','govintranet'),
				'prepend' => '',
				'append' => '',
				'maxlength' => '',
				'readonly' => 0,
				'disabled' => 0,
			),
			array (
				'key' => 'field_53d256630bc52',
				'label' => __('End time','govintranet'),
				'name' => 'event_end_time',
				'prefix' => '',
				'type' => 'text',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'default_value' => '',
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
				'maxlength' => '',
				'readonly' => 0,
				'disabled' => 0,
			),
			array (
				'key' => 'field_536ece2684630',
				'label' => 'Gravity Forms ID',
				'name' => 'event_gravity_forms_id',
				'prefix' => '',
				'type' => 'number',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'default_value' => '',
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
				'min' => '',
				'max' => '',
				'step' => '',
				'readonly' => 0,
				'disabled' => 0,
			),
			array (
				'key' => 'field_536ece4884631',
				'label' => __('Eventbrite ticket #','govintranet'),
				'name' => 'eventbrite_ticket',
				'prefix' => '',
				'type' => 'text',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'default_value' => '',
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
				'formatting' => 'html',
				'maxlength' => '',
				'readonly' => 0,
				'disabled' => 0,
			),
			array (
				'key' => 'field_547a52aa11a3e',
				'label' => __('Event location name','govintranet'),
				'name' => 'event_location',
				'prefix' => '',
				'type' => 'text',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'default_value' => '',
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
				'maxlength' => '',
				'readonly' => 0,
				'disabled' => 0,
			),
			array (
				'key' => 'field_547a52d911a3f',
				'label' => __('Event map location','govintranet'),
				'name' => 'event_map_location',
				'prefix' => '',
				'type' => 'google_map',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'center_lat' => '-0.4',
				'center_lng' => '51.3',
				'zoom' => 10,
				'height' => '',
			),
		),
		'location' => array (
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'event',
				),
			),
		),
		'menu_order' => 0,
		'position' => 'normal',
		'style' => 'seamless',
		'label_placement' => 'top',
		'instruction_placement' => 'label',
		'hide_on_screen' => '',
	));
	
	acf_add_local_field_group(array (
		'key' => 'group_53bd5ee0643f8',
		'title' => __('External link','govintranet'),
		'fields' => array (
			array (
				'key' => 'field_536ec7ecd5837',
				'label' => __('External link','govintranet'),
				'name' => 'external_link',
				'prefix' => '',
				'type' => 'text',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'default_value' => '',
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
				'formatting' => 'html',
				'maxlength' => '',
				'readonly' => 0,
				'disabled' => 0,
			),
		),
		'location' => array (
			array (
				array (
					'param' => 'post_format',
					'operator' => '==',
					'value' => 'link',
				),
			),
		),
		'menu_order' => 0,
		'position' => 'normal',
		'style' => 'seamless',
		'label_placement' => 'top',
		'instruction_placement' => 'label',
		'hide_on_screen' => array (
			0 => 'the_content',
		),
	));
	
	$homepage = get_page_by_title( 'Home', OBJECT, 'page' );
	if (!$homepage) $homepage = get_page_by_title( 'Homepage', OBJECT, 'page' ); 
	if ($homepageid = $homepage->ID):
		acf_add_local_field_group(array (
			'key' => 'group_53bd5ee06e039',
			'title' => __('Homepage','govintranet'),
			'fields' => array (
				array (
					'key' => 'field_536f714eb8aae',
					'label' => __('Emergency message style','govintranet'),
					'name' => 'emergency_message_style',
					'prefix' => '',
					'type' => 'select',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'choices' => array (
						'None' => 'None',
						'Success' => 'Success',
						'Info' => 'Info',
						'Warning' => 'Warning',
						'Danger' => 'Danger',
					),
					'default_value' => array (
					),
					'allow_null' => 0,
					'multiple' => 0,
					'ui' => 0,
					'ajax' => 0,
					'placeholder' => '',
					'disabled' => 0,
					'readonly' => 0,
				),
				array (
					'key' => 'field_536f71aab8aaf',
					'label' => __('Emergency message','govintranet'),
					'name' => 'emergency_message',
					'prefix' => '',
					'type' => 'wysiwyg',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => array (
						array (
							array (
								'field' => 'field_536f714eb8aae',
								'operator' => '!=',
								'value' => 'None',
							),
						),
					),
					'default_value' => '',
					'toolbar' => 'full',
					'media_upload' => 1,
				),
				array (
					'key' => 'field_536f71c4b8ab0',
					'label' => __('Campaign message','govintranet'),
					'name' => 'campaign_message',
					'prefix' => '',
					'type' => 'wysiwyg',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'default_value' => '',
					'toolbar' => 'full',
					'media_upload' => 1,
				),
			),
			'location' => array (
				array (
					array (
						'param' => 'page',
						'operator' => '==',
						'value' => $homepageid,
					),
				),
			),
			'menu_order' => 0,
			'position' => 'normal',
			'style' => 'normal',
			'label_placement' => 'top',
			'instruction_placement' => 'label',
			'hide_on_screen' => array (
			),
		));
	endif;

	acf_add_local_field_group(array (
		'key' => 'group_53bd5ee07ca71',
		'title' => __('Intranet configuration','govintranet'),
		'fields' => array (
			array (
				'key' => 'field_536f7306a21ae',
				'label' => __('Style','govintranet'),
				'name' => '',
				'type' => 'tab',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'placement' => 'top',
				'endpoint' => 0,
			),
			array (
				'key' => 'field_536f7343a21b0',
				'label' => __('Header logo','govintranet'),
				'name' => 'header_logo',
				'type' => 'image',
				'instructions' => __('Appears top-left in the header before your site title.','govintranet'),
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'return_format' => 'array',
				'preview_size' => 'full',
				'library' => 'all',
				'min_width' => '',
				'min_height' => '',
				'min_size' => '',
				'max_width' => '',
				'max_height' => '',
				'max_size' => '',
				'mime_types' => '',
			),
			array (
				'key' => 'field_536f75f2a21c2',
				'label' => __('Login logo','govintranet'),
				'name' => 'login_logo',
				'type' => 'image',
				'instructions' => __('Appears above the login form. Ideal size: 320 x 84px','govintranet'),
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'return_format' => 'array',
				'preview_size' => 'full',
				'library' => 'all',
				'min_width' => '',
				'min_height' => '',
				'min_size' => '',
				'max_width' => '',
				'max_height' => '',
				'max_size' => '',
				'mime_types' => '',
			),
			array (
				'key' => 'field_536f7373a21b1',
				'label' => __('Widget border height','govintranet'),
				'name' => 'widget_border_height',
				'type' => 'number',
				'instructions' => __('Height in pixels of the border that appears above widget titles.','govintranet'),
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'default_value' => '',
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
				'min' => '',
				'max' => '',
				'step' => '',
				'readonly' => 0,
				'disabled' => 0,
			),
			array (
				'key' => 'field_536f7388a21b2',
				'label' => __('Enable automatic complementary colour','govintranet'),
				'name' => 'enable_automatic_complementary_colour',
				'type' => 'true_false',
				'instructions' => __('Works in conjunction with the header background colour. Enabling this setting will provide a complementary colour for borders above widget titles. Disable to choose your own colour.','govintranet'),
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'message' => '',
				'default_value' => 0,
			),
			array (
				'key' => 'field_53827c0d41550',
				'label' => __('Complementary colour','govintranet'),
				'name' => 'complementary_colour',
				'type' => 'color_picker',
				'instructions' => __('Colour of the border above widget titles.','govintranet'),
				'required' => 0,
				'conditional_logic' => array (
					array (
						array (
							'field' => 'field_536f7388a21b2',
							'operator' => '!=',
							'value' => '1',
						),
					),
				),
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'default_value' => '',
			),
			array (
				'key' => 'field_536f75cda21c1',
				'label' => __('Custom CSS code','govintranet'),
				'name' => 'custom_css_code',
				'type' => 'textarea',
				'instructions' => __('Advanced users only! Customise theme styles with your own CSS.','govintranet'),
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'default_value' => '',
				'placeholder' => '',
				'maxlength' => '',
				'rows' => '',
				'new_lines' => '',
				'readonly' => 0,
				'disabled' => 0,
			),
			array (
				'key' => 'field_536f73a2a21b3',
				'label' => __('Search','govintranet'),
				'name' => '',
				'type' => 'tab',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'placement' => 'top',
				'endpoint' => 0,
			),
			array (
				'key' => 'field_536f73b5a21b4',
				'label' => __('Enable helpful search','govintranet'),
				'name' => 'enable_helpful_search',
				'type' => 'true_false',
				'instructions' => __('If search finds a perfect match result, go directly to the page instead of showing search results.','govintranet'),
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'message' => '',
				'default_value' => 0,
			),
			array (
				'key' => 'field_536f73cca21b5',
				'label' => __('Enable search stemmer','govintranet'),
				'name' => 'enable_search_stemmer',
				'type' => 'true_false',
				'instructions' => __('Enrich search queries by also searching for derivatives. E.g. searching for "speak" will also search for speakers and speaking etc.','govintranet'),
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'message' => '',
				'default_value' => 0,
			),
			array (
				'key' => 'field_536f73e6a21b6',
				'label' => __('Search placeholder','govintranet'),
				'name' => 'search_placeholder',
				'type' => 'text',
				'instructions' => __('Enter phrases separated by a comma to use as a nudge in the search box.	Phrases will appear at random with the first phrase appearing most frequently.','govintranet'),
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'default_value' => '',
				'placeholder' => __('Search the intranet, Search the intranet e.g. book a meeting room, Search for anything','govintranet'),
				'prepend' => '',
				'append' => '',
				'maxlength' => '',
				'readonly' => 0,
				'disabled' => 0,
			),
			array (
				'key' => 'field_55cfbabe6a350',
				'label' => __('Jumbo searchbox','govintranet'),
				'name' => 'search_jumbo_searchbox',
				'type' => 'true_false',
				'instructions' => __('Displays are full-width search box and removes the regular search box on the homepage only.','govintranet'),
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'message' => '',
				'default_value' => 0,
			),
			array (
				'key' => 'field_55a6b1424565d',
				'label' => __('Override search button icon','govintranet'),
				'name' => 'search_button_override',
				'type' => 'true_false',
				'instructions' => __('Override the default magnifying glass icon search boxes.','govintranet'),
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'message' => '',
				'default_value' => 0,
			),
			array (
				'key' => 'field_55a6b040e108a',
				'label' => __('Search button text','govintranet'),
				'name' => 'search_button_text',
				'type' => 'text',
				'instructions' => __('Text to replace the default magnifying glass icon search boxes.','govintranet'),
				'required' => 0,
				'conditional_logic' => array (
					array (
						array (
							'field' => 'field_55a6b1424565d',
							'operator' => '==',
							'value' => '1',
						),
					),
				),
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'default_value' => __('Search','govintranet'),
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
				'maxlength' => '',
				'readonly' => 0,
				'disabled' => 0,
			),
			array (
				'key' => 'field_536f741ca21b7',
				'label' => __('Analytics','govintranet'),
				'name' => '',
				'type' => 'tab',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'placement' => 'top',
				'endpoint' => 0,
			),
			array (
				'key' => 'field_536f747ca21bb',
				'label' => __('Track homepage','govintranet'),
				'name' => 'track_homepage',
				'type' => 'true_false',
				'instructions' => __('Track the intranet homepage in Google Analytics. If your intranet loads automatically in the browser then you may want to turn off tracking on the homepage.','govintranet'),
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'message' => '',
				'default_value' => 0,
			),
			array (
				'key' => 'field_536f7590a21c0',
				'label' => __('Google tracking code','govintranet'),
				'name' => 'google_tracking_code',
				'type' => 'textarea',
				'instructions' => __('You can also enter custom Javascript here. Advanced users only.','govintranet'),
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'default_value' => '',
				'placeholder' => '',
				'maxlength' => '',
				'rows' => '',
				'new_lines' => '',
				'readonly' => 0,
				'disabled' => 0,
			),
			array (
				'key' => 'field_536f74cfa21bc',
				'label' => __('General','govintranet'),
				'name' => '',
				'type' => 'tab',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'placement' => 'top',
				'endpoint' => 0,
			),
			array (
				'key' => 'field_536f74f2a21bd',
				'label' => __('Search not found','govintranet'),
				'name' => 'search_not_found',
				'type' => 'text',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'default_value' => '',
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
				'formatting' => 'html',
				'maxlength' => '',
				'readonly' => 0,
				'disabled' => 0,
			),
			array (
				'key' => 'field_55dddf0ea5852',
				'label' => __('Hide reciprocal related links','govintranet'),
				'name' => 'hide_reciprocal_related_links',
				'type' => 'true_false',
				'instructions' => __('By default, if you create a related link on one page it will be also be displayed as a related link on the destination page. Enable this option to make related links one-way.','govintranet'),
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'message' => '',
				'default_value' => 0,
			),
			array (
				'key' => 'field_536f7505a21be',
				'label' => __('404 page not found','govintranet'),
				'name' => 'page_not_found',
				'type' => 'text',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'default_value' => '',
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
				'formatting' => 'html',
				'maxlength' => '',
				'readonly' => 0,
				'disabled' => 0,
			),
			array (
				'key' => 'field_536f751fa21bf',
				'label' => __('Need to know icon','govintranet'),
				'name' => 'need_to_know_icon',
				'type' => 'text',
				'instructions' => __('See http://getbootstrap.com/components/#glyphicons','govintranet'),
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'default_value' => '',
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
				'formatting' => 'html',
				'maxlength' => '',
				'readonly' => 0,
				'disabled' => 0,
			),
			array (
				'key' => 'field_55fe03fa315df',
				'label' => __('Comment instructions (logged in)','govintranet'),
				'name' => 'comment_instructions_logged_in',
				'type' => 'wysiwyg',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'default_value' => '',
				'tabs' => 'all',
				'toolbar' => 'basic',
				'media_upload' => 1,
			),
			array (
				'key' => 'field_56043fdd3b3c5',
				'label' => __('Comment instructions (logged out)','govintranet'),
				'name' => 'comment_instructions_logged_out',
				'type' => 'wysiwyg',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'default_value' => '',
				'tabs' => 'all',
				'toolbar' => 'basic',
				'media_upload' => 1,
			),			
			array (
				'key' => 'field_545ec3c99411a',
				'label' => __('Homepage auto refresh','govintranet'),
				'name' => 'homepage_auto_refresh',
				'type' => 'number',
				'instructions' => __('Number of minutes to wait before refreshing the homepage. Enter 0 for no refresh.','govintranet'),
				'required' => 1,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'default_value' => 15,
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
				'min' => 0,
				'max' => 480,
				'step' => 1,
				'readonly' => 0,
				'disabled' => 0,
			),
			array (
				'key' => 'field_536f9ff7a8af3',
				'label' => __('Modules','govintranet'),
				'name' => '',
				'type' => 'tab',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'placement' => 'top',
				'endpoint' => 0,
			),
			array (
				'key' => 'field_536fa13da8af4',
				'label' => __('News','govintranet'),
				'name' => 'module_news',
				'type' => 'true_false',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'message' => '',
				'default_value' => 0,
			),
			array (
				'key' => 'field_536fa152a8af5',
				'label' => __('News page','govintranet'),
				'name' => 'module_news_page',
				'type' => 'relationship',
				'instructions' => '',
				'required' => 1,
				'conditional_logic' => array (
					array (
						array (
							'field' => 'field_536fa13da8af4',
							'operator' => '==',
							'value' => '1',
						),
					),
				),
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'return_format' => 'object',
				'post_type' => array (
					0 => 'page',
				),
				'taxonomy' => array (
				),
				'filters' => array (
					0 => 'search',
				),
				'result_elements' => '',
				'max' => '',
				'elements' => array (
				),
				'min' => 0,
			),
			array (
				'key' => 'field_558dd3eeeda3b',
				'label' => __('News updates','govintranet'),
				'name' => 'module_news_updates',
				'type' => 'true_false',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'message' => '',
				'default_value' => 0,
			),
			array (
				'key' => 'field_536fa173a8af6',
				'label' => __('Tasks and guides','govintranet'),
				'name' => 'module_tasks',
				'type' => 'true_false',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'message' => '',
				'default_value' => 0,
			),
			array (
				'key' => 'field_536fa18ca8af7',
				'label' => __('How do I? page','govintranet'),
				'name' => 'module_tasks_page',
				'type' => 'relationship',
				'instructions' => '',
				'required' => 1,
				'conditional_logic' => array (
					array (
						array (
							'field' => 'field_536fa173a8af6',
							'operator' => '==',
							'value' => '1',
						),
					),
				),
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'return_format' => 'object',
				'post_type' => array (
					0 => 'page',
				),
				'taxonomy' => array (
				),
				'filters' => array (
					0 => 'search',
				),
				'result_elements' => '',
				'max' => '',
				'elements' => array (
				),
				'min' => 0,
			),
			array (
				'key' => 'field_54dfc0fa682ea',
				'label' => __('Only show tags applicable to tasks and guides','govintranet'),
				'name' => 'module_tasks_showtags',
				'type' => 'true_false',
				'instructions' => __('If checked, will display a plain tag cloud showing only tags found in tasks and guides. If unchecked, will display tags from the whole intranet in variable font sizes, indicating volume of content.','govintranet'),
				'required' => 0,
				'conditional_logic' => array (
					array (
						array (
							'field' => 'field_536fa173a8af6',
							'operator' => '==',
							'value' => '1',
						),
					),
				),
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'message' => '',
				'default_value' => 0,
			),
			array (
				'key' => 'field_56a40f4efa805',
				'label' => 'Manuals',
				'name' => 'module_tasks_manuals',
				'type' => 'true_false',
				'instructions' => 'Add functionality for manuals within tasks.',
				'required' => 0,
				'conditional_logic' => array (
					array (
						array (
							'field' => 'field_536fa173a8af6',
							'operator' => '==',
							'value' => '1',
						),
					),
				),
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'message' => '',
				'default_value' => 0,
			),			
			array (
				'key' => 'field_55f451fdf1fa4',
				'label' => __('Icon for tasks','govintranet'),
				'name' => 'module_tasks_icon_tasks',
				'type' => 'text',
				'instructions' => __('Supports glyphicons and dashicons. Use the full CSS class e.g. glyphicon glyphicon-file
	http://getbootstrap.com/components/#glyphicons
	https://developer.wordpress.org/resource/dashicons/','govintranet'),
				'required' => 0,
				'conditional_logic' => array (
					array (
						array (
							'field' => 'field_536fa173a8af6',
							'operator' => '==',
							'value' => '1',
						),
					),
				),
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'default_value' => 'glyphicon glyphicon-file',
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
				'maxlength' => '',
				'readonly' => 0,
				'disabled' => 0,
			),
			array (
				'key' => 'field_55f452abf1fa5',
				'label' => __('Icon for guides','govintranet'),
				'name' => 'module_tasks_icon_guides',
				'type' => 'text',
				'instructions' => __('Supports glyphicons and dashicons. Use the full CSS class e.g. glyphicon glyphicon-file
	http://getbootstrap.com/components/#glyphicons
	https://developer.wordpress.org/resource/dashicons/','govintranet'),
				'required' => 0,
				'conditional_logic' => array (
					array (
						array (
							'field' => 'field_536fa173a8af6',
							'operator' => '==',
							'value' => '1',
						),
					),
				),
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'default_value' => 'glyphicon glyphicon-duplicate',
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
				'maxlength' => '',
				'readonly' => 0,
				'disabled' => 0,
			),		
			array (
				'key' => 'field_55cfd2dc57466',
				'label' => __('Start with tags open','govintranet'),
				'name' => 'module_tasks_tags_open',
				'type' => 'true_false',
				'instructions' => __('If checked, will automatically open the "Browse tags" button on individual task category pages.','govintranet'),
				'required' => 0,
				'conditional_logic' => array (
					array (
						array (
							'field' => 'field_536fa173a8af6',
							'operator' => '==',
							'value' => '1',
						),
					),
				),
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'message' => '',
				'default_value' => 0,
			),
			array (
				'key' => 'field_536fa1b3a8af8',
				'label' => _x('Projects','noun','govintranet'),
				'name' => 'module_projects',
				'type' => 'true_false',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'message' => '',
				'default_value' => 0,
			),
			array (
				'key' => 'field_536fa1d7a8af9',
				'label' => __('Projects page','govintranet'),
				'name' => 'module_projects_page',
				'type' => 'relationship',
				'instructions' => '',
				'required' => 1,
				'conditional_logic' => array (
					array (
						array (
							'field' => 'field_536fa1b3a8af8',
							'operator' => '==',
							'value' => '1',
						),
					),
				),
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'return_format' => 'object',
				'post_type' => array (
					0 => 'page',
				),
				'taxonomy' => array (
				),
				'filters' => array (
					0 => 'search',
				),
				'result_elements' => '',
				'max' => '',
				'elements' => array (
				),
				'min' => 0,
			),
			array (
				'key' => 'field_536fa1eea8afa',
				'label' => __('Vacancies','govintranet'),
				'name' => 'module_vacancies',
				'type' => 'true_false',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'message' => '',
				'default_value' => 0,
			),
			array (
				'key' => 'field_536fa1fda8afb',
				'label' => __('Vacancies page','govintranet'),
				'name' => 'module_vacancies_page',
				'type' => 'relationship',
				'instructions' => '',
				'required' => 1,
				'conditional_logic' => array (
					array (
						array (
							'field' => 'field_536fa1eea8afa',
							'operator' => '==',
							'value' => '1',
						),
					),
				),
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'return_format' => 'object',
				'post_type' => array (
					0 => 'page',
				),
				'taxonomy' => array (
				),
				'filters' => array (
					0 => 'search',
				),
				'result_elements' => '',
				'max' => '',
				'elements' => array (
				),
				'min' => 0,
			),
			array (
				'key' => 'field_536fa214a8afc',
				'label' => __('Blog posts','govintranet'),
				'name' => 'module_blog',
				'type' => 'true_false',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'message' => '',
				'default_value' => 0,
			),
			array (
				'key' => 'field_536fa226a8afd',
				'label' => __('Blog page','govintranet'),
				'name' => 'module_blog_page',
				'type' => 'relationship',
				'instructions' => '',
				'required' => 1,
				'conditional_logic' => array (
					array (
						array (
							'field' => 'field_536fa214a8afc',
							'operator' => '==',
							'value' => '1',
						),
					),
				),
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'return_format' => 'object',
				'post_type' => array (
					0 => 'page',
				),
				'taxonomy' => array (
				),
				'filters' => array (
					0 => 'search',
				),
				'result_elements' => '',
				'max' => '',
				'elements' => array (
				),
				'min' => 0,
			),
			array (
				'key' => 'field_536fa28bcb464',
				'label' => __('Events','govintranet'),
				'name' => 'module_events',
				'type' => 'true_false',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'message' => '',
				'default_value' => 0,
			),
			array (
				'key' => 'field_536fa29acb465',
				'label' => __('Events page','govintranet'),
				'name' => 'module_events_page',
				'type' => 'relationship',
				'instructions' => '',
				'required' => 1,
				'conditional_logic' => array (
					array (
						array (
							'field' => 'field_536fa28bcb464',
							'operator' => '==',
							'value' => '1',
						),
					),
				),
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'return_format' => 'object',
				'post_type' => array (
					0 => 'page',
				),
				'taxonomy' => array (
				),
				'filters' => array (
					0 => 'search',
				),
				'result_elements' => '',
				'max' => '',
				'elements' => array (
				),
				'min' => 0,
			),
			array (
				'key' => 'field_53af48cd60e21',
				'label' => __('Jargon buster','govintranet'),
				'name' => 'module_jargon_buster',
				'type' => 'true_false',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'message' => '',
				'default_value' => 0,
			),
			array (
				'key' => 'field_53af48f560e22',
				'label' => __('Jargon buster page','govintranet'),
				'name' => 'module_jargon_buster_page',
				'type' => 'relationship',
				'instructions' => '',
				'required' => 1,
				'conditional_logic' => array (
					array (
						array (
							'field' => 'field_53af48cd60e21',
							'operator' => '==',
							'value' => '1',
						),
					),
				),
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'return_format' => 'object',
				'post_type' => array (
					0 => 'page',
				),
				'taxonomy' => array (
				),
				'filters' => array (
					0 => 'search',
				),
				'result_elements' => '',
				'max' => '',
				'elements' => array (
				),
				'min' => 0,
			),
			array (
				'key' => 'field_55b7d69ff69d1',
				'label' => __('A to Z','govintranet'),
				'name' => 'module_a_to_z',
				'type' => 'true_false',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'message' => '',
				'default_value' => 0,
			),
			array (
				'key' => 'field_55b7d719f69d2',
				'label' => __('A to Z blacklist','govintranet'),
				'name' => 'module_a_to_z_blacklist',
				'type' => 'text',
				'instructions' => __('Words longer than 2 letter to ignore.	Separate with commas.','govintranet'),
				'required' => 0,
				'conditional_logic' => array (
					array (
						array (
							'field' => 'field_55b7d69ff69d1',
							'operator' => '==',
							'value' => '1',
						),
					),
				),
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'default_value' => '',
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
				'maxlength' => '',
				'readonly' => 0,
				'disabled' => 0,
			),
			array (
				'key' => 'field_55b7d75bf69d3',
				'label' => __('A to Z whitelist','govintranet'),
				'name' => 'module_a_to_z_whitelist',
				'type' => 'text',
				'instructions' => __('Words shorter than 3 letters to include. Separate with commas.','govintranet'),
				'required' => 0,
				'conditional_logic' => array (
					array (
						array (
							'field' => 'field_55b7d69ff69d1',
							'operator' => '==',
							'value' => '1',
						),
					),
				),
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'default_value' => '',
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
				'maxlength' => '',
				'readonly' => 0,
				'disabled' => 0,
			),
			array (
				'key' => 'field_54d164425d5c0',
				'label' => __('Teams','govintranet'),
				'name' => 'module_teams',
				'type' => 'true_false',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'message' => '',
				'default_value' => 0,
			),
			array (
				'key' => 'field_536f764ea21c4',
				'label' => __('Enable user account support','govintranet'),
				'name' => 'forum_support',
				'type' => 'true_false',
				'instructions' => __('Provides support for forums (bbPress)','govintranet'),
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'message' => '',
				'default_value' => 0,
			),
			array (
				'key' => 'field_566995ce908a1',
				'label' => __('Enable WYSIWYG in forums','govintranet'),
				'name' => 'forum_visual_editor',
				'type' => 'true_false',
				'instructions' => __('Enables the visual editor in forums (bbPress)','govintranet'),
				'required' => 0,
				'conditional_logic' => array (
					array (
						array (
							'field' => 'field_536f764ea21c4',
							'operator' => '==',
							'value' => '1',
						),
					),
				),
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'message' => '',
				'default_value' => 0,
			),		
			array (
				'key' => 'field_55d628c205b5b',
				'label' => __('Show My Profile link','govintranet'),
				'name' => 'show_my_profile',
				'type' => 'true_false',
				'instructions' => __('Add a "My Profile" link to the secondary menu if user is logged in.','govintranet'),
				'required' => 0,
				'conditional_logic' => array (
					array (
						array (
							'field' => 'field_536f764ea21c4',
							'operator' => '==',
							'value' => '1',
						),
					),
				),
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'message' => '',
				'default_value' => 0,
			),
			array (
				'key' => 'field_55d6292505b5c',
				'label' => __('Show login/logout link','govintranet'),
				'name' => 'show_login_logout',
				'type' => 'true_false',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => array (
					array (
						array (
							'field' => 'field_536f764ea21c4',
							'operator' => '==',
							'value' => '1',
						),
					),
				),
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'message' => '',
				'default_value' => 0,
			),
			array (
				'key' => 'field_53769e3b01f93',
				'label' => __('Staff directory','govintranet'),
				'name' => 'module_staff_directory',
				'type' => 'true_false',
				'instructions' => __('Provides support for staff directory and staff profiles. Integrates with teams, forums and blog posts.','govintranet'),
				'required' => 0,
				'conditional_logic' => array (
					array (
						array (
							'field' => 'field_536f764ea21c4',
							'operator' => '==',
							'value' => '1',
						),
					),
				),
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'message' => '',
				'default_value' => 0,
			),
			array (
				'key' => 'field_54cd5345482fc',
				'label' => __('Staff directory page','govintranet'),
				'name' => 'module_staff_directory_page',
				'type' => 'relationship',
				'instructions' => '',
				'required' => 1,
				'conditional_logic' => array (
					array (
						array (
							'field' => 'field_53769e3b01f93',
							'operator' => '==',
							'value' => '1',
						),
						array (
							'field' => 'field_536f764ea21c4',
							'operator' => '==',
							'value' => '1',
						),
					),
				),
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'post_type' => array (
					0 => 'page',
				),
				'taxonomy' => array (
				),
				'filters' => array (
					0 => 'search',
				),
				'elements' => '',
				'max' => '',
				'return_format' => 'object',
				'min' => 0,
			),
			array (
				'key' => 'field_536f76c2a21c9',
				'label' => __('Team dropdown name','govintranet'),
				'name' => 'team_dropdown_name',
				'type' => 'text',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => array (
					array (
						array (
							'field' => 'field_53769e3b01f93',
							'operator' => '==',
							'value' => '1',
						),
						array (
							'field' => 'field_54d164425d5c0',
							'operator' => '==',
							'value' => '1',
						),
						array (
							'field' => 'field_536f764ea21c4',
							'operator' => '==',
							'value' => '1',
						),
					),
				),
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'default_value' => '',
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
				'maxlength' => '',
				'readonly' => 0,
				'disabled' => 0,
			),
			array (
				'key' => 'field_536f7667a21c5',
				'label' => __('Show hyperlinks on staff cards','govintranet'),
				'name' => 'full_detail_staff_cards',
				'type' => 'true_false',
				'instructions' => __('Enabling this option allows you to click on individual links such as email address and name on staff tiles. With this option disabled, the whole staff tile is clickable and links to the staff profile.','govintranet'),
				'required' => 0,
				'conditional_logic' => array (
					array (
						array (
							'field' => 'field_53769e3b01f93',
							'operator' => '==',
							'value' => '1',
						),
						array (
							'field' => 'field_536f764ea21c4',
							'operator' => '==',
							'value' => '1',
						),
					),
				),
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'message' => '',
				'default_value' => 0,
			),
			array (
				'key' => 'field_536f7688a21c6',
				'label' => __('Show circular avatars','govintranet'),
				'name' => 'staff_directory_style',
				'type' => 'true_false',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => array (
					array (
						array (
							'field' => 'field_53769e3b01f93',
							'operator' => '==',
							'value' => '1',
						),
						array (
							'field' => 'field_536f764ea21c4',
							'operator' => '==',
							'value' => '1',
						),
					),
				),
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'message' => '',
				'default_value' => 0,
			),
			array (
				'key' => 'field_536f769da21c7',
				'label' => __('Show grade on staff cards','govintranet'),
				'name' => 'show_grade_on_staff_cards',
				'type' => 'true_false',
				'instructions' => __('Requires setting a grade code for each term in the Grades taxonomy.','govintranet'),
				'required' => 0,
				'conditional_logic' => array (
					array (
						array (
							'field' => 'field_53769e3b01f93',
							'operator' => '==',
							'value' => '1',
						),
						array (
							'field' => 'field_536f764ea21c4',
							'operator' => '==',
							'value' => '1',
						),
					),
				),
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'message' => '',
				'default_value' => 0,
			),
			array (
				'key' => 'field_536f76ada21c8',
				'label' => __('Show mobile on staff cards','govintranet'),
				'name' => 'show_mobile_on_staff_cards',
				'type' => 'true_false',
				'instructions' => __('Display mobile phone number on staff cards in the staff directory listings.','govintranet'),
				'required' => 0,
				'conditional_logic' => array (
					array (
						array (
							'field' => 'field_53769e3b01f93',
							'operator' => '==',
							'value' => '1',
						),
						array (
							'field' => 'field_536f764ea21c4',
							'operator' => '==',
							'value' => '1',
						),
					),
				),
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'message' => '',
				'default_value' => 0,
			),
		),
		'location' => array (
			array (
				array (
					'param' => 'options_page',
					'operator' => '==',
					'value' => 'acf-options',
				),
				array (
					'param' => 'current_user_role',
					'operator' => '==',
					'value' => 'administrator',
				),
			),
		),
		'menu_order' => 0,
		'position' => 'normal',
		'style' => 'seamless',
		'label_placement' => 'top',
		'instruction_placement' => 'label',
		'hide_on_screen' => '',
	));

	acf_add_local_field_group(array (
		'key' => 'group_53c6e3abc8544',
		'title' => __('Media','govintranet'),
		'fields' => array (
			array (
				'key' => 'field_53c6e3b04383e',
				'label' => __('Document type','govintranet'),
				'name' => 'document_type',
				'prefix' => '',
				'type' => 'taxonomy',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'taxonomy' => 'document-type',
				'field_type' => 'checkbox',
				'allow_null' => 0,
				'load_save_terms' => 0,
				'return_format' => 'id',
				'multiple' => 0,
			),
		),
		'location' => array (
			array (
				array (
					'param' => 'attachment',
					'operator' => '==',
					'value' => 'all',
				),
			),
		),
		'menu_order' => 0,
		'position' => 'side',
		'style' => 'default',
		'label_placement' => 'top',
		'instruction_placement' => 'label',
		'hide_on_screen' => '',
	));

	acf_add_local_field_group(array (
		'key' => 'group_53bd5ee0dbdca',
		'title' => _x('Projects','noun','govintranet'),
		'fields' => array (
			array (
				'key' => 'field_536f674993a97',
				'label' => __('Project overview','govintranet'),
				'name' => 'project_overview',
				'prefix' => '',
				'type' => 'wysiwyg',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'default_value' => '',
				'toolbar' => 'full',
				'media_upload' => 1,
			),
			array (
				'key' => 'field_536f675d93a98',
				'label' => __('Project start date','govintranet'),
				'name' => 'project_start_date',
				'prefix' => '',
				'type' => 'date_picker',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'display_format' => 'd/m/Y',
				'return_format' => 'd/m/Y',
				'first_day' => 1,
			),
			array (
				'key' => 'field_536f677693a99',
				'label' => __('Project end date','govintranet'),
				'name' => 'project_end_date',
				'prefix' => '',
				'type' => 'date_picker',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'display_format' => 'd/m/Y',
				'return_format' => 'd/m/Y',
				'first_day' => 1,
			),
			array (
				'key' => 'field_536f679093a9a',
				'label' => __('Policy link','govintranet'),
				'name' => 'project_policy_link',
				'prefix' => '',
				'type' => 'text',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'default_value' => '',
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
				'formatting' => 'html',
				'maxlength' => '',
				'readonly' => 0,
				'disabled' => 0,
			),
			array (
				'key' => 'field_536f67c693a9b',
				'label' => __('Team members','govintranet'),
				'name' => 'project_team_members',
				'prefix' => '',
				'type' => 'user',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'role' => '',
				'allow_null' => 0,
				'multiple' => 1,
			),
		),
		'location' => array (
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'project',
				),
			),
		),
		'menu_order' => 0,
		'position' => 'normal',
		'style' => 'seamless',
		'label_placement' => 'top',
		'instruction_placement' => 'label',
		'hide_on_screen' => '',
	));

	if ( get_option( 'options_forum_support' )  ):
	
		if ( get_option( 'options_forum_visual_editor' )  ):
			function bbp_enable_visual_editor( $args = array() ) {
			    $args['tinymce'] = true;
			    return $args;
			}
			add_filter( 'bbp_after_get_the_content_parse_args', 'bbp_enable_visual_editor' );
			function bbp_tinymce_paste_plain_text( $plugins = array() ) {
			    $plugins[] = 'paste';
			    return $plugins;
			}
			add_filter( 'bbp_get_tiny_mce_plugins', 'bbp_tinymce_paste_plain_text' );
		endif;
	
		acf_add_local_field_group(array (
			'key' => 'group_53bd5ee0ea856',
			'title' => __('Users','govintranet'),
			'fields' => array (
				array (
					'key' => 'field_536f6ba7c9894',
					'label' => __('Job title','govintranet'),
					'name' => 'user_job_title',
					'type' => 'text',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'placeholder' => '',
					'prepend' => '',
					'append' => '',
					'formatting' => 'html',
					'maxlength' => '',
					'readonly' => 0,
					'disabled' => 0,
				),
				array (
					'key' => 'field_5380e9782feba',
					'label' => __('Team','govintranet'),
					'name' => 'user_team',
					'type' => 'relationship',
					'instructions' => __('Choose just your local team, e.g. if you work in Communications which is part of Business Services, you only need to choose Communications.','govintranet'),
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'post_type' => array (
						0 => 'team',
					),
					'taxonomy' => array (
					),
					'filters' => array (
						0 => 'search',
					),
					'elements' => '',
					'max' => '',
					'return_format' => 'id',
					'min' => 0,
				),
				array (
					'key' => 'field_536f6df635194',
					'label' => __('Line manager','govintranet'),
					'name' => 'user_line_manager',
					'type' => 'user',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'role' => '',
					'allow_null' => 1,
					'multiple' => 0,
				),
				array (
					'key' => 'field_536f6d8835190',
					'label' => __('Telephone number','govintranet'),
					'name' => 'user_telephone',
					'type' => 'text',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'placeholder' => '',
					'prepend' => '',
					'append' => '',
					'maxlength' => '',
					'readonly' => 0,
					'disabled' => 0,
				),
				array (
					'key' => 'field_536f6dae35191',
					'label' => __('Mobile number','govintranet'),
					'name' => 'user_mobile',
					'type' => 'text',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'placeholder' => '',
					'prepend' => '',
					'append' => '',
					'maxlength' => '',
					'readonly' => 0,
					'disabled' => 0,
				),
				array (
					'key' => 'field_53ff41c0dd1ee',
					'label' => __('Twitter handle','govintranet'),
					'name' => 'user_twitter_handle',
					'type' => 'text',
					'instructions' => __('e.g. @Luke_Oatham','govintranet'),
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'placeholder' => '',
					'prepend' => '',
					'append' => '',
					'maxlength' => '',
					'readonly' => 0,
					'disabled' => 0,
				),
				array (
					'key' => 'field_55e06dd4eab2e',
					'label' => 'LinkedIn',
					'name' => 'user_linkedin_url',
					'type' => 'url',
					'instructions' => __('Provide the full URL of your LinkedIn profile page.','govintranet'),
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'placeholder' => 'https://uk/linkedin.com/in/lukeoatham',
				),
				array (
					'key' => 'field_536f6dbe35192',
					'label' => _x('Working pattern','Hours of work','govintranet'),
					'name' => 'user_working_pattern',
					'type' => 'wysiwyg',
					'instructions' => __('Which days do you work? Where are you based?','govintranet'),
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'toolbar' => 'full',
					'media_upload' => 1,
					'tabs' => 'all',
				),
				array (
					'key' => 'field_536f6dd135193',
					'label' => __('Skills and experience','govintranet'),
					'name' => 'user_key_skills',
					'type' => 'wysiwyg',
					'instructions' => __('List your skills and experience so that others can find you.','govintranet'),
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'toolbar' => 'full',
					'media_upload' => 1,
					'tabs' => 'all',
				),
				array (
					'key' => 'field_548dd1f76a830',
					'label' => __('Grade','govintranet'),
					'name' => 'user_grade',
					'type' => 'taxonomy',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'taxonomy' => 'grade',
					'field_type' => 'select',
					'allow_null' => 1,
					'load_save_terms' => 0,
					'return_format' => 'object',
					'multiple' => 0,
					'add_term' => 0,
					'load_terms' => 0,
					'save_terms' => 0,
				),
			),
			'location' => array (
				array (
					array (
						'param' => 'user_role',
						'operator' => '==',
						'value' => 'all',
					),
				),
			),
			'menu_order' => 0,
			'position' => 'normal',
			'style' => 'seamless',
			'label_placement' => 'top',
			'instruction_placement' => 'label',
			'hide_on_screen' => '',
		));
	
	
	endif;
	
	if ( get_option( 'options_module_staff_directory' )  ):
	
		acf_add_local_field_group(array (
			'key' => 'group_55dd043b43161',
			'title' => __('Staff directory order','govintranet'),
			'fields' => array (
				array (
					'key' => 'field_55dd044565e83',
					'label' => __('Order','govintranet'),
					'name' => 'user_order',
					'type' => 'number',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => 0,
					'placeholder' => '',
					'prepend' => '',
					'append' => '',
					'min' => '',
					'max' => '',
					'step' => '',
					'readonly' => 0,
					'disabled' => 0,
				),
			),
			'location' => array (
				array (
					array (
						'param' => 'user_form',
						'operator' => '==',
						'value' => 'all',
					),
					array (
						'param' => 'current_user_role',
						'operator' => '==',
						'value' => 'administrator',
					),
				),
			),
			'menu_order' => 100,
			'position' => 'normal',
			'style' => 'default',
			'label_placement' => 'top',
			'instruction_placement' => 'label',
			'hide_on_screen' => '',
		));
	
	
	endif;

	if ( get_option( 'options_module_vacancies' )  ):
	
		acf_add_local_field_group(array (
			'key' => 'group_53bd5ee10ecdd',
			'title' => __('Vacancies','govintranet'),
			'fields' => array (
				array (
					'key' => 'field_536f694e9d08e',
					'label' => __('Vacancy reference','govintranet'),
					'name' => 'vacancy_reference',
					'prefix' => '',
					'type' => 'text',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'default_value' => '',
					'placeholder' => '',
					'prepend' => '',
					'append' => '',
					'formatting' => 'html',
					'maxlength' => '',
					'readonly' => 0,
					'disabled' => 0,
				),
				array (
					'key' => 'field_536f69949d090',
					'label' => __('Closing date','govintranet'),
					'name' => 'vacancy_closing_date',
					'prefix' => '',
					'type' => 'date_picker',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'display_format' => 'd/m/Y',
					'return_format' => 'd/m/Y',
					'first_day' => 1,
				),
				array (
					'key' => 'field_53e7c2d49e602',
					'label' => __('Closing time','govintranet'),
					'name' => 'vacancy_closing_time',
					'prefix' => '',
					'type' => 'text',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'default_value' => '',
					'placeholder' => '17:00',
					'prepend' => '',
					'append' => '',
					'maxlength' => '',
					'readonly' => 0,
					'disabled' => 0,
				),
				array (
					'key' => 'field_53b5ccf55edea',
					'label' => __('Team','govintranet'),
					'name' => 'vacancy_team',
					'prefix' => '',
					'type' => 'relationship',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'return_format' => 'object',
					'post_type' => array (
						0 => 'team',
					),
					'taxonomy' => array (
					),
					'filters' => array (
						0 => 'search',
					),
					'result_elements' => '',
					'max' => '',
					'elements' => array (
					),
				),
				array (
					'key' => 'field_53b5cd3b5edeb',
					'label' => _x('Project','noun','govintranet'),
					'name' => 'vacancy_project',
					'prefix' => '',
					'type' => 'relationship',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'return_format' => 'object',
					'post_type' => array (
						0 => 'project',
					),
					'taxonomy' => array (
					),
					'filters' => array (
						0 => 'search',
					),
					'result_elements' => array (
						0 => 'featured_image',
					),
					'max' => '',
					'elements' => array (
					),
				),
			),
			'location' => array (
				array (
					array (
						'param' => 'post_type',
						'operator' => '==',
						'value' => 'vacancy',
					),
				),
			),
			'menu_order' => 0,
			'position' => 'normal',
			'style' => 'seamless',
			'label_placement' => 'top',
			'instruction_placement' => 'label',
			'hide_on_screen' => '',
		));
	
	endif;

	acf_add_local_field_group(array (
		'key' => 'group_54b46b388f6cb',
		'title' => __('Video','govintranet'),
		'fields' => array (
			array (
				'key' => 'field_54b46b583b956',
				'label' => __('Video URL','govintranet'),
				'name' => 'news_video_url',
				'type' => 'url',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'default_value' => '',
				'placeholder' => '',
			),
		),
		'location' => array (
			array (
				array (
					'param' => 'post_format',
					'operator' => '==',
					'value' => 'video',
				),
			),
		),
		'menu_order' => 0,
		'position' => 'normal',
		'style' => 'default',
		'label_placement' => 'top',
		'instruction_placement' => 'label',
		'hide_on_screen' => '',
	));
	
	acf_add_local_field_group(array (
		'key' => 'group_53bd5ee11b027',
		'title' => __('News expiry','govintranet'),
		'fields' => array (
			array (
				'key' => 'field_536ec2de62b52',
				'label' => __('Auto expiry','govintranet'),
				'name' => 'news_auto_expiry',
				'prefix' => '',
				'type' => 'true_false',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'message' => '',
				'default_value' => 0,
			),
			array (
				'key' => 'field_536ec04f59dd7',
				'label' => __('Expiry date','govintranet'),
				'name' => 'news_expiry_date',
				'prefix' => '',
				'type' => 'date_picker',
				'instructions' => '',
				'required' => 1,
				'conditional_logic' => array (
					array (
						'rule_0' => array (
							'field' => 'field_536ec2de62b52',
							'operator' => '==',
							'value' => '1',
						),
					),
				),
				'display_format' => 'd/m/Y',
				'return_format' => 'Ymd',
				'first_day' => 1,
			),
			array (
				'key' => 'field_53dab2da88e2b',
				'label' => __('Expiry time','govintranet'),
				'name' => 'news_expiry_time',
				'prefix' => '',
				'type' => 'text',
				'instructions' => '',
				'required' => 1,
				'conditional_logic' => array (
					array (
						'rule_0' => array (
							'field' => 'field_536ec2de62b52',
							'operator' => '==',
							'value' => '1',
						),
					),
				),
				'default_value' => '',
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
				'maxlength' => '',
				'readonly' => 0,
				'disabled' => 0,
			),
			array (
				'key' => 'field_536ec0ad59dd8',
				'label' => __('Expiry action','govintranet'),
				'name' => 'news_expiry_action',
				'prefix' => '',
				'type' => 'select',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => array (
					array (
						array (
							'field' => 'field_536ec2de62b52',
							'operator' => '==',
							'value' => '1',
						),
					),
				),
				'choices' => array (
					'Revert to draft status' => 'Revert to draft status',
					'Change to regular news' => 'Change to regular news',
					'Move to trash' => 'Move to trash',
				),
				'default_value' => array (
					'Revert to draft status' => 'Revert to draft status',
				),
				'allow_null' => 0,
				'multiple' => 0,
				'ui' => 0,
				'ajax' => 0,
				'placeholder' => '',
				'disabled' => 0,
				'readonly' => 0,
			),
		),
		'location' => array (
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'news',
				),
			),
		),
		'menu_order' => 10,
		'position' => 'side',
		'style' => 'normal',
		'label_placement' => 'top',
		'instruction_placement' => 'label',
		'hide_on_screen' => '',
	));
	
	acf_add_local_field_group(array (
		'key' => 'group_53bd5ee124c55',
		'title' => __('Attachments','govintranet'),
		'fields' => array (
			array (
				'key' => 'field_536ec90dc8419',
				'label' => __('Document attachments','govintranet'),
				'name' => 'document_attachments',
				'prefix' => '',
				'type' => 'repeater',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'min' => '',
				'max' => '',
				'layout' => 'table',
				'button_label' => __('Add document','govintranet'),
				'sub_fields' => array (
					array (
						'key' => 'field_53bd6e229b9b3',
						'label' => __('Document','govintranet'),
						'name' => 'document_attachment',
						'prefix' => '',
						'type' => 'file',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'column_width' => '',
						'return_format' => 'array',
						'library' => 'all',
					),
				),
			),
		),
		'location' => array (
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'news',
				),
				array (
					'param' => 'post_format',
					'operator' => '==',
					'value' => 'standard',
				),
			),
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'task',
				),
				array (
					'param' => 'post_format',
					'operator' => '==',
					'value' => 'standard',
				),
			),
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'page',
				),
			),
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'event',
				),
			),
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'project',
				),
			),
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'vacancy',
				),
			),
		),
		'menu_order' => 12,
		'position' => 'normal',
		'style' => 'normal',
		'label_placement' => 'top',
		'instruction_placement' => 'label',
		'hide_on_screen' => '',
	));
	
	acf_add_local_field_group(array (
		'key' => 'group_53bd5ee129a41',
		'title' => __('Related','govintranet'),
		'fields' => array (
			array (
				'key' => 'field_536ec1db85f01',
				'label' => __('Related','govintranet'),
				'name' => 'related',
				'prefix' => '',
				'type' => 'relationship',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'return_format' => 'object',
				'post_type' => array (
					0 => 'page',
					1 => 'blog',
					2 => 'event',
					3 => 'task',
					4 => 'news',
				),
				'taxonomy' => array (
				),
				'filters' => array (
					0 => 'search',
					1 => 'post_type',
				),
				'result_elements' => array (
					0 => 'featured_image',
					1 => 'post_type',
				),
				'max' => '',
				'elements' => array (
				),
			),
		),
		'location' => array (
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'news',
				),
			),
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'page',
				),
			),
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'task',
				),
			),
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'event',
				),
			),
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'project',
				),
			),
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'vacancy',
				),
			),
		),
		'menu_order' => 15,
		'position' => 'normal',
		'style' => 'normal',
		'label_placement' => 'top',
		'instruction_placement' => 'label',
		'hide_on_screen' => array (
		),
	));
	
	acf_add_local_field_group(array (
		'key' => 'group_53bd5ee12e8a1',
		'title' => __('Keywords','govintranet'),
		'fields' => array (
			array (
				'key' => 'field_536ec1aaee33c',
				'label' => __('Keywords','govintranet'),
				'name' => 'keywords',
				'prefix' => '',
				'type' => 'text',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'default_value' => '',
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
				'formatting' => 'html',
				'maxlength' => '',
				'readonly' => 0,
				'disabled' => 0,
			),
		),
		'location' => array (
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'news',
				),
			),
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'page',
				),
			),
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'task',
				),
			),
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'blog',
				),
			),
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'event',
				),
			),
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'project',
				),
			),
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'vacancy',
				),
			),
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'team',
				),
			),
		),
		'menu_order' => 100,
		'position' => 'normal',
		'style' => 'normal',
		'label_placement' => 'top',
		'instruction_placement' => 'label',
		'hide_on_screen' => '',
	));

	acf_add_local_field_group(array (
		'key' => 'group_5522f15806b4b',
		'title' => __('Column placeholders','govintranet'),
		'fields' => array (
			array (
				'key' => 'field_5522f18701f2f',
				'label' => __('Column 1','govintranet'),
				'name' => 'aggregator_column_1',
				'type' => 'flexible_content',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'button_label' => __('Add to column 1','govintranet'),
				'min' => '',
				'max' => '',
				'layouts' => array (
					array (
						'key' => '5522f1a577034',
						'name' => 'aggregator_news_listing',
						'label' => __('News listing','govintranet'),
						'display' => 'block',
						'sub_fields' => array (
							array (
								'key' => 'field_5522f6f1c32b0',
								'label' => __('Title','govintranet'),
								'name' => 'aggregator_listing_title',
								'type' => 'text',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'default_value' => '',
								'placeholder' => '',
								'prepend' => '',
								'append' => '',
								'maxlength' => '',
								'readonly' => 0,
								'disabled' => 0,
							),
							array (
								'key' => 'field_5522f4cbfb45f',
								'label' => __('Need to know','govintranet'),
								'name' => 'aggregator_listing_need_to_know',
								'type' => 'radio',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'choices' => array (
									'Only need to know' => 'Only need to know',
									'Exclude need to know' => 'Exclude need to know',
									'Include need to know' => 'Include need to know',
								),
								'other_choice' => 0,
								'save_other_choice' => 0,
								'default_value' => 'Include need to know',
								'layout' => 'vertical',
							),
							array (
								'key' => 'field_5522f5354b383',
								'label' => __('Freshness','govintranet'),
								'name' => 'aggregator_listing_freshness',
								'type' => 'number',
								'instructions' => __('Don\'t show if older than this number of days. Leave blank to show all.','govintranet'),
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'default_value' => '',
								'placeholder' => '',
								'prepend' => '',
								'append' => '',
								'min' => 0,
								'max' => '',
								'step' => '',
								'readonly' => 0,
								'disabled' => 0,
							),
							array (
								'key' => 'field_5522f6904b384',
								'label' => __('Number to display','govintranet'),
								'name' => 'aggregator_listing_number',
								'type' => 'number',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'default_value' => '',
								'placeholder' => '',
								'prepend' => '',
								'append' => '',
								'min' => 0,
								'max' => '',
								'step' => '',
								'readonly' => 0,
								'disabled' => 0,
							),
							array (
								'key' => 'field_552526506f4c9',
								'label' => __('Compact list','govintranet'),
								'name' => 'aggregator_listing_compact_list',
								'type' => 'true_false',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'message' => '',
								'default_value' => 0,
							),
							array (
								'key' => 'field_55231b9ce6826',
								'label' => __('News items to display','govintranet'),
								'name' => 'message',
								'type' => 'message',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'message' => __('Display latest news items matching any of the chosen criteria in the tabs below.','govintranet'),
								'esc_html' => 0,
							),
							array (
								'key' => 'field_5522f280a1ad9',
								'label' => __('Team','govintranet'),
								'name' => 'aggregator_listing_type',
								'type' => 'tab',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'placement' => 'top',
								'endpoint' => 0,
							),
							array (
								'key' => 'field_5522f3b283f96',
								'label' => __('Team','govintranet'),
								'name' => 'aggregator_listing_team',
								'type' => 'relationship',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'post_type' => array (
									0 => 'team',
								),
								'taxonomy' => array (
								),
								'filters' => array (
									0 => 'search',
								),
								'elements' => '',
								'max' => 1,
								'return_format' => 'id',
								'min' => 0,
							),
							array (
								'key' => 'field_55231afad7dad',
								'label' => __('News type','govintranet'),
								'name' => 'aggregator_listing_type',
								'type' => 'tab',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'placement' => 'top',
								'endpoint' => 0,
							),
							array (
								'key' => 'field_5522f423de845',
								'label' => __('News type','govintranet'),
								'name' => 'aggregator_listing_tax',
								'type' => 'taxonomy',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'taxonomy' => 'news-type',
								'field_type' => 'checkbox',
								'allow_null' => 0,
								'load_save_terms' => 0,
								'return_format' => 'id',
								'multiple' => 0,
								'add_term' => 1,
								'load_terms' => 0,
								'save_terms' => 0,
							),
							array (
								'key' => 'field_55231b06d7dae',
								'label' => __('Tag','govintranet'),
								'name' => 'aggregator_listing_type',
								'type' => 'tab',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'placement' => 'top',
								'endpoint' => 0,
							),
							array (
								'key' => 'field_5522f450de846',
								'label' => __('Tag','govintranet'),
								'name' => 'aggregator_listing_tag',
								'type' => 'taxonomy',
								'instructions' => __('Match ANY of the tags (with teams/categories)','govintranet'),
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'taxonomy' => 'post_tag',
								'field_type' => 'multi_select',
								'allow_null' => 0,
								'load_save_terms' => 0,
								'return_format' => 'id',
								'multiple' => 0,
								'add_term' => 1,
								'load_terms' => 0,
								'save_terms' => 0,
							),
						),
						'min' => '',
						'max' => '',
					),
					array (
						'key' => '554a06258d677',
						'name' => 'aggregator_blog_listing',
						'label' => __('Blog listing','govintranet'),
						'display' => 'block',
						'sub_fields' => array (
							array (
								'key' => 'field_554a06258d678',
								'label' => __('Title','govintranet'),
								'name' => 'aggregator_listing_title',
								'type' => 'text',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'default_value' => '',
								'placeholder' => '',
								'prepend' => '',
								'append' => '',
								'maxlength' => '',
								'readonly' => 0,
								'disabled' => 0,
							),
							array (
								'key' => 'field_554a06258d67a',
								'label' => __('Freshness','govintranet'),
								'name' => 'aggregator_listing_freshness',
								'type' => 'number',
								'instructions' => __('Don\'t show if older than this number of days. Leave blank to show all.','govintranet'),
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'default_value' => '',
								'placeholder' => '',
								'prepend' => '',
								'append' => '',
								'min' => 0,
								'max' => '',
								'step' => '',
								'readonly' => 0,
								'disabled' => 0,
							),
							array (
								'key' => 'field_554a06258d67b',
								'label' => __('Number to display','govintranet'),
								'name' => 'aggregator_listing_number',
								'type' => 'number',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'default_value' => '',
								'placeholder' => '',
								'prepend' => '',
								'append' => '',
								'min' => 0,
								'max' => '',
								'step' => '',
								'readonly' => 0,
								'disabled' => 0,
							),
							array (
								'key' => 'field_554a06258d67c',
								'label' => __('Compact list','govintranet'),
								'name' => 'aggregator_listing_compact_list',
								'type' => 'true_false',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'message' => '',
								'default_value' => 0,
							),
							array (
								'key' => 'field_554a06258d67f',
								'label' => __('Team','govintranet'),
								'name' => 'aggregator_listing_team',
								'type' => 'relationship',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'post_type' => array (
									0 => 'team',
								),
								'taxonomy' => array (
								),
								'filters' => array (
									0 => 'search',
								),
								'elements' => '',
								'max' => 1,
								'return_format' => 'id',
								'min' => 0,
							),
						),
						'min' => '',
						'max' => '',
					),
					array (
						'key' => '5522f8e2a0432',
						'name' => 'aggregator_task_listing',
						'label' => __('Task listing','govintranet'),
						'display' => 'block',
						'sub_fields' => array (
							array (
								'key' => 'field_5522f8e2a0433',
								'label' => __('Title','govintranet'),
								'name' => 'aggregator_listing_title',
								'type' => 'text',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'default_value' => '',
								'placeholder' => '',
								'prepend' => '',
								'append' => '',
								'maxlength' => '',
								'readonly' => 0,
								'disabled' => 0,
							),
							array (
								'key' => 'field_552533ae1e480',
								'label' => __('Compact list','govintranet'),
								'name' => 'aggregator_listing_compact_list',
								'type' => 'true_false',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'message' => '',
								'default_value' => 0,
							),
							array (
								'key' => 'field_55240f7c69a28',
								'label' => __('Tasks to display','govintranet'),
								'name' => 'message',
								'type' => 'message',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'message' => __('Display tasks and guides matching any of the chosen criteria in the tabs below.','govintranet'),
								'esc_html' => 0,
							),
							array (
								'key' => 'field_55240f20bdd33',
								'label' => __('Team','govintranet'),
								'name' => 'team',
								'type' => 'tab',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'placement' => 'top',
								'endpoint' => 0,
							),
							array (
								'key' => 'field_5522f8e2a0435',
								'label' => __('Team','govintranet'),
								'name' => 'aggregator_listing_team',
								'type' => 'relationship',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'post_type' => array (
									0 => 'team',
								),
								'taxonomy' => array (
								),
								'filters' => array (
									0 => 'search',
								),
								'elements' => '',
								'max' => 1,
								'return_format' => 'id',
								'min' => 0,
							),
							array (
								'key' => 'field_55240f5d69a26',
								'label' => __('Category','govintranet'),
								'name' => 'team',
								'type' => 'tab',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'placement' => 'top',
								'endpoint' => 0,
							),
							array (
								'key' => 'field_5522f8e2a0436',
								'label' => __('Category','govintranet'),
								'name' => 'aggregator_listing_tax',
								'type' => 'taxonomy',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'taxonomy' => 'category',
								'field_type' => 'checkbox',
								'allow_null' => 0,
								'load_save_terms' => 0,
								'return_format' => 'id',
								'multiple' => 0,
								'add_term' => 1,
								'load_terms' => 0,
								'save_terms' => 0,
							),
							array (
								'key' => 'field_55240f6d69a27',
								'label' => __('Tag','govintranet'),
								'name' => 'team',
								'type' => 'tab',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'placement' => 'top',
								'endpoint' => 0,
							),
							array (
								'key' => 'field_5522f8e2a0437',
								'label' => __('Tag','govintranet'),
								'name' => 'aggregator_listing_tag',
								'type' => 'taxonomy',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'taxonomy' => 'post_tag',
								'field_type' => 'multi_select',
								'allow_null' => 0,
								'load_save_terms' => 0,
								'return_format' => 'id',
								'multiple' => 0,
								'add_term' => 1,
								'load_terms' => 0,
								'save_terms' => 0,
							),
						),
						'min' => '',
						'max' => '',
					),
					array (
						'key' => '5522f7a98627a',
						'name' => 'aggregator_free_format_area',
						'label' => __('Free-format area','govintranet'),
						'display' => 'row',
						'sub_fields' => array (
							array (
								'key' => 'field_5522f7ca8627b',
								'label' => _x('Content','Page content, data','govintranet'),
								'name' => 'aggregator_free_format_area_content',
								'type' => 'wysiwyg',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'default_value' => '',
								'tabs' => 'all',
								'toolbar' => 'full',
								'media_upload' => 1,
							),
						),
						'min' => '',
						'max' => '',
					),
					array (
						'key' => '5522f8048627d',
						'name' => 'aggregator_team_listing',
						'label' => __('Team listing','govintranet'),
						'display' => 'row',
						'sub_fields' => array (
							array (
								'key' => 'field_552318e1c1264',
								'label' => __('Title','govintranet'),
								'name' => 'aggregator_listing_title',
								'type' => 'text',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'default_value' => '',
								'placeholder' => '',
								'prepend' => '',
								'append' => '',
								'maxlength' => '',
								'readonly' => 0,
								'disabled' => 0,
							),
							array (
								'key' => 'field_5522f8128627e',
								'label' => __('Team','govintranet'),
								'name' => 'aggregator_listing_team',
								'type' => 'relationship',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'post_type' => array (
									0 => 'team',
								),
								'taxonomy' => array (
								),
								'filters' => array (
									0 => 'search',
								),
								'elements' => '',
								'max' => 1,
								'return_format' => 'id',
								'min' => 0,
							),
						),
						'min' => '',
						'max' => '',
					),
					array (
						'key' => '5522fc1de503b',
						'name' => 'aggregator_document_listing',
						'label' => __('Document listing','govintranet'),
						'display' => 'row',
						'sub_fields' => array (
							array (
								'key' => 'field_552318fcc1265',
								'label' => __('Title','govintranet'),
								'name' => 'aggregator_listing_title',
								'type' => 'text',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'default_value' => '',
								'placeholder' => '',
								'prepend' => '',
								'append' => '',
								'maxlength' => '',
								'readonly' => 0,
								'disabled' => 0,
							),
							array (
								'key' => 'field_5522fc2fe503c',
								'label' => __('Category','govintranet'),
								'name' => 'aggregator_listing_category',
								'type' => 'taxonomy',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'taxonomy' => 'category',
								'field_type' => 'checkbox',
								'allow_null' => 0,
								'load_save_terms' => 0,
								'return_format' => 'id',
								'multiple' => 0,
								'add_term' => 1,
								'load_terms' => 0,
								'save_terms' => 0,
							),
							array (
								'key' => 'field_5522fc78e503d',
								'label' => __('Type','govintranet'),
								'name' => 'aggregator_listing_doctype',
								'type' => 'taxonomy',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'taxonomy' => 'document-type',
								'field_type' => 'radio',
								'allow_null' => 1,
								'load_save_terms' => 0,
								'return_format' => 'id',
								'multiple' => 0,
								'add_term' => 1,
								'load_terms' => 0,
								'save_terms' => 0,
							),
						),
						'min' => '',
						'max' => '',
					),
					array (
						'key' => '5522fe5bc50ab',
						'name' => 'aggregator_link_listing',
						'label' => __('Link listing','govintranet'),
						'display' => 'row',
						'sub_fields' => array (
							array (
								'key' => 'field_55231936c1266',
								'label' => __('Title','govintranet'),
								'name' => 'aggregator_listing_title',
								'type' => 'text',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'default_value' => '',
								'placeholder' => '',
								'prepend' => '',
								'append' => '',
								'maxlength' => '',
								'readonly' => 0,
								'disabled' => 0,
							),
							array (
								'key' => 'field_5522fe67c50ac',
								'label' => __('Link','govintranet'),
								'name' => 'aggregator_listing_link',
								'type' => 'relationship',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'post_type' => array (
									0 => 'page',
									1 => 'blog',
									2 => 'news',
									3 => 'task',
								),
								'taxonomy' => array (
								),
								'filters' => array (
									0 => 'search',
									1 => 'post_type',
								),
								'elements' => '',
								'max' => '',
								'return_format' => 'id',
								'min' => 0,
							),
						),
						'min' => '',
						'max' => '',
					),
					array (
						'key' => '552308960a092',
						'name' => 'aggregator_gallery',
						'label' => __('Gallery','govintranet'),
						'display' => 'row',
						'sub_fields' => array (
							array (
								'key' => 'field_55231947c1267',
								'label' => __('Title','govintranet'),
								'name' => 'aggregator_listing_title',
								'type' => 'text',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'default_value' => '',
								'placeholder' => '',
								'prepend' => '',
								'append' => '',
								'maxlength' => '',
								'readonly' => 0,
								'disabled' => 0,
							),
							array (
								'key' => 'field_5523090f0a093',
								'label' => __('Images','govintranet'),
								'name' => 'aggregator_gallery_images',
								'type' => 'gallery',
								'instructions' => '',
								'required' => 1,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'min' => '',
								'max' => '',
								'preview_size' => 'thumbnail',
								'library' => 'all',
								'min_width' => 0,
								'min_height' => 0,
								'min_size' => 0,
								'max_width' => 0,
								'max_height' => 0,
								'max_size' => 0,
								'mime_types' => '',
							),
						),
						'min' => '',
						'max' => '',
					),
					array (
						'key' => '55d72e157085e',
						'name' => 'aggregator_event_listing',
						'label' => __('Event listing','govintranet'),
						'display' => 'row',
						'sub_fields' => array (
							array (
								'key' => 'field_55d72e657085f',
								'label' => __('Title','govintranet'),
								'name' => 'aggregator_listing_title',
								'type' => 'text',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'default_value' => '',
								'placeholder' => '',
								'prepend' => '',
								'append' => '',
								'maxlength' => '',
								'readonly' => 0,
								'disabled' => 0,
							),
							array (
								'key' => 'field_55d72e9a70860',
								'label' => __('Team','govintranet'),
								'name' => 'aggregator_listing_team',
								'type' => 'relationship',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'post_type' => array (
									0 => 'team',
								),
								'taxonomy' => array (
								),
								'filters' => array (
									0 => 'search',
								),
								'elements' => '',
								'min' => '',
								'max' => 1,
								'return_format' => 'id',
							),
							array (
								'key' => 'field_55d73b63351a8',
								'label' => __('Number to display','govintranet'),
								'name' => 'aggregator_listing_number',
								'type' => 'number',
								'instructions' => '',
								'required' => 1,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'default_value' => '',
								'placeholder' => '',
								'prepend' => '',
								'append' => '',
								'min' => '',
								'max' => '',
								'step' => '',
								'readonly' => 0,
								'disabled' => 0,
							),
							array (
								'key' => 'field_55da155110065',
								'label' => __('Options','govintranet'),
								'name' => 'aggregator_listing_options',
								'type' => 'checkbox',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'choices' => array (
									'Calendar' => 'Calendar',
									'Thumbnail' => 'Thumbnail',
									'Location' => 'Location',
								),
								'default_value' => array (
								),
								'layout' => 'vertical',
								'toggle' => 0,
							),
						),
						'min' => '',
						'max' => '',
					),
				),
			),
			array (
				'key' => 'field_552ababc4e4e9',
				'label' => __('Hero column','govintranet'),
				'name' => 'aggregator_column_hero',
				'type' => 'flexible_content',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'button_label' => __('Add to column 1','govintranet'),
				'min' => '',
				'max' => '',
				'layouts' => array (
					array (
						'key' => '5522f1a577034',
						'name' => 'aggregator_news_listing',
						'label' => __('News listing','govintranet'),
						'display' => 'block',
						'sub_fields' => array (
							array (
								'key' => 'field_552ababd4e4ea',
								'label' => __('Title','govintranet'),
								'name' => 'aggregator_listing_title',
								'type' => 'text',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'default_value' => '',
								'placeholder' => '',
								'prepend' => '',
								'append' => '',
								'maxlength' => '',
								'readonly' => 0,
								'disabled' => 0,
							),
							array (
								'key' => 'field_552ababd4e4eb',
								'label' => __('Need to know','govintranet'),
								'name' => 'aggregator_listing_need_to_know',
								'type' => 'radio',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'choices' => array (
									'Only need to know' => 'Only need to know',
									'Exclude need to know' => 'Exclude need to know',
									'Include need to know' => 'Include need to know',
								),
								'other_choice' => 0,
								'save_other_choice' => 0,
								'default_value' => 'Include need to know',
								'layout' => 'vertical',
							),
							array (
								'key' => 'field_552ababd4e4ec',
								'label' => __('Freshness','govintranet'),
								'name' => 'aggregator_listing_freshness',
								'type' => 'number',
								'instructions' => __('Don\'t show if older than this number of days. Leave blank to show all.','govintranet'),
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'default_value' => '',
								'placeholder' => '',
								'prepend' => '',
								'append' => '',
								'min' => 0,
								'max' => '',
								'step' => '',
								'readonly' => 0,
								'disabled' => 0,
							),
							array (
								'key' => 'field_552ababd4e4ed',
								'label' => __('Number to display','govintranet'),
								'name' => 'aggregator_listing_number',
								'type' => 'number',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'default_value' => '',
								'placeholder' => '',
								'prepend' => '',
								'append' => '',
								'min' => 0,
								'max' => '',
								'step' => '',
								'readonly' => 0,
								'disabled' => 0,
							),
							array (
								'key' => 'field_552ababd4e4ee',
								'label' => __('Compact list','govintranet'),
								'name' => 'aggregator_listing_compact_list',
								'type' => 'true_false',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'message' => '',
								'default_value' => 0,
							),
							array (
								'key' => 'field_552ababd4e4ef',
								'label' => __('News items to display','govintranet'),
								'name' => 'message',
								'type' => 'message',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'message' => __('Display latest news items matching any of the chosen criteria in the tabs below.','govintranet'),
								'esc_html' => 0,
							),
							array (
								'key' => 'field_552ababd4e4f0',
								'label' => __('Team','govintranet'),
								'name' => 'aggregator_listing_type',
								'type' => 'tab',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'placement' => 'top',
								'endpoint' => 0,
							),
							array (
								'key' => 'field_552ababd4e4f1',
								'label' => __('Team','govintranet'),
								'name' => 'aggregator_listing_team',
								'type' => 'relationship',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'post_type' => array (
									0 => 'team',
								),
								'taxonomy' => array (
								),
								'filters' => array (
									0 => 'search',
								),
								'elements' => '',
								'max' => 1,
								'return_format' => 'id',
								'min' => 0,
							),
							array (
								'key' => 'field_552ababd4e4f2',
								'label' => __('News type','govintranet'),
								'name' => 'aggregator_listing_type',
								'type' => 'tab',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'placement' => 'top',
								'endpoint' => 0,
							),
							array (
								'key' => 'field_552ababd4e4f3',
								'label' => __('News type','govintranet'),
								'name' => 'aggregator_listing_tax',
								'type' => 'taxonomy',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'taxonomy' => 'news-type',
								'field_type' => 'checkbox',
								'allow_null' => 0,
								'load_save_terms' => 0,
								'return_format' => 'id',
								'multiple' => 0,
								'add_term' => 1,
								'load_terms' => 0,
								'save_terms' => 0,
							),
							array (
								'key' => 'field_552ababd4e4f4',
								'label' => __('Tag','govintranet'),
								'name' => 'aggregator_listing_type',
								'type' => 'tab',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'placement' => 'top',
								'endpoint' => 0,
							),
							array (
								'key' => 'field_552ababd4e4f5',
								'label' => __('Tag','govintranet'),
								'name' => 'aggregator_listing_tag',
								'type' => 'taxonomy',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'taxonomy' => 'post_tag',
								'field_type' => 'multi_select',
								'allow_null' => 0,
								'load_save_terms' => 0,
								'return_format' => 'id',
								'multiple' => 0,
								'add_term' => 1,
								'load_terms' => 0,
								'save_terms' => 0,
							),
						),
						'min' => '',
						'max' => '',
					),
					array (
						'key' => '554a06bf237ed',
						'name' => 'aggregator_blog_listing',
						'label' => __('Blog listing','govintranet'),
						'display' => 'block',
						'sub_fields' => array (
							array (
								'key' => 'field_554a06bf237ee',
								'label' => __('Title','govintranet'),
								'name' => 'aggregator_listing_title',
								'type' => 'text',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'default_value' => '',
								'placeholder' => '',
								'prepend' => '',
								'append' => '',
								'maxlength' => '',
								'readonly' => 0,
								'disabled' => 0,
							),
							array (
								'key' => 'field_554a06bf237f0',
								'label' => __('Freshness','govintranet'),
								'name' => 'aggregator_listing_freshness',
								'type' => 'number',
								'instructions' => __('Don\'t show if older than this number of days. Leave blank to show all.','govintranet'),
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'default_value' => '',
								'placeholder' => '',
								'prepend' => '',
								'append' => '',
								'min' => 0,
								'max' => '',
								'step' => '',
								'readonly' => 0,
								'disabled' => 0,
							),
							array (
								'key' => 'field_554a06bf237f1',
								'label' => __('Number to display','govintranet'),
								'name' => 'aggregator_listing_number',
								'type' => 'number',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'default_value' => '',
								'placeholder' => '',
								'prepend' => '',
								'append' => '',
								'min' => 0,
								'max' => '',
								'step' => '',
								'readonly' => 0,
								'disabled' => 0,
							),
							array (
								'key' => 'field_554a06bf237f2',
								'label' => __('Compact list','govintranet'),
								'name' => 'aggregator_listing_compact_list',
								'type' => 'true_false',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'message' => '',
								'default_value' => 0,
							),
							array (
								'key' => 'field_554a06bf237f5',
								'label' => __('Team','govintranet'),
								'name' => 'aggregator_listing_team',
								'type' => 'relationship',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'post_type' => array (
									0 => 'team',
								),
								'taxonomy' => array (
								),
								'filters' => array (
									0 => 'search',
								),
								'elements' => '',
								'max' => 1,
								'return_format' => 'id',
								'min' => 0,
							),
						),
						'min' => '',
						'max' => '',
					),
					array (
						'key' => '5522f8e2a0432',
						'name' => 'aggregator_task_listing',
						'label' => __('Task listing','govintranet'),
						'display' => 'block',
						'sub_fields' => array (
							array (
								'key' => 'field_552ababd4e4f6',
								'label' => __('Title','govintranet'),
								'name' => 'aggregator_listing_title',
								'type' => 'text',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'default_value' => '',
								'placeholder' => '',
								'prepend' => '',
								'append' => '',
								'maxlength' => '',
								'readonly' => 0,
								'disabled' => 0,
							),
							array (
								'key' => 'field_552ababd4e4f7',
								'label' => __('Compact list','govintranet'),
								'name' => 'aggregator_listing_compact_list',
								'type' => 'true_false',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'message' => '',
								'default_value' => 0,
							),
							array (
								'key' => 'field_552ababd4e4f8',
								'label' => __('Tasks to display','govintranet'),
								'name' => 'message',
								'type' => 'message',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'message' => __('Display tasks and guides matching any of the chosen criteria in the tabs below.','govintranet'),
								'esc_html' => 0,
							),
							array (
								'key' => 'field_552ababd4e4f9',
								'label' => __('Team','govintranet'),
								'name' => 'team',
								'type' => 'tab',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'placement' => 'top',
								'endpoint' => 0,
							),
							array (
								'key' => 'field_552ababd4e4fa',
								'label' => __('Team','govintranet'),
								'name' => 'aggregator_listing_team',
								'type' => 'relationship',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'post_type' => array (
									0 => 'team',
								),
								'taxonomy' => array (
								),
								'filters' => array (
									0 => 'search',
								),
								'elements' => '',
								'max' => 1,
								'return_format' => 'id',
								'min' => 0,
							),
							array (
								'key' => 'field_552ababd4e4fb',
								'label' => __('Category','govintranet'),
								'name' => 'team',
								'type' => 'tab',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'placement' => 'top',
								'endpoint' => 0,
							),
							array (
								'key' => 'field_552ababd4e4fc',
								'label' => __('Category','govintranet'),
								'name' => 'aggregator_listing_tax',
								'type' => 'taxonomy',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'taxonomy' => 'category',
								'field_type' => 'checkbox',
								'allow_null' => 0,
								'load_save_terms' => 0,
								'return_format' => 'id',
								'multiple' => 0,
								'add_term' => 1,
								'load_terms' => 0,
								'save_terms' => 0,
							),
							array (
								'key' => 'field_552ababd4e4fd',
								'label' => __('Tag','govintranet'),
								'name' => 'team',
								'type' => 'tab',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'placement' => 'top',
								'endpoint' => 0,
							),
							array (
								'key' => 'field_552ababd4e4fe',
								'label' => __('Tag','govintranet'),
								'name' => 'aggregator_listing_tag',
								'type' => 'taxonomy',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'taxonomy' => 'post_tag',
								'field_type' => 'multi_select',
								'allow_null' => 0,
								'load_save_terms' => 0,
								'return_format' => 'id',
								'multiple' => 0,
								'add_term' => 1,
								'load_terms' => 0,
								'save_terms' => 0,
							),
						),
						'min' => '',
						'max' => '',
					),
					array (
						'key' => '5522f7a98627a',
						'name' => 'aggregator_free_format_area',
						'label' => __('Free-format area','govintranet'),
						'display' => 'row',
						'sub_fields' => array (
							array (
								'key' => 'field_552ababd4e4ff',
								'label' => _x('Content','Page content, data','govintranet'),
								'name' => 'aggregator_free_format_area_content',
								'type' => 'wysiwyg',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'default_value' => '',
								'tabs' => 'all',
								'toolbar' => 'full',
								'media_upload' => 1,
							),
						),
						'min' => '',
						'max' => '',
					),
					array (
						'key' => '5522f8048627d',
						'name' => 'aggregator_team_listing',
						'label' => __('Team listing','govintranet'),
						'display' => 'row',
						'sub_fields' => array (
							array (
								'key' => 'field_552ababd4e500',
								'label' => __('Title','govintranet'),
								'name' => 'aggregator_listing_title',
								'type' => 'text',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'default_value' => '',
								'placeholder' => '',
								'prepend' => '',
								'append' => '',
								'maxlength' => '',
								'readonly' => 0,
								'disabled' => 0,
							),
							array (
								'key' => 'field_552ababd4e501',
								'label' => __('Team','govintranet'),
								'name' => 'aggregator_listing_team',
								'type' => 'relationship',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'post_type' => array (
									0 => 'team',
								),
								'taxonomy' => array (
								),
								'filters' => array (
									0 => 'search',
								),
								'elements' => '',
								'max' => 1,
								'return_format' => 'id',
								'min' => 0,
							),
						),
						'min' => '',
						'max' => '',
					),
					array (
						'key' => '5522fc1de503b',
						'name' => 'aggregator_document_listing',
						'label' => __('Document listing','govintranet'),
						'display' => 'row',
						'sub_fields' => array (
							array (
								'key' => 'field_552ababd4e502',
								'label' => __('Title','govintranet'),
								'name' => 'aggregator_listing_title',
								'type' => 'text',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'default_value' => '',
								'placeholder' => '',
								'prepend' => '',
								'append' => '',
								'maxlength' => '',
								'readonly' => 0,
								'disabled' => 0,
							),
							array (
								'key' => 'field_552ababd4e503',
								'label' => __('Category','govintranet'),
								'name' => 'aggregator_listing_category',
								'type' => 'taxonomy',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'taxonomy' => 'category',
								'field_type' => 'checkbox',
								'allow_null' => 0,
								'load_save_terms' => 0,
								'return_format' => 'id',
								'multiple' => 0,
								'add_term' => 1,
								'load_terms' => 0,
								'save_terms' => 0,
							),
							array (
								'key' => 'field_552ababd4e504',
								'label' => __('Type','govintranet'),
								'name' => 'aggregator_listing_doctype',
								'type' => 'taxonomy',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'taxonomy' => 'document-type',
								'field_type' => 'radio',
								'allow_null' => 1,
								'load_save_terms' => 0,
								'return_format' => 'id',
								'multiple' => 0,
								'add_term' => 1,
								'load_terms' => 0,
								'save_terms' => 0,
							),
						),
						'min' => '',
						'max' => '',
					),
					array (
						'key' => '5522fe5bc50ab',
						'name' => 'aggregator_link_listing',
						'label' => __('Link listing','govintranet'),
						'display' => 'row',
						'sub_fields' => array (
							array (
								'key' => 'field_552ababd4e505',
								'label' => __('Title','govintranet'),
								'name' => 'aggregator_listing_title',
								'type' => 'text',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'default_value' => '',
								'placeholder' => '',
								'prepend' => '',
								'append' => '',
								'maxlength' => '',
								'readonly' => 0,
								'disabled' => 0,
							),
							array (
								'key' => 'field_552ababd4e506',
								'label' => __('Link','govintranet'),
								'name' => 'aggregator_listing_link',
								'type' => 'relationship',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'post_type' => array (
									0 => 'page',
									1 => 'blog',
									2 => 'news',
									3 => 'task',
								),
								'taxonomy' => array (
								),
								'filters' => array (
									0 => 'search',
									1 => 'post_type',
								),
								'elements' => '',
								'max' => '',
								'return_format' => 'id',
								'min' => 0,
							),
						),
						'min' => '',
						'max' => '',
					),
					array (
						'key' => '552308960a092',
						'name' => 'aggregator_gallery',
						'label' => __('Gallery','govintranet'),
						'display' => 'row',
						'sub_fields' => array (
							array (
								'key' => 'field_552ababd4e507',
								'label' => __('Title','govintranet'),
								'name' => 'aggregator_listing_title',
								'type' => 'text',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'default_value' => '',
								'placeholder' => '',
								'prepend' => '',
								'append' => '',
								'maxlength' => '',
								'readonly' => 0,
								'disabled' => 0,
							),
							array (
								'key' => 'field_552ababd4e508',
								'label' => __('Images','govintranet'),
								'name' => 'aggregator_gallery_images',
								'type' => 'gallery',
								'instructions' => '',
								'required' => 1,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'min' => '',
								'max' => '',
								'preview_size' => 'thumbnail',
								'library' => 'all',
								'min_width' => 0,
								'min_height' => 0,
								'min_size' => 0,
								'max_width' => 0,
								'max_height' => 0,
								'max_size' => 0,
								'mime_types' => '',
							),
						),
						'min' => '',
						'max' => '',
					),
					array (
						'key' => '55da0fda069ef',
						'name' => 'aggregator_event_listing',
						'label' => __('Event listing','govintranet'),
						'display' => 'block',
						'sub_fields' => array (
							array (
								'key' => 'field_55da0fda069f0',
								'label' => __('Title','govintranet'),
								'name' => 'aggregator_listing_title',
								'type' => 'text',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'default_value' => '',
								'placeholder' => '',
								'prepend' => '',
								'append' => '',
								'maxlength' => '',
								'readonly' => 0,
								'disabled' => 0,
							),
							array (
								'key' => 'field_55da0fda069f4',
								'label' => __('Team','govintranet'),
								'name' => 'aggregator_listing_team',
								'type' => 'relationship',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'post_type' => array (
									0 => 'team',
								),
								'taxonomy' => array (
								),
								'filters' => array (
									0 => 'search',
								),
								'elements' => '',
								'min' => '',
								'max' => 1,
								'return_format' => 'id',
							),
							array (
								'key' => 'field_55da0fda069f2',
								'label' => __('Number to display','govintranet'),
								'name' => 'aggregator_listing_number',
								'type' => 'number',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'default_value' => '',
								'placeholder' => '',
								'prepend' => '',
								'append' => '',
								'min' => 0,
								'max' => '',
								'step' => '',
								'readonly' => 0,
								'disabled' => 0,
							),
							array (
								'key' => 'field_55da153310064',
								'label' => __('Options','govintranet'),
								'name' => 'aggregator_listing_options',
								'type' => 'checkbox',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'choices' => array (
									'Calendar' => 'Calendar',
									'Thumbnail' => 'Thumbnail',
									'Location' => 'Location',
								),
								'default_value' => array (
								),
								'layout' => 'vertical',
								'toggle' => 0,
							),
						),
						'min' => '',
						'max' => '',
					),
				),
			),
			array (
				'key' => 'field_552a8996ca0ec',
				'label' => __('Column 2','govintranet'),
				'name' => 'aggregator_column_2',
				'type' => 'flexible_content',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'button_label' => __('Add to column 2','govintranet'),
				'min' => '',
				'max' => '',
				'layouts' => array (
					array (
						'key' => '5522f1a577034',
						'name' => 'aggregator_news_listing',
						'label' => __('News listing','govintranet'),
						'display' => 'block',
						'sub_fields' => array (
							array (
								'key' => 'field_552a8997ca0ed',
								'label' => __('Title','govintranet'),
								'name' => 'aggregator_listing_title',
								'type' => 'text',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'default_value' => '',
								'placeholder' => '',
								'prepend' => '',
								'append' => '',
								'maxlength' => '',
								'readonly' => 0,
								'disabled' => 0,
							),
							array (
								'key' => 'field_552a8997ca0ee',
								'label' => __('Need to know','govintranet'),
								'name' => 'aggregator_listing_need_to_know',
								'type' => 'radio',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'choices' => array (
									'Only need to know' => 'Only need to know',
									'Exclude need to know' => 'Exclude need to know',
									'Include need to know' => 'Include need to know',
								),
								'other_choice' => 0,
								'save_other_choice' => 0,
								'default_value' => 'Include need to know',
								'layout' => 'vertical',
							),
							array (
								'key' => 'field_552a8997ca0ef',
								'label' => __('Freshness','govintranet'),
								'name' => 'aggregator_listing_freshness',
								'type' => 'number',
								'instructions' => __('Don\'t show if older than this number of days. Leave blank to show all.','govintranet'),
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'default_value' => '',
								'placeholder' => '',
								'prepend' => '',
								'append' => '',
								'min' => 0,
								'max' => '',
								'step' => '',
								'readonly' => 0,
								'disabled' => 0,
							),
							array (
								'key' => 'field_552a8997ca0f0',
								'label' => __('Number to display','govintranet'),
								'name' => 'aggregator_listing_number',
								'type' => 'number',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'default_value' => '',
								'placeholder' => '',
								'prepend' => '',
								'append' => '',
								'min' => 0,
								'max' => '',
								'step' => '',
								'readonly' => 0,
								'disabled' => 0,
							),
							array (
								'key' => 'field_552a8997ca0f1',
								'label' => __('Compact list','govintranet'),
								'name' => 'aggregator_listing_compact_list',
								'type' => 'true_false',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'message' => '',
								'default_value' => 0,
							),
							array (
								'key' => 'field_552a8997ca0f2',
								'label' => __('News items to display','govintranet'),
								'name' => 'message',
								'type' => 'message',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'message' => __('Display latest news items matching any of the chosen criteria in the tabs below.','govintranet'),
								'esc_html' => 0,
							),
							array (
								'key' => 'field_552a8997ca0f3',
								'label' => __('Team','govintranet'),
								'name' => 'aggregator_listing_type',
								'type' => 'tab',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'placement' => 'top',
								'endpoint' => 0,
							),
							array (
								'key' => 'field_552a8997ca0f4',
								'label' => __('Team','govintranet'),
								'name' => 'aggregator_listing_team',
								'type' => 'relationship',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'post_type' => array (
									0 => 'team',
								),
								'taxonomy' => array (
								),
								'filters' => array (
									0 => 'search',
								),
								'elements' => '',
								'max' => 1,
								'return_format' => 'id',
								'min' => 0,
							),
							array (
								'key' => 'field_552a8997ca0f5',
								'label' => __('News type','govintranet'),
								'name' => 'aggregator_listing_type',
								'type' => 'tab',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'placement' => 'top',
								'endpoint' => 0,
							),
							array (
								'key' => 'field_552a8997ca0f6',
								'label' => __('News type','govintranet'),
								'name' => 'aggregator_listing_tax',
								'type' => 'taxonomy',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'taxonomy' => 'news-type',
								'field_type' => 'checkbox',
								'allow_null' => 0,
								'load_save_terms' => 0,
								'return_format' => 'id',
								'multiple' => 0,
								'add_term' => 1,
								'load_terms' => 0,
								'save_terms' => 0,
							),
							array (
								'key' => 'field_552a8997ca0f7',
								'label' => __('Tag','govintranet'),
								'name' => 'aggregator_listing_type',
								'type' => 'tab',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'placement' => 'top',
								'endpoint' => 0,
							),
							array (
								'key' => 'field_552a8997ca0f8',
								'label' => __('Tag','govintranet'),
								'name' => 'aggregator_listing_tag',
								'type' => 'taxonomy',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'taxonomy' => 'post_tag',
								'field_type' => 'multi_select',
								'allow_null' => 0,
								'load_save_terms' => 0,
								'return_format' => 'id',
								'multiple' => 0,
								'add_term' => 1,
								'load_terms' => 0,
								'save_terms' => 0,
							),
						),
						'min' => '',
						'max' => '',
					),
					array (
						'key' => '554a06fb237fa',
						'name' => 'aggregator_blog_listing',
						'label' => __('Blog listing','govintranet'),
						'display' => 'block',
						'sub_fields' => array (
							array (
								'key' => 'field_554a06fb237fb',
								'label' => __('Title','govintranet'),
								'name' => 'aggregator_listing_title',
								'type' => 'text',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'default_value' => '',
								'placeholder' => '',
								'prepend' => '',
								'append' => '',
								'maxlength' => '',
								'readonly' => 0,
								'disabled' => 0,
							),
							array (
								'key' => 'field_554a06fb237fc',
								'label' => __('Need to know','govintranet'),
								'name' => 'aggregator_listing_need_to_know',
								'type' => 'radio',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'choices' => array (
									'Only need to know' => 'Only need to know',
									'Exclude need to know' => 'Exclude need to know',
									'Include need to know' => 'Include need to know',
								),
								'other_choice' => 0,
								'save_other_choice' => 0,
								'default_value' => 'Include need to know',
								'layout' => 'vertical',
							),
							array (
								'key' => 'field_554a06fb237fd',
								'label' => __('Freshness','govintranet'),
								'name' => 'aggregator_listing_freshness',
								'type' => 'number',
								'instructions' => __('Don\'t show if older than this number of days. Leave blank to show all.','govintranet'),
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'default_value' => '',
								'placeholder' => '',
								'prepend' => '',
								'append' => '',
								'min' => 0,
								'max' => '',
								'step' => '',
								'readonly' => 0,
								'disabled' => 0,
							),
							array (
								'key' => 'field_554a06fb237fe',
								'label' => __('Number to display','govintranet'),
								'name' => 'aggregator_listing_number',
								'type' => 'number',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'default_value' => '',
								'placeholder' => '',
								'prepend' => '',
								'append' => '',
								'min' => 0,
								'max' => '',
								'step' => '',
								'readonly' => 0,
								'disabled' => 0,
							),
							array (
								'key' => 'field_554a06fb237ff',
								'label' => __('Compact list','govintranet'),
								'name' => 'aggregator_listing_compact_list',
								'type' => 'true_false',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'message' => '',
								'default_value' => 0,
							),
							array (
								'key' => 'field_554a06fb23802',
								'label' => __('Team','govintranet'),
								'name' => 'aggregator_listing_team',
								'type' => 'relationship',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'post_type' => array (
									0 => 'team',
								),
								'taxonomy' => array (
								),
								'filters' => array (
									0 => 'search',
								),
								'elements' => '',
								'max' => 1,
								'return_format' => 'id',
								'min' => 0,
							),
						),
						'min' => '',
						'max' => '',
					),
					array (
						'key' => '5522f8e2a0432',
						'name' => 'aggregator_task_listing',
						'label' => __('Task listing','govintranet'),
						'display' => 'block',
						'sub_fields' => array (
							array (
								'key' => 'field_552a8997ca0f9',
								'label' => __('Title','govintranet'),
								'name' => 'aggregator_listing_title',
								'type' => 'text',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'default_value' => '',
								'placeholder' => '',
								'prepend' => '',
								'append' => '',
								'maxlength' => '',
								'readonly' => 0,
								'disabled' => 0,
							),
							array (
								'key' => 'field_552a8997ca0fa',
								'label' => __('Compact list','govintranet'),
								'name' => 'aggregator_listing_compact_list',
								'type' => 'true_false',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'message' => '',
								'default_value' => 0,
							),
							array (
								'key' => 'field_552a8997ca0fb',
								'label' => __('Tasks to display','govintranet'),
								'name' => 'message',
								'type' => 'message',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'message' => __('Display tasks and guides matching any of the chosen criteria in the tabs below.','govintranet'),
								'esc_html' => 0,
							),
							array (
								'key' => 'field_552a8997ca0fc',
								'label' => __('Team','govintranet'),
								'name' => 'team',
								'type' => 'tab',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'placement' => 'top',
								'endpoint' => 0,
							),
							array (
								'key' => 'field_552a8997ca0fd',
								'label' => __('Team','govintranet'),
								'name' => 'aggregator_listing_team',
								'type' => 'relationship',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'post_type' => array (
									0 => 'team',
								),
								'taxonomy' => array (
								),
								'filters' => array (
									0 => 'search',
								),
								'elements' => '',
								'max' => 1,
								'return_format' => 'id',
								'min' => 0,
							),
							array (
								'key' => 'field_552a8997ca0fe',
								'label' => __('Category','govintranet'),
								'name' => 'team',
								'type' => 'tab',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'placement' => 'top',
								'endpoint' => 0,
							),
							array (
								'key' => 'field_552a8997ca0ff',
								'label' => __('Category','govintranet'),
								'name' => 'aggregator_listing_tax',
								'type' => 'taxonomy',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'taxonomy' => 'category',
								'field_type' => 'checkbox',
								'allow_null' => 0,
								'load_save_terms' => 0,
								'return_format' => 'id',
								'multiple' => 0,
								'add_term' => 1,
								'load_terms' => 0,
								'save_terms' => 0,
							),
							array (
								'key' => 'field_552a8997ca100',
								'label' => __('Tag','govintranet'),
								'name' => 'team',
								'type' => 'tab',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'placement' => 'top',
								'endpoint' => 0,
							),
							array (
								'key' => 'field_552a8997ca101',
								'label' => __('Tag','govintranet'),
								'name' => 'aggregator_listing_tag',
								'type' => 'taxonomy',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'taxonomy' => 'post_tag',
								'field_type' => 'multi_select',
								'allow_null' => 0,
								'load_save_terms' => 0,
								'return_format' => 'id',
								'multiple' => 0,
								'add_term' => 1,
								'load_terms' => 0,
								'save_terms' => 0,
							),
						),
						'min' => '',
						'max' => '',
					),
					array (
						'key' => '5522f7a98627a',
						'name' => 'aggregator_free_format_area',
						'label' => __('Free-format area','govintranet'),
						'display' => 'row',
						'sub_fields' => array (
							array (
								'key' => 'field_552a8997ca102',
								'label' => __('Content','Page content, data','govintranet'),
								'name' => 'aggregator_free_format_area_content',
								'type' => 'wysiwyg',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'default_value' => '',
								'tabs' => 'all',
								'toolbar' => 'full',
								'media_upload' => 1,
							),
						),
						'min' => '',
						'max' => '',
					),
					array (
						'key' => '5522f8048627d',
						'name' => 'aggregator_team_listing',
						'label' => __('Team listing','govintranet'),
						'display' => 'row',
						'sub_fields' => array (
							array (
								'key' => 'field_552a8997ca103',
								'label' => __('Title','govintranet'),
								'name' => 'aggregator_listing_title',
								'type' => 'text',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'default_value' => '',
								'placeholder' => '',
								'prepend' => '',
								'append' => '',
								'maxlength' => '',
								'readonly' => 0,
								'disabled' => 0,
							),
							array (
								'key' => 'field_552a8997ca104',
								'label' => __('Team','govintranet'),
								'name' => 'aggregator_listing_team',
								'type' => 'relationship',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'post_type' => array (
									0 => 'team',
								),
								'taxonomy' => array (
								),
								'filters' => array (
									0 => 'search',
								),
								'elements' => '',
								'max' => 1,
								'return_format' => 'id',
								'min' => 0,
							),
						),
						'min' => '',
						'max' => '',
					),
					array (
						'key' => '5522fc1de503b',
						'name' => 'aggregator_document_listing',
						'label' => __('Document listing','govintranet'),
						'display' => 'row',
						'sub_fields' => array (
							array (
								'key' => 'field_552a8997ca105',
								'label' => __('Title','govintranet'),
								'name' => 'aggregator_listing_title',
								'type' => 'text',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'default_value' => '',
								'placeholder' => '',
								'prepend' => '',
								'append' => '',
								'maxlength' => '',
								'readonly' => 0,
								'disabled' => 0,
							),
							array (
								'key' => 'field_552a8997ca106',
								'label' => __('Category','govintranet'),
								'name' => 'aggregator_listing_category',
								'type' => 'taxonomy',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'taxonomy' => 'category',
								'field_type' => 'checkbox',
								'allow_null' => 0,
								'load_save_terms' => 0,
								'return_format' => 'id',
								'multiple' => 0,
								'add_term' => 1,
								'load_terms' => 0,
								'save_terms' => 0,
							),
							array (
								'key' => 'field_552a8997ca107',
								'label' => __('Type','govintranet'),
								'name' => 'aggregator_listing_doctype',
								'type' => 'taxonomy',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'taxonomy' => 'document-type',
								'field_type' => 'radio',
								'allow_null' => 1,
								'load_save_terms' => 0,
								'return_format' => 'id',
								'multiple' => 0,
								'add_term' => 1,
								'load_terms' => 0,
								'save_terms' => 0,
							),
						),
						'min' => '',
						'max' => '',
					),
					array (
						'key' => '5522fe5bc50ab',
						'name' => 'aggregator_link_listing',
						'label' => __('Link listing','govintranet'),
						'display' => 'row',
						'sub_fields' => array (
							array (
								'key' => 'field_552a8997ca108',
								'label' => __('Title','govintranet'),
								'name' => 'aggregator_listing_title',
								'type' => 'text',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'default_value' => '',
								'placeholder' => '',
								'prepend' => '',
								'append' => '',
								'maxlength' => '',
								'readonly' => 0,
								'disabled' => 0,
							),
							array (
								'key' => 'field_552a8997ca109',
								'label' => __('Link','govintranet'),
								'name' => 'aggregator_listing_link',
								'type' => 'relationship',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'post_type' => array (
									0 => 'page',
									1 => 'blog',
									2 => 'news',
									3 => 'task',
								),
								'taxonomy' => array (
								),
								'filters' => array (
									0 => 'search',
									1 => 'post_type',
								),
								'elements' => '',
								'max' => '',
								'return_format' => 'id',
								'min' => 0,
							),
						),
						'min' => '',
						'max' => '',
					),
					array (
						'key' => '55da1017069f5',
						'name' => 'aggregator_event_listing',
						'label' => __('Event listing','govintranet'),
						'display' => 'block',
						'sub_fields' => array (
							array (
								'key' => 'field_55da1017069f6',
								'label' => __('Title','govintranet'),
								'name' => 'aggregator_listing_title',
								'type' => 'text',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'default_value' => '',
								'placeholder' => '',
								'prepend' => '',
								'append' => '',
								'maxlength' => '',
								'readonly' => 0,
								'disabled' => 0,
							),
							array (
								'key' => 'field_55da1017069fb',
								'label' => __('Team','govintranet'),
								'name' => 'aggregator_listing_team',
								'type' => 'relationship',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'post_type' => array (
									0 => 'team',
								),
								'taxonomy' => array (
								),
								'filters' => array (
									0 => 'search',
								),
								'elements' => '',
								'min' => '',
								'max' => 1,
								'return_format' => 'id',
							),
							array (
								'key' => 'field_55da1017069f9',
								'label' => __('Number to display','govintranet'),
								'name' => 'aggregator_listing_number',
								'type' => 'number',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'default_value' => '',
								'placeholder' => '',
								'prepend' => '',
								'append' => '',
								'min' => 0,
								'max' => '',
								'step' => '',
								'readonly' => 0,
								'disabled' => 0,
							),
							array (
								'key' => 'field_55da14e110063',
								'label' => __('Options','govintranet'),
								'name' => 'aggregator_listing_options',
								'type' => 'checkbox',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'choices' => array (
									'Calendar' => 'Calendar',
									'Thumbnail' => 'Thumbnail',
									'Location' => 'Location',
								),
								'default_value' => array (
								),
								'layout' => 'vertical',
								'toggle' => 0,
							),
						),
						'min' => '',
						'max' => '',
					),
				),
			),
			array (
				'key' => 'field_552a89ad6eb68',
				'label' => __('Column 3','govintranet'),
				'name' => 'aggregator_column_3',
				'type' => 'flexible_content',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'button_label' => __('Add to column 3','govintranet'),
				'min' => '',
				'max' => '',
				'layouts' => array (
					array (
						'key' => '5522f1a577034',
						'name' => 'aggregator_news_listing',
						'label' => __('News listing','govintranet'),
						'display' => 'block',
						'sub_fields' => array (
							array (
								'key' => 'field_552a89ae6eb69',
								'label' => __('Title','govintranet'),
								'name' => 'aggregator_listing_title',
								'type' => 'text',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'default_value' => '',
								'placeholder' => '',
								'prepend' => '',
								'append' => '',
								'maxlength' => '',
								'readonly' => 0,
								'disabled' => 0,
							),
							array (
								'key' => 'field_552a89ae6eb6a',
								'label' => __('Need to know','govintranet'),
								'name' => 'aggregator_listing_need_to_know',
								'type' => 'radio',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'choices' => array (
									'Only need to know' => 'Only need to know',
									'Exclude need to know' => 'Exclude need to know',
									'Include need to know' => 'Include need to know',
								),
								'other_choice' => 0,
								'save_other_choice' => 0,
								'default_value' => 'Include need to know',
								'layout' => 'vertical',
							),
							array (
								'key' => 'field_552a89ae6eb6b',
								'label' => __('Freshness','govintranet'),
								'name' => 'aggregator_listing_freshness',
								'type' => 'number',
								'instructions' => __('Don\'t show if older than this number of days. Leave blank to show all.','govintranet'),
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'default_value' => '',
								'placeholder' => '',
								'prepend' => '',
								'append' => '',
								'min' => 0,
								'max' => '',
								'step' => '',
								'readonly' => 0,
								'disabled' => 0,
							),
							array (
								'key' => 'field_552a89ae6eb6c',
								'label' => __('Number to display','govintranet'),
								'name' => 'aggregator_listing_number',
								'type' => 'number',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'default_value' => '',
								'placeholder' => '',
								'prepend' => '',
								'append' => '',
								'min' => 0,
								'max' => '',
								'step' => '',
								'readonly' => 0,
								'disabled' => 0,
							),
							array (
								'key' => 'field_552a89ae6eb6d',
								'label' => __('Compact list','govintranet'),
								'name' => 'aggregator_listing_compact_list',
								'type' => 'true_false',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'message' => '',
								'default_value' => 0,
							),
							array (
								'key' => 'field_552a89ae6eb6e',
								'label' => __('News items to display','govintranet'),
								'name' => 'message',
								'type' => 'message',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'message' => __('Display latest news items matching any of the chosen criteria in the tabs below.','govintranet'),
								'esc_html' => 0,
							),
							array (
								'key' => 'field_552a89ae6eb6f',
								'label' => __('Team','govintranet'),
								'name' => 'aggregator_listing_type',
								'type' => 'tab',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'placement' => 'top',
								'endpoint' => 0,
							),
							array (
								'key' => 'field_552a89ae6eb70',
								'label' => __('Team','govintranet'),
								'name' => 'aggregator_listing_team',
								'type' => 'relationship',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'post_type' => array (
									0 => 'team',
								),
								'taxonomy' => array (
								),
								'filters' => array (
									0 => 'search',
								),
								'elements' => '',
								'max' => 1,
								'return_format' => 'id',
								'min' => 0,
							),
							array (
								'key' => 'field_552a89ae6eb71',
								'label' => __('News type','govintranet'),
								'name' => 'aggregator_listing_type',
								'type' => 'tab',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'placement' => 'top',
								'endpoint' => 0,
							),
							array (
								'key' => 'field_552a89ae6eb72',
								'label' => __('News type','govintranet'),
								'name' => 'aggregator_listing_tax',
								'type' => 'taxonomy',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'taxonomy' => 'news-type',
								'field_type' => 'checkbox',
								'allow_null' => 0,
								'load_save_terms' => 0,
								'return_format' => 'id',
								'multiple' => 0,
								'add_term' => 1,
								'load_terms' => 0,
								'save_terms' => 0,
							),
							array (
								'key' => 'field_552a89ae6eb73',
								'label' => __('Tag','govintranet'),
								'name' => 'aggregator_listing_type',
								'type' => 'tab',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'placement' => 'top',
								'endpoint' => 0,
							),
							array (
								'key' => 'field_552a89ae6eb74',
								'label' => __('Tag','govintranet'),
								'name' => 'aggregator_listing_tag',
								'type' => 'taxonomy',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'taxonomy' => 'post_tag',
								'field_type' => 'multi_select',
								'allow_null' => 0,
								'load_save_terms' => 0,
								'return_format' => 'id',
								'multiple' => 0,
								'add_term' => 1,
								'load_terms' => 0,
								'save_terms' => 0,
							),
						),
						'min' => '',
						'max' => '',
					),
					array (
						'key' => '554a072f23807',
						'name' => 'aggregator_blog_listing',
						'label' => __('Blog listing','govintranet'),
						'display' => 'block',
						'sub_fields' => array (
							array (
								'key' => 'field_554a072f23808',
								'label' => __('Title','govintranet'),
								'name' => 'aggregator_listing_title',
								'type' => 'text',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'default_value' => '',
								'placeholder' => '',
								'prepend' => '',
								'append' => '',
								'maxlength' => '',
								'readonly' => 0,
								'disabled' => 0,
							),
							array (
								'key' => 'field_554a072f23809',
								'label' => __('Need to know','govintranet'),
								'name' => 'aggregator_listing_need_to_know',
								'type' => 'radio',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'choices' => array (
									'Only need to know' => 'Only need to know',
									'Exclude need to know' => 'Exclude need to know',
									'Include need to know' => 'Include need to know',
								),
								'other_choice' => 0,
								'save_other_choice' => 0,
								'default_value' => 'Include need to know',
								'layout' => 'vertical',
							),
							array (
								'key' => 'field_554a072f2380a',
								'label' => __('Freshness','govintranet'),
								'name' => 'aggregator_listing_freshness',
								'type' => 'number',
								'instructions' => __('Don\'t show if older than this number of days. Leave blank to show all.','govintranet'),
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'default_value' => '',
								'placeholder' => '',
								'prepend' => '',
								'append' => '',
								'min' => 0,
								'max' => '',
								'step' => '',
								'readonly' => 0,
								'disabled' => 0,
							),
							array (
								'key' => 'field_554a072f2380b',
								'label' => __('Number to display','govintranet'),
								'name' => 'aggregator_listing_number',
								'type' => 'number',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'default_value' => '',
								'placeholder' => '',
								'prepend' => '',
								'append' => '',
								'min' => 0,
								'max' => '',
								'step' => '',
								'readonly' => 0,
								'disabled' => 0,
							),
							array (
								'key' => 'field_554a072f2380c',
								'label' => __('Compact list','govintranet'),
								'name' => 'aggregator_listing_compact_list',
								'type' => 'true_false',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'message' => '',
								'default_value' => 0,
							),
							array (
								'key' => 'field_554a072f2380f',
								'label' => __('Team','govintranet'),
								'name' => 'aggregator_listing_team',
								'type' => 'relationship',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'post_type' => array (
									0 => 'team',
								),
								'taxonomy' => array (
								),
								'filters' => array (
									0 => 'search',
								),
								'elements' => '',
								'max' => 1,
								'return_format' => 'id',
								'min' => 0,
							),
						),
						'min' => '',
						'max' => '',
					),
					array (
						'key' => '5522f8e2a0432',
						'name' => 'aggregator_task_listing',
						'label' => __('Task listing','govintranet'),
						'display' => 'block',
						'sub_fields' => array (
							array (
								'key' => 'field_552a89ae6eb75',
								'label' => __('Title','govintranet'),
								'name' => 'aggregator_listing_title',
								'type' => 'text',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'default_value' => '',
								'placeholder' => '',
								'prepend' => '',
								'append' => '',
								'maxlength' => '',
								'readonly' => 0,
								'disabled' => 0,
							),
							array (
								'key' => 'field_552a89ae6eb76',
								'label' => __('Compact list','govintranet'),
								'name' => 'aggregator_listing_compact_list',
								'type' => 'true_false',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'message' => '',
								'default_value' => 0,
							),
							array (
								'key' => 'field_552a89ae6eb77',
								'label' => __('Tasks to display','govintranet'),
								'name' => 'message',
								'type' => 'message',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'message' => __('Display tasks and guides matching any of the chosen criteria in the tabs below.','govintranet'),
								'esc_html' => 0,
							),
							array (
								'key' => 'field_552a89ae6eb78',
								'label' => __('Team','govintranet'),
								'name' => 'team',
								'type' => 'tab',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'placement' => 'top',
								'endpoint' => 0,
							),
							array (
								'key' => 'field_552a89ae6eb79',
								'label' => __('Team','govintranet'),
								'name' => 'aggregator_listing_team',
								'type' => 'relationship',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'post_type' => array (
									0 => 'team',
								),
								'taxonomy' => array (
								),
								'filters' => array (
									0 => 'search',
								),
								'elements' => '',
								'max' => 1,
								'return_format' => 'id',
								'min' => 0,
							),
							array (
								'key' => 'field_552a89ae6eb7a',
								'label' => __('Category','govintranet'),
								'name' => 'team',
								'type' => 'tab',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'placement' => 'top',
								'endpoint' => 0,
							),
							array (
								'key' => 'field_552a89ae6eb7b',
								'label' => __('Category','govintranet'),
								'name' => 'aggregator_listing_tax',
								'type' => 'taxonomy',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'taxonomy' => 'category',
								'field_type' => 'checkbox',
								'allow_null' => 0,
								'load_save_terms' => 0,
								'return_format' => 'id',
								'multiple' => 0,
								'add_term' => 1,
								'load_terms' => 0,
								'save_terms' => 0,
							),
							array (
								'key' => 'field_552a89ae6eb7c',
								'label' => __('Tag','govintranet'),
								'name' => 'team',
								'type' => 'tab',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'placement' => 'top',
								'endpoint' => 0,
							),
							array (
								'key' => 'field_552a89ae6eb7d',
								'label' => __('Tag','govintranet'),
								'name' => 'aggregator_listing_tag',
								'type' => 'taxonomy',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'taxonomy' => 'post_tag',
								'field_type' => 'multi_select',
								'allow_null' => 0,
								'load_save_terms' => 0,
								'return_format' => 'id',
								'multiple' => 0,
								'add_term' => 1,
								'load_terms' => 0,
								'save_terms' => 0,
							),
						),
						'min' => '',
						'max' => '',
					),
					array (
						'key' => '5522f7a98627a',
						'name' => 'aggregator_free_format_area',
						'label' => __('Free-format area','govintranet'),
						'display' => 'row',
						'sub_fields' => array (
							array (
								'key' => 'field_552a89ae6eb7e',
								'label' => __('Content','Page content, data','govintranet'),
								'name' => 'aggregator_free_format_area_content',
								'type' => 'wysiwyg',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'default_value' => '',
								'tabs' => 'all',
								'toolbar' => 'full',
								'media_upload' => 1,
							),
						),
						'min' => '',
						'max' => '',
					),
					array (
						'key' => '5522f8048627d',
						'name' => 'aggregator_team_listing',
						'label' => __('Team listing','govintranet'),
						'display' => 'row',
						'sub_fields' => array (
							array (
								'key' => 'field_552a89ae6eb7f',
								'label' => __('Title','govintranet'),
								'name' => 'aggregator_listing_title',
								'type' => 'text',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'default_value' => '',
								'placeholder' => '',
								'prepend' => '',
								'append' => '',
								'maxlength' => '',
								'readonly' => 0,
								'disabled' => 0,
							),
							array (
								'key' => 'field_552a89ae6eb80',
								'label' => __('Team','govintranet'),
								'name' => 'aggregator_listing_team',
								'type' => 'relationship',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'post_type' => array (
									0 => 'team',
								),
								'taxonomy' => array (
								),
								'filters' => array (
									0 => 'search',
								),
								'elements' => '',
								'max' => 1,
								'return_format' => 'id',
								'min' => 0,
							),
						),
						'min' => '',
						'max' => '',
					),
					array (
						'key' => '5522fc1de503b',
						'name' => 'aggregator_document_listing',
						'label' => __('Document listing','govintranet'),
						'display' => 'row',
						'sub_fields' => array (
							array (
								'key' => 'field_552a89ae6eb81',
								'label' => __('Title','govintranet'),
								'name' => 'aggregator_listing_title',
								'type' => 'text',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'default_value' => '',
								'placeholder' => '',
								'prepend' => '',
								'append' => '',
								'maxlength' => '',
								'readonly' => 0,
								'disabled' => 0,
							),
							array (
								'key' => 'field_552a89ae6eb82',
								'label' => __('Category','govintranet'),
								'name' => 'aggregator_listing_category',
								'type' => 'taxonomy',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'taxonomy' => 'category',
								'field_type' => 'checkbox',
								'allow_null' => 0,
								'load_save_terms' => 0,
								'return_format' => 'id',
								'multiple' => 0,
								'add_term' => 1,
								'load_terms' => 0,
								'save_terms' => 0,
							),
							array (
								'key' => 'field_552a89ae6eb83',
								'label' => __('Type','govintranet'),
								'name' => 'aggregator_listing_doctype',
								'type' => 'taxonomy',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'taxonomy' => 'document-type',
								'field_type' => 'radio',
								'allow_null' => 1,
								'load_save_terms' => 0,
								'return_format' => 'id',
								'multiple' => 0,
								'add_term' => 1,
								'load_terms' => 0,
								'save_terms' => 0,
							),
						),
						'min' => '',
						'max' => '',
					),
					array (
						'key' => '5522fe5bc50ab',
						'name' => 'aggregator_link_listing',
						'label' => __('Link listing','govintranet'),
						'display' => 'row',
						'sub_fields' => array (
							array (
								'key' => 'field_552a89ae6eb84',
								'label' => __('Title','govintranet'),
								'name' => 'aggregator_listing_title',
								'type' => 'text',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'default_value' => '',
								'placeholder' => '',
								'prepend' => '',
								'append' => '',
								'maxlength' => '',
								'readonly' => 0,
								'disabled' => 0,
							),
							array (
								'key' => 'field_552a89ae6eb85',
								'label' => __('Link','govintranet'),
								'name' => 'aggregator_listing_link',
								'type' => 'relationship',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'post_type' => array (
									0 => 'page',
									1 => 'blog',
									2 => 'news',
									3 => 'task',
								),
								'taxonomy' => array (
								),
								'filters' => array (
									0 => 'search',
									1 => 'post_type',
								),
								'elements' => '',
								'max' => '',
								'return_format' => 'id',
								'min' => 0,
							),
						),
						'min' => '',
						'max' => '',
					),
					array (
						'key' => '55da103d069fc',
						'name' => 'aggregator_event_listing',
						'label' => __('Event listing','govintranet'),
						'display' => 'block',
						'sub_fields' => array (
							array (
								'key' => 'field_55da103d069fd',
								'label' => __('Title','govintranet'),
								'name' => 'aggregator_listing_title',
								'type' => 'text',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'default_value' => '',
								'placeholder' => '',
								'prepend' => '',
								'append' => '',
								'maxlength' => '',
								'readonly' => 0,
								'disabled' => 0,
							),
							array (
								'key' => 'field_55da103d06a02',
								'label' => __('Team','govintranet'),
								'name' => 'aggregator_listing_team',
								'type' => 'relationship',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'post_type' => array (
									0 => 'team',
								),
								'taxonomy' => array (
								),
								'filters' => array (
									0 => 'search',
								),
								'elements' => '',
								'min' => '',
								'max' => 1,
								'return_format' => 'id',
							),
							array (
								'key' => 'field_55da103d06a00',
								'label' => __('Number to display','govintranet'),
								'name' => 'aggregator_listing_number',
								'type' => 'number',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'default_value' => '',
								'placeholder' => '',
								'prepend' => '',
								'append' => '',
								'min' => 0,
								'max' => '',
								'step' => '',
								'readonly' => 0,
								'disabled' => 0,
							),
							array (
								'key' => 'field_55da147810062',
								'label' => __('Options','govintranet'),
								'name' => 'aggregator_listing_options',
								'type' => 'checkbox',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array (
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'choices' => array (
									'Calendar' => 'Calendar',
									'Thumbnail' => 'Thumbnail',
									'Location' => 'Location',
								),
								'default_value' => array (
								),
								'layout' => 'vertical',
								'toggle' => 0,
							),
						),
						'min' => '',
						'max' => '',
					),
				),
			),
		),
		'location' => array (
			array (
				array (
					'param' => 'page_template',
					'operator' => '==',
					'value' => 'page-aggregator/page-aggregator.php',
				),
			),
		),
		'menu_order' => 0,
		'position' => 'normal',
		'style' => 'default',
		'label_placement' => 'top',
		'instruction_placement' => 'label',
		'hide_on_screen' => '',
	));

	if ( get_option( 'options_module_teams' ) ):
		acf_add_local_field_group(array (
			'key' => 'group_5522eeebca049',
			'title' => __('Related teams','govintranet'),
			'fields' => array (
				array (
					'key' => 'field_5522eef7229a4',
					'label' => __('Team','govintranet'),
					'name' => 'related_team',
					'prefix' => '',
					'type' => 'relationship',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'post_type' => array (
						0 => 'team',
					),
					'taxonomy' => '',
					'filters' => array (
						0 => 'search',
					),
					'elements' => '',
					'max' => '',
					'return_format' => 'id',
				),
			),
			'location' => array (
				array (
					array (
						'param' => 'post_type',
						'operator' => '==',
						'value' => 'news',
					),
				),
				array (
					array (
						'param' => 'post_type',
						'operator' => '==',
						'value' => 'task',
					),
				),
				array (
					array (
						'param' => 'post_type',
						'operator' => '==',
						'value' => 'blog',
					),
				),
				array (
					array (
						'param' => 'post_type',
						'operator' => '==',
						'value' => 'event',
					),
				),
			),
			'menu_order' => 20,
			'position' => 'normal',
			'style' => 'default',
			'label_placement' => 'top',
			'instruction_placement' => 'label',
			'hide_on_screen' => '',
		));
	endif;
	
	acf_add_local_field_group(array (
		'key' => 'group_55feb1d56546e',
		'title' => __('Sidebar','govintranet'),
		'fields' => array (
			array (
				'key' => 'field_55feb1e8ab53b',
				'label' => __('Sidebar content','govintranet'),
				'name' => 'ht_sidebar_content',
				'type' => 'wysiwyg',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'default_value' => '',
				'tabs' => 'all',
				'toolbar' => 'full',
				'media_upload' => 1,
			),
		),
		'location' => array (
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'page',
				),
			),
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'blog',
				),
			),
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'news',
				),
			),
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'vacancy',
				),
			),
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'project',
				),
			),
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'task',
				),
			),
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'event',
				),
			),
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'news-update',
				),
			),
		),
		'menu_order' => 17,
		'position' => 'normal',
		'style' => 'default',
		'label_placement' => 'top',
		'instruction_placement' => 'label',
		'hide_on_screen' => '',
	));

	acf_add_local_field_group(array (
		'key' => 'group_5696cd9ca0e42',
		'title' => 'Columns',
		'fields' => array (
			array (
				'key' => 'field_5696cdbde4305',
				'label' => 'Restrict to 3 columns',
				'name' => 'ht_about_restrict',
				'type' => 'true_false',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'message' => '',
				'default_value' => 0,
			),
		),
		'location' => array (
			array (
				array (
					'param' => 'page_template',
					'operator' => '==',
					'value' => 'page-about.php',
				),
			),
		),
		'menu_order' => 0,
		'position' => 'normal',
		'style' => 'default',
		'label_placement' => 'top',
		'instruction_placement' => 'label',
		'hide_on_screen' => '',
		'active' => 1,
		'description' => '',
	));
	
	acf_add_local_field_group(array (
		'key' => 'group_5697ff3c468a8',
		'title' => 'Newsboard',
		'fields' => array (
			array (
				'key' => 'field_5697ff7f299ce',
				'label' => 'Tabs',
				'name' => 'newsboard_tabs',
				'type' => 'repeater',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'collapsed' => 'field_5697ffdc299cf',
				'min' => '',
				'max' => '',
				'layout' => 'row',
				'button_label' => 'Add a tab',
				'sub_fields' => array (
					array (
						'key' => 'field_5699209316262',
						'label' => 'Title',
						'name' => 'newsboard_tab_title',
						'type' => 'text',
						'instructions' => '',
						'required' => 1,
						'conditional_logic' => 0,
						'wrapper' => array (
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => '',
						'placeholder' => '',
						'prepend' => '',
						'append' => '',
						'maxlength' => '',
						'readonly' => 0,
						'disabled' => 0,
					),
					array (
						'key' => 'field_5697ffdc299cf',
						'label' => 'Content type',
						'name' => 'newsboard_tab_content_type',
						'type' => 'select',
						'instructions' => '',
						'required' => 1,
						'conditional_logic' => 0,
						'wrapper' => array (
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'choices' => array (
							1 => 'News',
							2 => 'News updates',
							3 => 'Blog posts',
							4 => 'Events',
							5 => 'News type dropdown',
							6 => 'News update type dropdown',
							7 => 'Blog category dropdown',
						),
						'default_value' => array (
						),
						'allow_null' => 0,
						'multiple' => 0,
						'ui' => 0,
						'ajax' => 0,
						'placeholder' => '',
						'disabled' => 0,
						'readonly' => 0,
					),
					array (
						'key' => 'field_56997ea39edeb',
						'label' => 'Feature first',
						'name' => 'newsboard_feature_first',
						'type' => 'true_false',
						'instructions' => 'Highlight the first post with a large feature image.',
						'required' => 0,
						'conditional_logic' => array (
							array (
								array (
									'field' => 'field_5697ffdc299cf',
									'operator' => '==',
									'value' => '1',
								),
							),
							array (
								array (
									'field' => 'field_5697ffdc299cf',
									'operator' => '==',
									'value' => '3',
								),
							),
						),
						'wrapper' => array (
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'message' => '',
						'default_value' => 0,
					),
					array (
						'key' => 'field_5698009c299d0',
						'label' => 'News type',
						'name' => 'newsboard_news_type',
						'type' => 'taxonomy',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => array (
							array (
								array (
									'field' => 'field_5697ffdc299cf',
									'operator' => '==',
									'value' => '1',
								),
							),
						),
						'wrapper' => array (
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'taxonomy' => 'news-type',
						'field_type' => 'checkbox',
						'allow_null' => 1,
						'add_term' => 0,
						'save_terms' => 0,
						'load_terms' => 0,
						'return_format' => 'id',
						'multiple' => 0,
					),
					array (
						'key' => 'field_56980141299d1',
						'label' => 'News update type',
						'name' => 'newsboard_news_update_type',
						'type' => 'taxonomy',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => array (
							array (
								array (
									'field' => 'field_5697ffdc299cf',
									'operator' => '==',
									'value' => '2',
								),
							),
						),
						'wrapper' => array (
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'taxonomy' => 'news-update-type',
						'field_type' => 'checkbox',
						'allow_null' => 0,
						'add_term' => 0,
						'save_terms' => 0,
						'load_terms' => 0,
						'return_format' => 'id',
						'multiple' => 0,
					),
					array (
						'key' => 'field_569920b916263',
						'label' => 'Blog category',
						'name' => 'newsboard_blog_category',
						'type' => 'taxonomy',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => array (
							array (
								array (
									'field' => 'field_5697ffdc299cf',
									'operator' => '==',
									'value' => '3',
								),
							),
						),
						'wrapper' => array (
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'taxonomy' => 'blog-category',
						'field_type' => 'checkbox',
						'allow_null' => 1,
						'add_term' => 0,
						'save_terms' => 0,
						'load_terms' => 0,
						'return_format' => 'id',
						'multiple' => 0,
					),
					array (
						'key' => 'field_56980175299d2',
						'label' => 'Event types',
						'name' => 'newsboard_event_types',
						'type' => 'taxonomy',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => array (
							array (
								array (
									'field' => 'field_5697ffdc299cf',
									'operator' => '==',
									'value' => '4',
								),
							),
						),
						'wrapper' => array (
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'taxonomy' => 'event-type',
						'field_type' => 'checkbox',
						'allow_null' => 1,
						'add_term' => 0,
						'save_terms' => 0,
						'load_terms' => 0,
						'return_format' => 'id',
						'multiple' => 0,
					),
					array (
						'key' => 'field_569982c2c49b9',
						'label' => 'Link to more',
						'name' => 'newsboard_link_to_more',
						'type' => 'true_false',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => array (
							array (
								array (
									'field' => 'field_5697ffdc299cf',
									'operator' => '==',
									'value' => '1',
								),
							),
							array (
								array (
									'field' => 'field_5697ffdc299cf',
									'operator' => '==',
									'value' => '2',
								),
							),
							array (
								array (
									'field' => 'field_5697ffdc299cf',
									'operator' => '==',
									'value' => '3',
								),
							),
							array (
								array (
									'field' => 'field_5697ffdc299cf',
									'operator' => '==',
									'value' => '4',
								),
							),
						),
						'wrapper' => array (
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'message' => '',
						'default_value' => 0,
					),
					array (
						'key' => 'field_569982f9c49bd',
						'label' => 'Link to',
						'name' => 'newsboard_link_to',
						'type' => 'radio',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => array (
							array (
								array (
									'field' => 'field_569982c2c49b9',
									'operator' => '==',
									'value' => '1',
								),
								array (
									'field' => 'field_5697ffdc299cf',
									'operator' => '!=',
									'value' => '5',
								),
								array (
									'field' => 'field_5697ffdc299cf',
									'operator' => '!=',
									'value' => '6',
								),
								array (
									'field' => 'field_5697ffdc299cf',
									'operator' => '!=',
									'value' => '7',
								),
							),
						),
						'wrapper' => array (
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'choices' => array (
							1 => 'Page',
							2 => 'Post type',
							3 => 'Term',
						),
						'other_choice' => 0,
						'save_other_choice' => 0,
						'default_value' => '',
						'layout' => 'horizontal',
					),
					array (
						'key' => 'field_569983a9c49be',
						'label' => 'Link',
						'name' => 'newsboard_page_link',
						'type' => 'relationship',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => array (
							array (
								array (
									'field' => 'field_569982c2c49b9',
									'operator' => '==',
									'value' => '1',
								),
								array (
									'field' => 'field_569982f9c49bd',
									'operator' => '==',
									'value' => '1',
								),
							),
						),
						'wrapper' => array (
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'post_type' => array (
							0 => 'page',
						),
						'taxonomy' => array (
						),
						'filters' => array (
							0 => 'search',
						),
						'elements' => '',
						'min' => '',
						'max' => '',
						'return_format' => 'id',
					),
				),
			),
		),
		'location' => array (
			array (
				array (
					'param' => 'page_template',
					'operator' => '==',
					'value' => 'newsboard/page-newsboard.php',
				),
			),
		),
		'menu_order' => 0,
		'position' => 'normal',
		'style' => 'default',
		'label_placement' => 'top',
		'instruction_placement' => 'label',
		'hide_on_screen' => '',
		'active' => 1,
		'description' => '',
	));

endif;

function my_post_queries( $query ) {
	// do not alter the query on wp-admin pages and only alter it if it's the main query
	if (!is_admin() && $query->is_main_query()){

		if(is_category() && !is_admin()){
			$query->set('post_type', 'task');
		}

	}
}
add_action( 'pre_get_posts', 'my_post_queries' );

// [listdocs type="policy" cat="hr" desc='true']
function listdocs_func( $atts ) {
	extract( shortcode_atts( array(
		'type' => 'any',
		'cat' => 'any',
		'desc' => false
	), $atts ) );
	
	$cat_id = $cat;
	$doctyp = $type;
	$taxonomies[]='category';
	$post_type[]='attachment';
	$post_cat = get_terms_by_media_type( $taxonomies, $post_type);
	
	if ($cat_id != "any") {
		$catterm = get_category_by_slug($cat_id);
		$catname = $catterm->name;
		$catid = $catterm->term_id;
	} else {
		$catname = __('All categories','govintranet');
	}	
	
	if ($doctyp != "any") {
		$dtterm = get_term_by('slug', $doctyp, 'document-type'); 
		$dtname = $dtterm->name;
		$dtid = $dtterm->term_id;
	} else {
		$dtname = __('All document types','govintranet');
	}	
	
	// get all document types for the left hand menu
	$args = array(
	    'orderby'       => 'name', 
	    'order'         => 'ASC',
	    'hide_empty'    => false 
	    );

	$subcat = get_terms( 'document-type', $args );
	$cathead = '';
	if ($cat_id!='any' && $doctyp!='any'){	// cat and doc type
	
	//tax queries need to be meta queries for document_type
		
		$docs = get_posts(array(
		'post_type'=>'attachment',
		'orderby'=>'title',
		'order'=>'ASC',
	    'posts_per_page' => -1,
	    'tax_query'=>array(
	    array(  
	    'taxonomy' => 'category',
		'field' => 'slug',
		'terms' => $cat_id)),
		'meta_query'=>array(
	    array(  
	    'key' => 'document_type',
	    'compare'=>"LIKE",
		'value' => '"' .$dtid.'"' ))
		));
	} 

	if ($cat_id=='any' && $doctyp!='any'){	// single doc type
		$docs = get_posts(Array(
		'post_type'=>'attachment',
		'orderby'=>'title',
		'order'=>'ASC',
	    'posts_per_page' => -1,
		'meta_query'=>array(
	    array(  
	    'key' => 'document_type',
	    'compare'=>"LIKE",
		'value' => '"' .$dtid.'"' ))
		));	
	}

	if ($cat_id=='any' && $doctyp=='any' ){ // no filter
		$inlist=array();
	    foreach ( $subcat as $term ) {
	       $inlist[] = $term->term_id; 
	     }
		$docs = get_posts(array(
			'post_type'=>'attachment',
			'orderby'=>'title',
			'order'=>'ASC',
	        'posts_per_page' => -1,
			'meta_query'=>array(
		    array(  
		    'key' => 'document_type',
		    'value' => '',
	        'compare'=>'!=',
			)),
	        
		));	
	}

	if ($cat_id!='any' && $doctyp=='any' ){ // single cat
		$inlist=array();
		foreach ( $subcat as $term ) {
			$inlist[] = $term->term_id; 
		}
		$docs = get_posts(array(
			'post_type'=>'attachment',
			'orderby'=>'title',
			'order'=>'ASC',
			'posts_per_page' => -1,
			'tax_query'=>array(
			array(  
			'taxonomy' => 'category',
			'field' => 'slug',
			'terms' => $cat_id)),
			'meta_query'=>array(
		    array(  
		    'key' => 'document_type',
		    'value' => '',
	        'compare'=>'!=',
			)),
		));	
	}

	$postsarray=array();
	foreach($docs as $doc){
		//if ( substr($doc->post_mime_type,0,5) == 'image' ) continue; //filter out images
		$postsarray[].=$doc->ID. ",";
	};

	if (count($docs) == 0 ) {
		$postsarray[]='';
	}
	$counter = 0;	
	$docs = new wp_query(array('orderby'=>'title','order'=>'ASC','post_status'=>'inherit','posts_per_page'=>-1,'post_type'=>'attachment','post__in'=>$postsarray));
	
	$html= '<ul class="docmenu">';
	global $post;
	if ( $docs->have_posts() ) while ( $docs->have_posts() ) : $docs->the_post(); 
		$html.= '<li><a href="'.$post->guid.'">';
		$html.= ''.$post->post_title;
		$html.= '</a>';
		if ($post->post_content && $desc) $html.='<br>'.$post->post_content.'</li>';
	endwhile;

	$html.= '</ul>';
	return $html;
	
}
add_shortcode( 'listdocs', 'listdocs_func' );

function ht_landingpages_shortcode($atts,$content){
    //get any attributes that may have been passed; override defaults
    $opts=shortcode_atts( array(
        'id' => '',
        'exclude' => '',
        'type'=>''
        ), $atts );

	// get child pages
	
	global $wp_query;
	$id = ($opts['id'] == "") ? $wp_query->post->ID : $opts['id'];
	
	$children = get_pages("child_of=".$id."&parent=".$id."&hierarchical=0&exclude=".$opts['exclude']."&posts_per_page=-1&post_type=page&sort_column=menu_order&sort_order=ASC");

	foreach((array)$children as $c) {
		
		if ($opts['type']=='list'){			
			$output .= "<li><a href='".get_permalink($c->ID)."'>".get_the_title($c->ID)."</a></li>";
		} else {
			$excerpt='';
			if ($c->post_excerpt) {
				$excerpt = $c->post_excerpt;
			} else {
				if (strlen($c->post_content)>200) {
					$excerpt = substr(strip_tags($c->post_content),0,200) . "&hellip;";
				} elseif ($c->post_content == "" || $c->post_content == 0) {
					$excerpt = "";
				} else {			
					$excerpt = strip_tags($c->post_content); 
				}
			}
			$output .= "
			<div class='htlandingpage clearfix'>
			  ".get_the_post_thumbnail($c->ID,"listingthumb","class=listingthumb")."
			  <h2><a href='".get_permalink($c->ID)."'>".get_the_title($c->ID)."</a></h2>
			  <p>".$excerpt."</p>
			</div>
			";
		}
	}
	if ($opts['type']=='list'){			
		$html = "<ul>" . $output . "</li>";
	} else {
		$html = "<div class='htlandingpageblock'>" . $output . "</div>";
	}
    return $html;
}

add_shortcode("landingpage", "ht_landingpages_shortcode");

function ht_listteams_shortcode(){
	$args = array(
		'echo'         => 0,
		'post_type'    => 'team',
		'post_status'  => 'publish',
		'title_li'     => "", 
	);			
	$teams = wp_list_pages( $args );
	return $teams;
}
add_shortcode("listteams", "ht_listteams_shortcode");

function ht_listtags_shortcode($atts,$content){

	 wp_register_script( 'masonry.pkgd.min', get_stylesheet_directory_uri() . "/js/masonry.pkgd.min.js");
	 wp_enqueue_script( 'masonry.pkgd.min',95 );

    //get any attributes that may have been passed; override defaults
    $opts=shortcode_atts( array(
        'tag' => '',
        'format' => '',
        
        ), $atts );
	
	global $wp_query;

	$tag = $opts['tag'];
	$format = $opts['format'];
	$q = 'posts_per_page=-1&tag='.$tag;
	$query = get_posts( $q );
	
	$output='';
	foreach ($query as $list){		
		$thisexcerpt='';
		$thistitle = get_the_title($list->ID);
		$thisURL=get_permalink($list->ID);
		$thisexcerpt= $list->post_excerpt;
		$thisdate= $list->post_date;
		$thisdate=date(get_option('date_format'),strtotime($thisdate));
		$image_url = get_the_post_thumbnail($list->ID, 'medium', array("class"=>"img img-responsive","width"=>175,"height"=>175));	
		
		$output.="
		<div class='grid-item well well-sm'>
			<div class='itemimage'><a href=\"".$thisURL."\" title=\"".$thistitle." ".$title_context."\">".$image_url."</a></div>
				<p><a href=\"".$thisURL."\" title=\"".$thistitle." ".$title_context."\">".$thistitle."</a></p>";
				if ($format=="full"){
					$output.="<p><span class='listglyph'><i class='glyphicon glyphicon-calendar'></i> ".$thisdate."</span> </p>".wpautop($thisexcerpt);
				}
		$output.="</div>";
	
	}
		$output=
		
		'<div id="container" class="js-masonry"
  data-masonry-options=\'{ "columnWidth": ".grid-sizer", "itemSelector": ".grid-item", "gutter": 10 }\'><div class="grid-sizer"></div>'.$output."</div>";
	wp_reset_query();
    return $output;
}

add_shortcode("listtags", "ht_listtags_shortcode");


function ht_people_shortcode($atts){
    //get any attributes that may have been passed; override defaults
    $opts=shortcode_atts( array(
        'id' => '',
        'team' => '',
        ), $atts );
	
	$userid = $opts['id'];
	$directorystyle = get_option('options_staff_directory_style'); // 0 = squares, 1 = circles
	$showmobile = get_option('options_show_mobile_on_staff_cards'); // 1 = show
	$fulldetails = get_option('options_full_detail_staff_cards');

	$context = get_user_meta($userid,'user_job_title',true);
	if ($context=='') $context="staff";
	$icon = "user";			
	$user_info = get_userdata($userid);
	$userurl = site_url().'/staff/'.$user_info->user_nicename;
	$displayname = get_user_meta($userid ,'first_name',true )." ".get_user_meta($userid ,'last_name',true );		
	$avatarhtml = get_avatar($userid,66);
	if ($directorystyle==1):
		$avatarhtml = str_replace("photo", "photo alignleft img-circle", $avatarhtml);
	else:
		$avatarhtml = str_replace("photo", "photo alignleft", $avatarhtml);
	endif;
	$html = '';
	$counter = 0;
	$tcounter = 0;
	if ($fulldetails){
			
		$html.= "<div class='col-lg-6 col-md-6 col-sm-6'><div class='media well well-sm'><a href='".site_url()."/staff/".$user_info->user_nicename."/'>".$avatarhtml."</a><div class='media-body'><p><a href='".site_url()."/staff/".$user_info->user_nicename."/'><strong>".$displayname."</strong></a><br>";

		// display team name(s)
		if ( get_user_meta($userid ,'user_job_title',true )) : 
			$html.= get_user_meta($userid ,'user_job_title',true )."<br>";
		endif;
		
		if ( get_user_meta($userid ,'user_telephone',true )) : 

			$html.= '<i class="dashicons dashicons-phone"></i> <a href="tel:'.str_replace(" ", "", get_user_meta($userid ,"user_telephone",true )).'">'.get_user_meta($userid ,'user_telephone',true )."</a><br>";

		endif; 

		if ( get_user_meta($userid ,'user_mobile',true ) && $showmobile ) : 

			$html.= '<i class="dashicons dashicons-smartphone"></i> <a href="tel:'.str_replace(" ", "", get_user_meta($userid ,"user_mobile",true )).'">'.get_user_meta($userid ,'user_mobile',true )."</a><br>";

		 endif;

		$html.=  '<a href="mailto:'.$user_info->user_email.'">' . __("Email","govintranet") . ' '. $user_info->first_name. '</a></p></div></div></div>';
		
		$counter++;	
		$tcounter++;	
		
	 //end full details
	} else { 
		$html.= "<div class='col-lg-6 col-md-6 col-sm-12'><div class='indexcard'><a href='".site_url()."/staff/".$user_info->user_nicename."/'><div class='media'>".$avatarhtml."<div class='media-body'><strong>".$displayname."</strong><br>";
			
		if ( get_user_meta($userid ,'user_job_title',true )) $html.= '<span class="small">'.get_user_meta($userid ,'user_job_title',true )."</span><br>";

		if ( get_user_meta($userid ,'user_telephone',true )) $html.= '<span class="small"><i class="dashicons dashicons-phone"></i> '.get_user_meta($userid ,'user_telephone',true )."</span><br>";
		if ( get_user_meta($userid ,'user_mobile',true ) && $showmobile ) $html.= '<span class="small"><i class="dashicons dashicons-smartphone"></i> '.get_user_meta($userid ,'user_mobile',true )."</span>";
						
		$html.= "</div></div></div></div></a>";
		$counter++;	
	}	
	
    return "<div id='peoplenav'>".$html."</div>";
}

add_shortcode("people", "ht_people_shortcode");

if (function_exists('acf_add_options_page')):
	acf_add_options_page();
endif;

function gi_tag_cloud($taxonomy, $term, $post_type) {
	$taxid = get_queried_object()->term_id;	
	$posts = get_posts(array(
		'post_type' => $post_type,
		'posts_per_page' => -1,
		'post_status' => 'publish',
		'tax_query' => array(
			array(
				'taxonomy' => $taxonomy,
				'field' => 'slug',
				'terms' => $term,
			))
		)	
	);
	$alltags = array();
	foreach ($posts as $p){
		$tags = get_the_tags($p->ID);
		if ( $tags ) foreach ($tags as $t){
			if (isset($alltags[$t->slug]['count'])){
				$alltags[$t->slug]['count']++;
			} else {
				if (isset($alltags[$t->slug]['count'])){
					$alltags[$t->slug]['count']++;
				} else {
					$alltags[$t->slug]['count'] = 1;
				}
				$alltags[$t->slug]['name'] = $t->name;
				$alltags[$t->slug]['slug'] = $t->slug;
				$alltags[$t->slug]['link'] = get_term_link( intval($t->term_id), $t->taxonomy );
			}
		}
	}
	
	ksort($alltags);
	$tagstr="<span><a  class='wptag t".$taxid."' href='?showtag=&paged=1'>"._x('All','all tags','govintranet')."</a></span> "; 
	foreach ($alltags as $a):
		$active='';
		if (isset( $_GET['showtag'] ) && $_GET['showtag'] == $a['slug']) { $active = 'active " '; $activeicon="<span class='dashicons dashicons-tag'></span>&nbsp;"; } else { $active = ''; $activeicon = '';};
		$tagstr.="<span><a class='wptag ".$active."t".$taxid;
		$tagstr.="' href='?showtag=".$a['slug']."&paged=1'>" . $activeicon . str_replace(' ', '&nbsp;' , $a['name']) . '</a></span> '; 
	endforeach;
	if ( "<span><a  class='wptag t".$taxid."' href='?showtag=&paged=1'>"._x('All','all tags','govintranet')."</a></span> " == $tagstr ):
		return;
	else:
		return $tagstr;
	endif;
}

function gi_howto_tag_cloud($posttype) {
	$temp = $posttype;
	$posts = get_posts(array(
		'post_type' => $posttype,
		'posts_per_page' => -1,
		'post_status' => 'publish',
	)	
	);
	$alltags = array();
	foreach ($posts as $p): 
		$tags = get_the_tags($p->ID); 
		if ( $tags ) foreach ((array)$tags as $t):
			if ( !isset( $alltags[$t->slug]['count'] ) ):
				$alltags[$t->slug]['count'] = 1;
			else: 
				$alltags[$t->slug]['count'] = $alltags[$t->slug]['count'] + 1; 
			endif;
			$alltags[$t->slug]['name'] = $t->name;
			$alltags[$t->slug]['slug'] = $t->slug;
			$alltags[$t->slug]['link'] = get_term_link( intval($t->term_id), $t->taxonomy );
		endforeach;
	endforeach;
	
	ksort($alltags);
	$tagstr=""; 
	foreach ($alltags as $a):
		$tagstr=$tagstr."<span><a class='wptag ";
		if (isset($_GET['tag']) && $_GET['tag'] == $a['slug']) $tagstr=$tagstr." active";
		$tagstr.="' href='".site_url()."/tag/".$a['slug']."/?paged=1&type=".$temp."'>" . str_replace(' ', '&nbsp;' , $a['name']) . '</a></span> '; 
	endforeach;
	return $tagstr;
}

function pippin_login_form_shortcode( $atts, $content = null ) {
 
	extract( shortcode_atts( array(
      'redirect' => ''
      ), $atts ) );
 
	if (!is_user_logged_in()) {
		if($redirect) {
			$redirect_url = $redirect;
		} else {
			$redirect_url = get_permalink();
		}
		$form = wp_login_form(array('echo' => false, 'redirect' => $redirect_url ));
	} 
	return $form;
}
add_shortcode('loginform', 'pippin_login_form_shortcode');


//allow attributing a post to a parent that is in draft status
function my_attributes_dropdown_pages_args($dropdown_args) {

    $dropdown_args['post_status'] = array('publish','draft','private','pending','future');

    return $dropdown_args;
}
add_filter('page_attributes_dropdown_pages_args', 'my_attributes_dropdown_pages_args', 1, 1);

/**
 * Save post metadata when a news post is saved.
 *
 * @param int $post_id The ID of the post.
 */
function save_news_meta( $post_id ) {

    /*
     * In production code, $slug should be set only once in the plugin,
     * preferably as a class property, rather than in each function that needs it.
     */
    $slug = 'news';

    // If this isn't a 'news' post, don't update it.
    if ( isset( $_POST['post_type'] ) && $slug != $_POST['post_type'] ) {
        return;
    }

    // - Update the post's metadata.
    if ( $prev = get_post_meta( $post_id, 'news_expiry_time',true ) ) {
    	$newvalue = date('H:i',strtotime($prev));
		update_post_meta( $post_id, 'news_expiry_time', $newvalue, $prev );
	}
	return;
}
add_action( 'save_post', 'save_news_meta' );

/**
 * Save post metadata when a news update post is saved.
 *
 * @param int $post_id The ID of the post.
 */
function save_news_update_meta( $post_id ) {

    /*
     * In production code, $slug should be set only once in the plugin,
     * preferably as a class property, rather than in each function that needs it.
     */
    $slug = 'news-update';

    // If this isn't an 'news update' post, don't update it.
    if ( isset( $_POST['post_type'] ) && $slug != $_POST['post_type'] ) {
        return;
    }

    // - Update the post's metadata.
    if ( $prev = get_post_meta( $post_id, 'news_update_expiry_time',true ) ) {
    	$newvalue = date('H:i',strtotime($prev));
		update_post_meta( $post_id, 'news_update_expiry_time', $newvalue, $prev );
	}
	return;
}
add_action( 'save_post', 'save_news_update_meta' );

/**
 * Save post metadata when a post is saved.
 *
 * @param int $post_id The ID of the post.
 */
function save_vacancy_meta( $post_id ) {

    /*
     * In production code, $slug should be set only once in the plugin,
     * preferably as a class property, rather than in each function that needs it.
     */
    $slug = 'vacancy';

    // If this isn't an 'vacancy' post, don't update it.
    if ( isset( $_POST['post_type'] ) && $slug != $_POST['post_type'] ) {
        return;
    }

    // - Update the post's metadata.
    if ( $prev = get_post_meta( $post_id, 'vacancy_closing_time', true ) ) {
    	$newvalue = date( 'H:i', strtotime( $prev ));
		update_post_meta( $post_id, 'vacancy_closing_time', $newvalue, $prev );
	}
	return;
}
add_action( 'save_post', 'save_vacancy_meta' );

function save_event_meta( $post_id ) {

    /*
     * In production code, $slug should be set only once in the plugin,
     * preferably as a class property, rather than in each function that needs it.
     */
    $slug = 'event';

    // If this isn't an 'event' post, don't update it.
    if ( isset( $_POST['post_type'] ) && $slug != $_POST['post_type'] ) {
        return;
    }

    // - Update the post's metadata.
    if ( $prev = get_post_meta( $post_id, 'event_start_time',true ) ) {
    	$newvalue = date('H:i',strtotime($prev));
		update_post_meta( $post_id, 'event_start_time', $newvalue, $prev );
	}
    if ( $prev = get_post_meta( $post_id, 'event_end_time',true ) ) {
    	$newvalue = date('H:i',strtotime($prev));
		update_post_meta( $post_id, 'event_end_time', $newvalue, $prev );
	}
	return;
}
add_action( 'save_post', 'save_event_meta' );

function save_keyword_meta( $post_id ) {

    /*
     * In production code, $slug should be set only once in the plugin,
     * preferably as a class property, rather than in each function that needs it.
     */
    $slug = array('news','news-update','page','task','blogpost','project','vacancy','team','event');

    if ( isset( $_POST['post_type'] ) && !in_array( $_POST['post_type'] , $slug ) ) {
        return;
    }

    // - Update the post's metadata.
    if ( $prev = get_post_meta( $post_id, 'keywords',true ) ) {
    	$newvalue = str_replace("," , " ", $prev);
    	$newvalue = str_replace("  " , " ", $newvalue);
		update_post_meta( $post_id, 'keywords', $newvalue, $prev );
	}
	return;
}
add_action( 'save_post', 'save_keyword_meta' );

function ht_filter_search($query) {
    if ($query->is_tag && !is_admin()) { 
		$query->set('post_type', array('any'));
    }
    return $query;
}; 
add_filter('pre_get_posts', 'ht_filter_search');

// set login banner link to intranet homepage
function ht_login_url(){
return site_url("/"); 
}

add_filter('login_headerurl', 'ht_login_url');

add_filter( 'wp_nav_menu_items', 'add_loginout_link', 10, 2 );
function add_loginout_link( $items, $args ) {
    if (is_user_logged_in() && $args->theme_location == 'secondary') {
	    $current_user = wp_get_current_user();
		$userurl = get_author_posts_url( $current_user->ID); 
		$staffdirectory = get_option('options_module_staff_directory');
		if (function_exists('bp_activity_screen_index')){ // if using BuddyPress - link to the members page
			$userurl=str_replace('/author', '/members', $userurl); }
		elseif (function_exists('bbp_get_displayed_user_field') && $staffdirectory ){ // if using bbPress - link to the staff page
			$userurl=str_replace('/author', '/staff', $userurl); }
		elseif (function_exists('bbp_get_displayed_user_field')  ){ // if using bbPress - link to the staff page
			$userurl=str_replace('/author', '/users', $userurl);
		}	    
	    if ( get_option("options_show_my_profile", false) ) $items .= '<li><a href="'. $userurl .'">'.__("My profile","govintranet").'</a></li>';
        if ( get_option("options_show_login_logout", false) ) $items .= '<li><a href="'. wp_logout_url() .'">'.__("Logout","govintranet").'</a></li>';
    }
    elseif (!is_user_logged_in() && $args->theme_location == 'secondary') {
        if ( get_option("options_show_login_logout", false) ) $items .= '<li><a href="'. site_url('wp-login.php') .'">'.__("Login","govintranet").'</a></li>';
    }
    return $items;
}


/*
 * Change the comment reply link to use 'Reply to &lt;Author First Name>'
 */
function add_comment_author_to_reply_link($link, $args, $comment){
 
    $comment = get_comment( $comment );
 
    // If no comment author is blank, use 'Anonymous'
    if ( empty($comment->comment_author) ) {
        if (!empty($comment->user_id)){
            $user=get_userdata($comment->user_id);
            $author=$user->user_login;
        } else {
            $author = __('Anonymous','govintranet');
        }
    } else {
        $author = $comment->comment_author;
    }
 
    // If the user provided more than a first name, use only first name
    if(strpos($author, ' ')){
        $author = substr($author, 0, strpos($author, ' '));
    }
 
    // Replace Reply Link with "Reply to &lt;Author First Name>"
    $reply_link_text = $args['reply_text'];
    $replyto = sprintf( __('Reply to %s', 'govintranet'), $author );
    $link = str_replace($reply_link_text, $replyto , $link);
 
    return $link;
}
add_filter('comment_reply_link', 'add_comment_author_to_reply_link', 10, 3);

function ht_add_comment_form_top($comment){
	$custom_comment_text = "";
	if ( is_user_logged_in() ):
		$custom_comment_text = get_option("options_comment_instructions_logged_in", "");	
		echo wpautop($custom_comment_text);
	else:
		$custom_comment_text = get_option("options_comment_instructions_logged_out", "");	
	endif;
	return;
}
add_filter('comment_form_top', 'ht_add_comment_form_top', 10, 3);


/*****************************
	
ADD FIRST NAME AND LAST 
NAME IN REGISTRATION FORM
	
*****************************/
//1. Add a new form element...
add_action( 'register_form', 'myplugin_register_form', 1 );
	
	function myplugin_register_form() {

	    $first_name = ( ! empty( $_POST['first_name'] ) ) ? trim( $_POST['first_name'] ) : '';
	    $last_name = ( ! empty( $_POST['last_name'] ) ) ? trim( $_POST['last_name'] ) : '';
        
        ?>
        <p>
            <label for="first_name"><?php _e( 'First Name', 'govintranet' ) ?><br />
                <input type="text" name="first_name" id="first_name" class="input" value="<?php echo esc_attr( wp_unslash( $first_name ) ); ?>" size="25" /></label>
        </p>
        <p>
            <label for="last_name"><?php _e( 'Last Name', 'govintranet' ) ?><br />
                <input type="text" name="last_name" id="last_name" class="input" value="<?php echo esc_attr( wp_unslash( $last_name ) ); ?>" size="25" /></label>
        </p>
        <?php
	}

    //2. Add validation. In this case, we make sure first_name is required.
    add_filter( 'registration_errors', 'myplugin_registration_errors', 10, 3 );
    function myplugin_registration_errors( $errors, $sanitized_user_login, $user_email ) {
        
        if ( empty( $_POST['first_name'] ) || ! empty( $_POST['first_name'] ) && trim( $_POST['first_name'] ) == '' ) {
            $errors->add( 'first_name_error', __( '<strong>ERROR</strong>: You must include a first name.', 'govintranet' ) );
        }

        if ( empty( $_POST['last_name'] ) || ! empty( $_POST['last_name'] ) && trim( $_POST['last_name'] ) == '' ) {
            $errors->add( 'last_name_error', __( '<strong>ERROR</strong>: You must include a last name.', 'govintranet' ) );
        }

        return $errors;
    }

    //3. Finally, save our extra registration user meta.
    add_action( 'user_register', 'myplugin_user_register' );
    function myplugin_user_register( $user_id ) {
        if ( ! empty( $_POST['first_name'] ) ) {
            update_user_meta( $user_id, 'first_name', trim( $_POST['first_name'] ) );
        }
        if ( ! empty( $_POST['last_name'] ) ) {
            update_user_meta( $user_id, 'last_name', trim( $_POST['last_name'] ) );
        }
        if ( ! empty( $_POST['first_name'] ) && ! empty( $_POST['last_name'] )) {
			$displayname = trim( $_POST['first_name'] ) . " " . trim( $_POST['last_name'] ) ;
			$user_id = wp_update_user( array( 'ID' => $user_id, 'display_name' => $displayname ) );
	    }
    }
    
function ht_update_post_term_count( $terms, $taxonomy ) {
    global $wpdb;
 
    $object_types = (array) $taxonomy->object_type;
 
    foreach ( $object_types as &$object_type )
        list( $object_type ) = explode( ':', $object_type );
 
    $object_types = array_unique( $object_types );
 
    if ( $object_types )
        $object_types = esc_sql( array_filter( $object_types, 'post_type_exists' ) );
 
    foreach ( (array) $terms as $term ) {
        $count = 0;
 
        if ( $object_types )
            $count += (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->term_relationships, $wpdb->posts WHERE $wpdb->posts.ID = $wpdb->term_relationships.object_id AND post_status = 'publish' AND post_type IN ('" . implode("', '", $object_types ) . "') AND term_taxonomy_id = %d", $term ) );
 
        /** This action is documented in wp-includes/taxonomy.php */
        do_action( 'edit_term_taxonomy', $term, $taxonomy->name );
        $wpdb->update( $wpdb->term_taxonomy, compact( 'count' ), array( 'term_taxonomy_id' => $term ) );
 
        /** This action is documented in wp-includes/taxonomy.php */
        do_action( 'edited_term_taxonomy', $term, $taxonomy->name );
    }
}    

/**
* filter function to force wordpress to add our custom srcset values
* @param array  $sources {
*     One or more arrays of source data to include in the 'srcset'.
*
*     @type type array $width {
*          @type type string $url        The URL of an image source.
*          @type type string $descriptor The descriptor type used in the image candidate string,
*                                        either 'w' or 'x'.
*          @type type int    $value      The source width, if paired with a 'w' descriptor or a
*                                        pixel density value if paired with an 'x' descriptor.
*     }
* }
* @param array  $size_array    Array of width and height values in pixels (in that order).
* @param string $image_src     The 'src' of the image.
* @param array  $image_meta    The image meta data as returned by 'wp_get_attachment_metadata()'.
* @param int    $attachment_id Image attachment ID.

* @author: Aakash Dodiya
* @website: http://www.developersq.com
*/
add_filter( 'wp_calculate_image_srcset', 'dq_add_custom_image_srcset', 10, 5 );
function dq_add_custom_image_srcset( $sources, $size_array, $image_src, $image_meta, $attachment_id ){
			
	$image_basename = wp_basename( $image_meta['file'] );
	$image_baseurl = _wp_upload_dir_baseurl();	
	// Uploads are (or have been) in year/month sub-directories.
	if ( $image_basename !== $image_meta['file'] ) {
		$dirname = dirname( $image_meta['file'] );

		if ( $dirname !== '.' ) {
			$image_baseurl = trailingslashit( $image_baseurl ) . $dirname;
		}
	}
        // get image baseurl 
	$image_baseurl = trailingslashit( $image_baseurl );
	// check whether our custom image size exists in image meta	
	if( array_key_exists('large', $image_meta['sizes'] ) ){
		// add source value to create srcset
		$sources[ $image_meta['sizes']['large']['width'] ] = array(
				 'url'        => $image_baseurl .  $image_meta['sizes']['newshead']['file'],
				 'descriptor' => 'w',
				 'value'      => $image_meta['sizes']['newshead']['width'],
		);
	}
	
        //return sources with new srcset value
	return $sources;
}

function govintranet_custom_styles() {
		$custom_css = "";
		
		// write custom css for background header colour

		$bg = get_theme_mod('link_color', '#428bca');
		$custom_css.= "
		a, a .listglyph  {
		color: ".$bg.";
		}
		";

		$bg = get_theme_mod('link_visited_color', '#7303aa');
		$custom_css.= "
		a:visited, a:visited .listglyph {
		color: ".$bg.";
		}
		";
		$gisheight = get_option('options_widget_border_height');
		if (!$gisheight) $gisheight = 7;
		$gis = "options_header_background";
		$gishex = get_theme_mod('header_background', '#0b2d49'); if ( substr($gishex, 0 , 1 ) != "#") $gishex="#".$gishex;
		if ( $gishex == "#") $gishex = "#0b2d49";
		$custom_css.= "
		.custom-background  {
		background-color: ".$gishex.";
		}
		";
		$headtext = get_theme_mod('header_textcolor', '#ffffff'); if ( substr($headtext, 0 , 1 ) != "#") $headtext="#".$headtext;
		if ( $headtext == "#") $headtext = "#ffffff";
		$headimage = get_theme_mod('header_image', '');
		$basecol=HTMLToRGB(substr($gishex,1,6));
		$topborder = ChangeLuminosity($basecol, 33);

		// set bar colour
		// if using automatic complementary colour then convert header color
		// otherwise use specified colour

		$giscc = get_option('options_enable_automatic_complementary_colour'); 
		if ($giscc):
			$giscc = RGBToHTML($topborder); 
		elseif (get_option('options_complementary_colour')):
			$giscc = get_option('options_complementary_colour');
		else:
			 $giscc = $gishex; 
		endif;
		
		if ($headimage != 'remove-header' ):
			$custom_css.= "
			#topstrip  {
			background: ".$gishex." url(".get_header_image().");
			color: ".$headtext.";
			}
			";
		else:
			$custom_css.= "
			#topstrip  {
			background: ".$gishex.";
			color: ".$headtext.";
			}
			";
		endif;

		$custom_css.= "
		@media only screen and (max-width: 767px)  {
			#masthead  {
			background: ".$gishex." !important;
			color: ".$headtext.";
			padding: 0 1em;
			}
			#primarynav ul li a {
			background: ".$gishex.";
			color: ".$headtext.";
			}	
			#primarynav ul li a:hover {
			color: ".$gishex." !important;
			background: ".$headtext.";
			}	
		}
		";

		$custom_css.= "
		.btn-primary, .btn-primary a  {
		background: ".$giscc.";
		border: 1px solid ".$giscc.";
		color: ".$headtext.";
		}
		";

		$custom_css.= "
		.btn-primary a:hover  {
		background: ".$gishex.";
		}
		";

		$custom_css.= "
		#topstrip a {
		color: ".$headtext.";
		}
		";

		$custom_css.= "
		#utilitybar ul#menu-utilities li a, #menu-utilities {
		color: ".$headtext.";
		}
		";

		$custom_css.= "
		#footerwrapper  {";
		$custom_css.= "border-top: ".$gisheight."px solid ".$giscc.";";
		$custom_css.= "}";

		$custom_css.= "
		.page-template-page-about-php .category-block h2 {";
		$custom_css.= "border-top: ".$gisheight."px solid ".$giscc.";";

		$custom_css.= "padding: 0.6em 0;
		}
		";

		$custom_css.= "
		.home.page .category-block h3 {
			border-bottom: 3px solid ".$gishex.";
		}
		.h3border {
		border-bottom: 3px solid ".$gishex.";
		}
		";
		
		$custom_css.= "
		#content .widget-box {
		padding: .1em 0 .7em 0;
		font-size: .9em;
		background: #fff;";
		$custom_css.= "border-top: ".$gisheight."px solid ".$giscc.";";
		$custom_css.= "margin-top: .7em;
		}
		";

		$custom_css.= "
		.home.page .category-block h3 {";
		$custom_css.= "border-top: ".$gisheight."px solid ".$giscc.";";
		$custom_css.= "border-bottom: none;
		padding-top: 16px;
		margin-top: 16px;
		}
		";

		$directorystyle = get_option('options_staff_directory_style'); // 0 = squares, 1 = circles
		if ( $directorystyle ):
			$custom_css.= "
			.bbp-user-page.single #bbp-user-avatar img.avatar   {
				border-radius: 50%;
			}
			";
		endif;
		
		$custom_css.= "
		.bbp-user-page .panel-heading {";
		$custom_css.= "border-top: ".$gisheight."px solid ".$giscc.";";
		$custom_css.= "
		}
		";
		
		$custom_css.= "
		.page-template-page-news-php h1 {
		border-bottom: ".$gisheight."px solid ".$giscc.";
		} 
		.tax-team h2 {
		border-bottom: ".$gisheight."px solid ".$giscc.";
		} 
		";

		//write custom css for logo
		$gisid = get_option('options_header_logo'); 
		$gislogow = wp_get_attachment_image_src( $gisid ); 
		$gislogo = $gislogow[0] ;
		$gisw = $gislogow[1] + 10;
		$custom_css.= "
		#crownlink  {
		background: url('".$gislogo."') no-repeat;	 
		background-position:left 10px;
		padding: 16px 0 0 ".$gisw."px;
		height: auto;
		min-height: 50px;
		margin-bottom: 0.6em;
		}
		";
		$custom_css.= "
		#primarynav ul li  {
		border-bottom: 1px solid ".$gishex.";
		border-top: 1px solid ".$gishex.";
		border-right: 1px solid ".$gishex.";
		}
		#primarynav ul li:last-child,  #primarynav ul li.last-link  {
		border-right: 1px solid ".$gishex.";
		}

		#primarynav ul li:first-child,  #primarynav ul li.first-link  {
		border-left: 1px solid ".$gishex.";
		}

		#searchformdiv button:hover { background: ".$gishex."; color: ".$headtext."; }
		";		


		$custom_css.= "a.wptag {color: ".$headtext."; background: ".$gishex.";} \n";
		$custom_css.= "a.:visited.wptag {color: ".$headtext."; background: ".$gishex.";} \n";



		if ($headimage != 'remove-header' && $headimage) $custom_css.= '#utilitybar ul#menu-utilities li a, #menu-utilities, #crownlink { text-shadow: 1px 1px #333; }'; 
		
		$terms = get_terms('category',array('hide_empty'=>false));
		if ($terms) {
	  		foreach ((array)$terms as $taxonomy ) {
	  		    $themeid = $taxonomy->term_id;
	  		    $themeURL= $taxonomy->slug;
	  			$background=get_option('category_'.$themeid.'_cat_background_colour');
	  			$foreground=get_option('category_'.$themeid.'_cat_foreground_colour');
	  			$custom_css.= "button.btn.t" . $themeid . ", a.btn.t" . $themeid . " {color: " . $foreground . "; background: " . $background . "; border: 1px solid ".$background.";} \n";
	  			$custom_css.= ".cattagbutton a.btn.t" . $themeid . ", a.btn.t" . $themeid . " {color: " . $foreground . "; background: " . $background . "; border-bottom: 3px solid #000; border-radius: 3px; } \n";
	  			$custom_css.= ".cattagbutton a:hover.btn.t" . $themeid . ", a.btn.t" . $themeid . " {color: " . $foreground . "; background: " . $background . "; border-bottom: 3px solid #000; border-radius: 3px; } \n";
	  			$custom_css.= ".category-block .t" . $themeid . ", .category-block .t" . $themeid . " a  {color: " . $foreground . "; background: " . $background . "; border: 1px solid ".$background."; width: 100%; padding: 0.5em; } \n";
	  			$custom_css.= "button:hover.btn.t" . $themeid . ", a:hover.btn.t" . $themeid . "{color: white; background: #333; border: 1px solid ".$background.";} \n";
	  			$custom_css.= "a.t" . $themeid . "{color: " . $foreground . "; background: " . $background . ";} \n";
	  			$custom_css.= "a.t" . $themeid . " a {color: " . $foreground . " !important;} \n";
	  			$custom_css.= ".brd" . $themeid . "{border-left: 1.2em solid " . $background . ";} \n";
	  			$custom_css.= ".hr" . $themeid . "{border-bottom: 1px solid " . $background . ";} \n";
	  			$custom_css.= ".h1_" . $themeid . "{border-bottom: ".$gisheight."px solid " . $background . "; margin-bottom: 0.4em; padding-bottom: 0.3em;} \n";
	  			$custom_css.= ".b" . $themeid . "{border-left: 20px solid " . $background . ";} \n";
	  			$custom_css.= ".dashicons.dashicons-category.gb" . $themeid . "{color: " . $background . ";} \n";
	  			$custom_css.= "a:visited.wptag.t". $themeid . "{color: " . $foreground . ";} \n";
			}
		}  
		$giscss = get_option('options_custom_css_code');
		$custom_css.= $giscss;
		$jumbo_searchbox = get_option("options_search_jumbo_searchbox", false);		
		
		if ( $jumbo_searchbox ) $custom_css.= "		
		#headsearch { padding-right: 0; }
		#searchformdiv.altsearch { padding: 1.75em 6em 1.75em 6em; background: " . $giscc . "; }
		#searchformdiv.altsearch button.btn.btn-primary { background: " . $gishex . "; color: white; }
		#searchformdiv.altsearch button.btn.btn-primary:hover { background-color: #eee; color: black; }
		";
		
		if ( get_option("options_staff_directory_style") && get_option("options_forum_support") ):
			$custom_css.= "#bbpress-forums img.avatar { border-radius: 50%; }";
		endif;

		$styleurl = get_stylesheet_directory_uri() . '/css/custom.css';
		wp_enqueue_style( 'govintranet_custom_styles', $styleurl );
		wp_add_inline_style('govintranet_custom_styles' , $custom_css);	
	}
	add_action( 'wp_enqueue_scripts', 'govintranet_custom_styles' );
		
		