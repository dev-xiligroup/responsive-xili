<?php
/**
 * ***** Functions to improve xili-language *****
 * ** selection for twentyten-xili child of twentyten and 2011 and twentyeleven and responsive **
 */
 

 
 

/**
 * ***** BreadCrump ******
 * @since 20101111
 *
 * can be adapted with two end params
 */
function xiliml_adjacent_join_filter($join, $in_same_cat, $excluded_categories) {
	global $post, $wpdb;
	$curlang = xiliml_get_lang_object_of_post( $post->ID );
	// in join p is $wpdb->posts AS p in get_adjacent_post of lin_template.php
	if ($curlang) { // only when language is defined !
		$join .= " LEFT JOIN $wpdb->term_relationships as xtr ON (p.ID = xtr.object_id) LEFT JOIN $wpdb->term_taxonomy as xtt ON (xtr.term_taxonomy_id = xtt.term_taxonomy_id) ";
	}	
return $join;
}

function xiliml_adjacent_where_filter($where, $in_same_cat, $excluded_categories) {
	global $post;
	$curlang = xiliml_get_lang_object_of_post( $post->ID );
	if ( $curlang ) {
		$wherereqtag = $curlang->term_id; 
		$where .= " AND xtt.taxonomy = '".TAXONAME."' ";
		$where .= " AND xtt.term_id = $wherereqtag "; 
	}
	return $where;
}

if ( class_exists('xili_language') ) {
	
	add_filter('get_next_post_join','xiliml_adjacent_join_filter',10,3);
	add_filter('get_previous_post_join','xiliml_adjacent_join_filter',10,3);
	
	add_filter('get_next_post_where','xiliml_adjacent_where_filter',10,3);
	add_filter('get_previous_post_where','xiliml_adjacent_where_filter',10,3);
	
}






/*special flags in list*/
function xiliml_infunc_the_other_posts( $post_ID, $before = "Read this post in", $separator = ", ", $type = "display") {
			global $xili_language;
			$outputarr = array();
			
			$listlanguages = $xili_language->get_listlanguages();
			//$listlanguages = get_terms(TAXONAME, array('hide_empty' => false));
			$post_lang = get_cur_language($post_ID); // to be used in multilingual loop since 1.1
			//$post_lang = $langpost['lang']; //print_r($langpost);
			$xili_theme_options = xili_child_get_theme_options() ; // see below
		    
			$show_flag =  ( isset ( $xili_theme_options['no_flags'] ) && $xili_theme_options['no_flags'] == 'hidden_flags' ) ? false : true ;
			if ( $separator == ", " && $show_flag ) $separator ='';
			foreach ($listlanguages as $language) {
				$otherpost = get_post_meta($post_ID, 'lang-'.$language->slug, true);
				
				if ($type == "display") { 
					if ('' != $otherpost && $language->slug != $post_lang ) {
						$flag = ( $show_flag ) ? ' <img src="'.get_bloginfo('stylesheet_directory').'/images/flags/'.$language->slug.'.png" alt="" />' : '';
						$text = ( $show_flag ) ? '' : __($language->description,the_theme_domain()) ;
						$outputarr[] = '<a href="'.get_permalink($otherpost).'" >'. $text . $flag . '</a>';
						
					}
				} elseif ($type == "array") { // here don't exclude cur lang
					if ('' != $otherpost)
						$outputarr[$language->slug] = $otherpost;
				}
			}
			
			if ($type == "display") {
				$output = "";
				if (!empty($outputarr))
					$output =  (($before !="") ? __( $before, the_theme_domain())." " : "" ).implode ($separator, $outputarr);
				if ('' != $output) { echo $output;}	
			} elseif ($type == "array") {
				if (!empty($outputarr)) {
					$outputarr[$post_ID] = $post_lang; 
					// add a key with curid to give his lang (empty if undefined)
					return $outputarr;
				} else {
					return false;	
				}
			}	
						
}
add_filter('xiliml_the_other_posts','xiliml_infunc_the_other_posts',10,4); // 1.1 090917

function my_xiliml_cat_language ($content, $category = null) {
		//if (has_filter('xiliml_cat_language')) return apply_filters('xiliml_cat_language',$content, $category,$this->curlang);
		/* default */ 
	            /*set by locale of wpsite*/
	      /*these rules can be changed by using */
	    if (!is_admin()) : /*to detect admin UI*/
	      	$new_cat_name =  __($category->name,the_theme_domain()); /*visible ??? in dashboard ???*/
	      	//if ($new_cat_name != $content) : 
	      		//$new_cat_name .= " (". $content .") ";
	      	//endif;
	      		 		/* due to default if no translation*/
	    else :
	    	$new_cat_name =  $content;
	    endif; 
	    return $new_cat_name;
	 } 
// add_filter('xiliml_cat_language','my_xiliml_cat_language',2,3);

/**
 * special flags in list of available languages and RSS
 * @updated 1.8.1 - 101026 - 1.8.9.1 
 * @updated 2.1.1 - 110611-13 - special language permalinks
 */
function xiliml_infunc_language_list($before = '<li>', $after ='</li>', $option, $echo = true, $hidden = false) {
	global $post, $xili_language; 
	
	$lang_perma = $xili_language->lang_perma; // since 2.1.1
	$before_class = false ;
	if ( substr($before,-2) == '.>' ) { // tips to add dynamic class in before
		$before_class = true ;
		$before = str_replace('.>','>',$before);
	}
	$listlanguages = $xili_language->get_listlanguages();
	$a = ''; // 1.6.1
	
	if ($option == 'typeone') {
		/* the rules : don't display the current lang if set and add link of category if is_category()*/
		if ( $lang_perma ) {	
			if (is_category()) {  
				remove_filter('term_link', 'insert_lang_4cat') ;
				$catcur = xiliml_get_category_link();
				add_filter( 'term_link', 'insert_lang_4cat', 10, 3 );
				$currenturl = $catcur; 
			} else {
			 	$currenturl = get_bloginfo('url').'/%lang%/';
			}
		} else {	
			if (is_category()) {  
				$catcur = xiliml_get_category_link();
				$permalink = get_option('permalink_structure'); /* 1.6.0 */
				$sep = ('' == $permalink) ? "&amp;" : "?" ;
				$currenturl = $catcur.$sep;
			} else {
		 		$currenturl = get_bloginfo('url').'/?';
			}
		}	
		foreach ($listlanguages as $language) {
			$display = ( $hidden && ( $xili_language->xili_settings['lang_features'][$language->slug]['hidden'] == 'hidden' ) ) ? false : true ;
			if ($language->slug != the_curlang()   && $display ) {
				$beforee = ( $before_class && $before == '<li>' ) ? '<li class="lang-'.$language->slug.'" >': $before;
				$class = ' class="lang-'.$language->slug.'"';
				
				$link = ( $lang_perma ) ? str_replace ( '%lang%', $language->slug, $currenturl ) : $currenturl.QUETAG."=".$language->slug ;
				
				$a .= $beforee .'<a '.$class.' href="'.$link.'" title="'.__('Posts selected',the_theme_domain()).' '.__('in '.$language->description, the_theme_domain()).'" >'. __($language->description,the_theme_domain()) .'</a>'.$after;
			}
		}
		
	} elseif ($option == 'typeonenew') {  // 2.1.0
			/* the rules : don't display the current lang if set and add link of category if is_category() but display linked singular */
		if ( $lang_perma ) {	
			if (is_category()) {  
				remove_filter('term_link', 'insert_lang_4cat') ;
				$catcur = xiliml_get_category_link();
				add_filter( 'term_link', 'insert_lang_4cat', 10, 3 );
				$currenturl = $catcur; 
			} else {
		 		$currenturl = get_bloginfo('url').'/%lang%/';
			}
		} else {	
			if (is_category()) {  
				$catcur = xiliml_get_category_link();
				$permalink = get_option('permalink_structure'); /* 1.6.0 */
				$sep = ('' == $permalink) ? "&amp;" : "?" ;
				$currenturl = $catcur.$sep;
			} else {
	 			$currenturl = get_bloginfo('url').'/?';
			}
		}
		foreach ($listlanguages as $language) {
			$display = ( $hidden && ( $xili_language->xili_settings['lang_features'][$language->slug]['hidden'] == 'hidden' ) ) ? false : true ;
			if ($language->slug != the_curlang()   && $display ) {
				$beforee = ( $before_class && $before == '<li>' ) ? '<li class="lang-'.$language->slug.'" >': $before;
				$class = ' class="lang-'.$language->slug.'"';
				
				if ( ( is_single() || is_page() ) && !is_front_page() ) {	
					$link = $xili_language->link_of_linked_post ( $post->ID, $language->slug ) ;
					$title = sprintf (__('Current post in %s',the_theme_domain()), __($language->description,the_theme_domain()) ) ;
				} else {
					$link = ( $lang_perma ) ? str_replace ( '%lang%', $language->slug, $currenturl ) : $currenturl.QUETAG."=".$language->slug ;
					$title = sprintf (__('Posts selected in %s',the_theme_domain()), __($language->description, the_theme_domain() ) ) ;
				}
		
				$a .= $beforee .'<a '.$class.' href="'.$link.'" title="'.$title.'" >'. __($language->description, the_theme_domain() ) .'</a>' . $after;
			}
		}
		
	} elseif ( $option == 'siderss' ) { // with flag as image 
	
		$rss = 'rss'; // (feed|rdf|rss|rss2|atom) 
		if ( $lang_perma ) {	
			if (is_category()) {  
				remove_filter('term_link', 'insert_lang_4cat') ;
				$catcur = xiliml_get_category_link();
				add_filter( 'term_link', 'insert_lang_4cat', 10, 3 );
				$currenturl = $catcur;
				$currentrss = $catcur."feed/".$rss."/"; 
			} else {
		 		$currenturl = get_bloginfo('url').'/%lang%/';
		 		$currentrss = get_bloginfo('url').'/%lang%/feed/'.$rss.'/';
			}
		} else {
			if (is_category()) {  
				$catcururl = xiliml_get_category_link();
				$currenturl = $catcururl.'&amp;';
				$cat_ID = $wp_query->query_vars['cat'];
				$currentrss = get_bloginfo('siteurl').'?feed='.$rss.'&amp;cat='.$cat_ID; 
			} else { // home
		 		$currenturl = get_bloginfo('url').'/?';
		 		$currentrss = get_bloginfo('url').'/?feed='.$rss;
			}	
		}
		
		foreach ($listlanguages as $language) {
			$display = ( $hidden && ( $xili_language->xili_settings['lang_features'][$language->slug]['hidden'] == 'hidden' ) ) ? false : true ;
			if ( $display ) {
				if ($before=='<li>') {
					if (the_curlang() == $language->slug) { 
						$beforee = '<li class="current-cat" >';
					} else {
						$beforee ='<li>';
					}
				}
				$link = ( $lang_perma ) ? str_replace ( '%lang%', $language->slug, $currenturl ) : $currenturl.QUETAG."=".$language->slug ;
				$linkrss = ( $lang_perma ) ? str_replace ( '%lang%', $language->slug, $currentrss ) : $currentrss.QUETAG."=".$language->slug ; 
				$a .= $beforee .'<a href="'.$link.'" title="'.__('Posts selected', the_theme_domain()).' '.__('in '.$language->description, the_theme_domain() ).'" ><img src="'.get_bloginfo('stylesheet_directory').'/images/flags/'.$language->slug.'.png" alt="" />'. __('in '.$language->description, the_theme_domain()) .'</a> <a href="'.$linkrss.'" ><img src="'.get_bloginfo('stylesheet_directory').'/images/rss.png" alt="rss" /></a>' . $after;
			}
		
		}
		
		if (is_category()) {
			if ( $lang_perma ) {
				remove_filter('term_link', 'insert_lang_4cat') ;
				$currenturl = str_replace ( '/%lang%', '', xiliml_get_category_link() );
				$currentrss = str_replace ( '/%lang%', '', xiliml_get_category_link() )."feed/".$rss."/";
				add_filter( 'term_link', 'insert_lang_4cat', 10, 3 );
			} else {
				$currenturl = xiliml_get_category_link();
				$currentrss = xiliml_get_category_link().'?feed='.$rss;
			}
			$a .= $before.'<a href="'.$currenturl.'" title="'.__('Posts of current category in all languages', the_theme_domain()).'" ><img src="'.get_bloginfo('stylesheet_directory').'/images/flags/www.png" alt="" /> '.__('in all languages', the_theme_domain()).'</a> <a href="'.$currentrss.'"><img src="'.get_bloginfo('stylesheet_directory').'/images/rss.png" alt="rss"/></a>'.$after;
		}		
		// end siderss
		
	} elseif ($option == 'navmenu')  {	 /* current list in nav menu 1.6.0 */
			foreach ($listlanguages as $language) { 
				$display = ( $hidden && ( $xili_language->xili_settings['lang_features'][$language->slug]['hidden'] == 'hidden' ) ) ? false : true ;
				if ( $display ) { 
					if ($language->slug != the_curlang() ) {
						$class = " class='menu-item menu-item-type-custom lang-".$language->slug."'";
					} else {
						$class = " class='menu-item menu-item-type-custom lang-".$language->slug." current-lang current-menu-item'";
					}
					$beforee = (substr($before,-1) == '>') ? str_replace('>',' '.$class.' >' , $before ) : $before ;
					
					$currenturl = get_bloginfo('url').'/%lang%/';
					$link = ( $lang_perma ) ? str_replace ( '%lang%', $language->slug, $currenturl ) : $currenturl.QUETAG."=".$language->slug ;
					
					$a .= $beforee .'<a href="'.$link.'" title="'.__('Posts selected', the_theme_domain()).' '.__('in '.$language->description,the_theme_domain()).'" >'. __( $language->description, the_theme_domain() ) . '</a>' . $after;
				}
			}
			
	} elseif ($option == 'navmenu-1')  {	// 2.1.0  and single
		if ( $lang_perma ) {	
			if (is_category()) {  
				remove_filter('term_link', 'insert_lang_4cat') ;
				$catcur = xiliml_get_category_link();
				add_filter( 'term_link', 'insert_lang_4cat', 10, 3 );
				$currenturl = $catcur; 
			} else {
		 		$currenturl = get_bloginfo('url').'/%lang%/';
			}
		} else {	
			if (is_category()) {  
				$catcur = xiliml_get_category_link();
				$permalink = get_option('permalink_structure'); /* 1.6.0 */
				$sep = ('' == $permalink) ? "&amp;" : "?" ;
				$currenturl = $catcur.$sep;
			} else {
	 			$currenturl = get_bloginfo('url').'/?';
			}
		}
			
			foreach ($listlanguages as $language) { 
				$display = ( $hidden && ( $xili_language->xili_settings['lang_features'][$language->slug]['hidden'] == 'hidden' ) ) ? false : true ;
				if ( $display ) { 
					
					if ($language->slug != the_curlang() ) {
						$class = " class='menu-item menu-item-type-custom lang-".$language->slug."'";
					} else {
						$class = " class='menu-item menu-item-type-custom lang-".$language->slug." current-lang current-menu-item'";
					}
					
					if ( ( is_single() || is_page() ) && !is_front_page() ) {	
						$link = $xili_language->link_of_linked_post ( $post->ID, $language->slug ) ;
						$title = sprintf (__('Current post in %s',the_theme_domain()), __($language->description,the_theme_domain()) ) ;
					} else {
						$link = ( $lang_perma ) ? str_replace ( '%lang%', $language->slug, $currenturl ) : $currenturl.QUETAG."=".$language->slug ;
						$title = sprintf ( __('Posts selected in %s',the_theme_domain()), __($language->description,the_theme_domain()) ) ;
					}
					
					$beforee = (substr($before,-1) == '>') ? str_replace('>',' '.$class.' >' , $before ) : $before ;
					$a .= $beforee .'<a href="'.$link.'" title="'.$title.'" >'. __($language->description,the_theme_domain()) .'</a>'.$after;
				}
			}
		
			
		} else {	/* current list only root */
			foreach ($listlanguages as $language) {
				$display = ( $hidden && ( $xili_language->xili_settings['lang_features'][$language->slug]['hidden'] == 'hidden' ) ) ? false : true ;
				$currenturl = ( $lang_perma ) ? get_bloginfo('url').'/%lang%/' : get_bloginfo('url').'/?' ; // fixe 0.9.8
				
				if ( $display ) {
					if ( $language->slug != the_curlang() ) {
						$class = " class='lang-".$language->slug."'";
					} else {
						$class = " class='lang-".$language->slug." current-lang'";
					}
					
					$link = ( $lang_perma ) ? str_replace ( '%lang%', $language->slug, $currenturl ) : $currenturl.QUETAG."=".$language->slug ;
					
					$beforee = ( $before_class && $before == '<li>' ) ? '<li class="lang-'.$language->slug.'" >': $before;
					$a .= $beforee .'<a '.$class.' href="'.$link.'" title="'.__('Posts selected',the_theme_domain()).' '.__('in '.$language->description,the_theme_domain()).'" >'. __( $language->description, the_theme_domain() ) .'</a>' . $after;
				}
			}
		}
		if ($echo) 
				echo $a;
			else
				return $a;
}


//

/** 
 * this part to populate popup menu in widget named language list 
 */
function my_xili_language_list_options ($option = null) {
	global $xili_language;
		$xili_language->langs_list_options = array( array( '', 'default' ), array( 'top', 'Type top' ), array( 'navmenu', 'Menu' ), array( 'navmenu-1', 'Menu #1' ), array( 'typeonenew', 'Type for single' ), array( 'siderss', 'Type RSS' )); 
	
	}

/**
 * this part for language like khmer without set_locale on server
 * to be active, the item  Server Entities Charset: must be set to "no_locale" for the target language (here km_kh)
 *
 */

/* inspired part copied from Nathan Author URI: http://www.sbbic.org/ (xili team don't read khmer ;-) ) */
function xili_translate_date ( $slug, $text ) {
	switch ($slug) {
		
		case 'hu_hu': // examples of texts kept in WP hu_HU.po kit - not able to verify - just for demo Hungarian - Magyar
		// Date Format: F j, Y is translated in Y. F j.  l
		// here with no_locale - not needed on internal 10.6.8 server when set UTF-8 on Charset or MAMP
			$text = str_replace('January', 'január', $text);
			$text = str_replace('February', 'február', $text);
			$text = str_replace('March', 'március', $text);
			$text = str_replace('April', 'április', $text);
			$text = str_replace('May', 'május', $text);
			$text = str_replace('June', 'június', $text);
			$text = str_replace('July', 'július', $text);
			$text = str_replace('August', 'augusztus', $text);
			$text = str_replace('September', 'szeptember', $text);
			$text = str_replace('October', 'október', $text); 
			$text = str_replace('November', 'november', $text); 
			$text = str_replace('December', 'december', $text);
			$text = str_replace('Jan', 'jan', $text);
			$text = str_replace('Feb', 'feb', $text);
			$text = str_replace('Mar', 'márc', $text);
			$text = str_replace('Apr', 'ápr', $text);
			$text = str_replace('May', 'máj', $text);
			$text = str_replace('Jun', 'jún', $text);
			$text = str_replace('Jul', 'júl', $text);
			$text = str_replace('Aug', 'auj', $text);
			$text = str_replace('Sep', 'szept', $text);
			$text = str_replace('Oct', 'okt', $text); 
			$text = str_replace('Nov', 'nov', $text); 
			$text = str_replace('Dec', 'dec', $text);
			
			$text = str_replace('Saturday', 'szombat', $text);
			$text = str_replace('Sunday', 'vasárnap', $text);
			$text = str_replace('Monday', 'hétfő', $text);
			$text = str_replace('Tuesday', 'kedd', $text);
			$text = str_replace('Wednesday', 'szerda', $text);
			$text = str_replace('Thursday', 'csütörtök', $text);
			$text = str_replace('Friday', 'péntek', $text);
			$text = str_replace('Sat', 'Szo', $text);
			$text = str_replace('Sun', 'Vas', $text);
			$text = str_replace('Mon', 'Hét', $text);
			$text = str_replace('Tues', 'Ked', $text);
			$text = str_replace('Tue', 'Ked', $text);
			$text = str_replace('Wed', 'Sze', $text);
			$text = str_replace('Thurs', 'Csü', $text);
			$text = str_replace('Thu', 'Csü', $text);
			$text = str_replace('Fri', 'Pén', $text);
			
			$text = str_replace('am', 'de.', $text); 
			$text = str_replace('pm', 'du.', $text); 
			$text = str_replace('AM', 'DE.', $text); 
			$text = str_replace('PM', 'DU.', $text); 
			
			$text = str_replace('th', '', $text); 
			$text = str_replace('st', '', $text);
			$text = str_replace('rd', '', $text);
		    break;
		
		case 'km_kh':
			$text = str_replace('1', '១', $text);
			$text = str_replace('2', '២', $text);
			$text = str_replace('3', '៣', $text);
			$text = str_replace('4', '៤', $text);
			$text = str_replace('5', '៥', $text);
			$text = str_replace('6', '៦', $text);
			$text = str_replace('7', '៧', $text);
			$text = str_replace('8', '៨', $text);
			$text = str_replace('9', '៩', $text);
			$text = str_replace('0', '៩', $text); 
									
			$text = str_replace('January', 'មករា', $text);
			$text = str_replace('February', 'កុម្ភៈ', $text);
			$text = str_replace('March', 'មីនា', $text);
			$text = str_replace('April', 'មេសា', $text);
			$text = str_replace('May', 'ឧសភា', $text);
			$text = str_replace('June', 'មិថុនា', $text);
			$text = str_replace('July', 'កក្កដា', $text);
			$text = str_replace('August', 'សីហា', $text);
			$text = str_replace('September', 'កញ្ញា', $text);
			$text = str_replace('October', 'តុលា', $text); 
			$text = str_replace('November', 'វិច្ឆិកា', $text); 
			$text = str_replace('December', 'ធ្នូ', $text);
			$text = str_replace('Jan', 'មករា', $text);
			$text = str_replace('Feb', 'កុម្ភៈ', $text);
			$text = str_replace('Mar', 'មីនា', $text);
			$text = str_replace('Apr', 'មេសា', $text);
			$text = str_replace('May', 'ឧសភា', $text);
			$text = str_replace('Jun', 'មិថុនា', $text);
			$text = str_replace('Jul', 'កក្កដា', $text);
			$text = str_replace('Aug', 'កញ្ញា', $text);
			$text = str_replace('Sep', 'កញ្ញា', $text);
			$text = str_replace('Oct', 'តុលា', $text); 
			$text = str_replace('Nov', 'វិច្ឆិកា', $text); 
			$text = str_replace('Dec', 'ធ្នូ', $text);
			
			$text = str_replace('Saturday', 'ថ្ងៃសុក្រ', $text);
			$text = str_replace('Sunday', 'ថ្ងៃអាទិត្យ', $text);
			$text = str_replace('Monday', 'ថ្ងៃចន្ទ', $text);
			$text = str_replace('Tuesday', 'ថ្ងៃអង្គារ', $text);
			$text = str_replace('Wednesday', 'ថ្ងៃពុធ', $text);
			$text = str_replace('Thursday', 'ថ្ងៃព្រហស្បតិ៍', $text);
			$text = str_replace('Friday', 'ថ្ងៃសុក្រ', $text);
			$text = str_replace('Sat', 'ស', $text);
			$text = str_replace('Sun', 'អា', $text);
			$text = str_replace('Mon', 'ច', $text);
			$text = str_replace('Tues', 'អ', $text);
			$text = str_replace('Tue', 'អ', $text);
			$text = str_replace('Wed', 'អ', $text);
			$text = str_replace('Thurs', 'ព្រ', $text);
			$text = str_replace('Thu', 'ព្រ', $text);
			$text = str_replace('Fri', 'សុ', $text);
			
			$text = str_replace('th', '', $text); 
			$text = str_replace('st', '', $text);
			$text = str_replace('rd', '', $text);

		break;
		default:
	
	}
	
	return $text;
}


?>
