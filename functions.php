<?php
/**
 * Frag functions and definitions.
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Frag
 */

	/*
	 * Switch default core markup for search form, comment form, and comments
	 * to output valid HTML5.
	 */
	add_theme_support( 'html5', array(
		'search-form',
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
	) );

	/*
	 * Enable support for Post Formats.
	 * See https://developer.wordpress.org/themes/functionality/post-formats/
	 */
	add_theme_support( 'post-formats', array(
		'aside',
		'image',
		'video',
		'quote',
		'link',
	) );


/**
 * Enqueue scripts and styles.
 */
function frag_scripts() {
	wp_enqueue_style( 'frag-style', get_stylesheet_uri() );

	wp_enqueue_script( 'frag-navigation', get_template_directory_uri() . '/js/navigation.js', array(), '20120206', true );

	wp_enqueue_script( 'frag-skip-link-focus-fix', get_template_directory_uri() . '/js/skip-link-focus-fix.js', array(), '20130115', true );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'frag_scripts' );

// ----------------------- Dave Bestpratice WP ------------------------ //

// remove unnecessary items wordpress
remove_action( 'wp_head', 'wp_generator');
remove_action( 'wp_head', 'feed_links_extra', 3 ); // Display the links to the extra feeds such as category feeds
remove_action( 'wp_head', 'feed_links', 2 ); // Display the links to the general feeds: Post and Comment Feed
remove_action( 'wp_head', 'rsd_link' ); // Display the link to the Really Simple Discovery service endpoint, EditURI link
remove_action( 'wp_head', 'wlwmanifest_link' ); // Display the link to the Windows Live Writer manifest file.
remove_action( 'wp_head', 'index_rel_link' ); // index link
remove_action( 'wp_head', 'parent_post_rel_link', 10, 0 ); // prev link
remove_action( 'wp_head', 'start_post_rel_link', 10, 0 ); // start link
remove_action( 'wp_head', 'adjacent_posts_rel_link', 10, 0 ); // Display relational links for the posts adjacent to the current post.
remove_action( 'wp_head', 'wp_shortlink_wp_head', 10, 0);

add_filter('the_generator', '__return_false'); //Remove the WordPress version from RSS feeds
add_theme_support( 'automatic-feed-links' );

// Disable support for emoji
remove_action( 'wp_head', 'print_emoji_detection_script', 7 ); 
remove_action( 'wp_print_styles', 'print_emoji_styles' );

add_filter( 'wpcf7_load_js', '__return_false' );
add_filter( 'wpcf7_load_css', '__return_false' );

/* Rimuove la versione dai file CSS e JS */
function remove_cssjs_ver( $src ) {
    if( strpos( $src, '?ver=' ) )
        $src = remove_query_arg( 'ver', $src );
    return $src;
}
add_filter( 'style_loader_src', 'remove_cssjs_ver', 1000 );
add_filter( 'script_loader_src', 'remove_cssjs_ver', 1000 );





/** Modifica body_class() */
function roots_body_class($classes) {
	// Add post/page slug
	if (is_single() || is_page() && !is_front_page()) {
		$classes[] = basename(get_permalink());
	}
	// Remove unnecessary classes
	$home_id_class = 'page-id-' . get_option('page_on_front');
	$remove_classes = array(
		'page-template-default',
		$home_id_class
	);
	$classes = array_diff($classes, $remove_classes);
	return $classes;
}
add_filter('body_class', 'roots_body_class');


/** Elimina inutili chiusure di tags */
function roots_remove_self_closing_tags($input) {
	return str_replace(' />', '>', $input);
}
add_filter('get_avatar',          'roots_remove_self_closing_tags'); // <img />
add_filter('comment_id_fields',   'roots_remove_self_closing_tags'); // <input />
add_filter('post_thumbnail_html', 'roots_remove_self_closing_tags'); // <img />


/** Fix for empty search queries redirecting to home page - da ROOTS 
 * @link http://wordpress.org/support/topic/blank-search-sends-you-to-the-homepage#post-1772565
 * @link http://core.trac.wordpress.org/ticket/11330
 */
function roots_request_filter($query_vars) {
  if (isset($_GET['s']) && empty($_GET['s'])) {
    $query_vars['s'] = ' ';
  }
  return $query_vars;
}
add_filter('request', 'roots_request_filter');


/***** RIMUOVERE PARTI DEL MENU per gli utenti NON admin *****/
function remove_menus()
{
    global $menu;
    global $current_user;
    get_currentuserinfo();

    if($current_user->user_level < 10)
    {
        $restricted = array(__('Links'),
                            __('Comments'),
                            __('Tools'),
        );
        end ($menu);
        while (prev($menu)){
            $value = explode(' ',$menu[key($menu)][0]);
            if(in_array($value[0] != NULL?$value[0]:"" , $restricted)){unset($menu[key($menu)]);}
        }// end while

    }// end if
}
add_action('admin_menu', 'remove_menus');



/***** RIMUOVERE PARTI della ADMIN BAR *****/
function remove_admin_bar_links() {
	global $wp_admin_bar;
	$wp_admin_bar->remove_menu('wp-logo');
	$wp_admin_bar->remove_menu('updates');
	$wp_admin_bar->remove_menu('comments');
	$wp_admin_bar->remove_menu('wpseo-menu');
}

add_action( 'wp_before_admin_bar_render', 'remove_admin_bar_links' );


// Permettere agli EDITOR di vedere la voce MENU
$role_object = get_role( 'editor' );
$role_object->add_cap( 'edit_theme_options' );
function hide_menu() {
 
    // Hide theme selection page
    remove_submenu_page( 'themes.php', 'themes.php' );
 
    // Hide widgets page
    remove_submenu_page( 'themes.php', 'widgets.php' );
 
    // Hide customize page
    global $submenu;
    unset($submenu['themes.php'][6]);
 
}
 
add_action('admin_head', 'hide_menu');



/** RIMOZIONE CLASSI MENU */
function custom_wp_nav_menu($var) { return is_array($var) ? array_intersect($var, array( // List of useful classes to keep 
'current_page_item', 'current_page_parent', 'current_page_ancestor' ) ) : '';} add_filter('nav_menu_css_class', 'custom_wp_nav_menu'); add_filter('nav_menu_item_id', 'custom_wp_nav_menu'); add_filter('page_css_class', 'custom_wp_nav_menu'); 


/***** PAGINA di LOGIN *****/
function custom_login() { 
	echo '<link rel=\'stylesheet\' id=\'newlogin\' href=\''.get_bloginfo('template_directory').'/css-login/csslogin.css\' type=\'text/css\' media=\'all\' /> '; 
}
add_action('login_head', 'custom_login');


/***** lunghezza RIASSUNTO (excerpt) *****/
function modifica_riassunto(){ return 24; }
add_filter('excerpt_length','modifica_riassunto');

/***** aggiungi RIASSUNTO a pagine *****/
function riassunto_pagine() { add_post_type_support( 'page', 'excerpt' ); }
add_action( 'init', 'riassunto_pagine' );

/**** thumbnails ***/ 
// add custom size image
if ( function_exists( 'add_theme_support' ) ) {
	add_theme_support( 'post-thumbnails' );
	//set_post_thumbnail_size( 150, 150, true );
	//add_image_size( 'prodotto-big', 600, 600, false ); /* elemento  big */
	//add_image_size( 'prodotto-thumb', 220, 220, true ); /* miniatura Prodotto */
}
/**** custoom menu ***/ 


if ( function_exists( 'register_nav_menus' ) ) {
	register_nav_menus(
		array(
		'main-menu' => __('Menu primario', ''),   
		'footer-menu' => __( 'Footer Menu' )
		)
	);
}

/** Elementi Custom **/	  

/** Custom post type for designer **/	  


      add_action( 'init', 'designers' );
      function designers() {
        $labels = array(
        'name'        => 'Designers',
        //'name'        => 'Prodotti',
        'singular_name'   => 'designer',
        'add_new'     => 'Aggiungi Designer',
        'add_new_item'    => 'Aggiungi nuovo Designer',
        'edit_item'     => 'Modifica Designer',
        'new_item'      => 'Nuovo Designer',
        'all_items'     => __('Elenco Designers'),
        'view_item'     => __('Visualizza Designer'),
        'search_items'    => 'Cerca Designer',
        'not_found'     =>  'Designer non trovato',
        'not_found_in_trash' => 'Designer non presente nel cestino',
        'parent_item_colon' => '',
        );
      
        $args = array( 
        'labels' => $labels,
        'description'     => 'Lista Designer',
        'public'      => true,
        'publicly_queryable' => true,
        'show_ui'       => true,
        'exclude_from_search' => false,
        'query_var'     => true,
        'rewrite'     => array('slug' => 'designer', 'hierarchical' => true, 'with_front' => false ),
        //'rewrite'     => array( 'hierarchical' => true ),*/
        //'rewrite'       => true,
        'capability_type'   => 'post',
        'has_archive'     => true,
        'hierarchical'    => true,
        //'hierarchical'    => false,
        'menu_position'   => 20,
        'supports'      => array( 'title','editor','thumbnail','excerpt' ),
        //'register_meta_box_cb' => 'admin_init', // Callback function for custom metaboxes
        );
        register_taxonomy('lista', 'designer', array(    
            'hierarchical' => true, 
            'label' => 'Categoria designers',
            'singular_name' => 'Categoria', 
            'name'              => _x( 'Categoria Designer', 'taxonomy general name' ),
            'search_items'      => __( 'Cerca Categorie dei designers' ),
            'all_items'         => __( 'Tutte le categorie dei Designer' ),
            'parent_item'       => __( 'Categoria genitore' ),
            'parent_item_colon' => __( 'Parent Product Category:' ),
            'edit_item'         => __( 'Edit Product Category' ), 
            'update_item'       => __( 'Update Product Category' ),
            'add_new_item'      => __( 'Add New Product Category' ),
            'new_item_name'     => __( 'New Product Category' ),
            'menu_name'         => __( 'Product Categories' ),
            'rewrite'     => array('slug' => 'lista', 'hierarchical' => true, 'with_front' => false ),
            "query_var" => true,
            'show_ui'           => true,
            'show_admin_column' => true,
            ));
          // register_post_type( 'prodotti', $args); 
            register_post_type( 'designer', $args);
          //flush_rewrite_rules();
        }
		

        show_admin_bar( false );

        // filtro custom post type designer
        function add_taxonomy_filters() {
          global $typenow;
         
          // an array of all the taxonomyies you want to display. Use the taxonomy name or slug
          $taxonomies = array('lista');
         
          // must set this to the post type you want the filter(s) displayed on
          if( $typenow == 'designer' ){
         
            foreach ($taxonomies as $tax_slug) {
              $tax_obj = get_taxonomy($tax_slug);
              $tax_name = $tax_obj->labels->name;
              $terms = get_terms($tax_slug);
              if(count($terms) > 0) {
                echo "<select name='$tax_slug' id='$tax_slug' class='postform'>";
                echo "<option value=''>Show All $tax_name</option>";
                foreach ($terms as $term) { 
                  echo '<option value='. $term->slug, $_GET[$tax_slug] == $term->slug ? ' selected="selected"' : '','>' . $term->name .' (' . $term->count .')</option>'; 
                }
                echo "</select>";
              }
            }
          }
        }

        add_action( 'restrict_manage_posts', 'add_taxonomy_filters' );

        /** Custom post type for prodotti **/	  


        add_action( 'init', 'crea_prodotto' );
              function crea_prodotto() {
                $labels = array(
                'name'        => 'Catalogo Frag',
                //'name'        => 'Prodotti',
                'singular_name'   => 'Prodotto',
                'add_new'     => 'Aggiungi Prodotto',
                'add_new_item'    => 'Aggiungi nuovo Prodotto',
                'edit_item'     => 'Modifica Prodotto',
                'new_item'      => 'Nuova Prodotto',
                'all_items'     => __('Elenco Prodotti'),
                'view_item'     => __('Visualizza Prodotti'),
                'search_items'    => 'Cerca Prodotto',
                'not_found'     =>  'Prodotto non trovato',
                'not_found_in_trash' => 'Prodotto non presente nel cestino',
                'parent_item_colon' => '',
                );
              
                $args = array( 
                'labels' => $labels,
                'description'     => 'Catalogo Prodotto',
                'public'      => true,
                'publicly_queryable' => true,
                'show_ui'       => true,
                'exclude_from_search' => false,
                'query_var'     => true,
                'rewrite'     => array('slug' => 'prodotto', 'hierarchical' => true, 'with_front' => false ),
                //'rewrite'     => array( 'hierarchical' => true ),*/
                //'rewrite'       => true,
                'capability_type'   => 'post',
                'has_archive'     => true,
                'hierarchical'    => true,
                //'hierarchical'    => false,
                'menu_position'   => 20,
                'supports'      => array( 'title','editor','thumbnail','excerpt' ),
                //'register_meta_box_cb' => 'admin_init', // Callback function for custom metaboxes
                );
                register_taxonomy('catalogo', 'prodotto', array(    
                    'hierarchical' => true, 
                    'label' => 'Categoria Prodotto',
                    'singular_name' => 'Categoria', 
                    'name'              => _x( 'Categoria Prodotto', 'taxonomy general name' ),
                    'search_items'      => __( 'Cerca Categorie dei prodotti' ),
                    'all_items'         => __( 'Tutte le categorie dei prodotti' ),
                    'parent_item'       => __( 'Categoria genitore' ),
                    'parent_item_colon' => __( 'Parent Product Category:' ),
                    'edit_item'         => __( 'Edit Product Category' ), 
                    'update_item'       => __( 'Update Product Category' ),
                    'add_new_item'      => __( 'Add New Product Category' ),
                    'new_item_name'     => __( 'New Product Category' ),
                    'menu_name'         => __( 'Product Categories' ),
                    'rewrite'     => array('slug' => 'catalogo', 'hierarchical' => true, 'with_front' => false ),
                    "query_var" => true,
                    'show_ui'           => true,
                    'show_admin_column' => true,
                    ));
                  // register_post_type( 'prodotti', $args); 
                    register_post_type( 'prodotto', $args);
                  //flush_rewrite_rules();
                }
        		

        function top_tags() {
                $tags = get_tags();

                if (empty($tags))
                        return;

                $counts = $tag_links = array();
                foreach ( (array) $tags as $tag ) {
                        $counts[$tag->name] = $tag->count;
                        $tag_links[$tag->name] = get_tag_link( $tag->term_id );
                }

                asort($counts);
                $counts = array_reverse( $counts, true );

                $i = 0;
                foreach ( $counts as $tag => $count ) {
                        $i++;
                        $tag_link = esc_url($tag_links[$tag]);
                        $tag = str_replace(' ', '&nbsp;', esc_html( $tag ));
                        if($i < 11){
                                print "<a class='category-link' href=\"$tag_link\">$tag</a>";
          //          print "<li><a href=\"$tag_link\">$tag ($count)</a></li>";
                        }
                }
        }
        ?>