<?php

	/**
	 * Starkers_Utilities
	 *
	 * Starkers Utilities Class v.1.1
	 *
	 * @package 	WordPress
	 * @subpackage 	Starkers
	 * @since 		Starkers 4.0
	 *
	 * We've included a number of helper functions that we use in every theme we produce.
	 * The main one that is used in Starkers is Starkers_Utilities::add_slug_to_body_class(), this will add the page or post slug to the body class
	 *
	 */
	 
	 class Starkers_Utilities {

    	/**
    	 * Print a pre formatted array to the browser - very useful for debugging
    	 *
    	 * @param 	array
    	 * @return 	void
    	 * @author 	Keir Whitaker
    	 **/
    	public static function print_a( $a ) {
    		print( '<pre>' );
    		print_r( $a );
    		print( '</pre>' );
    	}

    	/**
    	 * Simple wrapper for native get_template_part()
    	 * Allows you to pass in an array of parts and output them in your theme
    	 * e.g. <?php get_template_parts(array('part-1', 'part-2')); ?>
    	 *
    	 * @param 	array 
    	 * @return 	void
    	 * @author 	Keir Whitaker
    	 **/
    	public static function get_template_parts( $parts = array() ) {
    		foreach( $parts as $part ) {
    			get_template_part( $part );
    		};
    	}

    	/**
    	 * Pass in a path and get back the page ID
    	 * e.g. Starkers_Utilities::get_page_id_from_path('about/terms-and-conditions');
    	 *
    	 * @param 	string 
    	 * @return 	integer
    	 * @author 	Keir Whitaker
    	 **/
    	public static function get_page_id_from_path( $path ) {
    		$page = get_page_by_path( $path );
    		if( $page ) {
    			return $page->ID;
    		} else {
    			return null;
    		};
    	}

    	/**
    	 * Append page slugs to the body class
    	 * NB: Requires init via add_filter('body_class', 'add_slug_to_body_class');
    	 *
    	 * @param 	array 
    	 * @return 	array
    	 * @author 	Keir Whitaker
    	 */
    	public static function add_slug_to_body_class( $classes ) {
    		global $post;
	   
    		if( is_home() ) {			
    			$key = array_search( 'blog', $classes );
    			if($key > -1) {
    				unset( $classes[$key] );
    			};
    		} elseif( is_page() ) {
    			$classes[] = sanitize_html_class( $post->post_name );
    		} elseif(is_singular()) {
    			$classes[] = sanitize_html_class( $post->post_name );
    		};

    		return $classes;
    	}
	
    	/**
    	 * Get the category id from a category name
    	 *
    	 * @param 	string 
    	 * @return 	string
    	 * @author 	Keir Whitaker
    	 */
    	public static function get_category_id( $cat_name ){
    		$term = get_term_by( 'name', $cat_name, 'category' );
    		return $term->term_id;
    	}
	
    }