<?php

// Exit if accessed directly
if ( !defined('ABSPATH')) exit;


/**
 * The template for displaying search forms in Responsive
 *
 * @package Responsive
 * @subpackage Responsive-xili
 * @since 2012-07-08
 */
$value = ( isset( $_GET['s'] ) ) ? 'value="'. get_search_query() . '"': '' ;
$form = '<form method="get" id="searchform" action="'. home_url( '/' ) .'">
		<input type="text" class="field" name="s" id="s" '. $value .' placeholder="'. esc_attr__('search here &hellip;', 'responsive') .'" />
		<input type="submit" class="submit" name="submit" id="searchsubmit" value="'. esc_attr__('Go', 'responsive') . '" />';

echo apply_filters ( 'get_search_form', $form ) ;	// filter in functions.php of child

echo '</form>';
?>
	