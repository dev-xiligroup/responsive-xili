<?php
/**
 * responsive-xili
 * Version - see style.css
 * 2012-07-08 - first public release
 * 2014-02-12 - 1.9.4 - ready for  WP3.8 and XL 2.10+
 *
 */
define('RESPONSIVE_XILI_VER', '1.9.4'); // as mentioned in style.css

/**
 *  responsive for xili functions -
 *
 */
function parent_xilidev_setup () {

	$theme_domain = 'responsive';

	if (is_admin())
		load_textdomain( $theme_domain, get_stylesheet_directory() ."/langs/local-" . WPLANG . ".mo" ); // admin msgid terms are also in local of child !

	load_theme_textdomain( $theme_domain, STYLESHEETPATH . '/langs' ); // now use .mo of child

	$xl_required_version = false;

	if ( class_exists('xili_language') ) { // if temporary disabled

		$xl_required_version = version_compare ( XILILANGUAGE_VER, '2.9.99', '>' );

		global $xili_language;

		$xili_language_includes_folder = $xili_language->plugin_path .'xili-includes';

		$xili_functionsfolder = get_stylesheet_directory() . '/functions-xili' ;

		if ( file_exists( $xili_functionsfolder . '/multilingual-classes.php') ) {
			require_once ( $xili_functionsfolder . '/multilingual-classes.php' ); // xili-options

		} elseif ( file_exists( $xili_language_includes_folder . '/theme-multilingual-classes.php') ) {
			require_once ( $xili_language_includes_folder . '/theme-multilingual-classes.php' ); // ref xili-options based in plugin
		}

		if ( file_exists( $xili_functionsfolder . '/multilingual-functions.php') ) {
			require_once ( $xili_functionsfolder . '/multilingual-functions.php' );
		}

		if ( file_exists( $xili_functionsfolder . '/multilingual-permalinks.php') && $xili_language->is_permalink ) {
			require_once ( $xili_functionsfolder . '/multilingual-permalinks.php' ); // require subscribing premium services
		}


	//register_nav_menu ( 'toto', 'essai' );

		global $xili_language_theme_options ; // used on both side
	// Args dedicaced to this theme named TwentyTen
		$xili_args = array (
	 		'customize_clone_widget_containers' => false, // comment or set to true to clone widget containers
	 		'settings_name' => 'xili_responsive_theme_options', // name of array saved in options table
	 		'theme_name' => 'Responsive',
	 		'theme_domain' => $theme_domain,
	 		'child_version' => RESPONSIVE_XILI_VER
		);

		if ( is_admin() ) {

		// Admin args dedicaced to this theme

			$xili_admin_args = array_merge ( $xili_args, array (
		 		'customize_adds' => true, // add settings in customize page
		 		'customize_addmenu' => false, // done by 2013
		 		'capability' => 'edit_theme_options'
			) );
			if ( class_exists ( 'xili_language_theme_options_admin' )  ) {
				$xili_language_theme_options = new xili_language_theme_options_admin ( $xili_admin_args );
				$class_ok = true ;
			} else {
				$class_ok = false ;
			}


		} else { // visitors side - frontend

			if ( class_exists ( 'xili_language_theme_options' )  ) {
				$xili_language_theme_options = new xili_language_theme_options ( $xili_args );
				$class_ok = true ;
			} else {
				$class_ok = false ;
			}
		}
	}

	// errors and installation informations

	if ( ! class_exists( 'xili_language' ) ) {

		$msg = '
		<div class="error">
			<p>' . sprintf ( __('The %s child theme requires xili-language plugin installed and activated', $theme_domain ), get_option( 'current_theme' ) ).'</p>
		</div>';

	} elseif ( $class_ok === false )  {

		$msg = '
		<div class="error">
			<p>' . sprintf ( __('The %s child theme requires <em>xili_language_theme_options</em> class to set multilingual features.', $theme_domain ), get_option( 'current_theme' ) ).'</p>
		</div>';

	} elseif ( $xl_required_version )  {

		$msg = '
		<div class="updated">
			<p>' . sprintf ( __('The %s child theme was successfully activated with xili-language.', $theme_domain ), get_option( 'current_theme' ) ).'</p>
		</div>';

	} else {

		$msg = '
		<div class="error">
			<p>' . sprintf ( __('The %s child theme requires xili-language version 2.8.8+', $theme_domain ), get_option( 'current_theme' ) ).'</p>
		</div>';
	}
	// after activation and in themes list
	if ( isset( $_GET['activated'] ) || ( ! isset( $_GET['activated'] ) && ( ! $xl_required_version || ! $class_ok ) ) )
		add_action( 'admin_notices', $c = create_function( '', 'echo "' . addcslashes( $msg, '"' ) . '";' ) );

	// end errors...
}

/* actions and filters*/
add_action( 'after_setup_theme', 'parent_xilidev_setup', 11 );
add_action( 'wp_head', 'special_head' );

define('XILI_CATS_ALL','0');

/**
 * define when search form is completed by radio buttons to sub-select language when searching
 *
 */
function special_head() {

	if ( is_search() ) {
	 	add_filter('get_search_form', 'my_langs_in_search_form_responsive', 10, 1); // responsive bellow
	}

	$xili_theme_options = get_theme_xili_options() ;

	if ( !isset( $xili_theme_options['no_flags'] ) || $xili_theme_options['no_flags'] != '1' ) {
		xili_parent_flags_style();
	}
}

/**
 * introduce filter to translate category->name when called by old function get_category_parents
 *
 */
function xili_responsive_breadcrumb_lists () {

 	add_filter ('get_category', 'xili_responsive_category',10 ,2);
 	responsive_breadcrumb_lists();
 	remove_filter ('get_category', 'xili_responsive_category');

}
function xili_responsive_category ($term, $taxonomy) {
	if ( $taxonomy == 'category' ) {
		$term->name = __($term->name, 'responsive');
		$term->description = __($term->description, 'responsive');
	}
	return $term;
}

// now here for page and single
function responsive_post_meta_data() {
	printf( __( '<span class="%1$s">Posted on </span>%2$s<span class="%3$s"> by </span>%4$s', 'responsive' ),
	'meta-prep meta-prep-author posted',
	sprintf( '<a href="%1$s" title="%2$s" rel="bookmark"><span class="timestamp">%3$s</span></a>',
		get_permalink(),
		esc_attr( get_the_time() ),
		get_the_date()
	),
	'byline',
	sprintf( '<span class="author vcard"><a class="url fn n" href="%1$s" title="%2$s">%3$s</a></span>',
		get_author_posts_url( get_the_author_meta( 'ID' ) ),
		sprintf( esc_attr__( 'View all posts by %s', 'responsive' ), get_the_author() ),
		get_the_author()
	    )
	);

	if ( xiliml_new_list() ) {
		echo ' <span class="mdash">&mdash;</span> ';
		xiliml_the_other_posts();
	}
}



/**
 * add search other languages in form - see functions.php when fired
 *
 */
function my_langs_in_search_form_responsive ( $the_form ) {
	$form = $the_form ;
	if ( class_exists('xili_language') )
		$form .= '<div class="xili-s-radio">' . xiliml_langinsearchform ( $before='<span class="radio-lang">', $after='</span>', false) . '</div>';
	return $form ;
}

/**
 * dynamic style for flag depending current list
 *
 * @since 1.0.2 - add #access
 *
 */
function xili_parent_flags_style () {
	if ( class_exists('xili_language') ) {
		global $xili_language ;
		$language_xili_settings = get_option('xili_language_settings');
		if ( !is_array( $language_xili_settings['langs_ids_array'] ) ) {
			$xili_language->get_lang_slug_ids(); // update array when no lang_perma 110830 thanks to Pierre
			update_option( 'xili_language_settings', $xili_language->xili_settings );
			$language_xili_settings = get_option('xili_language_settings');
		}

		$language_slugs_list =  array_keys ( $language_xili_settings['langs_ids_array'] ) ;

		?>
		<!-- style for ©xili-language menu of responsive child -->
		<style type="text/css">

		<?php

		$path = get_stylesheet_directory_uri();

		$ulmenus = array();
		foreach ( $language_slugs_list as $slug ) { // only top-menu (.top-menu (1.9.1)
			echo "ul.top-menu li.lang-{$slug} {display:inline-block; background: transparent url('{$path}/images/flags/{$slug}.png') no-repeat center 04px; }\n";
			echo "ul.top-menu li.lang-{$slug}:hover > a {background:#efefef url('{$path}/images/flags/{$slug}.png') no-repeat center 04px !important;}\n";
			$ulmenus[] = "ul.top-menu li.lang-{$slug} a";
		}
			echo implode (', ', $ulmenus ) . " { display:inline-block; text-indent:-9000px !important; width:6px; border-left:none;}\n";
			echo "ul.top-menu li.menu-separator {display:none;}"; // to hidden separator = visibility:hidden;
			echo "ul.top-menu li.current-lang {display:none;}"; // to hidden separator
		?>
		</style>
		<?php

	}
}

/**
 *
 *
 */
function single_lang_dir($post_id) {
	$langdir = ((function_exists('get_cur_post_lang_dir')) ? get_cur_post_lang_dir($post_id) : array());
	if ( isset($langdir['direction']) ) return $langdir['direction'];
}

/**
 * to avoid display of old xiliml_the_other_posts in singular - only if forced
 * @since 1.1
 */
function xiliml_new_list() {
	if ( class_exists('xili_language') ) {
		global $xili_language;

		$xili_theme_options = get_theme_xili_options() ; // see below

		if ( isset ( $xili_theme_options['linked_posts'] ) &&  $xili_theme_options['linked_posts'] == 'show_linked' ) {
			if (is_page() && is_front_page() ) {
				return false;
			} else {
				return true;
			}
		} else {
			return false ;
		}
	}
	return true ;
}



?>