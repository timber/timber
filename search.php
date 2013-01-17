<?php
/**
 * Search results page
 * 
 * Methods for PostMaster and WPHelper can be found in the /functions sub-directory
 *
 * @package 	WordPress
 * @subpackage 	Timber
 * @since 		Timber 0.1
 */
  

	$templates = array('archive.html', 'index.html');

	$data['title'] = 'Search results for '. get_search_query();
	
	$data['wp_nav_menu'] = wp_nav_menu( array( 'container_class' => 'menu-header', 'theme_location' => 'primary' , 'echo' => false) );

	if ( have_posts() ){
		$data['posts'] = PostMaster::loop_to_array();
		render_twig($templates, $data);
	} else {

	}
