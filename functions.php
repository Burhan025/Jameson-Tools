<?php
//* Start the engine
include_once( get_template_directory() . '/lib/init.php' );

//* Setup Theme
include_once( get_stylesheet_directory() . '/lib/theme-defaults.php' );

//* Subpage Header Code
require_once('subpage-header.php');

//* Set Localization (do not remove)
load_child_theme_textdomain( 'parallax', apply_filters( 'child_theme_textdomain', get_stylesheet_directory() . '/languages', 'parallax' ) );

//* Add Image upload to WordPress Theme Customizer
add_action( 'customize_register', 'parallax_customizer' );
function parallax_customizer(){
	require_once( get_stylesheet_directory() . '/lib/customize.php' );
}

//* Include Section Image CSS
include_once( get_stylesheet_directory() . '/lib/output.php' );

global $blogurl;
$blogurl = get_stylesheet_directory_uri();

//* Enqueue scripts and styles
add_action( 'wp_enqueue_scripts', 'parallax_enqueue_scripts_styles' );
function parallax_enqueue_scripts_styles() {
	// Styles
	wp_enqueue_style( 'dashicons' );
	wp_enqueue_style( 'custom', get_stylesheet_directory_uri() . '/css/allstyles.css', array() );


	wp_enqueue_style( 'new-custom-fonts', get_stylesheet_directory_uri() . '/fonts/new-custom-fonts/new-fonts.css', array() );

	// Scripts
	//wp_enqueue_script( 'responsive-menu-js', get_stylesheet_directory_uri() . '/js/responsive-menu/responsive-menu.js', array( 'jquery' ), '1.0.0' );
	
}

// Removes Query Strings from scripts and styles
function remove_script_version( $src ){
  if ( strpos( $src, 'uploads/bb-plugin' ) !== false || strpos( $src, 'uploads/bb-theme' ) !== false ) {
    return $src;
  }
  else {
    $parts = explode( '?ver', $src );
    return $parts[0];
  }
}
add_filter( 'script_loader_src', 'remove_script_version', 15, 1 );
add_filter( 'style_loader_src', 'remove_script_version', 15, 1 );


//* Add HTML5 markup structure
add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption' ) );

//* Add viewport meta tag for mobile browsers
add_theme_support( 'genesis-responsive-viewport' );

//* Reposition the primary navigation menu
//remove_action( 'genesis_after_header', 'genesis_do_nav' );
//add_action( 'genesis_header', 'genesis_do_nav', 12 );

// Add Search to Primary Nav
//add_filter( 'genesis_header', 'genesis_search_primary_nav_menu', 10 );
function genesis_search_primary_nav_menu( $menu ){
    locate_template( array( 'searchform-header.php' ), true );
}

//* Add support for structural wraps
add_theme_support( 'genesis-structural-wraps', array(
	'header',
	'nav',
	'subnav',
	'breadcrumb',
	'footer-widgets',
	'footer',
) );

// Add Read More Link to Excerpts
add_filter('excerpt_more', 'get_read_more_link');
add_filter( 'the_content_more_link', 'get_read_more_link' );
function get_read_more_link() {
   return '...&nbsp;<a class="readmore" href="' . get_permalink() . '">Read&nbsp;More &raquo;</a>';
}

// Add Beaver Builder Editable Footers to the Genesis Footer hook
add_action( 'genesis_before_footer', 'global_footer', 4 );
function global_footer(){
	echo do_shortcode('[fl_builder_insert_layout slug="global-footer"]');
}

//* Add support for 4-column footer widgets
add_theme_support( 'genesis-footer-widgets', 4 );

//* Customize the entry meta in the entry header (requires HTML5 theme support)
add_filter( 'genesis_post_info', 'sp_post_info_filter' );
function sp_post_info_filter($post_info) {
	$post_info = '[post_date] [post_comments] [post_edit]';
	return $post_info;
}

//* Custom Breadcrumb Hook 
function breadcrumb_hook() {
	do_action('breadcrumb_hook');
}

//* Remove breadcrumbs and reposition them
remove_action( 'genesis_before_loop', 'genesis_do_breadcrumbs' );
add_action( 'breadcrumb_hook', 'genesis_do_breadcrumbs', 12 );

// Modify Breadcrumbs Args
add_filter( 'genesis_breadcrumb_args', 'malcolm_breadcrumb_args' );
function malcolm_breadcrumb_args( $args ) {
	$args['prefix'] = '<div class="breadcrumbs"><div class="wrap">';
	$args['suffix'] = '</div></div>';
	$args['sep'] = ' <span class="bread-sep">/</span> ';
	$args['heirarchial_attachments'] = true;
	$args['heirarchial_categories'] = true;
	$args['display'] = true;
	$args['labels']['prefix'] = '';
    return $args;
}

// Widget - Latest News on home page
genesis_register_sidebar( array(
	'id'			=> 'home-latest-news',
	'name'			=> __( 'Latest News on Home Page', 'thrive' ),
	'description'	=> __( 'This is latest news home page widget', 'thrive' ),
) );

// Blog Widgets
genesis_register_sidebar( array(
	'id'			=> 'blog-sidebar',
	'name'			=> __( 'Blog Widgets', 'thrive' ),
	'description'	=> __( 'This is latest news widget', 'thrive' ),
) );

// Add Header Links Widget to Header
//add_action( 'genesis_before', 'header_widget', 1 );
	function header_widget() {
	if (is_active_sidebar( 'header-links' ) ) {
 	genesis_widget_area( 'header-links', array(
		'before' => '<div class="header-links">',
		'after'  => '</div>',
	) );
}}

//Unregister unused sidebar
//unregister_sidebar( 'header-right' );

// Previous / Next Post Navigation Filter For Genesis Pagination
add_filter( 'genesis_prev_link_text', 'gt_review_prev_link_text' );
function gt_review_prev_link_text() {
        $prevlink = '&laquo;';
        return $prevlink;
}
add_filter( 'genesis_next_link_text', 'gt_review_next_link_text' );
function gt_review_next_link_text() {
        $nextlink = '&raquo;';
        return $nextlink;
}

/* Subpage Header Backgrounds - Utilizes: Featured Images & Advanced Custom Fields Repeater Fields */

// AFC Repeater Setup - NOTE: Set Image Return Value to ID
// Row Field Name:
$rows = '';
$rows = get_field('subpage_header_backgrounds', 5);
// Counts the rows and selects a random row
$row_count = count($rows);
$i = rand(0, $row_count - 1);
// Set Image size to be returned
$image_size = 'subpage-header';
// Get Image ID from the random row
$image_id = $rows[ $i ]['background_image'];
// Use Image ID to get Image Array
$image_array = wp_get_attachment_image_src($image_id, $image_size);
// Set "Default BG" to first value of the Image Array. $image_array[0] = URL;
$default_bg = $image_array[0]; 


// Custom function for getting background images
function custom_background_image($postID = "") {
	// Variables
	global $default_bg;
	global $postID;
	global $blog_slug;
	
	$currentID = get_the_ID();
	$blogID = get_option( 'page_for_posts');
	$parentID = wp_get_post_parent_id( $currentID );

	// is_home detects if you're on the blog page- must be set in admin area
	if( is_home() ) {
		$currentID = $blogID;
	} 
	// Else if post page, set ID to BlogID.
	elseif( is_home() || is_single() || is_archive() || is_search() ) {
		$currentID = $blogID;
	}

	// Try to get custom background based on current page/post
	$currentBackground = wp_get_attachment_image_src(get_post_thumbnail_id($currentID), 'subpage-header');
	//Current page/post has no custom background loaded
	if(!$currentBackground) {
		// Find blog ID
		$blog_page = get_page_by_path($blog_slug, OBJECT, 'page');
		if ($blog_page) {
			$blogID = $blogID;
			$currentID = $blogID;
		}
		// Else if post page, set ID to BlogID.
		elseif(is_single() || is_archive()) {
			$currentID = $blogID; 
		}

		// Current page has a parent
		if($parentID) {
			// Try to get parents custom background
			$parent_background = wp_get_attachment_image_src(get_post_thumbnail_id($parentID), 'subpage-header');
			// Set parent background if it exists
			if($parent_background) {
				$background_image = $parent_background[0];
			}
			// Set default background
			else {
				$background_image = $default_bg;
			}
		}
		// NO parent or no parent background: set default bg.
		else {
			$background_image = $default_bg;
		}
	}
	// Current Page has a custom background: use that
	else {
		$background_image = $currentBackground[0];
	}
	return $background_image;
}

/* Changing the Copyright text */
function genesischild_footer_creds_text () {
	global $blogurl;
 	echo '<div class="clearboth copy-line">
 			<div class="copyright first">
 				<p><span id="copy">Copyright &copy; '. date("Y") .' - All rights reserved</span> <span class="format-pipe">&#124;</span>  
	 			<a href="/sitemap/">Site Map</a>  <span>&#124;</span>  
	 			<a href="/privacy-policy/">Privacy Policy</a>  
	 			</p>
 			</div>
 			<div class="credits">
 				<span>Site by</span>
 				<a target="_blank" href="https://thriveagency.com/">
 					<img class="svg" src="'.  $blogurl . '/images/thrive-logo.png" alt="Web Design by Thrive Internet Marketing">
 				</a>
 			</div>
 		  </div>';
}
add_filter( 'genesis_footer_creds_text', 'genesischild_footer_creds_text' );


//* Reposition the primary navigation menu
remove_action( 'genesis_after_header', 'genesis_do_nav' );
add_action( 'genesis_after_header', 'genesis_do_nav', 12 );

// Add Additional Image Sizes
add_image_size( 'genesis-post-thumbnail', 163, 108, true );
add_image_size( 'subpage-header', 1600, 162, true );
add_image_size( 'news-thumb', 260, 150, false );
add_image_size( 'news-full', 800, 300, false );
add_image_size( 'sidebar-thumb', 200, 150, false );
add_image_size( 'mailchimp', 564, 9999, false );
add_image_size( 'amp', 600, 9999, false  );


// Gravity Forms confirmation anchor on all forms
add_filter( 'gform_confirmation_anchor', '__return_true' );


// Button Shortcode
// Usage: [button url="https://www.google.com"] Button Shortcode [/button]
function button_shortcode($atts, $content = null) {
  extract( shortcode_atts( array(
	  'url' => '#',
	  'target' => '_self',
	  'onclick' => '',

  ), $atts ) 
);
return '<a target="' . $target . '" href="' . $url . '" class="button" onClick="' . $onclick . '"><span>' . do_shortcode($content) . '</span></a>';
}
add_shortcode('button', 'button_shortcode');

// Link Shortcode
// Usage: [link url=”tel:1-817-447-9194″ onClick=”onClick=”ga(‘send’, ‘event’, { eventCategory: ‘Click to Call’, eventAction: ‘Clicked Phone Number’, eventLabel: ‘Header Number’});”]
function link_shortcode($atts, $content = null) {
  extract( shortcode_atts( array(
	  'url' => '#',
	  'target' => '_self',
	  'onclick' => '',
  ), $atts ) 
);
return '<a target="' . $target . '" href="' . $url . '" onClick="' . $onclick . '">' . do_shortcode($content) . '</a>';
}
add_shortcode('link', 'link_shortcode');

//* Declare WooCommerce support
add_action( 'after_setup_theme', 'woocommerce_support' );
function woocommerce_support() {
    add_theme_support( 'woocommerce' );
}

// Advance Custom field for Scheme Markups will be output under wphead tag
add_action('wp_head', 'add_scripts_to_wphead');
function add_scripts_to_wphead() {
	if( get_field('custom_javascript') ):	
		echo get_field('custom_javascript', 5);
	endif;
}

// Run shortcodes in Text Widgets
add_filter('widget_text', 'do_shortcode');


//Removing unused Default Wordpress Emoji Script - Performance Enhancer
function disable_emoji_dequeue_script() {
    wp_dequeue_script( 'emoji' );
}
add_action( 'wp_print_scripts', 'disable_emoji_dequeue_script', 100 );
remove_action( 'wp_head', 'print_emoji_detection_script', 7 ); 
remove_action( 'wp_print_styles', 'print_emoji_styles' );

// Removes Emoji Scripts 
add_action('init', 'remheadlink');
function remheadlink() {
	remove_action('wp_head', 'rsd_link');
	remove_action('wp_head', 'wp_generator');
	remove_action('wp_head', 'index_rel_link');
	remove_action('wp_head', 'wlwmanifest_link');
	remove_action('wp_head', 'feed_links', 2);
	remove_action('wp_head', 'feed_links_extra', 3);
	remove_action('wp_head', 'parent_post_rel_link', 10, 0);
	remove_action('wp_head', 'start_post_rel_link', 10, 0);
	remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0);
	remove_action('wp_head', 'wp_shortlink_header', 10, 0);
	remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);
}

// Add "nav-primary" class to Main Menu as this gets removed when we reposition the menu inside header/widget area
add_filter( 'genesis_attr_nav-header', 'thrive_custom_nav_id' );
function thrive_custom_nav_id( $attributes ) {
 	$attributes['class'] = 'nav-primary';
 	return $attributes;
}

//****** AMP Customizations ******/

//* Enqueue "stylesheet" for AMP */
add_action('amp_init','amp_css', 11);
function amp_css() { 
	require_once('css/amp.php');
}

//* Add Featured Images to AMP content
add_action( 'pre_amp_render_post', 'amp_add_custom_actions' );
function amp_add_custom_actions() {
    add_filter( 'the_content', 'amp_add_featured_image' );
}

function amp_add_featured_image( $content ) {
    if ( has_post_thumbnail() ) {
        // Just add the raw <img /> tag; our sanitizer will take care of it later.
        $image = sprintf( '<p class="featured-image">%s</p>', get_the_post_thumbnail(get_the_ID(), 'amp') );
        $content = $image . $content;
    }
    return $content;
}

// Add Fav Icon to AMP Pages
add_action('amp_post_template_head','amp_favicon');
function amp_favicon() { ?>
	<link rel="icon" href="<?php echo get_site_icon_url(); ?>" />
<?php } 

// Add Banner below content of AMP Pages
add_action('ampforwp_after_post_content','amp_custom_banner_extension_insert_banner');
function amp_custom_banner_extension_insert_banner() { ?>
	<div class="amp-custom-banner-after-post">
		<h2>COMMITTED TO OUR CUSTOMERS</h2>
		<a class="ampforwp-comment-button" href="/contact/">
			CONTACT US
		</a>
	</div>
<?php } 

//Sets the number of revisions for all post types
add_filter( 'wp_revisions_to_keep', 'revisions_count', 10, 2 );
function revisions_count( $num, $post ) {
	$num = 3;
    return $num;
}

// Enable Featured Images in RSS Feed and apply Custom image size so it doesn't generate large images in emails
function featuredtoRSS($content) {
global $post;
if ( has_post_thumbnail( $post->ID ) ){
$content = '<div>' . get_the_post_thumbnail( $post->ID, 'mailchimp', array( 'style' => 'margin-bottom: 15px;' ) ) . '</div>' . $content;
}
return $content;
}
 
add_filter('the_excerpt_rss', 'featuredtoRSS');
add_filter('the_content_feed', 'featuredtoRSS');

add_filter( 'genesis_pre_get_sitemap', 'thrive_genesis_pre_get_sitemap', 10 );
/**
 * Modifies the sitemap html to include a limit to the amount of pages, categories, authors, etc, that will be displayed.
 * @return string sitemap html
 */
function thrive_genesis_pre_get_sitemap() {

	$heading = 'h2';

	$sitemap  = sprintf( '<%2$s>%1$s</%2$s>', __( 'Pages:', 'genesis' ), $heading );
	$sitemap .= sprintf( '<ul>%s</ul>', wp_list_pages( array(
		'title_li' => null,
		'echo' => false,
		'depth' => 1,
		'sort_column' => 'post_title',
	)));

	$sitemap .= sprintf( '<%2$s>%1$s</%2$s>', __( 'Categories:', 'genesis' ), $heading );
	$sitemap .= sprintf( '<ul>%s</ul>', wp_list_categories( array(
		'sort_column' => 'name',
		'title_li' => null,
		'echo' => false,
		'depth' => 1,
	)));

	$users = get_users( array(
		'number' => 10,
		'who' => 'authors',
		'has_published_posts' => true,
	));

	ob_start();
	foreach ( $users as $user ) {
		$author_url = get_author_posts_url( $user->ID );
		?>
		<li>
			<a href="<?php echo esc_url( $author_url ); ?>"><?php echo esc_html( $user->display_name ); ?></a>
		</li>
		<?php
	}
	$user_li_html = ob_get_clean();

	$sitemap .= sprintf( '<%2$s>%1$s</%2$s>', __( 'Authors:', 'genesis' ), $heading );
	$sitemap .= sprintf( '<ul>%s</ul>', $user_li_html );

	$sitemap .= sprintf( '<%2$s>%1$s</%2$s>', __( 'Monthly:', 'genesis' ), $heading );
	$sitemap .= sprintf( '<ul>%s</ul>', wp_get_archives( array(
		'type' => 'monthly',
		'echo' => false,
		'limit' => 12,
	)));

	$sitemap .= sprintf( '<%2$s>%1$s</%2$s>', __( 'Recent Posts:', 'genesis' ), $heading );
	$sitemap .= sprintf( '<ul>%s</ul>', wp_get_archives( array(
		'type' => 'postbypost',
		'limit' => 10,
		'echo' => false,
	)));


	return $sitemap;
}

/****************************Start woocommerce hooks*********************************/


// Display Product Title and Short description for All products in Home and Products page
// remove the Title from category product 
remove_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10, 2 );

add_action('woocommerce_after_shop_loop_item', 'woocommerce_subtitle_add');    
function woocommerce_subtitle_add() {
	global $product;
	$pid = $product->get_id();
	$link = $product->get_permalink();
	$ptitle = $product->get_title($pid);
	
	// Display Model No
    echo '<h2 class="woocommerce-loop-product__title">';
		the_field('product_model_no');
	echo '</h2>';
	

	// Display Short description After title
	echo '<p class="woocom_desc"><a href="' . $link . '">';
		woocommerce_get_template( 'single-product/tabs/description.php' );
	echo '</a></p>';
	
	// Display Price if products are in SHOP category
	if ( has_term( array( 'store' ), 'product_cat', $product->get_id() ) ) {
	
	$currency = get_woocommerce_currency_symbol();
	$price = get_post_meta( get_the_ID(), '_regular_price', true);
	$sale = get_post_meta( get_the_ID(), '_sale_price', true);
 
	if($sale) : ?>
		<p class="product-price-tickr"><del><?php echo $currency; echo $price; ?></del> <?php echo $currency; echo $sale; ?></p>    
	<?php elseif($price) : ?>
		<p class="product-price-tickr"><?php echo $currency; echo $price; ?></p>    
	<?php endif;
	
	}

}

remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
add_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );

// Display category with description and View Products button on category/Home page
add_action( 'woocommerce_after_subcategory_title', 'custom_add_product_description', 12);
function custom_add_product_description ($category) {
	$cat_id       =    $category->term_id;
	$prod_term    =    get_term($cat_id,'product_cat');
	$description  =    $prod_term->description;

	echo '<div class="cat_desc">'.$description.'</div>';
?>
	<a class="cat_link" href="<?php echo get_category_link($category); ?>">VIEW PRODUCTS</a>
<?php
}


/************************** Start Product Details Page functionality **************************/

// Remove price,add to cart options
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15 );


// Remove product title from single page
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5 );

// Display Product Model No in palace of title on single page
function implement_product_logo_above_title() {
	
        if( get_field('product_model_no') ): ?>
                <h1 class="product_title entry-title"><?php the_field('product_model_no'); ?></h1>
        <?php endif;
		
		if ( is_product() && has_term( 'store', 'product_cat' ) ) {
			add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
			add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
		}
}
add_action( 'woocommerce_single_product_summary', 'implement_product_logo_above_title', 1 );


// remove description and review tabs
add_filter( 'woocommerce_product_tabs', 'woo_remove_product_tabs', 98 );
function woo_remove_product_tabs( $tabs ) {
	unset( $tabs['description'] );        			// Remove the description tab
  	unset( $tabs['reviews'] );            			// Remove the reviews tab
  	unset( $tabs['additional_information'] );     // Remove the additional information tab

  	return $tabs;
}

// add description after title
	function woocommerce_template_product_description() {
			
		echo '<p class="single_page_des"><b>';
			woocommerce_get_template( 'single-product/tabs/description.php' );
		echo '</b></p>';
		
		echo '<div class="descrption_new">';
				the_field('short_description');
		echo'</div>';
			
	}
	add_action( 'woocommerce_single_product_summary', 'woocommerce_template_product_description' );

// After description add featured content and sale support and find a distributor bottons
add_action( 'woocommerce_single_product_summary', 'add_data_after_description' );
function add_data_after_description(){

	 if ( is_product() && !has_term( 'store', 'product_cat' ) ) {
?>	
      <div class="sale_find_action">
          <div class="sale_support">
              <a target ="blank" href="/contact#contact_custom_map" >SALES SUPPORT</a>
          </div>
          <div class="sale_support">
              <a target ="blank" href="/contact#Contact-form-section">CONTACT US</a>
          </div>
      </div>	
<?php
	 }
}

/*****************Product Tab Functionality**************/

/****** Start product TAB functionality for specifications*******/
	add_filter( 'woocommerce_product_tabs', 'woo_new_specification_product_tab' );
	function woo_new_specification_product_tab( $tabspec ) {
		// check if the repeater field has rows of data
			if( get_field('new_specification_editor') ){
			
				$tabspec['specific'] = array(
					'title'     => __( 'SPECIFICATIONS', 'woocommerce' ),
					'priority'  => 30,
					'callback'  => 'woo_new_product_specification_tab_content'
				);
				
				return $tabspec;
			}else{
				return $tabspec;
			}
		//}
	}

	// Add Content to SPECIFICATIONS tab
	function woo_new_product_specification_tab_content() {
		echo'<div class="speci_cus" id="specification_tab">';
			
			if( get_field('new_specification_editor') ):
					//echo'<ul class="specification_content">';
						
			?>			
							<?php the_field('new_specification_editor');?>
			<?php		
						
					//echo'</ul>';	
				endif;		
			
		echo'</div>';	
	}
/****** End product TAB functionality for Specification*******/

/****** Start product TAB functionality for Videos *******/
add_filter( 'woocommerce_product_tabs', 'woo_new_videos_product_tab' );
function woo_new_videos_product_tab( $tabspec ) {
    // check if the repeater field has rows of data
    if( get_field('new_video_editor') ){

        $tabspec['specific'] = array(
            'title'     => __( 'VIDEOS', 'woocommerce' ),
            'priority'  => 60,
            'callback'  => 'woo_new_product_video_tab_content'
        );

        return $tabspec;
    }else{
        return $tabspec;
    }
    //}
}

// Add Content to Videos tab
function woo_new_product_video_tab_content() {
    echo'<div class="speci_cus" id="video_tab">';

    if( get_field('new_video_editor') ):
        ?>
        <?php the_field('new_video_editor');?>
    <?php

        //echo'</ul>';
    endif;

    echo'</div>';
}
/****** End product TAB functionality for Videos *******/


/****** Start product TAB functionality for Featured content*******/
	add_filter( 'woocommerce_product_tabs', 'woo_new_featured_product_tab' );
	function woo_new_featured_product_tab( $tabfeature ) {
		// check if the repeater field has rows of data
			if( get_field('new_features_editor') ){
			
				$tabfeature['feature'] = array(
					'title'     => __( 'FEATURES', 'woocommerce' ),
					'priority'  => 20,
					'callback'  => 'woo_new_product_featured_tab_content'
				);
				
				return $tabfeature;
			}else{
				return $tabfeature;
			}
		//}
	}
	


	// Add Content to featured tab
	function woo_new_product_featured_tab_content() {
		echo'<div class="specific_custom" id="featured_cont">';
			
			if( get_field('new_features_editor') ):
					//echo'<ul class="specification_content">';
						
			?>			
							<?php the_field('new_features_editor');?>
			<?php		
						
					//echo'</ul>';	
				endif;		
			
		echo'</div>';	
	}
/****** End product TAB functionality for Specification*******/


/****** Start Product TAB functionality for Documentation*******/
	add_filter( 'woocommerce_product_tabs', 'woo_new_document_product_tab' );
	function woo_new_document_product_tab( $tabdocu ) {
		// check if the repeater field has rows of data
		//if( have_rows('documentation') ){
			if( have_rows('documentation') ) { 	
				$tabdocu['document'] = array(
					'title'     => __( 'DOCUMENTATION', 'woocommerce' ),
					'priority'  => 40,
					'callback'  => 'woo_new_product_document_tab_content'
				);
				
				return $tabdocu;
			}else{
				return $tabdocu;
			}
		//}
	}

	// The documentation tab content
	function woo_new_product_document_tab_content() {
		
		echo'<ul class="document_custom">';
			// check if the repeater field has rows of data
			if( have_rows('documentation_listing') ):
				/*echo'<tr>
					<th>Title</th>
					<th>Type</th>
				</tr>';*/

				while ( have_rows('documentation_listing') ) : the_row();
?>					
						<!--td><?php //the_sub_field('documentation_title');?></td-->

						<li><a target="_blank" href="<?php the_sub_field('doc_type_pdf_link');?>"><?php the_sub_field('documentation_pdf_title');?></a></li>
						
<?php        	
				endwhile;
			endif;
			
		echo'</ul> ';	
	}
// End product TAB functionality for Documentation	


/****** Start Product TAB functionality for Product Variation*******/
	add_filter( 'woocommerce_product_tabs', 'woo_product_variation_tab' );
	function woo_product_variation_tab( $tabvariation ) {
		global $woocommerce, $product, $post;
			if (is_product() and $product->product_type == 'variable') {
				$tabvariation['variation'] = array(
					'title'     => __( 'ORDER GUIDE', 'woocommerce' ),
					'priority'  => 50,
					'callback'  => 'woo_new_product_variation_tab_content'
				);
				
				return $tabvariation;
			}else{
				return $tabvariation;
			}
		//}
	}

	// The documentation tab content
	function woo_new_product_variation_tab_content() {
		global $woocommerce, $product, $post, $re_wcvt_options;
		$available_variations = $product->get_available_variations();
		$attributes = $product->get_attributes();
		
		if (!empty($available_variations)) {
?>		
		
			<table class="custom_varations-table">
				<thead>
					<tr>
						<th><b>Product Number</b></th>
						<th><b>Description</b></th>
					</tr>
				</thead>	
				<tbody>
			   
				<?php foreach ($available_variations as $prod_variation) : ?>
					
					<tr>
					<td>
						<?php
							echo $prod_variation['sku'];
							//echo'<pre>';
								//print_r($prod_variation);
							//echo'</pre>';
						?>
						</td>
					
						<td><?php echo $prod_variation['variation_description']; ?>
                        <!--<a href="http://natural-mechanism.flywheelsites.com/cart/?add-to-cart=5924&variation_id=5957&attribute_product-configuration=RCC-MK754ACM" class="button">Add to Cart</a>-->
                        </td>
						
					</tr>
				<?php endforeach;?>
				</tbody>
			</table>
<?php	
		}	
	}
// End product TAB functionality for Documentation


/************************** End Product Details Page functionality **************************/

add_filter( 'woocommerce_product_subcategories_hide_empty', '__return_false' );


// Add Phone Number in Mobile after menu
add_filter( 'wp_nav_menu_items','add_phone_mobile_menu', 10, 2 );
function add_phone_mobile_menu( $items, $args ) {
    
	$items .= '<div class="fl-module fl-module-rich-text fl-node-5cefb67bbd005 phone-top on-mobile" data-node="5cefb67bbd005">
		<div class="fl-module-content fl-node-content">
			<div class="fl-rich-text">
		<p><a href="tel:+800-346-1956">800-346-1956</a></p>
	</div>
		</div>
	</div>';

	return $items;
}

add_action( 'woocommerce_after_single_product_summary', 'bbloomer_single_product_ID' );
function bbloomer_single_product_ID() {
 
	//if ( is_single( '787' ) ) { ?>
		<script type="text/javascript">
			jQuery(function(){
			    jQuery('#select').click(function(){
			        jQuery('#sel-option').toggle(); 
			        //jQuery('#sel-option').hide();        
			    });
			})
				
			if (typeof jQuery("#featured_cont").html() === "undefined") {
				//Don't do any thing.
			}else{
				var str = jQuery("#featured_cont").html();
				var regex = /<br\s*[\/]?>/gi;
				jQuery("#featured_cont").html(str.replace(regex, "<span>"));
			}

			
			if (typeof jQuery("#specification_tab").html() === "undefined") {
				//Don't do any thing.
			}else{
				var str1 = jQuery("#specification_tab").html();
				var regex1 = /<br\s*[\/]?>/gi;
				jQuery("#specification_tab").html(str1.replace(regex1, "<span>"));				
			}
			
		</script>
	<?php //}
}

// change search order (Products,posts,after that page content)
add_filter( 'posts_orderby', 'order_search_by_posttype', 10, 2 );
function order_search_by_posttype( $orderby, $wp_query ){
    if( ! $wp_query->is_admin && $wp_query->is_search ) :
        global $wpdb;
        $orderby =
            "
            CASE WHEN {$wpdb->prefix}posts.post_type = 'product' THEN '1' 
                 WHEN {$wpdb->prefix}posts.post_type = 'post' THEN '2' 
            ELSE {$wpdb->prefix}posts.post_type END ASC, 
            {$wpdb->prefix}posts.post_title ASC";
    endif;
    return $orderby;
}

/* Put a unique ID on Gravity Form (single form ID) entries.
----------------------------------------------------------------------------------------*/
add_filter("gform_field_value_uuid", "get_unique");
function get_unique(){
    $prefix = "202008-"; // update the prefix here
    do {
        $unique = mt_rand();
        $unique = substr($unique, 0, 4);
        $unique = $prefix . $unique;
    } while (!check_unique($unique));
    return $unique;
}
function check_unique($unique) {
    global $wpdb;
    $table = $wpdb->prefix . 'rg_lead_detail';
    $form_id = 3; // update to the form ID your unique id field belongs to
    $field_id = 51; // update to the field ID your unique id is being prepopulated in
    $result = $wpdb->get_var("SELECT value FROM $table WHERE form_id = '$form_id' AND field_number = '$field_id' AND value = '$unique'");
    if(empty($result))
        return true;
    return false;
}

add_filter( 'gform_countries', 'remove_country' );
function remove_country( $countries ){
    return array( 'United States','Canada' );
}

/**
 * Change number or products per row to 3
 */
add_filter('loop_shop_columns', 'loop_columns', 999);
if (!function_exists('loop_columns')) {
	function loop_columns() {
		return 4; // 3 products per row
	}
}

// Display products per page
add_filter( 'loop_shop_per_page', 'bbloomer_redefine_products_per_page', 9999 );
function bbloomer_redefine_products_per_page( $per_page ) {
  $per_page = 24;
  return $per_page;
}

add_filter( 'woocommerce_return_to_shop_redirect', 'st_woocommerce_shop_url' );
/**
 * Redirect WooCommerce Shop URL
 */
function st_woocommerce_shop_url(){
	return site_url() . '/product-category/store/';
}

add_filter( 'gettext', 'change_woocommerce_return_to_shop_text', 20, 3 );

function change_woocommerce_return_to_shop_text( $translated_text, $text, $domain ) {

        switch ( $translated_text ) {

            case 'Return to shop' :

                $translated_text = __( 'View Store', 'woocommerce' );
                break;

        }

    return $translated_text;
}

/**
* @snippet Move & Change Number of Cross-Sells @ WooCommerce Cart
* @how-to Get CustomizeWoo.com FREE
* @sourcecode https://businessbloomer.com/?p=20449
* @author Rodolfo Melogli
* @testedwith WooCommerce 2.6.2
*/
 
 
// ---------------------------------------------
// Remove Cross Sells From Default Position 
 
remove_action( 'woocommerce_cart_collaterals', 'woocommerce_cross_sell_display' );
 
 
// ---------------------------------------------
// Add them back UNDER the Cart Table
 
add_action( 'woocommerce_after_cart', 'woocommerce_cross_sell_display' );
 
 
// ---------------------------------------------
// Display Cross Sells on 3 columns instead of default 4
 
add_filter( 'woocommerce_cross_sells_columns', 'bbloomer_change_cross_sells_columns' );
 
function bbloomer_change_cross_sells_columns( $columns ) {
return 4;
}
 
 
// ---------------------------------------------
// Display Only 3 Cross Sells instead of default 4
 
add_filter( 'woocommerce_cross_sells_total', 'bbloomer_change_cross_sells_product_no' );
  
function bbloomer_change_cross_sells_product_no( $columns ) {
	return 50;
}